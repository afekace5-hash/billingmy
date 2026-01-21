<?php

namespace App\Controllers;

use App\Models\RouterOSModel;

class RouterOSConf extends BaseController
{
    /**
     * Endpoint untuk AJAX select2 lokasi server
     * GET /lokasi-server/options
     * Output: [{"id":..., "name":...}, ...]
     */
    public function options()
    {
        $model = new \App\Models\RouterOSModel();
        // Ganti 'name' ke 'nama as name' jika kolom di DB adalah 'nama'
        $data = $model->select('id_lokasi as id, name')->findAll();
        return $this->response->setJSON($data);
    }
    public function index()
    {
        $data = [
            'title' => 'Router OS Configuration'
        ];
        return view('routeros/index', $data);
    }
    public function view($id)
    {
        $model = new RouterOSModel();
        $data = $model->find($id);

        if (!$data) {
            return redirect()->to('router-os-conf')->with('error', 'Router configuration not found');
        }

        return view('routeros/view', ['data' => $data]);
    }

    public function edit($id)
    {
        $model = new RouterOSModel();
        $data = $model->find($id);

        if (!$data) {
            return redirect()->to('router-os-conf')->with('error', 'Router configuration not found');
        }

        return view('routeros/edit', ['data' => $data]);
    }

    public function getData()
    {
        try {
            $request = $this->request;
            $db = \Config\Database::connect();
            $builder = $db->table('lokasi_server');

            // DataTables params
            $draw = intval($request->getPost('draw') ?? 1);
            $start = intval($request->getPost('start') ?? 0);
            $length = intval($request->getPost('length') ?? 10);
            $searchValue = $request->getPost('search')['value'] ?? '';

            // Total records
            $totalRecords = $builder->countAll();

            // Filtering
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('name', $searchValue)
                    ->orLike('ip_router', $searchValue)
                    ->orLike('lokasi', $searchValue)
                    ->orLike('jenis_isolir', $searchValue)
                    ->groupEnd();
            }

            $totalFiltered = $builder->countAllResults(false);

            // Ordering and Pagination
            $builder->orderBy('id_lokasi', 'desc');
            if ($length != -1) {
                $builder->limit($length, $start);
            }

            $query = $builder->get();
            $data = [];
            foreach ($query->getResult() as $row) {
                $data[] = [
                    'id' => $row->id_lokasi ?? 0,
                    'name' => $row->name ?? '-',
                    'branch' => $row->lokasi ?? '-',
                    'host' => $row->ip_router ?? '-',
                    'isolir_type' => $row->jenis_isolir ?? '-',
                    'prefix_email' => $row->email ?? '-',
                    'last_sync' => $row->last_sync ?? '-',
                    'created_at' => $row->created_at ?? '-',
                    'updated_at' => $row->updated_at ?? '-',
                ];
            }

            $output = [
                "draw" => $draw,
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalFiltered,
                "data" => $data
            ];
            return $this->response->setJSON($output);
        } catch (\Exception $e) {
            log_message('error', 'RouterOSConf getData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }
    public function checkConnection()
    {
        // Start output buffering to suppress unwanted output from libraries
        ob_start();

        try {
            log_message('debug', 'checkConnection method started');

            // Check if ID is provided (for testing existing server)
            $id = $this->request->getPost('id');

            if (!empty($id)) {
                // Get server details from database
                $lokasiModel = new RouterOSModel();
                $server = $lokasiModel->find($id);

                if (!$server) {
                    while (ob_get_level()) ob_end_clean();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'title' => 'Error',
                        'message' => 'Server tidak ditemukan dengan ID: ' . $id
                    ]);
                }

                // Use server details from database
                $ip = $server['ip_router'];
                $username = $server['username'];
                $password = $server['password'] ?? $server['password_router'] ?? '';
                $port = $server['port_api'];

                log_message('debug', 'checkConnection using server ID ' . $id . ': ip=' . $ip . ', username=' . $username . ', port=' . $port);
            } else {
                // Get parameters from POST request (for new server form)
                $ip = $this->request->getPost('ip_router');
                $username = $this->request->getPost('username');
                $password = $this->request->getPost('password') ?: $this->request->getPost('password_router');
                $port = $this->request->getPost('port_api');

                log_message('debug', 'checkConnection called with direct params: ip=' . $ip . ', username=' . $username . ', port=' . $port);
            }

            // Validate required parameters
            if (empty($ip) || empty($username) || empty($password) || empty($port)) {
                $missingFields = [];
                if (empty($ip)) $missingFields[] = 'IP Router';
                if (empty($username)) $missingFields[] = 'Username';
                if (empty($password)) $missingFields[] = 'Password';
                if (empty($port)) $missingFields[] = 'Port API';

                while (ob_get_level()) ob_end_clean();
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Data koneksi tidak lengkap: ' . implode(', ', $missingFields) . ' wajib diisi.',
                    'details' => !empty($id) ? 'Silakan lengkapi data server di database atau gunakan form edit untuk melengkapi informasi koneksi.' : 'Silakan lengkapi semua field koneksi.'
                ]);
            }

