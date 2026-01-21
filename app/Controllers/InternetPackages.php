<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BandwidthModel;
use App\Models\GroupProfileModel;
use App\Models\PackageProfileModel;
use App\Services\MikroTikService;

class InternetPackages extends BaseController
{
    protected $bandwidthModel;
    protected $groupProfileModel;
    protected $packageProfileModel;
    protected $mikrotikService;

    public function __construct()
    {
        $this->bandwidthModel = new BandwidthModel();
        $this->groupProfileModel = new GroupProfileModel();
        $this->packageProfileModel = new PackageProfileModel();
        $this->mikrotikService = new MikroTikService();
    }

    /**
     * Bandwidth Management Page
     */
    public function bandwidth()
    {
        $data = [
            'title' => 'Bandwidth Management',
            'page_title' => 'Internet Packages - Bandwidth',
            'breadcrumb' => [
                'Internet Packages' => 'internet-packages',
                'Bandwidth' => ''
            ]
        ];

        return view('internet-packages/bandwidth', $data);
    }
    /**
     * Get Bandwidth Data for DataTable
     */
    public function getBandwidthData()
    {
        try {
            $bandwidths = $this->bandwidthModel->findAll();

            $data = [];
            foreach ($bandwidths as $bandwidth) {
                $data[] = [
                    'id' => (int)$bandwidth['id'],
                    'name' => $bandwidth['name'] ?? '',
                    'download_min' => (int)($bandwidth['download_min'] ?? 0),
                    'download_max' => (int)($bandwidth['download_max'] ?? 0),
                    'upload_min' => (int)($bandwidth['upload_min'] ?? 0),
                    'upload_max' => (int)($bandwidth['upload_max'] ?? 0),
                    'status' => $bandwidth['status'] ?? 'inactive',
                    'description' => $bandwidth['description'] ?? '',
                    'created_at' => $bandwidth['created_at'] ?? null
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'draw' => $this->request->getGet('draw'),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading bandwidth data: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Failed to load bandwidth data: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Create Bandwidth
     */    public function createBandwidth()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[255]',
            'download_min' => 'required|integer|greater_than[0]',
            'download_max' => 'required|integer|greater_than[0]',
            'upload_min' => 'required|integer|greater_than[0]',
            'upload_max' => 'required|integer|greater_than[0]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Validate that max >= min
            $downloadMin = (int)$this->request->getPost('download_min');
            $downloadMax = (int)$this->request->getPost('download_max');
            $uploadMin = (int)$this->request->getPost('upload_min');
            $uploadMax = (int)$this->request->getPost('upload_max');

            if ($downloadMax < $downloadMin) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Maximum download speed must be greater than or equal to minimum download speed'
                ]);
            }

            if ($uploadMax < $uploadMin) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Maximum upload speed must be greater than or equal to minimum upload speed'
                ]);
            }
            $data = [
                'name' => $this->request->getPost('name'),
                'download_min' => $downloadMin,
                'download_max' => $downloadMax,
                'upload_min' => $uploadMin,
                'upload_max' => $uploadMax,
                'status' => $this->request->getPost('status') ?: 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->bandwidthModel->insert($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Bandwidth profile created successfully'
                ]);
            } else {
                throw new \Exception('Failed to create bandwidth profile');
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Group Profile Management Page
     */
    public function groupProfile()
    {
        $data = [
            'title' => 'Group Profile Management',
            'page_title' => 'Internet Packages - Group Profile',
            'breadcrumb' => [
                'Internet Packages' => 'internet-packages',
                'Group Profile' => ''
            ]
        ];

        return view('internet-packages/group-profile', $data);
    }
    /**
     * Get Group Profile Data for DataTable
     */
    public function getGroupProfileData()
    {
        try {
            // Get group profiles without bandwidth profile data since it's been removed
            $groupProfiles = $this->groupProfileModel->findAll();

            $data = [];
            foreach ($groupProfiles as $profile) {
                // Show the selected router from data_owner field in the routers column
                $selectedRouter = $profile['data_owner'] ?: 'No router selected';

                $data[] = [
                    'id' => $profile['id'],
                    'name' => $profile['name'] ?: '-',
                    'router_type' => $profile['router_type'] ?: 'PPP',
                    'parent_pool' => $profile['parent_pool'] ?: 'NONE',
                    'ip_pool_module' => $profile['ip_pool_module'] ?: 'Radius SQL-IP-POOL ( Global )',
                    'local_address' => $profile['local_address'] ?: '172.16.1.1',
                    'ip_range_start' => $profile['ip_range_start'] ?: '172.16.1.2',
                    'ip_range_end' => $profile['ip_range_end'] ?: '172.16.1.254',
                    'routers' => $selectedRouter,
                    'description' => $profile['description'] ?: '',
                    'dns_server' => $profile['dns_server'] ?: '8.8.8.8,8.8.4.4',
                    'parent_queue' => $profile['parent_queue'] ?: '',
                    'max_users' => $profile['max_users'] ?: null,
                    'session_timeout' => $profile['session_timeout'] ?: null,
                    'idle_timeout' => $profile['idle_timeout'] ?: null,
                    'status' => $profile['status'] ?: 'active',
                    'created_at' => $profile['created_at'] ? date('d/m/Y H:i', strtotime($profile['created_at'])) : ''
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'draw' => $this->request->getGet('draw'),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load group profile data: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Failed to load group profile data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create Group Profile
     */
    public function createGroupProfile()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[255]',
            'max_users' => 'permit_empty|integer|greater_than_equal_to[0]',
            'session_timeout' => 'permit_empty|integer|greater_than_equal_to[0]',
            'idle_timeout' => 'permit_empty|integer|greater_than_equal_to[0]',
            'simultaneous_use' => 'permit_empty|integer|greater_than[0]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }
        try {
            // Check for duplicate name first
            $existingProfile = $this->groupProfileModel->where('name', $this->request->getPost('name'))->first();
            if ($existingProfile) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'A group profile with this name already exists. Please choose a different name.'
                ]);
            }

            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'data_owner' => $this->request->getPost('data_owner'),
                'router_type' => $this->request->getPost('router_type') ?: 'PPP',
                'ip_pool_module' => $this->request->getPost('ip_pool_module'),
                'parent_pool' => $this->request->getPost('parent_pool'),
                'local_address' => $this->request->getPost('local_address') ?: '172.16.1.1',
                'ip_range_start' => $this->request->getPost('ip_range_start') ?: '172.16.1.2',
                'ip_range_end' => $this->request->getPost('ip_range_end') ?: '172.16.1.254',
                'dns_server' => $this->request->getPost('dns_server') ?: '8.8.8.8,8.8.4.4',
                'parent_queue' => $this->request->getPost('parent_queue'),
                'max_users' => $this->request->getPost('max_users') ?: null,
                'session_timeout' => $this->request->getPost('session_timeout') ?: null,
                'idle_timeout' => $this->request->getPost('idle_timeout') ?: null,
                'simultaneous_use' => $this->request->getPost('simultaneous_use') ?: 1,
                'accounting_update_interval' => $this->request->getPost('accounting_update_interval') ?: 300,
                'bandwidth_profile_id' => $this->request->getPost('bandwidth_profile_id') ?: null,
                'status' => $this->request->getPost('status') ?: 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Save to database first
            $profileId = $this->groupProfileModel->insert($data);

            if (!$profileId) {
                throw new \Exception('Failed to create group profile in database');
            }            // Get the created profile with ID
            $groupProfile = $this->groupProfileModel->find($profileId);
            // Debug log
            log_message('info', 'About to sync group profile to MikroTik: ' . json_encode($groupProfile));            // Try to add to MikroTik routers with timeout protection
            try {
                log_message('info', 'Starting MikroTik sync with timeout protection');
                $mikrotikResult = $this->mikrotikService->addGroupProfileToRouters($groupProfile);
                log_message('info', 'MikroTik sync completed: ' . json_encode($mikrotikResult));
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                log_message('error', 'Exception during MikroTik sync: ' . $errorMsg);

                // Handle timeout errors specifically
                if (
                    strpos($errorMsg, 'timeout') !== false ||
                    strpos($errorMsg, 'timed out') !== false ||
                    strpos($errorMsg, 'Maximum execution time') !== false
                ) {
                    $mikrotikResult = [
                        'success' => false,
                        'message' => 'Connection timeout - Some routers may be slow or unresponsive',
                        'results' => []
                    ];
                } else {
                    $mikrotikResult = [
                        'success' => false,
                        'message' => 'Sync failed: ' . $errorMsg,
                        'results' => []
                    ];
                }
            }

            // Debug log result
            $response = [
                'success' => true,
                'message' => 'Group profile created successfully',
                'data' => $groupProfile
            ];

            // Add MikroTik sync information to response
            if ($mikrotikResult['success']) {
                $response['message'] .= ' and synced to ' . count($mikrotikResult['results']) . ' router(s)';
                $response['mikrotik_sync'] = $mikrotikResult;
            } else {
                $response['message'] .= ' but failed to sync to MikroTik routers';
                $response['mikrotik_sync'] = $mikrotikResult;
                $response['warning'] = 'Profile created locally but MikroTik sync failed';
            }

            log_message('info', 'Group profile created: ' . json_encode($response));

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Error creating group profile: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Package Profile Management Page
     */
    public function packageProfile()
    {
        $data = [
            'title' => 'Package Profile Management',
            'page_title' => 'Internet Packages - Package Profile',
            'breadcrumb' => [
                'Internet Packages' => 'internet-packages',
                'Package Profile' => ''
            ]
        ];

        return view('internet-packages/package-profile', $data);
    }
    /**
     * Get Package Profile Data for DataTable
     */    public function getPackageProfileData()
    {
        try {
            // Get data ordered by ID ascending (oldest first, newest last)
            $packageProfiles = $this->packageProfileModel->orderBy('id', 'ASC')->findAll();

            $data = [];
            foreach ($packageProfiles as $profile) {
                $data[] = [
                    'id' => $profile['id'],
                    'name' => $profile['name'],
                    'description' => $profile['description'] ?? '',
                    'bandwidth_profile_id' => $profile['bandwidth_profile_id'],
                    'group_profile_id' => $profile['group_profile_id'],
                    'bandwidth_profile' => $profile['bandwidth_profile'] ?? '',
                    'group_profile' => $profile['group_profile'] ?? '',
                    'default_profile_mikrotik' => $profile['default_profile_mikrotik'] ?? '',
                    'price' => $profile['price'], // Keep as number for frontend formatting
                    'validity_period' => $profile['validity_period'], // Keep as number
                    'grace_period' => $profile['grace_period'] ?? 0,
                    'auto_renewal' => $profile['auto_renewal'] ?? 0,
                    'status' => $profile['status'],
                    'created_at' => $profile['created_at']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'draw' => $this->request->getGet('draw'),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load package profile data: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Failed to load package profile data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create Package Profile
     */
    public function createPackageProfile()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[255]',
            'price' => 'required|decimal|greater_than[0]',
            'validity_period' => 'required|integer|greater_than[0]',
            'grace_period' => 'permit_empty|integer|greater_than_equal_to[0]',
            'burst_limit_down' => 'permit_empty|string',
            'burst_limit_up' => 'permit_empty|string',
            'burst_threshold_down' => 'permit_empty|string',
            'burst_threshold_up' => 'permit_empty|string',
            'burst_time_down' => 'permit_empty|integer',
            'burst_time_up' => 'permit_empty|integer',
            'burst_priority_down' => 'permit_empty|integer',
            'burst_priority_up' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Get profile names for legacy compatibility
            $bandwidthProfile = '';
            $groupProfile = '';

            $bandwidthProfileId = $this->request->getPost('bandwidth_profile_id');
            $groupProfileId = $this->request->getPost('group_profile_id');

            if ($bandwidthProfileId) {
                $bandwidth = $this->bandwidthModel->find($bandwidthProfileId);
                if ($bandwidth) {
                    $bandwidthProfile = $bandwidth['name'];
                }
            }

            if ($groupProfileId) {
                $group = $this->groupProfileModel->find($groupProfileId);
                if ($group) {
                    $groupProfile = $group['name'];
                }
            }

            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'bandwidth_profile_id' => $bandwidthProfileId ?: null,
                'group_profile_id' => $groupProfileId ?: null,
                'bandwidth_profile' => $bandwidthProfile,
                'group_profile' => $groupProfile,
                'default_profile_mikrotik' => $this->request->getPost('default_profile_mikrotik'),
                'price' => $this->request->getPost('price'),
                'validity_period' => $this->request->getPost('validity_period'),
                'grace_period' => $this->request->getPost('grace_period') ?: 3,
                'auto_renewal' => $this->request->getPost('auto_renewal') ?: 0,
                'status' => $this->request->getPost('status') ?: 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->packageProfileModel->insert($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Package profile created successfully'
                ]);
            } else {
                throw new \Exception('Failed to create package profile');
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format speed in Kbps/Mbps
     */
    private function formatSpeed($speed)
    {
        if ($speed >= 1024) {
            return number_format($speed / 1024, 1) . ' Mbps';
        }
        return $speed . ' Kbps';
    }
    /**
     * Update Bandwidth
     */
    public function updateBandwidth($id)
    {
        // Debug logging
        log_message('info', 'updateBandwidth called with ID: ' . $id);
        log_message('info', 'Request method: ' . $this->request->getMethod());
        log_message('info', 'Request data: ' . json_encode($this->request->getPost()));
        log_message('info', 'Raw input: ' . $this->request->getBody());

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[255]',
            'download_min' => 'required|integer|greater_than[0]',
            'download_max' => 'required|integer|greater_than[0]',
            'upload_min' => 'required|integer|greater_than[0]',
            'upload_max' => 'required|integer|greater_than[0]'
        ]);

        // For PUT requests, we need to parse the input differently
        $inputData = [];
        if ($this->request->getMethod() === 'put') {
            parse_str($this->request->getBody(), $inputData);
            log_message('info', 'Parsed PUT data: ' . json_encode($inputData));
        } else {
            $inputData = $this->request->getPost();
        }

        if (!$validation->run($inputData)) {
            log_message('warning', 'Validation failed: ' . json_encode($validation->getErrors()));
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Validate that max >= min
            $downloadMin = (int)($inputData['download_min'] ?? 0);
            $downloadMax = (int)($inputData['download_max'] ?? 0);
            $uploadMin = (int)($inputData['upload_min'] ?? 0);
            $uploadMax = (int)($inputData['upload_max'] ?? 0);

            if ($downloadMax < $downloadMin) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Maximum download speed must be greater than or equal to minimum download speed'
                ]);
            }

            if ($uploadMax < $uploadMin) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Maximum upload speed must be greater than or equal to minimum upload speed'
                ]);
            }

            $data = [
                'name' => $inputData['name'],
                'download_min' => $downloadMin,
                'download_max' => $downloadMax,
                'upload_min' => $uploadMin,
                'upload_max' => $uploadMax,
                'status' => $inputData['status'] ?? 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('info', 'Update data: ' . json_encode($data));

            if ($this->bandwidthModel->update($id, $data)) {
                log_message('info', 'Bandwidth profile updated successfully');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Bandwidth profile updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update bandwidth profile');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in updateBandwidth: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Delete Bandwidth
     */
    public function deleteBandwidth($id)
    {
        // Verify CSRF token for security
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Direct access not allowed'
            ]);
        }

        // Verify user session
        if (!session('id_user')) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }

        try {
            // Validate ID parameter
            if (!$id || !is_numeric($id)) {
                log_message('error', 'Delete bandwidth: Invalid ID parameter: ' . $id);
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid bandwidth profile ID'
                ]);
            }

            // Check if bandwidth profile exists
            $bandwidth = $this->bandwidthModel->find($id);
            log_message('info', 'Bandwidth profile found: ' . ($bandwidth ? 'true' : 'false'));
            if (!$bandwidth) {
                log_message('error', 'Delete bandwidth: Profile not found for ID: ' . $id);
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Bandwidth profile dengan ID ' . $id . ' tidak ditemukan'
                ]);
            }

            // Check if bandwidth profile is being used by group profiles
            $groupProfileModel = new \App\Models\GroupProfileModel();
            $usedByGroups = $groupProfileModel->where('bandwidth_profile_id', $id)->countAllResults();

            if ($usedByGroups > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Cannot delete bandwidth profile. It is being used by ' . $usedByGroups . ' group profile(s).'
                ]);
            }

            // Perform the deletion
            if ($this->bandwidthModel->delete($id)) {
                log_message('info', 'Bandwidth profile deleted successfully. ID: ' . $id . ', User: ' . session('username'));
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Bandwidth profile deleted successfully'
                ]);
            } else {
                throw new \Exception('Failed to delete bandwidth profile from database');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting bandwidth profile: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single bandwidth profile
     */
    public function getBandwidth($id)
    {
        // Debug logging
        log_message('info', 'getBandwidth called with ID: ' . $id);
        log_message('info', 'Request method: ' . $this->request->getMethod());
        log_message('info', 'User session: ' . (session('id_user') ? 'exists' : 'not found'));

        try {
            $bandwidth = $this->bandwidthModel->find($id);

            if (!$bandwidth) {
                log_message('warning', 'Bandwidth profile not found for ID: ' . $id);
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Bandwidth profile not found'
                ]);
            }

            log_message('info', 'Bandwidth profile found: ' . json_encode($bandwidth));
            return $this->response->setJSON([
                'success' => true,
                'data' => $bandwidth
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in getBandwidth: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single group profile
     */
    public function getGroupProfile($id)
    {
        try {
            $groupProfile = $this->groupProfileModel->find($id);

            if (!$groupProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Group profile not found'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $groupProfile
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Update Group Profile
     */    public function updateGroupProfile($id)
    {
        try {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'name' => 'required|min_length[3]|max_length[255]'
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ]);
            }

            // Check if the record exists first
            $existing = $this->groupProfileModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Group profile not found'
                ]);
            }

            // Check for duplicate name (but exclude current record)
            $newName = $this->request->getPost('name');
            if ($newName !== $existing['name']) {
                $existingProfile = $this->groupProfileModel->where('name', $newName)->first();
                if ($existingProfile) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'A group profile with this name already exists. Please choose a different name.'
                    ]);
                }
            }

            $data = [
                'name' => $newName,
                'data_owner' => $this->request->getPost('data_owner'),
                'router_type' => $this->request->getPost('router_type') ?: 'PPP',
                'local_address' => $this->request->getPost('local_address'),
                'ip_range_start' => $this->request->getPost('ip_range_start'),
                'ip_range_end' => $this->request->getPost('ip_range_end'),
                'dns_server' => $this->request->getPost('dns_server'),
                'parent_queue' => $this->request->getPost('parent_queue'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if ($this->groupProfileModel->update($id, $data)) {                // Get updated group profile for MikroTik sync
                $updatedProfile = $this->groupProfileModel->find($id);
                // Debug log
                log_message('info', 'About to sync updated group profile to MikroTik: ' . json_encode($updatedProfile));                // Try to sync to MikroTik routers with timeout protection
                try {
                    log_message('info', 'Starting MikroTik update sync with timeout protection');
                    $mikrotikResult = $this->mikrotikService->updateGroupProfileOnRouters($updatedProfile);
                    log_message('info', 'MikroTik update sync completed: ' . json_encode($mikrotikResult));
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    log_message('error', 'Exception during MikroTik update sync: ' . $errorMsg);

                    // Handle timeout errors specifically
                    if (
                        strpos($errorMsg, 'timeout') !== false ||
                        strpos($errorMsg, 'timed out') !== false ||
                        strpos($errorMsg, 'Maximum execution time') !== false
                    ) {
                        $mikrotikResult = [
                            'success' => false,
                            'message' => 'Connection timeout - Some routers may be slow or unresponsive',
                            'results' => []
                        ];
                    } else {
                        $mikrotikResult = [
                            'success' => false,
                            'message' => 'Sync failed: ' . $errorMsg,
                            'results' => []
                        ];
                    }
                }

                // Debug log result
                $response = [
                    'success' => true,
                    'message' => 'Group profile updated successfully',
                    'data' => $updatedProfile
                ];

                // Add MikroTik sync information to response
                if ($mikrotikResult['success']) {
                    $response['message'] .= ' and synced to ' . count($mikrotikResult['results']) . ' router(s)';
                    $response['mikrotik_sync'] = $mikrotikResult;
                } else {
                    $response['message'] .= ' but failed to sync to MikroTik routers';
                    $response['mikrotik_sync'] = $mikrotikResult;
                    $response['warning'] = 'Profile updated locally but MikroTik sync failed';
                }

                return $this->response->setJSON($response);
            } else {
                $errors = $this->groupProfileModel->errors();
                throw new \Exception('Failed to update group profile: ' . json_encode($errors));
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Delete Group Profile
     */
    public function deleteGroupProfile($id)
    {
        // Ensure this is an AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        try {
            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid group profile ID'
                ]);
            }

            // Check if group profile exists
            $existingProfile = $this->groupProfileModel->find($id);
            if (!$existingProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Group profile not found'
                ]);
            }            // Attempt to delete
            if ($this->groupProfileModel->delete($id)) {
                // Try to remove from MikroTik routers with proper error handling
                $mikrotikResult = [];
                try {
                    $mikrotikResult = $this->mikrotikService->removeGroupProfileFromRouters($existingProfile['name']);
                } catch (\Exception $e) {
                    log_message('error', 'MikroTik removal failed during delete: ' . $e->getMessage());
                    // Continue with successful delete response even if MikroTik sync fails
                }

                // Log the deletion
                log_message('info', "Group profile deleted: ID {$id} by user " . session('user_id'));

                $response = [
                    'success' => true,
                    'message' => 'Group profile deleted successfully'
                ];

                // Add MikroTik removal information if available
                if (!empty($mikrotikResult) && is_array($mikrotikResult)) {
                    if (isset($mikrotikResult['results']) && is_array($mikrotikResult['results'])) {
                        $successCount = count(array_filter($mikrotikResult['results'], function ($r) {
                            return isset($r['success']) && $r['success'];
                        }));
                        $totalCount = count($mikrotikResult['results']);

                        if ($successCount > 0) {
                            $response['message'] .= " and removed from {$successCount}/{$totalCount} router(s)";
                        } else {
                            $response['warning'] = 'Profile deleted locally but failed to remove from MikroTik routers';
                        }
                        $response['mikrotik_sync'] = $mikrotikResult;
                    }
                }

                return $this->response->setJSON($response);
            } else {
                throw new \Exception('Failed to delete group profile from database');
            }
        } catch (\Exception $e) {
            // Log the error
            log_message('error', 'Error deleting group profile: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Sync Group Profile to MikroTik Routers
     */
    public function syncGroupProfileToMikroTik($id)
    {
        try {
            // Validate ID
            if (empty($id) || !is_numeric($id)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid group profile ID'
                ]);
            }

            // Get group profile
            $groupProfile = $this->groupProfileModel->find($id);
            if (!$groupProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Group profile not found'
                ]);
            }

            // Sync to MikroTik routers
            $mikrotikResult = $this->mikrotikService->addGroupProfileToRouters($groupProfile);

            return $this->response->setJSON([
                'success' => $mikrotikResult['success'],
                'message' => $mikrotikResult['message'],
                'details' => $mikrotikResult['results']
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync All Group Profiles to MikroTik Routers
     */
    public function syncAllGroupProfilesToMikroTik()
    {
        try {
            // Get all group profiles
            $groupProfiles = $this->groupProfileModel->findAll();

            if (empty($groupProfiles)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No group profiles found to sync'
                ]);
            }

            $allResults = [];

            foreach ($groupProfiles as $profile) {
                $result = $this->mikrotikService->addGroupProfileToRouters($profile);
                $allResults[] = [
                    'profile' => $profile['name'],
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'router_results' => $result['results'] ?? []
                ];
            }

            $successCount = count(array_filter($allResults, function ($r) {
                return $r['success'];
            }));
            $totalCount = count($allResults);

            return $this->response->setJSON([
                'success' => $successCount > 0,
                'message' => "Synchronized {$successCount}/{$totalCount} group profiles to all active routers",
                'results' => $allResults
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Get Router Data for Dropdown
     */
    public function getRouterData()
    {
        try {
            log_message('info', 'getRouterData called');

            $lokasiServerModel = new \App\Models\ServerLocationModel();
            $routers = $lokasiServerModel->findAll();

            log_message('info', 'Found ' . count($routers) . ' routers');

            $data = [];
            foreach ($routers as $router) {
                // Safely check for name fields
                $routerName = '';
                if (isset($router['name']) && !empty($router['name'])) {
                    $routerName = $router['name'];
                } elseif (isset($router['nama']) && !empty($router['nama'])) {
                    $routerName = $router['nama'];
                } else {
                    $routerName = 'Router-' . $router['id_lokasi'];
                }

                $data[] = [
                    'id_lokasi' => $router['id_lokasi'],
                    'name' => $routerName,
                    'ip_router' => isset($router['ip_router']) ? $router['ip_router'] : 'N/A',
                    'ping_status' => isset($router['ping_status']) ? $router['ping_status'] : 'unknown',
                    'is_connected' => isset($router['is_connected']) ? $router['is_connected'] : '0'
                ];
            }

            log_message('info', 'Returning router data: ' . json_encode($data));

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load router data: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Failed to load router data: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Debug method to list all bandwidth profiles
     */
    public function listBandwidthProfiles()
    {
        try {
            $bandwidths = $this->bandwidthModel->findAll();

            return $this->response->setJSON([
                'success' => true,
                'count' => count($bandwidths),
                'data' => $bandwidths
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync specific Group Profile to specific MikroTik Router
     */
    public function syncGroupProfileToSpecificRouter()
    {
        try {
            $profileId = $this->request->getPost('profile_id');
            $routerId = $this->request->getPost('router_id');

            if (empty($profileId) || empty($routerId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Profile ID and Router ID are required'
                ]);
            }

            // Get group profile
            $groupProfile = $this->groupProfileModel->find($profileId);
            if (!$groupProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Group profile not found'
                ]);
            }

            // Get router
            $lokasiServerModel = new \App\Models\ServerLocationModel();
            $router = $lokasiServerModel->find($routerId);
            if (!$router) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Router not found'
                ]);
            }

            // Sync to router
            $result = $this->mikrotikService->addGroupProfileToRouter($router, $groupProfile);

            return $this->response->setJSON([
                'success' => $result['success'],
                'message' => $result['message'],
                'details' => $result['details'] ?? []
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get MikroTik profiles created by billingku from specific router
     */
    public function getMikroTikProfilesFromRouter($routerId)
    {
        try {
            if (empty($routerId) || !is_numeric($routerId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid router ID'
                ]);
            }

            $result = $this->mikrotikService->getBillingkuProfilesFromRouter($routerId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up orphaned profiles from specific router
     */
    public function cleanupMikroTikProfiles($routerId)
    {
        try {
            if (empty($routerId) || !is_numeric($routerId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid router ID'
                ]);
            }

            $result = $this->mikrotikService->cleanupOrphanedProfilesFromRouter($routerId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync all Group Profiles to specific Router
     */
    public function syncAllGroupProfilesToSpecificRouter($routerId)
    {
        try {
            if (empty($routerId) || !is_numeric($routerId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid router ID'
                ]);
            }

            $result = $this->mikrotikService->syncAllGroupProfilesToRouter($routerId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verify group profile on specific router
     */
    public function verifyGroupProfileOnRouter()
    {
        try {
            $profileId = $this->request->getPost('profile_id');
            $routerId = $this->request->getPost('router_id');

            if (empty($profileId) || empty($routerId)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Profile ID and Router ID are required'
                ]);
            }

            $result = $this->mikrotikService->verifyGroupProfileOnRouter($routerId, $profileId);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Package Profile by ID
     */
    public function getPackageProfile($id)
    {
        try {
            $packageProfile = $this->packageProfileModel->find($id);

            if (!$packageProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Package profile not found'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $packageProfile
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Update Package Profile
     */    public function updatePackageProfile($id)
    {
        // Debug logging
        log_message('info', 'updatePackageProfile called with ID: ' . $id);
        log_message('info', 'Request method: ' . $this->request->getMethod());

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[255]',
            'price' => 'required|decimal|greater_than[0]',
            'validity_period' => 'required|integer|greater_than[0]',
            'grace_period' => 'permit_empty|integer|greater_than_equal_to[0]',
            'burst_limit_down' => 'permit_empty|string',
            'burst_limit_up' => 'permit_empty|string',
            'burst_threshold_down' => 'permit_empty|string',
            'burst_threshold_up' => 'permit_empty|string',
            'burst_time_down' => 'permit_empty|integer',
            'burst_time_up' => 'permit_empty|integer',
            'burst_priority_down' => 'permit_empty|integer',
            'burst_priority_up' => 'permit_empty|integer'
        ]);

        // Handle PUT request data parsing more reliably
        $inputData = [];
        if ($this->request->getMethod() === 'put' || $this->request->getMethod() === 'PUT') {
            $rawInput = $this->request->getBody();
            log_message('info', 'Raw PUT input: ' . $rawInput);

            // Try to parse URL-encoded data
            parse_str($rawInput, $inputData);

            // If parsing failed or data is empty, try JSON parsing
            if (empty($inputData)) {
                $jsonData = json_decode($rawInput, true);
                if ($jsonData) {
                    $inputData = $jsonData;
                }
            }

            // Remove CSRF token from validation data
            unset($inputData[csrf_token()]);
        } else {
            $inputData = $this->request->getPost();
        }

        log_message('info', 'Parsed input data: ' . json_encode($inputData));

        if (!$validation->run($inputData)) {
            log_message('error', 'Validation failed: ' . json_encode($validation->getErrors()));
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }
        try {
            // Check if package profile exists
            $existingProfile = $this->packageProfileModel->find($id);
            if (!$existingProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Package profile not found'
                ]);
            }

            // Get profile names for legacy compatibility// Get profile names for legacy compatibility
            $bandwidthProfile = '';
            $groupProfile = '';

            $bandwidthProfileId = $inputData['bandwidth_profile_id'] ?? null;
            $groupProfileId = $inputData['group_profile_id'] ?? null;

            if ($bandwidthProfileId) {
                $bandwidth = $this->bandwidthModel->find($bandwidthProfileId);
                if ($bandwidth) {
                    $bandwidthProfile = $bandwidth['name'];
                }
            }

            if ($groupProfileId) {
                $group = $this->groupProfileModel->find($groupProfileId);
                if ($group) {
                    $groupProfile = $group['name'];
                }
            }

            $data = [
                'name' => $inputData['name'],
                'description' => $inputData['description'] ?? '',
                'bandwidth_profile_id' => $bandwidthProfileId ?: null,
                'group_profile_id' => $groupProfileId ?: null,
                'bandwidth_profile' => $bandwidthProfile,
                'group_profile' => $groupProfile,
                'default_profile_mikrotik' => $inputData['default_profile_mikrotik'] ?? null,
                'price' => $inputData['price'],
                'validity_period' => $inputData['validity_period'],
                'grace_period' => $inputData['grace_period'] ?? 0,
                'auto_renewal' => $inputData['auto_renewal'] ?? 0,
                'status' => $inputData['status'] ?? 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->packageProfileModel->update($id, $data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Package profile updated successfully'
                ]);
            } else {
                throw new \Exception('Failed to update package profile');
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete Package Profile
     */
    public function deletePackageProfile($id)
    {
        try {
            // Check if package profile exists
            $packageProfile = $this->packageProfileModel->find($id);
            if (!$packageProfile) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Package profile not found'
                ]);
            }

            // TODO: Check if package profile is being used by customers
            // This should be implemented when customer management is available

            if ($this->packageProfileModel->delete($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Package profile deleted successfully'
                ]);
            } else {
                throw new \Exception('Failed to delete package profile');
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get MikroTik Profiles for synchronization
     */
    public function getMikroTikProfiles()
    {
        try {
            // Import MikroTik API library
            $mikrotikAPI = new \App\Libraries\MikrotikAPI();

            if (!$mikrotikAPI->isConnected()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak dapat terhubung ke MikroTik: ' . $mikrotikAPI->getLastError()
                ]);
            }

            // Get PPP profiles from MikroTik
            $profiles = [];
            $client = $mikrotikAPI->getClient();

            // Get PPP profiles
            $pppProfiles = $client->comm('/ppp/profile/print');
            $profiles['ppp_profiles'] = [];

            if (is_array($pppProfiles)) {
                foreach ($pppProfiles as $profile) {
                    if (isset($profile['name'])) {
                        $profiles['ppp_profiles'][] = [
                            'name' => $profile['name'],
                            'local-address' => $profile['local-address'] ?? '',
                            'remote-address' => $profile['remote-address'] ?? '',
                            'rate-limit' => $profile['rate-limit'] ?? '',
                            'session-timeout' => $profile['session-timeout'] ?? '',
                            'idle-timeout' => $profile['idle-timeout'] ?? '',
                            'incoming-filter' => $profile['incoming-filter'] ?? '',
                            'outgoing-filter' => $profile['outgoing-filter'] ?? ''
                        ];
                    }
                }
            }

            // Get Queue Simple profiles (for bandwidth management)
            $queueProfiles = $client->comm('/queue/simple/print');
            $profiles['queue_profiles'] = [];

            if (is_array($queueProfiles)) {
                foreach ($queueProfiles as $queue) {
                    if (isset($queue['name'])) {
                        $profiles['queue_profiles'][] = [
                            'name' => $queue['name'],
                            'target' => $queue['target'] ?? '',
                            'max-limit' => $queue['max-limit'] ?? '',
                            'limit-at' => $queue['limit-at'] ?? '',
                            'burst-limit' => $queue['burst-limit'] ?? '',
                            'burst-threshold' => $queue['burst-threshold'] ?? '',
                            'burst-time' => $queue['burst-time'] ?? '',
                            'priority' => $queue['priority'] ?? ''
                        ];
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $profiles,
                'ppp_count' => count($profiles['ppp_profiles']),
                'queue_count' => count($profiles['queue_profiles'])
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get MikroTik profiles: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil profile dari MikroTik: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync MikroTik profiles to database
     */
    public function syncMikroTikProfiles()
    {
        try {
            $profileType = $this->request->getPost('profile_type'); // 'ppp' or 'queue'
            $selectedProfiles = $this->request->getPost('selected_profiles'); // array of profile names

            if (!$profileType || !$selectedProfiles || !is_array($selectedProfiles)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap'
                ]);
            }

            // Get MikroTik profiles first
            $mikrotikAPI = new \App\Libraries\MikrotikAPI();

            if (!$mikrotikAPI->isConnected()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak dapat terhubung ke MikroTik: ' . $mikrotikAPI->getLastError()
                ]);
            }

            $client = $mikrotikAPI->getClient();
            $synced = 0;
            $errors = [];

            foreach ($selectedProfiles as $profileName) {
                try {
                    if ($profileType === 'ppp') {
                        // Sync PPP profile to group_profiles table
                        $pppProfile = $client->comm('/ppp/profile/print', ['?name=' . $profileName]);

                        if (!empty($pppProfile) && isset($pppProfile[0])) {
                            $profile = $pppProfile[0];

                            // Check if profile already exists
                            $existing = $this->groupProfileModel->where('name', $profileName)->first();

                            $profileData = [
                                'name' => $profileName,
                                'local_address' => $profile['local-address'] ?? '',
                                'remote_address' => $profile['remote-address'] ?? '',
                                'rate_limit' => $profile['rate-limit'] ?? '',
                                'session_timeout' => $profile['session-timeout'] ?? '',
                                'idle_timeout' => $profile['idle-timeout'] ?? '',
                                'incoming_filter' => $profile['incoming-filter'] ?? '',
                                'outgoing_filter' => $profile['outgoing-filter'] ?? '',
                                'description' => 'Synced from MikroTik PPP Profile',
                                'updated_at' => date('Y-m-d H:i:s')
                            ];

                            if ($existing) {
                                // Update existing profile
                                $this->groupProfileModel->update($existing['id'], $profileData);
                            } else {
                                // Create new profile
                                $profileData['created_at'] = date('Y-m-d H:i:s');
                                $this->groupProfileModel->insert($profileData);
                            }

                            $synced++;
                        }
                    } elseif ($profileType === 'queue') {
                        // Sync Queue profile to bandwidth_profiles table (if exists)
                        $queueProfile = $client->comm('/queue/simple/print', ['?name=' . $profileName]);

                        if (!empty($queueProfile) && isset($queueProfile[0])) {
                            $queue = $queueProfile[0];

                            // Parse max-limit and limit-at to extract speeds
                            $maxLimit = $queue['max-limit'] ?? '';
                            $limitAt = $queue['limit-at'] ?? '';

                            // Example: "10M/5M" -> download: 10M, upload: 5M
                            $downloadMax = $uploadMax = $downloadMin = $uploadMin = 0;

                            if (preg_match('/^(\d+[KMG]?)\/(\d+[KMG]?)$/', $maxLimit, $maxMatches)) {
                                $downloadMax = $this->parseSpeedToKbps($maxMatches[1]);
                                $uploadMax = $this->parseSpeedToKbps($maxMatches[2]);
                            }

                            if (preg_match('/^(\d+[KMG]?)\/(\d+[KMG]?)$/', $limitAt, $limitMatches)) {
                                $downloadMin = $this->parseSpeedToKbps($limitMatches[1]);
                                $uploadMin = $this->parseSpeedToKbps($limitMatches[2]);
                            }

                            // Check if bandwidth profile already exists
                            $existing = $this->bandwidthModel->where('name', $profileName)->first();

                            $bandwidthData = [
                                'name' => $profileName,
                                'download_max' => $downloadMax,
                                'upload_max' => $uploadMax,
                                'download_min' => $downloadMin,
                                'upload_min' => $uploadMin,
                                'burst_limit' => $queue['burst-limit'] ?? '',
                                'burst_threshold' => $queue['burst-threshold'] ?? '',
                                'burst_time' => $queue['burst-time'] ?? '',
                                'priority' => $queue['priority'] ?? '',
                                'description' => 'Synced from MikroTik Queue Profile',
                                'updated_at' => date('Y-m-d H:i:s')
                            ];

                            if ($existing) {
                                // Update existing profile
                                $this->bandwidthModel->update($existing['id'], $bandwidthData);
                            } else {
                                // Create new profile
                                $bandwidthData['created_at'] = date('Y-m-d H:i:s');
                                $this->bandwidthModel->insert($bandwidthData);
                            }

                            $synced++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Profile {$profileName}: " . $e->getMessage();
                }
            }

            $message = "Berhasil sinkronisasi {$synced} profile";
            if (!empty($errors)) {
                $message .= ". Error: " . implode(', ', $errors);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'synced_count' => $synced,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to sync MikroTik profiles: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal sinkronisasi profile: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Helper method to parse speed string to Kbps
     */
    private function parseSpeedToKbps($speedStr)
    {
        $speedStr = strtoupper(trim($speedStr));

        if (preg_match('/^(\d+)([KMG]?)$/', $speedStr, $matches)) {
            $value = intval($matches[1]);
            $unit = $matches[2] ?? '';

            switch ($unit) {
                case 'G':
                    return $value * 1024 * 1024;
                case 'M':
                    return $value * 1024;
                case 'K':
                    return $value;
                default:
                    return $value; // Assume Kbps if no unit
            }
        }
        return 0;
    }
}
