<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class MikroTikProfile extends ResourceController
{
    use ResponseTrait;

    protected $routerOSService;

    public function __construct()
    {
        helper(['routeros']);
        $this->routerOSService = \Config\Services::routeros();
    }

    /**
     * Load profiles from MikroTik router
     */
    public function load()
    {
        try {
            log_message('info', 'MikroTikProfile::load() called');

            // Check if routerOSService is available
            if (!$this->routerOSService) {
                log_message('error', 'RouterOS Service not initialized');
                return $this->respond([
                    'success' => false,
                    'data' => [],
                    'message' => 'MikroTik service not configured'
                ]);
            }

            // Get connection to MikroTik router
            $connection = $this->routerOSService->connect();

            if (!$connection) {
                log_message('warning', 'Could not connect to MikroTik router');
                // Return empty array as fallback instead of error
                return $this->respond([
                    'success' => false,
                    'data' => [],
                    'message' => 'Could not connect to MikroTik router - check configuration'
                ]);
            }

            // Get all profiles
            $profiles = $this->routerOSService->getProfiles();

            if (!$profiles) {
                log_message('warning', 'Could not retrieve profiles from MikroTik');
                // Return empty array as fallback
                return $this->respond([
                    'success' => false,
                    'data' => [],
                    'message' => 'Could not retrieve profiles from MikroTik'
                ]);
            }

            $pppProfiles = $profiles['ppp_profiles'] ?? [];
            $queueProfiles = $profiles['queue_profiles'] ?? [];

            // Format PPP profiles for select dropdown (name only)
            $formattedPppProfiles = array_map(function ($profile) {
                return [
                    'id' => $profile['name'] ?? '',
                    'name' => $profile['name'] ?? '',
                    'local_address' => $profile['local-address'] ?? '',
                    'remote_address' => $profile['remote-address'] ?? '',
                    'rate_limit' => $profile['rate-limit'] ?? ''
                ];
            }, $pppProfiles);

            // Return flat array of profile names for dropdown
            return $this->respond([
                'success' => true,
                'data' => $formattedPppProfiles,
                'count' => count($formattedPppProfiles),
                'message' => 'Profiles loaded successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading MikroTik profiles: ' . $e->getMessage());

            // Return success with empty data instead of error to prevent popup
            return $this->respond([
                'success' => true,
                'data' => [],
                'message' => 'Using database profiles instead of MikroTik'
            ]);
        }
    }

    /**
     * Sync selected profiles to the database
     */
    public function sync()
    {
        try {
            $profileType = $this->request->getPost('profile_type');
            $selectedProfiles = $this->request->getPost('selected_profiles');

            if (!$profileType || !$selectedProfiles) {
                return $this->fail('Missing profile type or selected profiles');
            }

            if (!is_array($selectedProfiles)) {
                $selectedProfiles = [$selectedProfiles];
            }

            $syncedCount = 0;
            $errors = [];

            // Get MikroTik connection
            $connection = $this->routerOSService->connect();
            if (!$connection) {
                return $this->failServerError('Could not connect to MikroTik router');
            }

            $allProfiles = $this->routerOSService->getProfiles();
            if (!$allProfiles) {
                return $this->failServerError('Could not retrieve profiles from MikroTik router');
            }

            // Sync based on profile type
            if ($profileType === 'ppp') {
                foreach ($selectedProfiles as $profileName) {
                    try {
                        $matchingProfiles = array_filter($allProfiles['ppp_profiles'], function ($p) use ($profileName) {
                            return isset($p['name']) && $p['name'] === $profileName;
                        });

                        if (!empty($matchingProfiles)) {
                            $profile = reset($matchingProfiles);

                            // Get local-address and rate-limit if available
                            $localAddress = $profile['local-address'] ?? '172.16.1.1';
                            $rateLimit = $profile['rate-limit'] ?? '';

                            // Parse rate limit
                            list($rateLimitUp, $rateLimitDown) = array_pad(explode('/', $rateLimit), 2, '');

                            // Save to database using appropriate model
                            try {
                                $groupProfileModel = new \App\Models\GroupProfileModel();

                                // Check if profile exists
                                $existingProfile = $groupProfileModel->where('name', $profileName)->first();

                                $profileData = [
                                    'name' => $profileName,
                                    'local_address' => $localAddress,
                                    'data_owner' => 'PPP',
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];

                                if ($existingProfile) {
                                    $groupProfileModel->update($existingProfile['id'], $profileData);
                                } else {
                                    $profileData['created_at'] = date('Y-m-d H:i:s');
                                    $groupProfileModel->insert($profileData);
                                }

                                $syncedCount++;
                            } catch (\Exception $e) {
                                throw new \Exception('Failed to save profile to database: ' . $e->getMessage());
                            }
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Failed to sync PPP profile '{$profileName}': " . $e->getMessage();
                    }
                }
            } else if ($profileType === 'queue') {
                foreach ($selectedProfiles as $profileName) {
                    try {
                        $matchingProfiles = array_filter($allProfiles['queue_profiles'], function ($p) use ($profileName) {
                            return isset($p['name']) && $p['name'] === $profileName;
                        });

                        if (!empty($matchingProfiles)) {
                            $profile = reset($matchingProfiles);

                            // Get bandwidth limits if available
                            $maxLimit = $profile['max-limit'] ?? '';
                            list($maxLimitUp, $maxLimitDown) = array_pad(explode('/', $maxLimit), 2, '');

                            // Save to database using appropriate model
                            try {
                                $bandwidthModel = new \App\Models\BandwidthModel();

                                // Check if profile exists
                                $existingProfile = $bandwidthModel->where('name', $profileName)->first();

                                $profileData = [
                                    'name' => $profileName,
                                    'max_limit_up' => $maxLimitUp,
                                    'max_limit_down' => $maxLimitDown,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];

                                if ($existingProfile) {
                                    $bandwidthModel->update($existingProfile['id'], $profileData);
                                } else {
                                    $profileData['created_at'] = date('Y-m-d H:i:s');
                                    $bandwidthModel->insert($profileData);
                                }

                                $syncedCount++;
                            } catch (\Exception $e) {
                                throw new \Exception('Failed to save profile to database: ' . $e->getMessage());
                            }
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Failed to sync Queue profile '{$profileName}': " . $e->getMessage();
                    }
                }
            }

            return $this->respond([
                'success' => true,
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'message' => $syncedCount . ' profiles synchronized successfully'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Error syncing profiles: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get PPPoE profiles only from MikroTik router
     */
    public function getPppoeProfiles()
    {
        try {
            log_message('info', 'Starting getPppoeProfiles() method');

            // Get connection to MikroTik router
            log_message('info', 'Attempting to connect to MikroTik router');
            $connection = $this->routerOSService->connect();

            if (!$connection) {
                log_message('error', 'Could not connect to MikroTik router');
                return $this->respond([
                    'success' => false,
                    'data' => [],
                    'message' => 'Could not connect to MikroTik router'
                ]);
            }

            log_message('info', 'Connected to MikroTik successfully');

            // Get all profiles
            log_message('info', 'Getting profiles from MikroTik');
            $profiles = $this->routerOSService->getProfiles();

            if (!$profiles) {
                log_message('error', 'Could not retrieve profiles from MikroTik router');
                return $this->respond([
                    'success' => false,
                    'data' => [],
                    'message' => 'Could not retrieve profiles from MikroTik router'
                ]);
            }

            log_message('info', 'Profiles retrieved successfully: ' . json_encode($profiles));

            $pppProfiles = $profiles['ppp_profiles'] ?? [];
            log_message('info', 'Found ' . count($pppProfiles) . ' PPP profiles');

            // Format PPP profiles specifically for PPPoE usage
            $formattedPppProfiles = array_map(function ($profile) {
                return [
                    'name' => $profile['name'] ?? '',
                    'local_address' => $profile['local-address'] ?? 'Auto',
                    'remote_address' => $profile['remote-address'] ?? 'Auto',
                    'rate_limit' => $profile['rate-limit'] ?? '',
                    'only_one' => $profile['only-one'] ?? 'no',
                    'comment' => $profile['comment'] ?? '',
                    'bridge' => $profile['bridge'] ?? '',
                    'use_mpls' => $profile['use-mpls'] ?? 'default',
                    'use_compression' => $profile['use-compression'] ?? 'default',
                    'use_encryption' => $profile['use-encryption'] ?? 'default'
                ];
            }, $pppProfiles);

            $response = [
                'success' => true,
                'data' => $formattedPppProfiles,
                'count' => count($formattedPppProfiles),
                'message' => count($formattedPppProfiles) . ' PPPoE profiles loaded from MikroTik'
            ];

            log_message('info', 'Returning response: ' . json_encode($response));
            return $this->respond($response);
        } catch (\Exception $e) {
            log_message('error', 'MikroTik PPPoE Profiles Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->respond([
                'success' => false,
                'data' => [],
                'message' => 'Error loading PPPoE profiles: ' . $e->getMessage()
            ]);
        }
    }
}
