<?php

namespace App\Services;

use App\Libraries\MikrotikAPI;
use App\Models\ServerLocationModel;
use App\Models\BandwidthModel;
use Exception;

/**
 * MikroTik Integration Service - REBUILT
 * Handles automatic creation of profiles on MikroTik routers
 */
class MikroTikService
{
    protected $lokasiServerModel;
    protected $bandwidthModel;

    public function __construct()
    {
        $this->lokasiServerModel = new ServerLocationModel();
        $this->bandwidthModel = new BandwidthModel();
    }

    /**
     * Get bandwidth rate limit in MikroTik format
     * Converts bandwidth profile data to MikroTik rate-limit format (upload/download)
     */
    protected function getBandwidthRateLimit($bandwidthProfileId)
    {
        if (!$bandwidthProfileId) {
            return null;
        }

        try {
            $bandwidth = $this->bandwidthModel->find($bandwidthProfileId);
            if (!$bandwidth) {
                return null;
            }

            // MikroTik rate-limit format: upload/download
            // Convert kbps to proper format (if stored in kbps)
            $uploadMax = intval($bandwidth['upload_max']);
            $downloadMax = intval($bandwidth['download_max']);

            // If values are too small, assume they're in Mbps and convert to kbps
            if ($uploadMax < 1000 && $downloadMax < 1000) {
                $uploadMax *= 1000;
                $downloadMax *= 1000;
            }

            return $uploadMax . 'k/' . $downloadMax . 'k';
        } catch (Exception $e) {
            log_message('error', 'Error getting bandwidth rate limit: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add Group Profile to all active MikroTik routers
     */
    public function addGroupProfileToRouters($groupProfile)
    {
        $results = [];

        // Get all active routers
        $routers = $this->lokasiServerModel->where('ping_status', 'online')
            ->where('is_connected', '1')
            ->findAll();

        if (empty($routers)) {
            return [
                'success' => false,
                'message' => 'No active routers found',
                'results' => []
            ];
        }

        foreach ($routers as $router) {
            try {
                $startTime = time();
                $result = $this->addGroupProfileToRouter($router, $groupProfile);
                $endTime = time();
                $duration = $endTime - $startTime;

                $results[] = [
                    'router' => $router['name'],
                    'ip' => $router['ip_router'],
                    'success' => $result['success'],
                    'message' => $result['message'] . " (Duration: {$duration}s)",
                    'duration' => $duration
                ];

                log_message('info', "Router {$router['name']} sync completed in {$duration} seconds");
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();

                // Detect timeout errors and provide better message
                if (
                    strpos($errorMsg, 'timeout') !== false ||
                    strpos($errorMsg, 'timed out') !== false ||
                    strpos($errorMsg, 'Maximum execution time') !== false
                ) {
                    $errorMsg = 'Connection timeout - Router may be slow or unresponsive';
                }

                $results[] = [
                    'router' => $router['name'],
                    'ip' => $router['ip_router'],
                    'success' => false,
                    'message' => 'Error: ' . $errorMsg,
                    'duration' => 0
                ];

                log_message('error', "Router {$router['name']} sync failed: " . $errorMsg);

                // Continue with other routers - don't break the loop
                continue;
            }
        }

        $successCount = count(array_filter($results, function ($r) {
            return $r['success'];
        }));
        $totalCount = count($results);

        return [
            'success' => $successCount > 0,
            'message' => "Profile added to {$successCount}/{$totalCount} routers",
            'results' => $results
        ];
    }

    /**
     * Add Group Profile to a specific MikroTik router - REBUILT
     */
    public function addGroupProfileToRouter($router, $groupProfile)
    {
        // Set maximum execution time for this router to prevent hanging
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(30); // 30 seconds max per router

        try {
            log_message('info', 'Starting MikroTik sync for profile: ' . $groupProfile['name'] . ' to router: ' . $router['name']);

            // Connect to MikroTik using direct RouterOS API
            $api = $this->connectToRouter($router);

            $results = [];

            // 1. Create IP Pool
            try {
                $poolResult = $this->createIpPool($api, $groupProfile);
                $results[] = $poolResult;
                log_message('info', 'IP Pool result: ' . $poolResult);
            } catch (Exception $e) {
                $error = 'Failed to create IP Pool: ' . $e->getMessage();
                $results[] = $error;
                log_message('error', $error);
            }

            // 2. Create PPPoE Profile
            try {
                $profileResult = $this->createPppoeProfile($api, $groupProfile);
                $results[] = $profileResult;
                log_message('info', 'PPPoE Profile result: ' . $profileResult);
            } catch (Exception $e) {
                $error = 'Failed to create PPPoE Profile: ' . $e->getMessage();
                $results[] = $error;
                log_message('error', $error);
            }

            // Disconnect
            $api->disconnect();

            log_message('info', 'Completed MikroTik sync for profile: ' . $groupProfile['name'] . ' to router: ' . $router['name']);

            return [
                'success' => true,
                'message' => 'Profile synced successfully',
                'details' => $results
            ];
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            log_message('error', 'Failed to sync profile to MikroTik router: ' . $router['name'] . ' - ' . $errorMsg);

            // Handle different types of errors
            if (strpos($errorMsg, 'timeout') !== false || strpos($errorMsg, 'timed out') !== false) {
                $error = 'Connection timeout - Router may be slow or unresponsive';
            } elseif (strpos($errorMsg, '10061') !== false || strpos($errorMsg, 'refused') !== false) {
                $error = 'Cannot connect to router - MikroTik API service may not be enabled on port ' . ($router['port_api'] ?: 8728);
            } else {
                $error = 'Connection failed: ' . $errorMsg;
            }

            return [
                'success' => false,
                'message' => $error
            ];
        } finally {
            // Restore original time limit
            set_time_limit($originalTimeLimit);
        }
    }

    /**
     * Sync all group profiles to a specific router
     */
    public function syncAllGroupProfilesToRouter($routerId)
    {
        try {
            // Get router data
            $router = $this->lokasiServerModel->find($routerId);
            if (!$router) {
                return [
                    'success' => false,
                    'message' => 'Router not found'
                ];
            }

            // Get all group profiles
            $groupProfileModel = new \App\Models\GroupProfileModel();
            $profiles = $groupProfileModel->findAll();

            if (empty($profiles)) {
                return [
                    'success' => false,
                    'message' => 'No group profiles found'
                ];
            }

            $results = [];
            $successCount = 0;

            foreach ($profiles as $profile) {
                try {
                    $result = $this->addGroupProfileToRouter($router, $profile);
                    $results[] = [
                        'profile' => $profile['name'],
                        'success' => $result['success'],
                        'message' => $result['message']
                    ];

                    if ($result['success']) {
                        $successCount++;
                    }
                } catch (Exception $e) {
                    $results[] = [
                        'profile' => $profile['name'],
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ];
                }
            }

            return [
                'success' => $successCount > 0,
                'message' => "Synced {$successCount}/" . count($profiles) . " profiles to router {$router['name']}",
                'results' => $results
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Connect to MikroTik router using RouterOS API directly
     */
    private function connectToRouter($router)
    {
        $api = new \App\Libraries\MikrotikNew();

        // Get port
        $port = $router['port_api'] ?: 8728;

        log_message('debug', "Connecting to {$router['ip_router']} with user {$router['username']} on port {$port}");

        $connected = $api->connect($router['ip_router'], $router['username'], $router['password_router'], intval($port));

        if (!$connected) {
            throw new Exception('Failed to connect to MikroTik router: ' . ($api->error_str ?? 'Unknown error'));
        }

        log_message('info', 'Successfully connected to MikroTik router: ' . $router['name']);
        return $api;
    }

    /**
     * Create IP Pool on MikroTik - REBUILT with IP Address Population
     */
    private function createIpPool($api, $groupProfile)
    {
        $poolName = 'billingku_' . $groupProfile['name'];

        // Get IP range from group profile
        $startIp = $groupProfile['ip_range_start'] ?? '172.16.1.2';
        $endIp = $groupProfile['ip_range_end'] ?? '172.16.1.254';

        log_message('debug', "Creating IP Pool: {$poolName} with range {$startIp}-{$endIp}");

        try {
            // Check if pool already exists by getting all pools and searching
            $allPools = $api->comm('/ip/pool/print');

            $poolExists = false;
            $poolId = null;
            if (is_array($allPools)) {
                foreach ($allPools as $pool) {
                    if (isset($pool['name']) && $pool['name'] === $poolName) {
                        $poolExists = true;
                        $poolId = $pool['.id'] ?? null;
                        break;
                    }
                }
            }

            if ($poolExists) {
                log_message('info', "IP Pool '{$poolName}' already exists");
                return "IP Pool '{$poolName}' already exists";
            }

            // Create IP pool with range
            $result = $api->comm('/ip/pool/add', [
                'name' => $poolName,
                'ranges' => $startIp . '-' . $endIp,
                'comment' => 'created by billingku - ' . date('Y-m-d H:i:s')
            ]);

            // Wait a moment for creation to complete
            usleep(500000); // 0.5 seconds

            // Verify the pool was created
            $allPoolsAfter = $api->comm('/ip/pool/print');

            $poolCreated = false;
            if (is_array($allPoolsAfter)) {
                foreach ($allPoolsAfter as $pool) {
                    if (isset($pool['name']) && $pool['name'] === $poolName) {
                        $poolCreated = true;
                        log_message('info', "IP Pool '{$poolName}' created successfully");
                        break;
                    }
                }
            }

            if ($poolCreated) {
                log_message('info', 'IP Pool created successfully: ' . $poolName);
                return "IP Pool '{$poolName}' created successfully";
            } else {
                throw new Exception("IP Pool '{$poolName}' creation failed - verification failed");
            }
        } catch (Exception $e) {
            // If the error is array offset, try a simpler approach
            if (strpos($e->getMessage(), 'array offset') !== false) {
                log_message('warning', 'Array offset error caught, trying alternative approach');

                // Simple creation without verification
                try {
                    $api->comm('/ip/pool/add', [
                        'name' => $poolName,
                        'ranges' => $startIp . '-' . $endIp,
                        'comment' => 'created by billingku (alt method) - ' . date('Y-m-d H:i:s')
                    ]);

                    return "IP Pool '{$poolName}' created successfully (alternative method)";
                } catch (Exception $e2) {
                    if (strpos($e2->getMessage(), 'already exists') !== false || strpos($e2->getMessage(), 'already have') !== false) {
                        return "IP Pool '{$poolName}' already exists";
                    }
                    throw $e2;
                }
            }
            throw $e;
        }
    }

    /**
     * Create PPPoE Profile on MikroTik - FIXED
     */
    private function createPppoeProfile($api, $groupProfile)
    {
        $profileName = 'billingku_' . $groupProfile['name'];
        $poolName = 'billingku_' . $groupProfile['name'];

        log_message('debug', "Creating PPPoE Profile: {$profileName} with pool: {$poolName}");

        try {
            // Check if profile already exists
            $allProfiles = $api->comm('/ppp/profile/print');

            $profileExists = false;
            if (is_array($allProfiles)) {
                foreach ($allProfiles as $profile) {
                    if (isset($profile['name']) && $profile['name'] === $profileName) {
                        $profileExists = true;
                        log_message('info', "PPPoE Profile '{$profileName}' already exists");
                        break;
                    }
                }
            }

            if ($profileExists) {
                return "PPPoE Profile '{$profileName}' already exists";
            }

            // Get rate limit if available
            $rateLimit = null;
            if (isset($groupProfile['bandwidth_profile_id']) && $groupProfile['bandwidth_profile_id']) {
                $rateLimit = $this->getBandwidthRateLimit($groupProfile['bandwidth_profile_id']);
                log_message('debug', "Rate limit for profile {$profileName}: " . ($rateLimit ?: 'none'));
            }

            // Create PPPoE profile parameters
            $params = [
                'name' => $profileName,
                'local-address' => $groupProfile['local_address'] ?? '192.168.1.1',
                'remote-address' => $poolName, // Use pool name, not IP
                'dns-server' => $groupProfile['dns_server'] ?? '8.8.8.8,8.8.4.4',
                'comment' => 'created by billingku - ' . date('Y-m-d H:i:s')
            ];

            // Add rate limit if available
            if ($rateLimit) {
                $params['rate-limit'] = $rateLimit;
                log_message('debug', "Adding rate limit to PPPoE profile: {$rateLimit}");
            }

            // Add session timeout if specified
            if (isset($groupProfile['session_timeout']) && $groupProfile['session_timeout']) {
                $params['session-timeout'] = $groupProfile['session_timeout'];
            }

            // Add idle timeout if specified
            if (isset($groupProfile['idle_timeout']) && $groupProfile['idle_timeout']) {
                $params['idle-timeout'] = $groupProfile['idle_timeout'];
            }

            log_message('debug', "PPPoE Profile parameters: " . json_encode($params));

            // Create the profile
            $result = $api->comm('/ppp/profile/add', $params);

            log_message('debug', "PPPoE Profile creation result: " . json_encode($result));

            // Wait a moment for creation to complete
            usleep(200000); // 0.2 seconds

            // Verify the profile was created
            $allProfilesAfter = $api->comm('/ppp/profile/print');

            $profileCreated = false;
            if (is_array($allProfilesAfter)) {
                foreach ($allProfilesAfter as $profile) {
                    if (isset($profile['name']) && $profile['name'] === $profileName) {
                        $profileCreated = true;
                        log_message('info', 'PPPoE Profile verified successfully: ' . $profileName);
                        break;
                    }
                }
            }

            if ($profileCreated) {
                return "PPPoE Profile '{$profileName}' created successfully";
            } else {
                // Try alternative verification - check if we got an error
                if (is_array($result) && isset($result['!trap'])) {
                    $errorMsg = isset($result['message']) ? $result['message'] : 'Unknown MikroTik error';
                    throw new Exception("PPPoE Profile creation failed: {$errorMsg}");
                } else {
                    // Assume success if no error and no verification issues
                    log_message('warning', "PPPoE Profile verification uncertain but no errors detected for: {$profileName}");
                    return "PPPoE Profile '{$profileName}' created (verification uncertain)";
                }
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            log_message('error', "Error creating PPPoE Profile '{$profileName}': {$errorMsg}");

            // Handle specific MikroTik errors
            if (
                strpos($errorMsg, 'already exists') !== false ||
                strpos($errorMsg, 'already have') !== false
            ) {
                return "PPPoE Profile '{$profileName}' already exists";
            }

            // Try simpler creation method for array offset errors
            if (
                strpos($errorMsg, 'array offset') !== false ||
                strpos($errorMsg, 'undefined') !== false
            ) {

                log_message('warning', 'Trying alternative PPPoE profile creation method...');

                try {
                    // Simpler parameters
                    $simpleParams = [
                        'name' => $profileName,
                        'local-address' => $groupProfile['local_address'] ?? '192.168.1.1',
                        'remote-address' => $poolName,
                        'comment' => 'created by billingku'
                    ];

                    $api->comm('/ppp/profile/add', $simpleParams);

                    return "PPPoE Profile '{$profileName}' created successfully (simple method)";
                } catch (Exception $e2) {
                    if (strpos($e2->getMessage(), 'already exists') !== false) {
                        return "PPPoE Profile '{$profileName}' already exists";
                    }
                    throw new Exception("Failed to create PPPoE Profile (both methods): " . $e2->getMessage());
                }
            }

            throw $e;
        }
    }


    /**
     * Update group profile on all routers
     */
    public function updateGroupProfileOnRouters($groupProfile)
    {
        $results = [];

        // Get all active routers
        $routers = $this->lokasiServerModel->where('ping_status', 'online')
            ->where('is_connected', '1')
            ->findAll();

        if (empty($routers)) {
            return [
                'success' => false,
                'message' => 'No active routers found',
                'results' => []
            ];
        }

        foreach ($routers as $router) {
            try {
                $startTime = time();
                $result = $this->updateGroupProfileOnRouter($router, $groupProfile);
                $endTime = time();
                $duration = $endTime - $startTime;

                $results[] = [
                    'router' => $router['name'],
                    'ip' => $router['ip_router'],
                    'success' => $result['success'],
                    'message' => $result['message'] . " (Duration: {$duration}s)",
                    'duration' => $duration
                ];
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();

                if (
                    strpos($errorMsg, 'timeout') !== false ||
                    strpos($errorMsg, 'timed out') !== false ||
                    strpos($errorMsg, 'Maximum execution time') !== false
                ) {
                    $errorMsg = 'Connection timeout - Router may be slow or unresponsive';
                }

                $results[] = [
                    'router' => $router['name'],
                    'ip' => $router['ip_router'],
                    'success' => false,
                    'message' => 'Error: ' . $errorMsg,
                    'duration' => 0
                ];
            }
        }

        $successCount = count(array_filter($results, function ($r) {
            return $r['success'];
        }));
        $totalCount = count($results);

        return [
            'success' => $successCount > 0,
            'message' => "Profile updated on {$successCount}/{$totalCount} routers",
            'results' => $results
        ];
    }
    /**
     * Update group profile on a specific router
     */
    private function updateGroupProfileOnRouter($router, $groupProfile)
    {
        // Set maximum execution time for this router to prevent hanging
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(30); // 30 seconds max per router

        try {
            log_message('info', 'Starting MikroTik update for profile: ' . $groupProfile['name'] . ' to router: ' . $router['name']);

            $api = $this->connectToRouter($router);

            $poolName = 'billingku_' . $groupProfile['name'];
            $profileName = 'billingku_' . $groupProfile['name'];

            $results = [];

            // Update IP Pool if it exists
            try {
                $existingPools = $api->comm('/ip/pool/print', ['?name=' . $poolName]);
                if (is_array($existingPools) && !empty($existingPools) && isset($existingPools[0]) && isset($existingPools[0]['.id'])) {
                    $poolId = $existingPools[0]['.id'];
                    $startIp = $groupProfile['ip_range_start'] ?? '172.16.1.2';
                    $endIp = $groupProfile['ip_range_end'] ?? '172.16.1.254';

                    $api->comm('/ip/pool/set', [
                        '=.id=' . $poolId,
                        '=ranges=' . $startIp . '-' . $endIp
                    ]);

                    $results[] = "IP Pool '{$poolName}' updated successfully";
                    log_message('info', "IP Pool '{$poolName}' updated successfully");
                } else {
                    // Create if doesn't exist
                    $poolResult = $this->createIpPool($api, $groupProfile);
                    $results[] = $poolResult;
                    log_message('info', "IP Pool created: " . $poolResult);
                }
            } catch (Exception $e) {
                $error = "Failed to update IP Pool: " . $e->getMessage();
                $results[] = $error;
                log_message('error', $error);
            }

            // Update PPPoE Profile if it exists
            try {
                $existingProfiles = $api->comm('/ppp/profile/print', ['?name=' . $profileName]);
                if (is_array($existingProfiles) && !empty($existingProfiles) && isset($existingProfiles[0]) && isset($existingProfiles[0]['.id'])) {
                    $profileId = $existingProfiles[0]['.id'];
                    $rateLimit = $this->getBandwidthRateLimit($groupProfile['bandwidth_profile_id'] ?? null);

                    $updateParams = [
                        '=.id=' . $profileId,
                        '=local-address=' . ($groupProfile['local_address'] ?? '172.16.1.1')
                    ];

                    if ($rateLimit) {
                        $updateParams[] = '=rate-limit=' . $rateLimit;
                    }

                    $api->comm('/ppp/profile/set', $updateParams);
                    $results[] = "PPPoE Profile '{$profileName}' updated successfully";
                    log_message('info', "PPPoE Profile '{$profileName}' updated successfully");
                } else {
                    // Create if doesn't exist
                    $profileResult = $this->createPppoeProfile($api, $groupProfile);
                    $results[] = $profileResult;
                    log_message('info', "PPPoE Profile created: " . $profileResult);
                }
            } catch (Exception $e) {
                $error = "Failed to update PPPoE Profile: " . $e->getMessage();
                $results[] = $error;
                log_message('error', $error);
            }

            $api->disconnect();

            log_message('info', 'Completed MikroTik update for profile: ' . $groupProfile['name'] . ' to router: ' . $router['name']);

            return [
                'success' => true,
                'message' => 'Profile updated successfully',
                'details' => $results
            ];
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            log_message('error', 'Failed to update profile on MikroTik router: ' . $router['name'] . ' - ' . $errorMsg);

            // Handle different types of errors
            if (strpos($errorMsg, 'timeout') !== false || strpos($errorMsg, 'timed out') !== false) {
                $error = 'Connection timeout - Router may be slow or unresponsive';
            } elseif (strpos($errorMsg, '10061') !== false || strpos($errorMsg, 'refused') !== false) {
                $error = 'Cannot connect to router - MikroTik API service may not be enabled on port ' . ($router['port_api'] ?: 8728);
            } else {
                $error = 'Update failed: ' . $errorMsg;
            }

            return [
                'success' => false,
                'message' => $error
            ];
        } finally {
            // Restore original time limit
            set_time_limit($originalTimeLimit);
        }
    }

    /**
     * Remove group profile from all routers
     */
    public function removeGroupProfileFromRouters($groupProfileName)
    {
        $results = [];

        // Get all active routers
        $routers = $this->lokasiServerModel->where('ping_status', 'online')
            ->where('is_connected', '1')
            ->findAll();

        if (empty($routers)) {
            return [
                'success' => false,
                'message' => 'No active routers found',
                'results' => []
            ];
        }

        foreach ($routers as $router) {
            try {
                $result = $this->removeGroupProfileFromRouter($router, $groupProfileName);
                $results[] = [
                    'router' => $router['name'],
                    'ip' => $router['ip_router'],
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
            } catch (Exception $e) {
                $results[] = [
                    'router' => $router['name'],
                    'ip' => $router['ip_router'],
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }

        $successCount = count(array_filter($results, function ($r) {
            return $r['success'];
        }));
        $totalCount = count($results);

        return [
            'success' => $successCount > 0,
            'message' => "Profile removed from {$successCount}/{$totalCount} routers",
            'results' => $results
        ];
    }
    /**
     * Remove group profile from a specific router
     */
    private function removeGroupProfileFromRouter($router, $groupProfileName)
    {
        try {
            // Use MikrotikAPI library for proper connection handling (same as isolir)
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $router['port_api'] ?: 8728
            ];

            $api = new MikrotikAPI($mikrotikConfig);

            if (!$api->isConnected()) {
                throw new Exception('Failed to connect to MikroTik: ' . ($api->getLastError() ?? 'Unknown error'));
            }

            $connection = $api->getClient();

            $poolName = 'billingku_' . $groupProfileName;
            $profileName = 'billingku_' . $groupProfileName;

            $removed = [];            // Remove PPPoE Profile first
            $existingProfiles = $connection->comm('/ppp/profile/print', ['?name=' . $profileName]);
            if (is_array($existingProfiles) && !empty($existingProfiles) && isset($existingProfiles[0]['.id'])) {
                $connection->comm('/ppp/profile/remove', ['=.id=' . $existingProfiles[0]['.id']]);
                $removed[] = 'PPPoE Profile';
            }

            // Remove IP Pool
            $existingPools = $connection->comm('/ip/pool/print', ['?name=' . $poolName]);
            if (is_array($existingPools) && !empty($existingPools) && isset($existingPools[0]['.id'])) {
                $connection->comm('/ip/pool/remove', ['=.id=' . $existingPools[0]['.id']]);
                $removed[] = 'IP Pool';
            }

            $connection->disconnect();

            if (empty($removed)) {
                return [
                    'success' => true,
                    'message' => 'Profile not found on router'
                ];
            }

            return [
                'success' => true,
                'message' => 'Removed: ' . implode(', ', $removed)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Remove failed: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Get all billingku profiles from a specific router
     */
    public function getBillingkuProfilesFromRouter($routerId)
    {
        try {
            $router = $this->lokasiServerModel->find($routerId);
            if (!$router) {
                return [
                    'success' => false,
                    'message' => 'Router not found'
                ];
            }

            // Use MikrotikAPI library for proper connection handling (same as isolir)
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $router['port_api'] ?: 8728
            ];

            $api = new MikrotikAPI($mikrotikConfig);

            if (!$api->isConnected()) {
                throw new Exception('Failed to connect to MikroTik: ' . ($api->getLastError() ?? 'Unknown error'));
            }

            $connection = $api->getClient();            // Get IP Pools with billingku comment
            $ipPools = $connection->comm('/ip/pool/print', ['?comment=added by billingku']);

            // Get PPPoE Profiles with billingku comment
            $pppoeProfiles = $connection->comm('/ppp/profile/print', ['?comment=added by billingku']);

            $connection->disconnect();

            // Ensure arrays are returned, not boolean values
            $ipPoolsArray = is_array($ipPools) ? $ipPools : [];
            $pppoeProfilesArray = is_array($pppoeProfiles) ? $pppoeProfiles : [];

            return [
                'success' => true,
                'message' => 'Profiles retrieved successfully',
                'data' => [
                    'ip_pools' => $ipPoolsArray,
                    'pppoe_profiles' => $pppoeProfilesArray
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get profiles: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Clean up orphaned profiles from router
     */
    public function cleanupOrphanedProfilesFromRouter($routerId)
    {
        try {
            $router = $this->lokasiServerModel->find($routerId);
            if (!$router) {
                return [
                    'success' => false,
                    'message' => 'Router not found'
                ];
            }

            // Get current group profiles from database
            $groupProfileModel = new \App\Models\GroupProfileModel();
            $dbProfiles = $groupProfileModel->findAll();
            $dbProfileNames = array_map(function ($p) {
                return 'billingku_' . $p['name'];
            }, $dbProfiles);

            // Get profiles from router
            $routerProfiles = $this->getBillingkuProfilesFromRouter($routerId);
            if (!$routerProfiles['success']) {
                return $routerProfiles;
            }

            // Use MikrotikAPI library for proper connection handling (same as isolir)
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $router['port_api'] ?: 8728
            ];

            $api = new MikrotikAPI($mikrotikConfig);

            if (!$api->isConnected()) {
                throw new Exception('Failed to connect to MikroTik: ' . ($api->getLastError() ?? 'Unknown error'));
            }

            $connection = $api->getClient();
            $removed = [];

            // Remove orphaned PPPoE profiles - Add safety checks
            if (isset($routerProfiles['data']['pppoe_profiles']) && is_array($routerProfiles['data']['pppoe_profiles'])) {
                foreach ($routerProfiles['data']['pppoe_profiles'] as $profile) {
                    if (isset($profile['name']) && isset($profile['.id']) && !in_array($profile['name'], $dbProfileNames)) {
                        try {
                            $connection->comm('/ppp/profile/remove', ['=.id=' . $profile['.id']]);
                            $removed[] = 'PPPoE Profile: ' . $profile['name'];
                        } catch (Exception $e) {
                            log_message('error', 'Failed to remove PPPoE profile: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Remove orphaned IP pools - Add safety checks
            if (isset($routerProfiles['data']['ip_pools']) && is_array($routerProfiles['data']['ip_pools'])) {
                foreach ($routerProfiles['data']['ip_pools'] as $pool) {
                    if (isset($pool['name']) && isset($pool['.id']) && !in_array($pool['name'], $dbProfileNames)) {
                        try {
                            $connection->comm('/ip/pool/remove', ['=.id=' . $pool['.id']]);
                            $removed[] = 'IP Pool: ' . $pool['name'];
                        } catch (Exception $e) {
                            log_message('error', 'Failed to remove IP pool: ' . $e->getMessage());
                        }
                    }
                }
            }

            $connection->disconnect();

            return [
                'success' => true,
                'message' => count($removed) > 0 ? 'Removed: ' . implode(', ', $removed) : 'No orphaned profiles found',
                'removed' => $removed
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Verify that group profile is properly synced to router
     */
    public function verifyGroupProfileOnRouter($routerId, $groupProfileId)
    {
        try {
            // Get router data
            $router = $this->lokasiServerModel->find($routerId);
            if (!$router) {
                return [
                    'success' => false,
                    'message' => 'Router not found'
                ];
            }

            // Get group profile data
            $groupProfileModel = new \App\Models\GroupProfileModel();
            $groupProfile = $groupProfileModel->find($groupProfileId);
            if (!$groupProfile) {
                return [
                    'success' => false,
                    'message' => 'Group profile not found'
                ];
            }

            // Connect to MikroTik
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $router['port_api'] ?: 8728
            ];

            $api = new MikrotikAPI($mikrotikConfig);

            if (!$api->isConnected()) {
                throw new Exception('Failed to connect to MikroTik: ' . ($api->getLastError() ?? 'Unknown error'));
            }

            $connection = $api->getClient();

            $poolName = 'billingku_' . $groupProfile['name'];
            $profileName = 'billingku_' . $groupProfile['name'];

            $verification = [
                'ip_pool' => [
                    'exists' => false,
                    'details' => null
                ],
                'pppoe_profile' => [
                    'exists' => false,
                    'details' => null
                ]
            ];

            // Check IP Pool
            $existingPools = $connection->comm('/ip/pool/print', ['?name=' . $poolName]);
            if (is_array($existingPools) && !empty($existingPools)) {
                $verification['ip_pool']['exists'] = true;
                $verification['ip_pool']['details'] = $existingPools[0];
            }

            // Check PPPoE Profile
            $existingProfiles = $connection->comm('/ppp/profile/print', ['?name=' . $profileName]);
            if (is_array($existingProfiles) && !empty($existingProfiles)) {
                $verification['pppoe_profile']['exists'] = true;
                $verification['pppoe_profile']['details'] = $existingProfiles[0];
            }

            $connection->disconnect();

            $poolExists = $verification['ip_pool']['exists'];
            $profileExists = $verification['pppoe_profile']['exists'];

            return [
                'success' => true,
                'message' => sprintf(
                    'Verification complete: IP Pool %s, PPPoE Profile %s',
                    $poolExists ? 'found' : 'missing',
                    $profileExists ? 'found' : 'missing'
                ),
                'data' => [
                    'fully_synced' => $poolExists && $profileExists,
                    'verification' => $verification,
                    'expected_names' => [
                        'ip_pool' => $poolName,
                        'pppoe_profile' => $profileName
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }
}
