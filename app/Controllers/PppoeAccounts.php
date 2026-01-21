<?php

namespace App\Controllers;

use App\Libraries\MikrotikNew as Mikrotik;

class PppoeAccounts extends BaseController
{
    protected $pppoeModel;
    protected $serverModel;

    public function __construct()
    {
        $this->pppoeModel = model('PppoeAccountModel');
        $this->serverModel = model('ServerLocationModel');
    }

    public function index()
    {
        $data = [
            'title' => 'Router OS | PPOE',
            'servers' => $this->serverModel->findAll()
        ];

        return view('pppoe/index', $data);
    }

    public function create()
    {
        $customerModel = model('CustomerModel');

        $data = [
            'title' => 'Create PPPoE Account',
            'servers' => $this->serverModel->findAll(),
            'customers' => $customerModel->select('id_customers, nama_pelanggan, nomor_layanan, pppoe_username')
                ->where('login', 'enable')
                ->orderBy('nama_pelanggan', 'ASC')
                ->findAll()
        ];

        return view('pppoe/create', $data);
    }

    /**
     * Auto-create PPPoE account from customer ID
     * Username: nomor_layanan@dfhm.id
     * Password: nomor_layanan
     */
    public function createAccount($customerId)
    {
        try {
            $customerModel = model('CustomerModel');
            $packageProfileModel = model('PackageProfileModel');

            // Get customer with package profile details
            $customer = $customerModel
                ->select('customers.*, package_profiles.default_profile_mikrotik, package_profiles.bandwidth_profile, package_profiles.name as package_name')
                ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
                ->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }

            // Validate nomor_layanan exists
            if (empty($customer['nomor_layanan'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer does not have nomor_layanan (service number)'
                ]);
            }

            // Get server location - use customer's assigned server or get default server
            $serverId = null;
            if (!empty($customer['id_lokasi_server'])) {
                $serverId = $customer['id_lokasi_server'];
            } else {
                // Get first available server as default
                $defaultServer = $this->serverModel->first();
                if (!$defaultServer) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No router/server available in system. Please add a server first.'
                    ]);
                }
                $serverId = $defaultServer['id_lokasi'];

                // Auto-assign server to customer
                $customerModel->update($customerId, [
                    'id_lokasi_server' => $serverId
                ]);
            }

            // Generate username and password
            $username = $customer['nomor_layanan'] . '@dfhm.id';
            $password = $customer['nomor_layanan'];

            // Get server first to create MikroTik account
            $server = $this->serverModel->find($serverId);
            if (!$server) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Server not found'
                ]);
            }

            // Create PPPoE Secret in MikroTik first
            $mikrotikSuccess = false;
            $mikrotikMessage = '';

            try {
                $mikrotik = new Mikrotik();

                // Use correct IP field and port
                $hostString = $server['local_ip'] ?? $server['ip_router'] ?? $server['ip_address'];

                // Parse host:port if provided in hostString
                $host = $hostString;
                $port = $server['port_api'] ?? 8728;

                if (strpos($hostString, ':') !== false) {
                    $parts = explode(':', $hostString);
                    $host = $parts[0];
                    $port = (int)$parts[1];
                }

                log_message('info', 'Connecting to MikroTik: ' . $host . ':' . $port . ' with user: ' . $server['username']);

                if ($mikrotik->connect($host, $server['username'], $server['password'], $port)) {
                    // Determine profile to use - priority: default_profile_mikrotik > bandwidth_profile > default
                    $profileName = 'default';
                    if (!empty($customer['default_profile_mikrotik'])) {
                        $profileName = $customer['default_profile_mikrotik'];
                        log_message('info', 'Using MikroTik profile from package: ' . $profileName);
                    } elseif (!empty($customer['bandwidth_profile'])) {
                        $profileName = $customer['bandwidth_profile'];
                        log_message('info', 'Using bandwidth profile: ' . $profileName);
                    } else {
                        log_message('warning', 'No profile found for customer, using default');
                    }

                    // First, check if secret already exists
                    $existingSecret = $mikrotik->comm('/ppp/secret/print', [
                        '?name' => $username
                    ]);

                    log_message('info', 'Checking existing secret: ' . json_encode($existingSecret));

                    if (!empty($existingSecret) && is_array($existingSecret) && count($existingSecret) > 0) {
                        // Secret already exists, update it instead
                        $secretId = $existingSecret[0]['.id'] ?? null;

                        if ($secretId) {
                            log_message('info', 'PPP secret already exists with ID: ' . $secretId . ', updating password and profile...');

                            $updateParams = [
                                '.id' => $secretId,
                                'password' => $password,
                                'service' => 'pppoe',
                                'profile' => $profileName
                            ];

                            $response = $mikrotik->comm('/ppp/secret/set', $updateParams);

                            log_message('info', 'PPP secret update response: ' . json_encode($response));

                            $mikrotikSuccess = true;
                            $mikrotikMessage = 'PPPoE secret already exists, password and profile updated (ID: ' . $secretId . ')';
                            $pppoeId = $secretId;

                            // Save the ID
                            $customerModel->update($customerId, [
                                'pppoe_id' => $pppoeId
                            ]);
                        } else {
                            $mikrotikSuccess = false;
                            $mikrotikMessage = 'Secret exists but cannot get ID for update';
                        }
                    } else {
                        // Secret doesn't exist, create new one
                        // Prepare parameters for PPP Secret
                        $params = [
                            'name' => $username,
                            'password' => $password,
                            'service' => 'pppoe',
                            'profile' => $profileName
                        ];

                        log_message('info', 'Adding PPP secret with params: ' . json_encode($params));

                        // Add PPP Secret to MikroTik
                        $response = $mikrotik->comm('/ppp/secret/add', $params);

                        log_message('info', 'PPP secret response: ' . json_encode($response));

                        // Check for error response
                        if (is_array($response) && isset($response[0]) && isset($response[0]['message'])) {
                            // Error from MikroTik - check if it's "already exists" error
                            $errorMsg = $response[0]['message'];

                            if (strpos($errorMsg, 'already exists') !== false) {
                                // Secret exists but wasn't detected in initial check, try to find and update it
                                log_message('info', 'Secret already exists according to error, attempting to find and update...');

                                $existingSecret = $mikrotik->comm('/ppp/secret/print', [
                                    '?name' => $username
                                ]);

                                if (!empty($existingSecret) && is_array($existingSecret) && isset($existingSecret[0]['.id'])) {
                                    $secretId = $existingSecret[0]['.id'];

                                    $updateParams = [
                                        '.id' => $secretId,
                                        'password' => $password,
                                        'service' => 'pppoe',
                                        'profile' => $profileName
                                    ];

                                    $updateResponse = $mikrotik->comm('/ppp/secret/set', $updateParams);

                                    log_message('info', 'Updated existing secret after detection: ' . json_encode($updateResponse));

                                    $mikrotikSuccess = true;
                                    $mikrotikMessage = 'PPPoE secret already exists, password and profile updated';
                                    $pppoeId = $secretId;

                                    // Save the ID
                                    $customerModel->update($customerId, [
                                        'pppoe_id' => $pppoeId
                                    ]);
                                } else {
                                    $mikrotikSuccess = false;
                                    $mikrotikMessage = 'MikroTik error: ' . $errorMsg;
                                    log_message('error', 'Could not find existing secret for update');
                                }
                            } else {
                                // Other error from MikroTik
                                $mikrotikSuccess = false;
                                $mikrotikMessage = 'MikroTik error: ' . $errorMsg;
                                log_message('error', 'MikroTik returned error: ' . json_encode($response));
                            }
                        } else {
                            // Get the PPPoE ID from MikroTik response
                            $pppoeId = null;
                            if (is_array($response) && isset($response['after']['ret'])) {
                                $pppoeId = $response['after']['ret'];
                            } elseif (is_array($response) && isset($response[0]['ret'])) {
                                $pppoeId = $response[0]['ret'];
                            } elseif (is_string($response)) {
                                $pppoeId = $response; // Sometimes returns just the ID string
                            }

                            // If we got the ID, save it
                            if ($pppoeId) {
                                $customerModel->update($customerId, [
                                    'pppoe_id' => $pppoeId
                                ]);
                                log_message('info', 'PPPoE ID saved: ' . $pppoeId);
                            } else {
                                log_message('warning', 'Could not extract PPPoE ID from response');
                            }

                            $mikrotikSuccess = true;
                            $mikrotikMessage = 'PPPoE secret created in MikroTik' . ($pppoeId ? ' (ID: ' . $pppoeId . ')' : '');

                            log_message('info', 'PPP secret created successfully');
                        }
                    }
                } else {
                    $mikrotikMessage = 'Could not connect to MikroTik router at ' . $host . ':' . $port;
                    log_message('error', $mikrotikMessage);
                }
            } catch (\Exception $e) {
                $mikrotikMessage = 'MikroTik error: ' . $e->getMessage();
                log_message('error', 'MikroTik exception: ' . $e->getMessage());
            }

            // Update customer table with pppoe_username and pppoe_password
            // Also save to pppoe_accounts table if successful
            if (!$mikrotikSuccess) {
                // Only update if MikroTik creation failed (to store credentials for retry)
                $customerModel->update($customerId, [
                    'pppoe_username' => $username,
                    'pppoe_password' => $password,
                    'id_lokasi_server' => $serverId
                ]);
            } else {
                // Successfully created in MikroTik - update customer and save to pppoe_accounts
                $customerModel->update($customerId, [
                    'pppoe_username' => $username,
                    'pppoe_password' => $password,
                    'id_lokasi_server' => $serverId
                ]);

                // Save to pppoe_accounts table
                $pppoeAccountData = [
                    'server_id' => $serverId,
                    'customer_id' => $customerId,
                    'pppoe_id' => $pppoeId ?? null,
                    'username' => $username,
                    'password' => $password,
                    'profile_name' => $profileName,
                    'status' => 'active',
                    'disabled' => 0
                ];

                // Check if account already exists in pppoe_accounts table
                $existingAccount = $this->pppoeModel->where('username', $username)->first();
                if ($existingAccount) {
                    // Update existing record
                    $this->pppoeModel->update($existingAccount['id'], $pppoeAccountData);
                    log_message('info', 'Updated existing pppoe_accounts record for username: ' . $username);
                } else {
                    // Insert new record
                    $this->pppoeModel->insert($pppoeAccountData);
                    log_message('info', 'Inserted new pppoe_accounts record for username: ' . $username);
                }
            }

            return $this->response->setJSON([
                'success' => $mikrotikSuccess,
                'message' => $mikrotikSuccess
                    ? 'PPPoE account created successfully in MikroTik. Username: ' . $username
                    : 'Failed to create PPPoE account in MikroTik: ' . $mikrotikMessage,
                'data' => [
                    'username' => $username,
                    'password' => $password,
                    'mikrotik_status' => $mikrotikSuccess,
                    'mikrotik_message' => $mikrotikMessage,
                    'server' => $server['nama_lokasi'] ?? 'Unknown'
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PPPoE createAccount error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create PPPoE account: ' . $e->getMessage()
            ]);
        }
    }

    public function getData()
    {
        try {
            $request = service('request');

            $draw = intval($request->getPost('draw'));
            $start = intval($request->getPost('start'));
            $length = intval($request->getPost('length'));
            $searchValue = $request->getPost('search')['value'] ?? '';

            $filterServer = $request->getPost('filterServer');
            $filterDisabled = $request->getPost('filterDisabled');

            $builder = $this->pppoeModel->builder();
            $builder->select('pppoe_accounts.*, 
                COALESCE(lokasi_server.name, customer_server.name) as branch_name, 
                customers.nama_pelanggan as customer_name');
            $builder->join('lokasi_server', 'lokasi_server.id_lokasi = pppoe_accounts.server_id', 'left');
            $builder->join('customers', 'customers.id_customers = pppoe_accounts.customer_id', 'left');
            $builder->join('lokasi_server as customer_server', 'customer_server.id_lokasi = customers.id_lokasi_server', 'left');

            // Exclude soft deleted records
            $builder->where('pppoe_accounts.deleted_at', null);

            // Apply filters
            if ($filterServer && $filterServer != '0') {
                $builder->where('pppoe_accounts.server_id', $filterServer);
            }

            if ($filterDisabled !== null && $filterDisabled !== '') {
                $builder->where('pppoe_accounts.disabled', $filterDisabled);
            }

            // Search
            if ($searchValue) {
                $builder->groupStart();
                $builder->like('pppoe_accounts.username', $searchValue);
                $builder->orLike('pppoe_accounts.remote_address', $searchValue);
                $builder->orLike('pppoe_accounts.mac_address', $searchValue);
                $builder->orLike('customers.nama_pelanggan', $searchValue);
                $builder->groupEnd();
            }

            $totalRecords = $builder->countAllResults(false);

            $data = $builder->orderBy('pppoe_accounts.id', 'DESC')
                ->limit($length, $start)
                ->get()
                ->getResultArray();

            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                $disabledBadge = $row['disabled'] ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>';

                $action = '<div class="btn-group">';
                $action .= '<button type="button" class="btn btn-sm btn-info viewPppoe" data-id="' . $row['id'] . '" title="View"><i class="bx bx-show"></i></button>';
                $action .= '<button type="button" class="btn btn-sm btn-warning editPppoe" data-id="' . $row['id'] . '" title="Edit"><i class="bx bx-edit"></i></button>';
                $action .= '<button type="button" class="btn btn-sm btn-danger deletePppoe" data-id="' . $row['id'] . '" title="Delete"><i class="bx bx-trash"></i></button>';
                $action .= '</div>';

                $result[] = [
                    'DT_RowIndex' => $no++,
                    'action' => $action,
                    'id' => $row['id'],
                    'pppoe_id' => $row['pppoe_id'] ?? '-',
                    'branch' => $row['branch_name'] ?? '-',
                    'customer' => $row['customer_name'] ?? '-',
                    'disabled' => $disabledBadge,
                    'username' => $row['username'] ?? '-',
                    'remote_address' => $row['remote_address'] ?? '-',
                    'mac_address' => $row['mac_address'] ?? '-',
                    'local_address' => $row['local_address'] ?? '-',
                    'last_sync' => $row['last_sync'] ? date('d M Y H:i', strtotime($row['last_sync'])) : 'Never',
                    'created_at' => $row['created_at'] ? date('d M Y H:i', strtotime($row['created_at'])) : '-'
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PPPoE getData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sync()
    {
        try {
            $servers = $this->serverModel->findAll();
            $totalFound = 0;
            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            foreach ($servers as $server) {
                try {
                    $mikrotik = new Mikrotik();

                    log_message('info', "Attempting to connect to MikroTik: {$server['name']} ({$server['ip_router']})");

                    if ($mikrotik->connect($server['ip_router'], $server['username'], $server['password'], (int)$server['port_api'])) {
                        log_message('info', "Connected to MikroTik: {$server['name']}");

                        // Retry mechanism for slow VPN connections
                        $pppoeSecrets = null;
                        $maxRetries = 2; // Reduce retries since we have better timeout handling now
                        $retryCount = 0;

                        while ($retryCount < $maxRetries && (empty($pppoeSecrets) || !is_array($pppoeSecrets))) {
                            try {
                                $retryCount++;
                                log_message('info', "Attempt {$retryCount}/{$maxRetries}: Calling /ppp/secret/print");

                                // Add delay before retry (not on first attempt)
                                if ($retryCount > 1) {
                                    log_message('info', "Waiting 5 seconds before retry {$retryCount}...");
                                    sleep(5);
                                }

                                $pppoeSecrets = $mikrotik->comm('/ppp/secret/print');

                                log_message('info', "Attempt {$retryCount}: Response type: " . gettype($pppoeSecrets) . ", Count: " . (is_array($pppoeSecrets) ? count($pppoeSecrets) : 'N/A'));

                                if (is_array($pppoeSecrets)) {
                                    log_message('info', "Attempt {$retryCount}: Sample data: " . json_encode(array_slice($pppoeSecrets, 0, 1)));
                                }

                                if (is_array($pppoeSecrets) && count($pppoeSecrets) > 0) {
                                    log_message('info', "Success on attempt {$retryCount}!");
                                    break; // Success, exit retry loop
                                }

                                if ($retryCount < $maxRetries) {
                                    log_message('warning', "Attempt {$retryCount}: No data received, will retry...");
                                }
                            } catch (\Exception $e) {
                                log_message('error', "Attempt {$retryCount} exception: " . $e->getMessage());
                                // Don't increment retryCount here, it's already incremented in loop
                            }
                        }

                        log_message('info', "Final PPPoE response - Type: " . gettype($pppoeSecrets) . ", Count: " . (is_array($pppoeSecrets) ? count($pppoeSecrets) : 'N/A') . " after {$retryCount} attempt(s)");

                        if (is_array($pppoeSecrets) && count($pppoeSecrets) > 0) {
                            $totalFound += count($pppoeSecrets);
                            log_message('info', "Found " . count($pppoeSecrets) . " PPPoE secrets in {$server['name']}");

                            foreach ($pppoeSecrets as $secret) {
                                $username = $secret['name'] ?? '';
                                $pppoeId = $secret['.id'] ?? null;

                                if (empty($username) || empty($pppoeId)) {
                                    $skipped++;
                                    continue;
                                }
                                $existing = $this->pppoeModel
                                    ->where('username', $username)
                                    ->where('server_id', $server['id_lokasi'])
                                    ->first();

                                if ($existing) {
                                    // Update data yang sudah ada
                                    log_message('info', "Updating existing PPPoE: {$username} (ID: {$existing['id']})");
                                    $updateData = [
                                        'pppoe_id' => $pppoeId,
                                        'remote_address' => $secret['remote-address'] ?? $secret['last-logged-out'] ?? null,
                                        'local_address' => $secret['local-address'] ?? null,
                                        'mac_address' => $secret['last-caller-id'] ?? $secret['caller-id'] ?? null,
                                        'profile_name' => $secret['profile'] ?? null,
                                        'disabled' => isset($secret['disabled']) && $secret['disabled'] === 'true' ? 1 : 0,
                                        'last_sync' => date('Y-m-d H:i:s')
                                    ];

                                    log_message('info', "Update data: " . json_encode($updateData));
                                    $this->pppoeModel->update($existing['id'], $updateData);
                                    $updated++;
                                    log_message('info', "Updated successfully");
                                } else {
                                    // Cari customer berdasarkan pppoe_username
                                    log_message('info', "Inserting new PPPoE: {$username}");
                                    $customerModel = model('CustomerModel');
                                    $customer = $customerModel->where('pppoe_username', $username)->first();

                                    $customerId = $customer ? $customer['id_customers'] : null;

                                    // Insert data baru (dengan atau tanpa customer_id)
                                    $insertData = [
                                        'server_id' => $server['id_lokasi'],
                                        'customer_id' => $customerId,
                                        'pppoe_id' => $pppoeId,
                                        'username' => $username,
                                        'password' => $secret['password'] ?? 'synced',
                                        'remote_address' => $secret['remote-address'] ?? $secret['last-logged-out'] ?? null,
                                        'local_address' => $secret['local-address'] ?? null,
                                        'mac_address' => $secret['last-caller-id'] ?? $secret['caller-id'] ?? null,
                                        'profile_name' => $secret['profile'] ?? null,
                                        'radius_reply_attributes' => [],
                                        'status' => 'active',
                                        'disabled' => isset($secret['disabled']) && $secret['disabled'] === 'true' ? 1 : 0,
                                        'last_sync' => date('Y-m-d H:i:s')
                                    ];

                                    try {
                                        $this->pppoeModel->insert($insertData);
                                        $inserted++;
                                    } catch (\Exception $e) {
                                        $skipped++;
                                        log_message('error', "Failed to insert PPPoE {$username}: " . $e->getMessage());
                                    }
                                }
                            }
                        }

                        $mikrotik->disconnect();
                    } else {
                        $errors[] = "Failed to connect to {$server['name']}";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error syncing {$server['name']}: " . $e->getMessage();
                    log_message('error', "PPPoE sync error for server {$server['id_lokasi']}: " . $e->getMessage());
                }
            }

            $synced = $inserted + $updated;
            $message = "Found $totalFound PPPoE(s). Inserted: $inserted, Updated: $updated, Skipped: $skipped";
            if (count($errors) > 0) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'total_found' => $totalFound,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $pppoe = $this->pppoeModel->find($id);

            if (!$pppoe) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'PPPoE account not found'
                ]);
            }

            // Try to delete from MikroTik first
            $mikrotikDeleted = false;
            $mikrotikMessage = '';

            // Skip MikroTik deletion if we can't connect or find the secret
            // Focus on deleting from local database to avoid blocking the operation
            try {
                $serverModel = model('ServerLocationModel');
                $server = $serverModel->find($pppoe['server_id']);

                if ($server) {
                    $mikrotik = new \App\Libraries\MikrotikNew();
                    $host = $server['ip_router'];
                    $port = $server['port_api'] ?? 8728;

                    if ($mikrotik->connect($host, $server['username'], $server['password_router'], $port)) {
                        log_message('info', 'Connected to MikroTik, searching for secret: ' . $pppoe['username']);

                        // Find secret by username
                        $existing = $mikrotik->comm('/ppp/secret/print', [
                            '?name' => $pppoe['username']
                        ]);

                        log_message('info', 'Search result count: ' . (is_array($existing) ? count($existing) : 0));

                        if (!empty($existing) && is_array($existing) && count($existing) > 0) {
                            $secret = $existing[0];
                            $secretId = $secret['.id'] ?? null;

                            if ($secretId && !empty($secretId)) {
                                log_message('info', 'Attempting to delete secret ID: ' . $secretId);

                                // Use string format for remove command
                                $deleteResult = $mikrotik->comm('/ppp/secret/remove', [
                                    '=numbers=' . $secretId
                                ]);

                                log_message('info', 'Delete result: ' . json_encode($deleteResult));
                                $mikrotikDeleted = true;
                                $mikrotikMessage = 'Deleted from MikroTik';
                            } else {
                                log_message('warning', 'Secret ID is empty');
                                $mikrotikMessage = 'Secret found but ID is invalid - continuing with local delete';
                            }
                        } else {
                            log_message('info', 'Secret not found in MikroTik (may already be deleted manually)');
                            $mikrotikMessage = 'Not found in MikroTik (already deleted)';
                        }
                    } else {
                        log_message('warning', 'Failed to connect to MikroTik - continuing with local delete');
                        $mikrotikMessage = 'MikroTik connection failed - local delete only';
                    }
                } else {
                    log_message('warning', 'Server not found - continuing with local delete');
                    $mikrotikMessage = 'Server not configured - local delete only';
                }
            } catch (\Exception $e) {
                // Don't fail the whole operation if MikroTik delete fails
                log_message('warning', 'MikroTik delete error (continuing with local delete): ' . $e->getMessage());
                $mikrotikMessage = 'MikroTik error - local delete only';
            }

            // Always delete from local database regardless of MikroTik result
            $this->pppoeModel->delete($id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'PPPoE account deleted successfully' . ($mikrotikMessage ? ' (' . $mikrotikMessage . ')' : '')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Delete failed: ' . $e->getMessage()
            ]);
        }
    }

    public function export()
    {
        try {
            $builder = $this->pppoeModel->builder();
            $builder->select('pppoe_accounts.*, lokasi_server.name as branch_name, customers.nama_pelanggan as customer_name');
            $builder->join('lokasi_server', 'lokasi_server.id_lokasi = pppoe_accounts.server_id', 'left');
            $builder->join('customers', 'customers.id_customers = pppoe_accounts.customer_id', 'left');

            $data = $builder->get()->getResultArray();

            $filename = 'pppoe_accounts_' . date('Y-m-d_His') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Header
            fputcsv($output, ['ID', 'PPPoE ID', 'Branch', 'Customer', 'Username', 'Remote Address', 'MAC Address', 'Local Address', 'Disabled', 'Last Sync', 'Created At']);

            foreach ($data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['pppoe_id'] ?? '-',
                    $row['branch_name'] ?? '-',
                    $row['customer_name'] ?? '-',
                    $row['username'] ?? '-',
                    $row['remote_address'] ?? '-',
                    $row['mac_address'] ?? '-',
                    $row['local_address'] ?? '-',
                    $row['disabled'] ? 'Yes' : 'No',
                    $row['last_sync'] ?? '-',
                    $row['created_at'] ?? '-'
                ]);
            }

            fclose($output);
            exit;
        } catch (\Exception $e) {
            log_message('error', 'PPPoE export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function getCustomers()
    {
        try {
            $customerModel = model('CustomerModel');
            $customers = $customerModel->select('id_customers, nama_pelanggan, nomor_layanan, pppoe_username')
                ->where('login', 'enable')
                ->orderBy('nama_pelanggan', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load customers: ' . $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {
            $request = service('request');

            $validation = \Config\Services::validation();
            $validation->setRules([
                'server_id' => 'required|integer',
                'customer_id' => 'required|integer',
                'name' => 'required|min_length[3]|max_length[64]',
                'username' => 'required|min_length[3]|max_length[64]',
                'password' => 'required|min_length[6]|max_length[64]',
            ]);

            if (!$validation->withRequest($request)->run()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => implode(', ', $validation->getErrors())
                ]);
            }

            $data = [
                'server_id' => $request->getPost('server_id'),
                'customer_id' => $request->getPost('customer_id'),
                'username' => $request->getPost('username'),
                'password' => $request->getPost('password'),
                'profile_name' => $request->getPost('profile_name'),
                'remote_address' => $request->getPost('remote_address'),
                'local_address' => $request->getPost('local_address'),
                'radius_reply_attributes' => [],
                'status' => 'active',
                'disabled' => 0
            ];

            $this->pppoeModel->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'PPPoE account created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create PPPoE account: ' . $e->getMessage()
            ]);
        }
    }
}