            $host = $ip;
            $connectionPort = intval($port);
            $isTunnel = false;

            // Check if IP contains tunnel endpoint format (hostname:port)
            if (strpos($ip, ':') !== false) {
                $parts = explode(':', $ip);
                if (count($parts) >= 2) {
                    $tunnelPort = intval(end($parts)); // Get last part as port
                    array_pop($parts); // Remove port from array
                    $host = implode(':', $parts); // Join remaining parts as host
                    $connectionPort = $tunnelPort; // Use tunnel port for connection
                    $isTunnel = true;

                    log_message('debug', "Tunnel connection detected - Host: {$host}, Tunnel Port: {$tunnelPort}");
                }
            }            // Set a shorter timeout for connection tests
            set_time_limit(30); // Limit execution to 30 seconds

            // Test basic connectivity first with a socket test
            log_message('debug', "Testing basic socket connectivity to {$host}:{$connectionPort}");
            $socket = @fsockopen($host, $connectionPort, $errno, $errstr, 5); // 5 second timeout

            if (!$socket) {
                while (ob_get_level()) ob_end_clean();

                // Provide specific error messages based on error codes
                if ($errno == 110 || $errno == 10060) { // Connection timeout
                    $message = "Koneksi timeout ke {$host}:{$connectionPort}. Server mungkin tidak dapat dijangkau atau port tidak terbuka.";
                } elseif ($errno == 111 || $errno == 10061) { // Connection refused
                    if ($isTunnel) {
                        $message = "Koneksi ditolak melalui tunnel {$host}:{$connectionPort}. Pastikan:\n• Tunnel aktif dan stabil\n• Port API MikroTik ter-forward dengan benar\n• Service API MikroTik aktif di router";
                    } else {
                        $message = "Koneksi ditolak ke {$host}:{$connectionPort}. Pastikan:\n• Service API MikroTik aktif\n• Port API dapat diakses\n• Firewall tidak memblokir koneksi";
                    }
                } elseif ($errno == 113) { // No route to host
                    $message = "Tidak ada rute ke host {$host}. Periksa koneksi internet dan alamat host.";
                } else {
                    $message = "Tidak dapat terhubung ke {$host}:{$connectionPort}. Error: {$errstr} ({$errno})";
                }

                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Connection Failed',
                    'message' => $message
                ]);
            }

            fclose($socket);
            log_message('debug', "Basic socket test passed for {$host}:{$connectionPort}");

            // Now test actual MikroTik API connection
            try {
                $config = [
                    'host' => $host,
                    'user' => $username,
                    'pass' => $password,
                    'port' => $connectionPort,
                    'timeout' => 10
                ];

                $mikrotik = new \App\Libraries\MikrotikAPI($config);

                log_message('debug', "Attempting MikroTik API connection to {$host}:{$connectionPort} with username: {$username}");

                // Use testConnection method which handles everything
                $result = $mikrotik->testConnection();

                while (ob_get_level()) ob_end_clean();

                if ($result['success']) {
                    $identity = $result['identity'] ?? $result['board_name'] ?? 'MikroTik Router';
                    $connectionType = $result['connection_type'] ?? 'direct';

                    // Update is_connected status in database if ID is provided
                    if (!empty($id)) {
                        $db = \Config\Database::connect();
                        $db->table('lokasi_server')
                            ->where('id_lokasi', $id)
                            ->update([
                                'is_connected' => 1,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'status' => 'success',
                        'title' => 'Koneksi Berhasil',
                        'message' => "Berhasil terhubung ke MikroTik pada {$host}:{$connectionPort}\nIdentity: {$identity}\nConnection: " . ucfirst($connectionType)
                    ]);
                } else {
                    // Update is_connected status to 0 if connection failed
                    if (!empty($id)) {
                        $db = \Config\Database::connect();
                        $db->table('lokasi_server')
                            ->where('id_lokasi', $id)
                            ->update([
                                'is_connected' => 0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    }

                    return $this->response->setJSON([
                        'success' => false,
                        'status' => 'error',
                        'title' => 'Login Gagal',
                        'message' => "Port dapat dijangkau, tetapi login ke MikroTik API gagal.\n" .
                            ($result['message'] ?? "Periksa username dan password")
                    ]);
                }
            } catch (\Exception $apiError) {
                while (ob_get_level()) ob_end_clean();
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'API Error',
                    'message' => "Port dapat dijangkau, tetapi gagal terhubung ke MikroTik API: " . $apiError->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            while (ob_get_level()) ob_end_clean();
            $errorMessage = $e->getMessage();
            log_message('debug', 'MikroTik connection failed: ' . $errorMessage);

            // Provide more specific error messages
            if (strpos($errorMessage, 'Connection refused') !== false || strpos($errorMessage, '10061') !== false) {
                if ($isTunnel) {
                    $message = 'Koneksi ditolak melalui tunnel. Kemungkinan penyebab:\n• Port API MikroTik tidak ter-forward melalui tunnel\n• Service API MikroTik tidak aktif\n• Firewall memblokir koneksi';
                } else {
                    $message = 'Koneksi ditolak. Pastikan port API MikroTik dapat diakses.';
                }
            } elseif (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Maximum execution time') !== false) {
                $message = 'Koneksi timeout. Periksa alamat IP/hostname dan pastikan dapat dijangkau.';
            } elseif (strpos($errorMessage, 'authentication') !== false || strpos($errorMessage, 'login') !== false) {
                $message = 'Gagal autentikasi. Periksa username dan password.';
            } else {
                $message = 'Gagal terhubung ke MikroTik: ' . $errorMessage;
            }

            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Connection Failed',
                'message' => $message
            ]);
        } catch (\Throwable $e) {
            while (ob_get_level()) ob_end_clean();
            log_message('debug', 'Fatal error in checkConnection: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Fatal Error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    private function testTunnelConnectivity($host, $port)
    {
        service('logger')->debug("Testing tunnel connectivity to {$host}:{$port}");

        // Test if we can reach the tunnel endpoint
        $socket = @fsockopen($host, $port, $errno, $errstr, 10);

        if (!$socket) {
            service('logger')->error("Tunnel connectivity test failed: {$errno} - {$errstr}");
            return [
                'success' => false,
                'message' => "Tidak dapat terhubung ke tunnel endpoint {$host}:{$port}. Error: {$errstr} ({$errno})"
            ];
        }

        fclose($socket);
        service('logger')->debug("Tunnel connectivity test successful");

        return [
            'success' => true,
            'message' => 'Tunnel endpoint accessible'
        ];
    }


    public function create()
    {
        $validation = \Config\Services::validation();

        // Validasi field sesuai form baru
        $validation->setRules([
            'name' => 'required',
            'branch' => 'required',
            'host' => 'required|min_length[3]|max_length[255]',
            'username' => 'required',
            'password' => 'required',
            'isolir_type' => 'required',
            'local_ip' => 'required',
            'remote_url' => 'required',
            'comment_nat' => 'required',
            'prefix_email' => 'required',
        ]);

        $postData = $this->request->getPost();

        // Validate form data
        if (!$validation->run($postData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }

        // Mapping ke field database
        $data = [
            'name' => $this->request->getPost('name'),
            'lokasi' => $this->request->getPost('branch'),
            'ip_router' => $this->request->getPost('host'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'jenis_isolir' => $this->request->getPost('isolir_type'),
            'local_ip' => $this->request->getPost('local_ip'),
            'legacy_login' => $this->request->getPost('legacy_login') ? 1 : 0,
            'remote_url' => $this->request->getPost('remote_url'),
            'comment_nat' => $this->request->getPost('comment_nat'),
            'email' => $this->request->getPost('prefix_email'),
            'notes' => $this->request->getPost('notes'),
            'is_connected' => 0,
            'port_api' => 8728, // default port
        ];

        $model = new RouterOSModel();
        try {
            $model->insert($data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'RouterOS Configuration berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'ip_router' => $this->request->getPost('host'),
                'username' => $this->request->getPost('username'),
                'password' => $this->request->getPost('password'),
                'port_api' => $this->request->getPost('port_api') ?? 8728,
                'email' => $this->request->getPost('prefix_email'),
                'local_ip' => $this->request->getPost('local_ip'),
                'legacy_login' => $this->request->getPost('legacy_login') == '1' ? 1 : 0,
                'remote_url' => $this->request->getPost('remote_url'),
                'comment_nat' => $this->request->getPost('comment_nat'),
                'notes' => $this->request->getPost('notes'),
                'jenis_isolir' => $this->request->getPost('isolir_type'),
                'lokasi' => $this->request->getPost('branch'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db = \Config\Database::connect();
            $builder = $db->table('lokasi_server');
            $builder->where('id_lokasi', $id);
            $builder->update($data);

            return $this->response->setJSON(['success' => true, 'message' => 'Router configuration updated successfully']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update router configuration: ' . $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('lokasi_server');
            $builder->where('id_lokasi', $id);
            $builder->delete();

            return $this->response->setJSON(['success' => true, 'message' => 'Router configuration deleted successfully']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete router configuration: ' . $e->getMessage()]);
        }
    }

    public function diagnose()
    {
        $ip = $this->request->getPost('ip_router');
        $port = $this->request->getPost('port_api');

        $diagnostics = [];

        if (strpos($ip, ':') !== false) {
            $parts = explode(':', $ip);
            $tunnelPort = intval(end($parts));
            array_pop($parts);
            $host = implode(':', $parts);

            // Test 1: DNS Resolution
            $dnsTest = gethostbyname($host);
            $diagnostics['dns'] = [
                'test' => 'DNS Resolution',
                'result' => $dnsTest !== $host ? 'PASS' : 'FAIL',
                'details' => "Hostname {$host} resolves to: {$dnsTest}"
            ];

            // Test 2: Ping (simplified)
            $pingTest = @fsockopen($host, 80, $errno, $errstr, 5);
            $diagnostics['connectivity'] = [
                'test' => 'Basic Connectivity',
                'result' => $pingTest ? 'PASS' : 'FAIL',
                'details' => $pingTest ? "Host {$host} is reachable" : "Cannot reach {$host}: {$errstr}"
            ];
            if ($pingTest) fclose($pingTest);

            // Test 3: Tunnel Port
            $tunnelTest = @fsockopen($host, $tunnelPort, $errno, $errstr, 10);
            $diagnostics['tunnel'] = [
                'test' => 'Tunnel Port',
                'result' => $tunnelTest ? 'PASS' : 'FAIL',
                'details' => $tunnelTest ? "Tunnel port {$tunnelPort} is open" : "Tunnel port {$tunnelPort} is closed: {$errstr}"
            ];
            if ($tunnelTest) fclose($tunnelTest);

            // Test 4: Try to connect through tunnel (basic socket)
            if ($tunnelTest) {
                $apiTest = @fsockopen($host, $tunnelPort, $errno, $errstr, 10);
                $diagnostics['api_through_tunnel'] = [
                    'test' => 'API through Tunnel',
                    'result' => $apiTest ? 'PASS' : 'FAIL',
                    'details' => $apiTest ? "Can establish socket to tunnel" : "Cannot establish socket through tunnel: {$errstr}"
                ];
                if ($apiTest) fclose($apiTest);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'diagnostics' => $diagnostics
        ]);
    }

    public function testAlternativeConnection()
    {
        $ip = $this->request->getPost('ip_router');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password') ?: $this->request->getPost('password_router');
        $port = $this->request->getPost('port_api');

        $results = [];

        if (strpos($ip, ':') !== false) {
            $parts = explode(':', $ip);
            $tunnelPort = intval(end($parts));
            array_pop($parts);
            $host = implode(':', $parts);

            // Test 1: Try different connection methods
            $results['methods'] = [];            // Method 1: Direct tunnel port connection
            try {
                $mt1 = new \App\Libraries\MikrotikNew([
                    'host' => $host,
                    'user' => $username,
                    'pass' => $password,
                    'port' => $tunnelPort,
                    'timeout' => 60,
                ]);
                $result1 = $mt1->comm('/system/identity/print');
                $results['methods']['tunnel_port'] = ['status' => 'success', 'data' => $result1];
            } catch (\Exception $e) {
                $results['methods']['tunnel_port'] = ['status' => 'failed', 'error' => $e->getMessage()];
            }

            // Method 2: Try standard API port
            try {
                $mt2 = new \App\Libraries\MikrotikNew([
                    'host' => $host,
                    'user' => $username,
                    'pass' => $password,
                    'port' => intval($port),
                    'timeout' => 60,
                ]);
                $result2 = $mt2->comm('/system/identity/print');
                $results['methods']['api_port'] = ['status' => 'success', 'data' => $result2];
            } catch (\Exception $e) {
                $results['methods']['api_port'] = ['status' => 'failed', 'error' => $e->getMessage()];
            }

            // Method 3: Try without port specification (let library decide)
            try {
                $mt3 = new \App\Libraries\MikrotikNew([
                    'host' => $ip, // Use full endpoint
                    'user' => $username,
                    'pass' => $password,
                    'timeout' => 60,
                ]);
                $result3 = $mt3->comm('/system/identity/print');
                $results['methods']['full_endpoint'] = ['status' => 'success', 'data' => $result3];
            } catch (\Exception $e) {
                $results['methods']['full_endpoint'] = ['status' => 'failed', 'error' => $e->getMessage()];
            }

            // Test 2: Socket connectivity tests
            $results['socket_tests'] = [];

            $testPorts = [$tunnelPort, intval($port), 8728, 80, 443];
            foreach ($testPorts as $testPort) {
                $socket = @fsockopen($host, $testPort, $errno, $errstr, 10);
                if ($socket) {
                    $results['socket_tests'][$testPort] = 'open';
                    fclose($socket);
                } else {
                    $results['socket_tests'][$testPort] = "closed ({$errstr})";
                }
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'results' => $results
        ]);
    }

    /**
     * Get servers status with ping monitoring data for AJAX DataTable
     * Matches the table structure shown in the image
     */
    public function getServersStatus()
    {
        $request = $this->request;

        if ($request->isAJAX() || $request->getGet('draw')) {
            $pingService = new \App\Libraries\PingMonitoringService();
            $servers = $pingService->getServersStatus();

            $draw = intval($request->getGet('draw'));
            $start = intval($request->getGet('start'));
            $length = intval($request->getGet('length'));
            $searchValue = $request->getGet('search[value]');

            // Filter servers based on search
            $filteredServers = $servers;
            if (!empty($searchValue)) {
                $filteredServers = array_filter($servers, function ($server) use ($searchValue) {
                    return stripos($server['name'], $searchValue) !== false ||
                        stripos($server['ip_address'], $searchValue) !== false ||
                        stripos($server['description'], $searchValue) !== false;
                });
            }

            $totalRecords = count($servers);
            $totalFiltered = count($filteredServers);

            // Pagination
            $paginatedServers = array_slice($filteredServers, $start, $length);

            $data = [];
            $no = $start + 1;

            foreach ($paginatedServers as $server) {
                // Format ping status badge
                $pingStatusBadge = match ($server['ping_status']) {
                    'online' => '<span class="badge bg-success">✓ online</span>',
                    'offline' => '<span class="badge bg-danger">✗ offline</span>',
                    default => '<span class="badge bg-warning">? unknown</span>'
                };

                // Format last checked time
                $lastChecked = $server['last_ping_check'] ?
                    date('Y-m-d H:i:s', strtotime($server['last_ping_check'])) :
                    'Never';

                // Format timezone (for now using Asia/Jakarta)
                $timezone = '+07:00 Asia/Jakarta';

                $data[] = [
                    'DT_RowIndex' => $no++,
                    'api_features' => '<button class="btn btn-sm btn-info" onclick="testAPI(' . $server['id'] . ')" title="Test API"><i class="bx bx-wifi"></i></button>',
                    'ping_status' => $pingStatusBadge,
                    'router_name' => $server['name'],
                    'ip_address' => $server['ip_address'],
                    'timezone' => $timezone,
                    'description' => $server['description'],
                    'last_checked' => $lastChecked,
                    'action' => '
                        <button class="btn btn-sm btn-primary editData" data-id="' . $server['id'] . '" title="Edit">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger deleteData" data-id="' . $server['id'] . '" title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>'
                ];
            }

            $output = [
                "draw" => $draw,
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalFiltered,
                "data" => $data
            ];

            return $this->response->setJSON($output);
        }

        // Return regular view if not AJAX
        return view('server-location/index');
    }

    /**
     * Manual ping check for a specific server
     */
    public function pingCheck($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server ID is required'
            ]);
        }

        $pingService = new \App\Libraries\PingMonitoringService();
        $result = $pingService->manualPingCheck($id);

        return $this->response->setJSON($result);
    }

    /**
     * Get real-time status for all servers (for auto-refresh)
     */
    public function getRealtimeStatus()
    {
        $pingService = new \App\Libraries\PingMonitoringService();
        $servers = $pingService->getServersStatus();

        $statusData = [];
        foreach ($servers as $server) {
            $statusData[] = [
                'id' => $server['id'],
                'ping_status' => $server['ping_status'],
                'last_ping_check' => $server['last_ping_check'],
                'last_ping_response_time' => $server['last_ping_response_time'],
                'ping_failures_count' => $server['ping_failures_count']
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'servers' => $statusData
        ]);
    }

    /**
     * Enable/disable auto ping monitoring for a server
     */
    public function toggleAutoPing($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server ID is required'
            ]);
        }

        $model = new RouterOSModel();
        $server = $model->find($id);

        if (!$server) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server not found'
            ]);
        }

        $newStatus = !($server['auto_ping_enabled'] ?? 1);

        $updated = $model->update($id, ['auto_ping_enabled' => $newStatus]);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Auto ping monitoring ' . ($newStatus ? 'enabled' : 'disabled'),
                'auto_ping_enabled' => $newStatus
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update auto ping setting'
            ]);
        }
    }

    public function sync($id)
    {
        try {
            $model = new \App\Models\RouterOSModel();
            $server = $model->find($id);

            if (!$server) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router configuration not found'
                ]);
            }

            // Initialize MikroTik API
            // Parse host and port from ip_router (format: host:port or just host)
            $ipRouter = $server['ip_router'];
            $host = $ipRouter;
            $port = (int)($server['port_api'] ?? 8728);

            // Check if ip_router contains port (e.g., "us-1.hostddns.us:31014")
            if (strpos($ipRouter, ':') !== false) {
                $parts = explode(':', $ipRouter);
                $host = $parts[0];
                // Use port from ip_router if exists, otherwise use port_api
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $port = (int)$parts[1];
                }
            }

            $mikrotik = new \App\Libraries\MikrotikAPI([
                'host' => $host,
                'user' => $server['username'],
                'pass' => $server['password'] ?? $server['password_router'] ?? '',
                'port' => $port
            ]);

            // Test connection first
            $connectionTest = $mikrotik->testConnection();
            if (!$connectionTest['success']) {
                $model->update($id, [
                    'is_connected' => 0
                ]);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to connect to MikroTik: ' . ($connectionTest['message'] ?? 'Unknown error')
                ]);
            }

            // Get PPPoE secrets from MikroTik
            $mikrotikSecrets = $mikrotik->getPPPSecrets();
            if ($mikrotikSecrets === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to retrieve PPPoE accounts from MikroTik'
                ]);
            }

            // Create associative array of MikroTik accounts for faster lookup
            $mikrotikAccountsMap = [];
            foreach ($mikrotikSecrets as $secret) {
                $username = $secret['name'] ?? '';
                if ($username) {
                    $mikrotikAccountsMap[$username] = $secret;
                }
            }

            // Get customers from billing database for this router
            $customerModel = new \App\Models\CustomerModel();
            $customers = $customerModel->where('id_lokasi_server', $id)
                ->where('pppoe_username !=', '')
                ->whereNotIn('pppoe_username', [null, ''])
                ->findAll();

            $syncReport = [
                'added' => 0,
                'updated' => 0,
                'removed' => 0,
                'isolated' => 0,
                'restored' => 0,
                'errors' => []
            ];

            // Sync customers from billing to MikroTik
            foreach ($customers as $customer) {
                $username = $customer['pppoe_username'];
                $password = $customer['pppoe_password'];
                $isIsolated = ($customer['isolir_status'] === '1' || $customer['isolir_status'] === 1);

                if (!isset($mikrotikAccountsMap[$username])) {
                    // Account doesn't exist in MikroTik - add it
                    $secretData = [
                        'name' => $username,
                        'password' => $password,
                        'service' => $customer['pppoe_service'] ?? 'pppoe',
                        'profile' => $isIsolated ? ($server['jenis_isolir'] ?? 'ISOLIR') : ($customer['original_profile'] ?? 'default'),
                        'local-address' => $customer['pppoe_local_ip'] ?? '',
                        'remote-address' => $isIsolated ? '' : ($customer['pppoe_remote_address'] ?? ''),
                        'comment' => $customer['pppoe_comment'] ?? $customer['nama_pelanggan'] ?? ''
                    ];

                    if ($mikrotik->addPPPSecret($secretData)) {
                        $syncReport['added']++;
                    } else {
                        $syncReport['errors'][] = "Failed to add account: $username";
                    }
                } else {
                    // Account exists - check if isolation status needs update
                    $mikrotikSecret = $mikrotikAccountsMap[$username];
                    $currentProfile = $mikrotikSecret['profile'] ?? '';
                    $isolirProfile = $server['jenis_isolir'] ?? 'ISOLIR';

                    // Check if isolation status matches
                    $isIsolatedInMikrotik = ($currentProfile === $isolirProfile);

                    if ($isIsolated && !$isIsolatedInMikrotik) {
                        // Customer should be isolated but isn't - isolate in MikroTik
                        // Store original profile if not already stored
                        if (empty($customer['original_profile'])) {
                            $customerModel->update($customer['id_customers'], [
                                'original_profile' => $currentProfile
                            ]);
                        }

                        // Update to isolation profile
                        $updateData = [
                            'profile' => $isolirProfile,
                            'remote-address' => ''
                        ];
                        if ($mikrotik->updatePPPSecret($username, $updateData)) {
                            $syncReport['isolated']++;
                        } else {
                            $syncReport['errors'][] = "Failed to isolate: $username";
                        }
                    } elseif (!$isIsolated && $isIsolatedInMikrotik) {
                        // Customer should NOT be isolated but is - restore in MikroTik
                        $restoreProfile = $customer['original_profile'] ?? 'default';
                        $updateData = [
                            'profile' => $restoreProfile,
                            'remote-address' => $customer['original_remote_address'] ?? $customer['pppoe_remote_address'] ?? ''
                        ];
                        if ($mikrotik->updatePPPSecret($username, $updateData)) {
                            $syncReport['restored']++;
                            // Clear isolation status in billing
                            $customerModel->update($customer['id_customers'], [
                                'isolir_status' => '0',
                                'isolir_date' => null,
                                'isolir_reason' => null
                            ]);
                        } else {
                            $syncReport['errors'][] = "Failed to restore: $username";
                        }
                    }

                    // Remove from map (so we know what's left in MikroTik but not in billing)
                    unset($mikrotikAccountsMap[$username]);
                }
            }

            // Handle accounts that exist in MikroTik but not in billing database
            // These are orphaned accounts that should potentially be removed
            foreach ($mikrotikAccountsMap as $username => $secret) {
                // Optional: You can choose to remove orphaned accounts or just log them
                // For safety, we'll just log them in the errors array
                $syncReport['errors'][] = "Orphaned account in MikroTik (not in billing): $username";

                // Uncomment below to auto-remove orphaned accounts:
                // if ($mikrotik->removePPPSecret($username)) {
                //     $syncReport['removed']++;
                // }
            }

            // Update sync timestamp and connection status
            $model->update($id, [
                'is_connected' => 1,
                'last_sync' => date('Y-m-d H:i:s')
            ]);

            $message = sprintf(
                'Sync completed: %d added, %d isolated, %d restored. %s',
                $syncReport['added'],
                $syncReport['isolated'],
                $syncReport['restored'],
                empty($syncReport['errors']) ? '' : count($syncReport['errors']) . ' errors occurred.'
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'report' => $syncReport
            ]);
        } catch (\Exception $e) {
            log_message('error', 'RouterOS Sync Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }
}
