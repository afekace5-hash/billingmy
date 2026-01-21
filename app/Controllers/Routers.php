<?php

namespace App\Controllers;

use App\Libraries\MikrotikNew as Mikrotik;

class Routers extends BaseController
{
    /**
     * Parse connection details for tunnel connections
     * @param string $ipRouter IP router which may contain port (e.g., "id-14.hostddns.us:8211")
     * @param int $portApi Standard API port from database
     * @return array ['host' => string, 'port' => int]
     */
    private function parseConnectionDetails($ipRouter, $portApi)
    {
        $actualHost = $ipRouter;
        $actualPort = intval($portApi);

        // For tunnel connections like "id-14.hostddns.us:8211", 
        // the entire string is the tunnel host, not host:port to be split
        // The API port (8728) should be used as-is for connecting through the tunnel

        log_message('debug', "Tunnel connection detected - Host: {$actualHost}, API Port: {$actualPort}");

        return ['host' => $actualHost, 'port' => $actualPort];
    }

    // GET: /routers/ping
    public function ping()
    {
        // You can return a modal view or a simple JSON for ping utility
        // Example: return a simple HTML modal content (adjust as needed)
        return view('routers/ping_modal');
    }
    public function list()
    {
        $model = new \App\Models\LokasiServerModel();
        $routers = $model->findAll();
        return view('routers/list', ['routers' => $routers]);
    }
    public function syncronize()
    {
        // Disable output buffering and error display for JSON response
        @ob_clean();

        try {
            $model = new \App\Models\LokasiServerModel();
            $routers = $model->findAll();

            // Format data for JSON response
            $formattedRouters = [];
            foreach ($routers as $router) {
                $formattedRouters[] = [
                    'id' => $router['id_lokasi'],
                    'nama' => $router['nama'] ?? $router['name'] ?? 'Unknown',
                    'ip_router' => $router['ip_router'],
                    'status' => $router['status'] ?? 'active',
                    'type' => $router['type'] ?? 'mikrotik',
                    'district' => $router['district'] ?? '',
                    'village' => $router['village'] ?? ''
                ];
            }

            // Set proper headers for JSON response
            $this->response->setContentType('application/json');

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $formattedRouters,
                'message' => 'Data router berhasil dimuat.',
                'count' => count($formattedRouters)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading routers: ' . $e->getMessage());

            // Set proper headers for error response
            $this->response->setContentType('application/json');

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memuat data router: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    } // Endpoint: /routers/{id}/mikrotik-info
    public function mikrotikInfo($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }        // --- Mikrotik API connection ---
        $ip = $router['ip_router'] ?? '';
        $username = $router['username'] ?? '';
        $password = $router['password_router'] ?? '';
        $port = $router['port_api'] ?? 8728;

        // GUNAKAN HOST LENGKAP SEPERTI YANG DIMINTA: id-14.hostddns.us:8211
        // JANGAN PERNAH UBAH ATAU KURANGI HOST INI
        $host = $ip; // Gunakan ip_router lengkap sebagai host (id-14.hostddns.us:8211)
        $connectionPort = $port; // Gunakan port_api (8728)

        // Log connection attempt (only in debug mode to reduce log spam)
        if (ENVIRONMENT === 'development') {
            log_message('debug', "Attempting MikroTik connection for router ID {$id}: Host={$host}, API_Port={$connectionPort}");
        }

        // Jika data koneksi tidak lengkap, kembalikan error
        if (empty($ip) || empty($username)) {
            log_message('warning', "Incomplete connection data for router ID {$id}");
            return $this->response->setStatusCode(503)->setJSON([
                'error' => 'Router connection data incomplete',
                'details' => 'IP Router or Username is missing'
            ]);
        }        // Gunakan host lengkap dan port API yang benar
        try {
            // Only log in debug mode
            if (ENVIRONMENT === 'development') {
                log_message('debug', "MikroTik connection attempt: Host={$host}, Port={$connectionPort}, User={$username}");
            }

            $mt = new Mikrotik();
            $connected = $mt->connect($host, $username, $password, intval($connectionPort));

            if (!$connected) {
                log_message('info', "MikroTik connection failed for router ID {$id}");
                return $this->response->setStatusCode(503)->setJSON([
                    'status' => 'error',
                    'message' => 'Unable to connect to MikroTik router',
                    'error' => 'Connection failed'
                ]);
            }

            // Test connection with simple system resource query
            $result = $mt->comm('/system/resource/print');

            if ($result && isset($result[0])) {
                // Connection successful, get all data
                if (ENVIRONMENT === 'development') {
                    log_message('debug', "MikroTik connection successful for router ID {$id}");
                }

                // Get PPPoE and binding stats
                $pppoeSecrets = $mt->comm('/ppp/secret/print');
                $pppoeActive = $mt->comm('/ppp/active/print');
                $binding = $mt->comm('/ip/hotspot/ip-binding/print');

                $pppoeTotal = is_array($pppoeSecrets) ? count($pppoeSecrets) : 0;
                $pppoeActiveCount = is_array($pppoeActive) ? count($pppoeActive) : 0;
                $pppoeInactive = $pppoeTotal - $pppoeActiveCount;

                $bindingActive = 0;
                $bindingInactive = 0;
                if (is_array($binding)) {
                    foreach ($binding as $b) {
                        if (isset($b['disabled']) && $b['disabled'] == 'false') {
                            $bindingActive++;
                        } else {
                            $bindingInactive++;
                        }
                    }
                }

                // Add connection and stats data
                $response = $result[0];
                $response['active_count'] = $pppoeActiveCount;
                $response['inactive_count'] = $pppoeInactive;
                $response['binding_active_count'] = $bindingActive;
                $response['binding_inactive_count'] = $bindingInactive;
                $response['connection_successful'] = true;

                return $this->response->setJSON($response);
            } else {
                // No data returned from MikroTik
                log_message('info', "MikroTik connected but no system data returned for router ID {$id}");
                return $this->response->setStatusCode(503)->setJSON([
                    'status' => 'error',
                    'error' => 'MikroTik connected but no system data available',
                    'details' => 'System resource query returned empty result'
                ]);
            }
        } catch (\Exception $e) {
            // Connection failed
            $error = $e->getMessage();
            log_message('info', "MikroTik connection exception for router ID {$id}: {$error}");

            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'error',
                'error' => 'Unable to connect to MikroTik router',
                'message' => $error
            ]);
        }
    }

    // POST: /routers/{id}/ping-mikrotik
    public function pingMikrotik($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }
        $ip = $router['ip_router'] ?? '';
        $username = $router['username'] ?? '';
        $password = $router['password_router'] ?? '';
        $port = $router['port_api'] ?? 8728;
        $target = $this->request->getPost('address');

        if (!$target) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Target address is required.']);
        }

        // Parse host dan port dengan benar seperti di method lain
        $host = $ip;
        $connectionPort = $port;

        // Jika ip_router berformat "host:port", pisahkan
        if (strpos($ip, ':') !== false) {
            $hostParts = explode(':', $ip);
            $host = $hostParts[0];
            $portFromHost = isset($hostParts[1]) ? (int)$hostParts[1] : null;
            if ($portFromHost) {
                $connectionPort = $portFromHost;
            }
        }

        try {
            $mt = new Mikrotik([
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => intval($connectionPort),
            ]);
            $result = $mt->comm('/ping', [
                'address' => $target,
                'count' => 4
            ]);
            return $this->response->setJSON(['data' => $result]);
        } catch (\Exception $e) {
            log_message('debug', 'Mikrotik connection failed for router ID ' . $id . ' (pingMikrotik): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Mikrotik API error: ' . $e->getMessage()]);
        }
    }
    // Endpoint: /routers/{id}/pppoe-active-count
    public function pppoeActiveCount($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }
        $ip = $router['ip_router'] ?? '';
        $username = $router['username'] ?? '';
        $password = $router['password_router'] ?? '';
        $port = $router['port_api'] ?? 8728;

        // Parse host dan port dengan benar seperti di method lain
        $host = $ip;
        $connectionPort = $port;

        // Jika ip_router berformat "host:port", pisahkan
        if (strpos($ip, ':') !== false) {
            $hostParts = explode(':', $ip);
            $host = $hostParts[0];
            $portFromHost = isset($hostParts[1]) ? (int)$hostParts[1] : null;
            if ($portFromHost) {
                $connectionPort = $portFromHost;
            }
        }

        try {
            $mt = new Mikrotik([
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => intval($connectionPort),
            ]);
            // Ambil data PPPoE aktif
            $result = $mt->comm('/ppp/active/print');
            $count = is_array($result) ? count($result) : 0;
            return $this->response->setJSON(['count' => $count]);
        } catch (\Exception $e) {
            log_message('debug', 'Mikrotik connection failed for router ID ' . $id . ' (pppoeActiveCount): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['count' => 0, 'error' => 'Mikrotik API error: ' . $e->getMessage()]);
        }
    }
    // GET: /routers/{id}/edit
    public function edit($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Data tidak ditemukan']);
        }
        // id_lokasi adalah primary key di LokasiServerModel
        return $this->response->setJSON([
            'id' => $router['id_lokasi'],
            'ip_router' => $router['ip_router'] ?? '',
            'username' => $router['username'] ?? '',
            'password' => $router['password_router'] ?? '',
            'port_api' => $router['port_api'] ?? '',
        ]);
    }    // Endpoint: /routers/{id}/pppoe-binding-stats
    public function pppoeBindingStats($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }
        $ip = $router['ip_router'] ?? '';
        $username = $router['username'] ?? '';
        $password = $router['password_router'] ?? '';
        $port = $router['port_api'] ?? 8728;

        // Parse host dan port dengan benar seperti di method lain
        $host = $ip;
        $connectionPort = $port;

        // Jika ip_router berformat "host:port", pisahkan
        if (strpos($ip, ':') !== false) {
            $hostParts = explode(':', $ip);
            $host = $hostParts[0];
            $portFromHost = isset($hostParts[1]) ? (int)$hostParts[1] : null;
            if ($portFromHost) {
                $connectionPort = $portFromHost;
            }
        }

        try {
            $mt = new Mikrotik();
            $connected = $mt->connect($host, $username, $password, intval($connectionPort));

            if (!$connected) {
                log_message('error', 'MikroTik connection failed');
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to connect to MikroTik router']);
            }

            $pppoeSecrets = $mt->comm('/ppp/secret/print');
            $pppoeActive = $mt->comm('/ppp/active/print');
            $binding = $mt->comm('/ip/hotspot/ip-binding/print');

            $pppoeTotal = is_array($pppoeSecrets) ? count($pppoeSecrets) : 0;
            $pppoeActiveCount = is_array($pppoeActive) ? count($pppoeActive) : 0;
            $pppoeInactive = $pppoeTotal - $pppoeActiveCount;

            $bindingActive = 0;
            $bindingInactive = 0;
            if (is_array($binding)) {
                foreach ($binding as $b) {
                    if (isset($b['disabled']) && $b['disabled'] == 'false') $bindingActive++;
                    else $bindingInactive++;
                }
            }

            return $this->response->setJSON([
                'pppoe_inactive' => $pppoeInactive,
                'binding_active' => $bindingActive,
                'binding_inactive' => $bindingInactive,
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    // POST: /routers/store
    public function store()
    {
        $model = new \App\Models\LokasiServerModel();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'ip_router' => 'required|min_length[3]|max_length[255]',
            'username' => 'required',
            'password' => 'required',
            'port_api' => 'required|numeric'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name') ?? 'Router',
            'ip_router' => $this->request->getPost('ip_router'),
            'username' => $this->request->getPost('username'),
            'password_router' => $this->request->getPost('password'),
            'port_api' => $this->request->getPost('port_api'),
            'address' => $this->request->getPost('address') ?? '',
            'due_date' => $this->request->getPost('due_date') ?? 1,
            'tax' => $this->request->getPost('tax') ?? 0,
            'tax_amount' => $this->request->getPost('tax_amount') ?? 0,
            'is_connected' => $this->request->getPost('is_connected') ?? 0
        ];

        try {
            if ($model->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'title' => 'Berhasil',
                    'message' => 'Router berhasil ditambahkan!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Gagal',
                    'message' => 'Gagal menambahkan router.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    // DELETE: /routers/{id}/delete
    public function delete($id)
    {
        $model = new \App\Models\LokasiServerModel();

        try {
            $router = $model->find($id);
            if (!$router) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'title' => 'Tidak Ditemukan',
                    'message' => 'Router tidak ditemukan.'
                ]);
            }

            if ($model->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'title' => 'Berhasil',
                    'message' => 'Router berhasil dihapus!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Gagal',
                    'message' => 'Gagal menghapus router.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/{id}/pppoe/{pppoeId} - Get specific PPPoE connection for editing
    public function getPppoe($id, $pppoeId)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }

        // Parse connection details like other methods
        $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

        try {
            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed for getPppoe');
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Mikrotik API error: Failed to connect']);
            }

            // Get specific PPPoE secret by ID or username
            $result = $mt->comm('/ppp/secret/print', ['?name' => $pppoeId]);

            if (is_array($result) && count($result) > 0) {
                return $this->response->setJSON($result[0]);
            } else {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'PPPoE connection not found']);
            }
        } catch (\Exception $e) {
            log_message('debug', 'Mikrotik connection failed for router ID ' . $id . ' (getPppoe): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Mikrotik API error: ' . $e->getMessage()]);
        }
    } // POST: /routers/{id}/pppoe - Create new PPPoE connection
    public function createPppoe($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }

        // Get form data
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $service = $this->request->getPost('service') ?: 'any';
        $profile = $this->request->getPost('profile') ?: 'default';
        $remoteAddress = $this->request->getPost('remote_address');
        $localAddress = $this->request->getPost('local_address');
        $disabled = $this->request->getPost('disabled') ? 'true' : 'false';
        $comment = $this->request->getPost('comment');

        // Validation
        if (empty($username) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'Username and password are required',
                'message' => 'Username dan password wajib diisi'
            ]);
        }

        // Parse connection details like other methods
        $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

        try {
            log_message('info', "Creating PPPoE secret - Router: {$id}, Username: {$username}");

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed for createPppoe');
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'error' => 'Mikrotik API error: Failed to connect',
                    'message' => 'Gagal terhubung ke MikroTik'
                ]);
            }

            // Check if username already exists
            $existingSecrets = $mt->comm('/ppp/secret/print', ['?name=' . $username]);
            if (!empty($existingSecrets)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Username already exists',
                    'message' => 'Username "' . $username . '" sudah ada di router'
                ]);
            }

            // Prepare parameters for adding PPPoE secret
            $params = [
                'name' => $username,
                'password' => $password,
                'service' => $service,
                'profile' => $profile,
                'disabled' => $disabled
            ];

            if (!empty($remoteAddress)) {
                $params['remote-address'] = $remoteAddress;
            }
            if (!empty($localAddress)) {
                $params['local-address'] = $localAddress;
            }
            if (!empty($comment)) {
                $params['comment'] = $comment;
            }

            log_message('info', 'PPPoE secret parameters: ' . json_encode($params));

            // Add PPPoE secret
            $result = $mt->comm('/ppp/secret/add', $params);

            log_message('info', 'PPPoE secret creation result: ' . json_encode($result));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PPPoE profile berhasil ditambahkan ke MikroTik',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Mikrotik connection failed for router ID ' . $id . ' (createPppoe): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Mikrotik API error: ' . $e->getMessage(),
                'message' => 'Gagal terhubung ke MikroTik: ' . $e->getMessage()
            ]);
        }
    }    // PUT: /routers/{id}/pppoe/{pppoeId} - Update PPPoE connection
    public function updatePppoe($id, $pppoeId)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }

        // Get form data - support both JSON and form data
        $input = $this->request->getJSON(true);
        $username = $input['username'] ?? $this->request->getPost('username');
        $password = $input['password'] ?? $this->request->getPost('password');
        $service = $input['service'] ?? $this->request->getPost('service') ?: 'any';
        $profile = $input['profile'] ?? $this->request->getPost('profile') ?: 'default';
        $remoteAddress = $input['remote_address'] ?? $this->request->getPost('remote_address');
        $localAddress = $input['local_address'] ?? $this->request->getPost('local_address');
        $disabled = ($input['disabled'] ?? $this->request->getPost('disabled')) ? 'true' : 'false';
        $comment = $input['comment'] ?? $this->request->getPost('comment');

        // Parse connection details like other methods
        $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

        try {
            log_message('info', "Updating PPPoE secret - Router: {$id}, Username: {$pppoeId}");

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed for updatePppoe');
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'error' => 'Mikrotik API error: Failed to connect',
                    'message' => 'Gagal terhubung ke MikroTik'
                ]);
            }

            // Find the PPPoE secret by username
            $existing = $mt->comm('/ppp/secret/print', ['?name' => $pppoeId]);
            if (!is_array($existing) || count($existing) === 0) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'error' => 'PPPoE connection not found',
                    'message' => 'PPPoE dengan username "' . $pppoeId . '" tidak ditemukan'
                ]);
            }

            $secretId = $existing[0]['.id'];

            // Prepare parameters for updating PPPoE secret
            $params = ['.id' => $secretId];

            if (!empty($username)) $params['name'] = $username;
            if (!empty($password)) $params['password'] = $password;
            if (!empty($service)) $params['service'] = $service;
            if (!empty($profile)) $params['profile'] = $profile;
            if (!empty($remoteAddress)) $params['remote-address'] = $remoteAddress;
            if (!empty($localAddress)) $params['local-address'] = $localAddress;
            if (!empty($comment)) $params['comment'] = $comment;
            $params['disabled'] = $disabled;

            log_message('info', 'PPPoE update parameters: ' . json_encode($params));

            // Update PPPoE secret
            $result = $mt->comm('/ppp/secret/set', $params);

            log_message('info', 'PPPoE update result: ' . json_encode($result));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PPPoE profile berhasil diupdate',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Mikrotik connection failed for router ID ' . $id . ' (updatePppoe): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Mikrotik API error: ' . $e->getMessage(),
                'message' => 'Gagal terhubung ke MikroTik: ' . $e->getMessage()
            ]);
        }
    }    // DELETE: /routers/{id}/pppoe/{pppoeId} - Delete PPPoE connection
    public function deletePppoe($id, $pppoeId)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }

        // Parse connection details like other methods
        $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

        try {
            log_message('info', "Deleting PPPoE secret - Router: {$id}, Username: {$pppoeId}");

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed for deletePppoe');
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'error' => 'Mikrotik API error: Failed to connect',
                    'message' => 'Gagal terhubung ke MikroTik'
                ]);
            }

            // Find the PPPoE secret by username
            $existing = $mt->comm('/ppp/secret/print', ['?name' => $pppoeId]);
            if (!is_array($existing) || count($existing) === 0) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'error' => 'PPPoE connection not found',
                    'message' => 'PPPoE dengan username "' . $pppoeId . '" tidak ditemukan'
                ]);
            }

            $secretId = $existing[0]['.id'];

            // Remove PPPoE secret
            $result = $mt->comm('/ppp/secret/remove', ['.id' => $secretId]);

            log_message('info', 'PPPoE delete result: ' . json_encode($result));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PPPoE profile berhasil dihapus dari MikroTik',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Mikrotik connection failed for router ID ' . $id . ' (deletePppoe): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Mikrotik API error: ' . $e->getMessage(),
                'message' => 'Gagal terhubung ke MikroTik: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/{id}/system-resources - Get MikroTik system resources
    public function systemResources($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);
        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Router not found']);
        }

        $ip = $router['ip_router'] ?? '';
        $username = $router['username'] ?? '';
        $password = $router['password_router'] ?? '';
        $port = $router['port_api'] ?? 8728;

        try {
            // Parse connection details for tunnel connections
            $connectionDetails = $this->parseConnectionDetails($ip, $port);

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $username, $password, $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed for systemResources');
                return $this->response->setStatusCode(503)->setJSON([
                    'error' => 'Unable to connect to MikroTik router',
                    'details' => 'Connection failed'
                ]);
            }

            // Get system resource information
            $systemResource = $mt->comm('/system/resource/print');

            if (!$systemResource || !isset($systemResource[0])) {
                return $this->response->setStatusCode(503)->setJSON([
                    'error' => 'Unable to retrieve system resources from MikroTik'
                ]);
            }

            $resource = $systemResource[0];

            // Format the response data
            $response = [
                'success' => true,
                'data' => [
                    // Basic system info
                    'board_name' => $resource['board-name'] ?? 'Unknown',
                    'version' => $resource['version'] ?? 'Unknown',
                    'architecture' => $resource['architecture-name'] ?? 'Unknown',
                    'cpu_frequency' => $resource['cpu-frequency'] ?? 'Unknown',
                    'cpu_count' => $resource['cpu-count'] ?? 'Unknown',
                    'platform' => $resource['platform'] ?? 'Unknown',

                    // Memory information
                    'total_memory' => isset($resource['total-memory']) ? $this->formatBytes($resource['total-memory']) : 'Unknown',
                    'free_memory' => isset($resource['free-memory']) ? $this->formatBytes($resource['free-memory']) : 'Unknown',
                    'used_memory' => isset($resource['total-memory'], $resource['free-memory'])
                        ? $this->formatBytes($resource['total-memory'] - $resource['free-memory']) : 'Unknown',
                    'memory_usage_percent' => isset($resource['total-memory'], $resource['free-memory']) && $resource['total-memory'] > 0
                        ? round((($resource['total-memory'] - $resource['free-memory']) / $resource['total-memory']) * 100, 1) : 0,

                    // Storage information
                    'total_hdd_space' => isset($resource['total-hdd-space']) ? $this->formatBytes($resource['total-hdd-space']) : 'Unknown',
                    'free_hdd_space' => isset($resource['free-hdd-space']) ? $this->formatBytes($resource['free-hdd-space']) : 'Unknown',
                    'used_hdd_space' => isset($resource['total-hdd-space'], $resource['free-hdd-space'])
                        ? $this->formatBytes($resource['total-hdd-space'] - $resource['free-hdd-space']) : 'Unknown',
                    'hdd_usage_percent' => isset($resource['total-hdd-space'], $resource['free-hdd-space']) && $resource['total-hdd-space'] > 0
                        ? round((($resource['total-hdd-space'] - $resource['free-hdd-space']) / $resource['total-hdd-space']) * 100, 1) : 0,

                    // Performance information
                    'cpu_load' => $resource['cpu-load'] ?? '0%',
                    'cpu_load_percent' => isset($resource['cpu-load']) ? (int) str_replace('%', '', $resource['cpu-load']) : 0,
                    'uptime' => $resource['uptime'] ?? 'Unknown',
                    'uptime_formatted' => isset($resource['uptime']) ? $this->formatUptime($resource['uptime']) : 'Unknown',

                    // Additional information
                    'factory_firmware' => $resource['factory-firmware'] ?? 'Unknown',
                    'current_firmware' => $resource['current-firmware'] ?? 'Unknown',
                    'upgrade_firmware' => $resource['upgrade-firmware'] ?? 'Unknown',

                    // Raw data for debugging
                    'raw_data' => $resource
                ],
                'timestamp' => time()
            ];

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get system resources for router ID ' . $id . ': ' . $e->getMessage());
            return $this->response->setStatusCode(503)->setJSON([
                'error' => 'Unable to connect to MikroTik router',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if (!is_numeric($bytes) || $bytes < 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Format uptime to human readable format
     */
    private function formatUptime($uptime)
    {
        // MikroTik uptime format examples: "1w2d3h4m5s", "5d10h30m", "2h45m30s"
        if (empty($uptime)) {
            return 'Unknown';
        }

        // Parse MikroTik uptime format
        $formatted = [];

        if (preg_match('/(\d+)w/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' week' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' day' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' hour' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' minute' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' second' . ($matches[1] > 1 ? 's' : '');
        }

        return !empty($formatted) ? implode(', ', $formatted) : $uptime;
    }



    // GET: /routers/isolir - Show isolir page with filtering and management
    public function isolir()
    {
        // Get router list for dropdown
        $routerModel = new \App\Models\LokasiServerModel();
        $routers = $routerModel->findAll();

        // Get customer data with PPPoE info
        $customerModel = new \App\Models\CustomerModel();
        $customers = $customerModel->select('id_customers, nama_pelanggan, pppoe_username, id_lokasi_server, status_tagihan, isolir_status, isolir_date, isolir_reason')
            ->where('pppoe_username IS NOT NULL')
            ->where('pppoe_username !=', '')
            ->where('isolir_status', 1)
            ->findAll();

        // Check if this is an AJAX request for refresh
        if ($this->request->getGet('ajax')) {
            return $this->response->setJSON([
                'success' => true,
                'customers' => $customers,
                'routers' => $routers
            ]);
        }

        // Debug: log jumlah dan contoh data customer
        log_message('debug', 'Isolir page: customers count = ' . count($customers));
        if (!empty($customers)) {
            log_message('debug', 'Isolir page: first customer = ' . print_r($customers[0], true));
        } else {
            log_message('debug', 'Isolir page: customers array is empty');
        }

        $data = [
            'title' => 'Isolir PPPoE Management',
            'subtitle' => 'Manajemen Isolir PPPoE Customers',
            'routers' => $routers,
            'customers' => $customers
        ];

        return view('routers/isolir', $data);
    }

    // POST: /routers/isolir/execute - Execute isolir for specific customer
    public function executeIsolir()
    {
        try {
            $customerId = $this->request->getPost('customer_id');
            $routerId = $this->request->getPost('router_id');
            $action = $this->request->getPost('action'); // 'isolir' or 'unIsolir'
            $reason = $this->request->getPost('reason') ?? '';

            if (!$customerId || !$routerId || !$action) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap (customer_id, router_id, action diperlukan)'
                ]);
            }

            // Get customer data
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            if (empty($customer['pppoe_username'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak memiliki PPPoE username'
                ]);
            }

            // Get router data
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);
            if (!$router) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router tidak ditemukan'
                ]);
            }

            // Execute isolir action in MikroTik
            $result = $this->executeMikrotikIsolir($router, $customer['pppoe_username'], $action);

            if ($result['success']) {
                // Update customer isolir status in database
                $updateData = [];
                if ($action === 'isolir') {
                    $updateData = [
                        'isolir_status' => 1,
                        'isolir_date' => date('Y-m-d H:i:s'),
                        'isolir_reason' => $reason
                    ];
                } else {
                    $updateData = [
                        'isolir_status' => 0,
                        'isolir_date' => null,
                        'isolir_reason' => null
                    ];
                }

                $customerModel->update($customerId, $updateData);

                // Log the action
                $this->logIsolirAction($customerId, $routerId, $action, $reason, 'success');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => ucfirst($action) . ' berhasil untuk customer ' . $customer['nama_pelanggan'],
                    'data' => $result['data']
                ]);
            } else {
                // Log the failed action
                $this->logIsolirAction($customerId, $routerId, $action, $reason, 'failed', $result['message']);

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal ' . $action . ': ' . $result['message']
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in executeIsolir: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/isolir/bulk-execute - Execute bulk isolir
    public function bulkExecuteIsolir()
    {
        try {
            $customerIds = $this->request->getPost('customer_ids'); // Array of customer IDs
            $action = $this->request->getPost('action'); // 'isolir' or 'unIsolir'
            $reason = $this->request->getPost('reason') ?? '';

            if (empty($customerIds) || !is_array($customerIds) || !$action) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap (customer_ids array dan action diperlukan)'
                ]);
            }

            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($customerIds as $customerId) {
                // Get customer data
                $customerModel = new \App\Models\CustomerModel();
                $customer = $customerModel->find($customerId);

                if (!$customer || empty($customer['pppoe_username'])) {
                    $results[] = [
                        'customer_id' => $customerId,
                        'success' => false,
                        'message' => 'Customer tidak valid atau tidak memiliki PPPoE'
                    ];
                    $failCount++;
                    continue;
                }

                // Get router data
                $routerModel = new \App\Models\LokasiServerModel();
                $router = $routerModel->find($customer['id_lokasi_server']);

                if (!$router) {
                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $customer['nama_pelanggan'],
                        'success' => false,
                        'message' => 'Router tidak ditemukan'
                    ];
                    $failCount++;
                    continue;
                }

                // Execute isolir action
                $result = $this->executeMikrotikIsolir($router, $customer['pppoe_username'], $action);

                if ($result['success']) {
                    // Update customer isolir status
                    $updateData = [];
                    if ($action === 'isolir') {
                        $updateData = [
                            'isolir_status' => 1,
                            'isolir_date' => date('Y-m-d H:i:s'),
                            'isolir_reason' => $reason
                        ];
                    } else {
                        $updateData = [
                            'isolir_status' => 0,
                            'isolir_date' => null,
                            'isolir_reason' => null
                        ];
                    }

                    $customerModel->update($customerId, $updateData);
                    $this->logIsolirAction($customerId, $customer['id_lokasi_server'], $action, $reason, 'success');

                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $customer['nama_pelanggan'],
                        'success' => true,
                        'message' => ucfirst($action) . ' berhasil'
                    ];
                    $successCount++;
                } else {
                    $this->logIsolirAction($customerId, $customer['id_lokasi_server'], $action, $reason, 'failed', $result['message']);

                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $customer['nama_pelanggan'],
                        'success' => false,
                        'message' => 'Gagal ' . $action . ': ' . $result['message']
                    ];
                    $failCount++;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Bulk {$action} selesai. Berhasil: {$successCount}, Gagal: {$failCount}",
                'summary' => [
                    'total' => count($customerIds),
                    'success' => $successCount,
                    'failed' => $failCount
                ],
                'results' => $results
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in bulkExecuteIsolir: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/isolir/log - Get isolir action logs
    public function isolirLog()
    {
        try {
            $limit = $this->request->getGet('limit') ?? 100;
            $offset = $this->request->getGet('offset') ?? 0;
            $customerId = $this->request->getGet('customer_id');
            $db = \Config\Database::connect();
            $builder = $db->table('isolir_log il');
            $builder->select('il.*, c.nama_pelanggan, c.pppoe_username, ls.name as router_name');
            $builder->join('customers c', 'c.id_customers = il.customer_id', 'left');
            $builder->join('lokasi_server ls', 'ls.id_lokasi = il.router_id', 'left');

            if ($customerId) {
                $builder->where('il.customer_id', $customerId);
            }

            $builder->orderBy('il.created_at', 'DESC');
            $builder->limit($limit, $offset);

            $logs = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $logs,
                'count' => count($logs)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in isolirLog: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute isolir action in MikroTik router
     */
    private function executeMikrotikIsolir($router, $pppoeUsername, $action)
    {
        try {
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 10,
            ]);

            if ($action === 'isolir') {
                // Get PPPoE secret info first to save original profile
                $secrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);
                if (empty($secrets)) {
                    return [
                        'success' => false,
                        'message' => 'PPPoE secret tidak ditemukan di router'
                    ];
                }

                $secretId = $secrets[0]['.id'];
                $originalProfile = $secrets[0]['profile'] ?? '';
                $isolirProfile = 'isolir'; // atau ambil dari config

                // Change to isolir profile and disable if needed
                $result = $mt->comm('/ppp/secret/set', [
                    '.id' => $secretId,
                    'profile' => $isolirProfile,
                    // Uncomment next line if you want to also disable
                    // 'disabled' => 'yes'
                ]);

                // Disconnect active connections to force re-authentication with new profile
                $activeConnections = $mt->comm('/ppp/active/print', ['?name' => $pppoeUsername]);
                if (!empty($activeConnections)) {
                    foreach ($activeConnections as $connection) {
                        $mt->comm('/ppp/active/remove', ['.id' => $connection['.id']]);
                    }
                }

                // Log the profile change for future un-isolir
                $this->logIsolirWithProfile($pppoeUsername, $router['id_lokasi'], 'isolir', $originalProfile, $isolirProfile, 'success');

                return [
                    'success' => true,
                    'message' => 'PPPoE user berhasil diisolir (profile changed to ' . $isolirProfile . ')',
                    'data' => [
                        'original_profile' => $originalProfile,
                        'isolir_profile' => $isolirProfile,
                        'mikrotik_result' => $result
                    ]
                ];
            } else { // unIsolir
                // Get PPPoE secret info
                $secrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);
                if (empty($secrets)) {
                    return [
                        'success' => false,
                        'message' => 'PPPoE secret tidak ditemukan di router'
                    ];
                }

                $secretId = $secrets[0]['.id'];
                $currentProfile = $secrets[0]['profile'] ?? '';

                // Get original profile from log
                $originalProfile = $this->getOriginalProfileFromLog($pppoeUsername, $router['id_lokasi']);

                if (!$originalProfile) {
                    // Fallback: try to get from customer database
                    $originalProfile = $this->getCustomerDefaultProfile($pppoeUsername);
                }

                // Restore original profile and enable
                $result = $mt->comm('/ppp/secret/set', [
                    '.id' => $secretId,
                    'profile' => $originalProfile,
                    'disabled' => 'no'
                ]);

                // Log the profile restoration
                $this->logIsolirWithProfile($pppoeUsername, $router['id_lokasi'], 'unIsolir', $currentProfile, $originalProfile, 'success');

                return [
                    'success' => true,
                    'message' => 'PPPoE user berhasil dibuka isolirnya (profile restored to ' . $originalProfile . ')',
                    'data' => [
                        'previous_profile' => $currentProfile,
                        'restored_profile' => $originalProfile,
                        'mikrotik_result' => $result
                    ]
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'MikroTik isolir error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Koneksi MikroTik gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get original profile from isolir log
     */
    private function getOriginalProfileFromLog($pppoeUsername, $routerId)
    {
        try {
            $db = \Config\Database::connect();

            // Find the last isolir action for this username
            // Note: status field may not exist in all records, so don't filter by it
            $query = $db->table('isolir_log')
                ->where('username', $pppoeUsername)
                ->where('router_id', $routerId)
                ->where('action', 'isolir')
                ->orderBy('created_at', 'DESC')
                ->limit(1);

            $result = $query->get()->getRowArray();

            if ($result && isset($result['old_profile'])) {
                log_message('info', "Found original profile for $pppoeUsername: " . $result['old_profile']);
                return $result['old_profile'];
            }

            log_message('warning', "No isolir log found for username: $pppoeUsername, router: $routerId");
            return null;
        } catch (\Exception $e) {
            log_message('error', 'Error getting original profile from log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get customer default profile
     */
    private function getCustomerDefaultProfile($pppoeUsername)
    {
        try {
            // Get customer data
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->where('pppoe_username', $pppoeUsername)->first();

            if ($customer && !empty($customer['group_profile_id'])) {
                // Get profile name from group_profile table if exists
                $db = \Config\Database::connect();
                $profile = $db->table('group_profile')
                    ->select('profile_name')
                    ->where('id', $customer['group_profile_id'])
                    ->get()
                    ->getRowArray();

                if ($profile && !empty($profile['profile_name'])) {
                    return $profile['profile_name'];
                }
            }

            // Default fallback
            return 'default';
        } catch (\Exception $e) {
            log_message('error', 'Error getting customer default profile: ' . $e->getMessage());
            return 'default';
        }
    }

    /**
     * Log isolir action with profile information
     */
    private function logIsolirWithProfile($username, $routerId, $action, $oldProfile, $newProfile, $status, $errorMessage = null)
    {
        try {
            $db = \Config\Database::connect();

            // Add columns if they don't exist
            $fields = $db->getFieldNames('isolir_log');
            if (!in_array('username', $fields)) {
                $forge = \Config\Database::forge();
                $forge->addColumn('isolir_log', [
                    'username' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => true,
                        'after' => 'error_message'
                    ]
                ]);
            }
            if (!in_array('old_profile', $fields)) {
                $forge = \Config\Database::forge();
                $forge->addColumn('isolir_log', [
                    'old_profile' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => true,
                        'after' => 'username'
                    ]
                ]);
            }
            if (!in_array('new_profile', $fields)) {
                $forge = \Config\Database::forge();
                $forge->addColumn('isolir_log', [
                    'new_profile' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => true,
                        'after' => 'old_profile'
                    ]
                ]);
            }

            // Insert log record
            $logData = [
                'customer_id' => 0, // Will be updated if customer ID available
                'router_id' => $routerId,
                'action' => $action,
                'reason' => "Profile change: $oldProfile -> $newProfile",
                'status' => $status,
                'error_message' => $errorMessage,
                'username' => $username,
                'old_profile' => $oldProfile,
                'new_profile' => $newProfile,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('isolir_log')->insert($logData);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log isolir action with profile: ' . $e->getMessage());
        }
    }

    /**
     * Log isolir action to database
     */
    private function logIsolirAction($customerId, $routerId, $action, $reason, $status, $errorMessage = null)
    {
        try {
            $db = \Config\Database::connect();

            // Create isolir_log table if not exists
            $forge = \Config\Database::forge();
            if (!$db->tableExists('isolir_log')) {
                $forge->addField([
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true,
                    ],
                    'customer_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'router_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'action' => [
                        'type' => 'ENUM',
                        'constraint' => ['isolir', 'unIsolir'],
                    ],
                    'reason' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'status' => [
                        'type' => 'ENUM',
                        'constraint' => ['success', 'failed'],
                    ],
                    'error_message' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'created_at' => [
                        'type' => 'TIMESTAMP',
                        'default' => 'CURRENT_TIMESTAMP',
                    ],
                ]);
                $forge->addKey('id', true);
                $forge->addKey('customer_id');
                $forge->addKey('router_id');
                $forge->createTable('isolir_log');
            }

            // Insert log entry
            $data = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'action' => $action,
                'reason' => $reason,
                'status' => $status,
                'error_message' => $errorMessage,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->table('isolir_log')->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log isolir action: ' . $e->getMessage());
        }
    }

    // GET: /routers/auto-isolir-config - Show auto isolir configuration page
    public function autoIsolirConfig()
    {
        $data = [
            'title' => 'Konfigurasi Auto Isolir',
            'subtitle' => 'Pengaturan Auto Isolir untuk Pelanggan Menunggak'
        ];

        // Get router list
        $routerModel = new \App\Models\LokasiServerModel();
        $routers = $routerModel->findAll();
        $data['routers'] = $routers;

        // Get auto isolir configurations
        $configModel = new \App\Models\AutoIsolirConfigModel();
        $configs = $configModel->findAll();
        $data['configs'] = $configs;

        return view('routers/auto_isolir_config', $data);
    }

    // POST: /routers/auto-isolir-config/save - Save auto isolir configuration
    public function saveAutoIsolirConfig()
    {
        try {
            $routerId = $this->request->getPost('router_id');
            $isolirIp = $this->request->getPost('isolir_ip');
            $isolirPageUrl = $this->request->getPost('isolir_page_url');
            $gracePeriod = $this->request->getPost('grace_period_days') ?? 0;
            $isEnabled = $this->request->getPost('is_enabled') ? 1 : 0;

            if (!$routerId || !$isolirIp) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router dan IP Isolir wajib diisi'
                ]);
            }

            $configModel = new \App\Models\AutoIsolirConfigModel();

            // Check if config already exists for this router
            $existingConfig = $configModel->getByRouter($routerId);

            $data = [
                'router_id' => $routerId,
                'isolir_ip' => $isolirIp,
                'isolir_page_url' => $isolirPageUrl,
                'grace_period_days' => $gracePeriod,
                'is_enabled' => $isEnabled
            ];

            if ($existingConfig) {
                // Update existing config
                $result = $configModel->update($existingConfig['id'], $data);
                $message = 'Konfigurasi auto isolir berhasil diupdate';
            } else {
                // Create new config
                $result = $configModel->insert($data);
                $message = 'Konfigurasi auto isolir berhasil disimpan';
            }

            if ($result) {
                // BACKGROUND SETUP: Setup profile/pool isolir di MikroTik dengan timeout handling
                if ($isEnabled) {
                    // Update database immediately
                    $configModel->where('router_id', $routerId)->set([
                        'pool_name' => 'pool-isolir',
                        'profile_name' => 'profile-isolir',
                        'setup_completed' => 0, // Will be updated when setup actually completes
                        'last_setup_at' => date('Y-m-d H:i:s')
                    ])->update();

                    // Try simple setup with timeout protection
                    $setupResult = $this->setupIsolirProfileAndPoolSimple($routerId, $isolirIp);
                    if ($setupResult['success']) {
                        $message .= ' - Setup MikroTik: ' . $setupResult['message'];
                        $configModel->where('router_id', $routerId)->set(['setup_completed' => 1])->update();
                    } else {
                        $message .= ' - ' . $setupResult['message'];
                    }

                    // INFO: Auto isolir siap digunakan
                    $message .= ' - Auto isolir siap dijalankan dengan tombol "Jalankan"';
                }
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan konfigurasi'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving auto isolir config: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/auto-isolir-config/delete/{id} - Delete auto isolir configuration
    public function deleteAutoIsolirConfig($id)
    {
        try {
            $configModel = new \App\Models\AutoIsolirConfigModel();

            if ($configModel->delete($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Konfigurasi auto isolir berhasil dihapus'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menghapus konfigurasi'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting auto isolir config: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/getAutoIsolirConfig/{id} - Get auto isolir config for view
    public function getAutoIsolirConfig($id)
    {
        try {
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $config = $configModel->find($id);

            if (!$config) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Konfigurasi tidak ditemukan'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting auto isolir config: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/addAutoIsolirConfig - Add new auto isolir config
    public function addAutoIsolirConfig()
    {
        // Just redirect to saveAutoIsolirConfig method
        return $this->saveAutoIsolirConfig();
    }

    // GET: /routers/editAutoIsolirConfig/{id} - Get auto isolir config for editing
    public function editAutoIsolirConfig($id)
    {
        try {
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $config = $configModel->find($id);

            if (!$config) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Konfigurasi tidak ditemukan'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting auto isolir config: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function updateAutoIsolirConfig()
    {
        try {
            $configId = $this->request->getPost('id');
            $routerId = $this->request->getPost('router_id');
            $isolirIp = $this->request->getPost('isolir_ip');
            $isolirPageUrl = $this->request->getPost('isolir_page_url');
            $gracePeriod = $this->request->getPost('grace_period_days') ?? 0;
            $isEnabled = $this->request->getPost('is_enabled') ? 1 : 0;

            if (!$configId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID konfigurasi tidak ditemukan'
                ]);
            }

            if (!$routerId || !$isolirIp) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router dan IP Isolir wajib diisi'
                ]);
            }

            $configModel = new \App\Models\AutoIsolirConfigModel();

            $data = [
                'router_id' => $routerId,
                'isolir_ip' => $isolirIp,
                'isolir_page_url' => $isolirPageUrl,
                'grace_period_days' => $gracePeriod,
                'is_enabled' => $isEnabled
            ];

            $result = $configModel->update($configId, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Konfigurasi berhasil diupdate'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengupdate konfigurasi'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating auto isolir config: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/auto-isolir/run - Run auto isolir manually
    public function runAutoIsolir()
    {
        try {
            // Set execution time limit to avoid timeout (5 minutes max)
            set_time_limit(300);

            $isolatedCount = 0;
            $failedCount = 0;
            $details = [];

            // Ambil semua router yang aktif dan memiliki konfigurasi isolir
            $isolirConfigModel = new \App\Models\AutoIsolirConfigModel();
            $activeConfigs = $isolirConfigModel->where('is_enabled', 1)->findAll();

            if (empty($activeConfigs)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada konfigurasi isolir yang aktif'
                ]);
            }

            // Loop untuk setiap router
            foreach ($activeConfigs as $config) {
                $routerId = $config['router_id'];

                // Ambil customer yang telat bayar untuk router ini
                $customerModel = new \App\Models\CustomerModel();                // Query customer yang telat bayar (tgl_tempo < hari ini dan belum isolir)
                $overdueCustomers = $customerModel->select('customers.*, lokasi_server.id_lokasi as router_id')
                    ->join('lokasi_server', 'customers.id_lokasi_server = lokasi_server.id_lokasi')
                    ->where('customers.tgl_tempo <', date('Y-m-d'))
                    ->where('customers.isolir_status', 0)
                    ->where('lokasi_server.id_lokasi', $routerId)
                    ->where('customers.pppoe_username IS NOT NULL')
                    ->where('customers.pppoe_username !=', '')
                    ->findAll();

                foreach ($overdueCustomers as $customer) {
                    try {
                        // Proses isolir untuk customer ini dengan timeout protection
                        $isolirResult = $this->processCustomerIsolirFast($customer, $config);
                        if ($isolirResult['success']) {
                            $isolatedCount++;
                            $details[] = "Berhasil isolir: " . ($customer['pppoe_username'] ?? $customer['nama_pelanggan']);
                        } else {
                            $failedCount++;
                            $details[] = "Gagal isolir: " . ($customer['pppoe_username'] ?? $customer['nama_pelanggan']) . " - " . $isolirResult['message'];
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $details[] = "Error isolir: " . ($customer['username'] ?? $customer['name']) . " - " . $e->getMessage();
                        log_message('error', 'Auto isolir error for customer ' . $customer['id'] . ': ' . $e->getMessage());
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Auto isolir selesai. Berhasil: {$isolatedCount}, Gagal: {$failedCount}",
                'data' => [
                    'isolated_count' => $isolatedCount,
                    'failed_count' => $failedCount,
                    'details' => implode('; ', array_slice($details, 0, 10)) // Batasi detail
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in runAutoIsolir: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menjalankan auto isolir: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Run auto isolir for specific router only
     */
    private function runAutoIsolirForSpecificRouter($routerId)
    {
        try {
            $isolatedCount = 0;
            $failedCount = 0;
            $details = [];

            // Ambil konfigurasi isolir untuk router ini
            $isolirConfigModel = new \App\Models\AutoIsolirConfigModel();
            $config = $isolirConfigModel->where('router_id', $routerId)
                ->where('is_enabled', 1)
                ->first();

            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada konfigurasi isolir yang aktif untuk router ini'
                ];
            }

            // Ambil customer yang telat bayar untuk router ini
            $customerModel = new \App\Models\CustomerModel();
            $overdueCustomers = $customerModel->select('customers.*, lokasi_server.id_lokasi as router_id')
                ->join('lokasi_server', 'customers.id_lokasi_server = lokasi_server.id_lokasi')
                ->where('customers.tgl_tempo <', date('Y-m-d'))
                ->where('customers.isolir_status', 0)
                ->where('lokasi_server.id_lokasi', $routerId)
                ->where('customers.pppoe_username IS NOT NULL')
                ->where('customers.pppoe_username !=', '')
                ->findAll();

            foreach ($overdueCustomers as $customer) {
                try {
                    // Proses isolir untuk customer ini
                    $isolirResult = $this->processCustomerIsolir($customer, $config);
                    if ($isolirResult['success']) {
                        $isolatedCount++;
                        $details[] = "Berhasil isolir: " . ($customer['pppoe_username'] ?? $customer['nama_pelanggan']);
                    } else {
                        $failedCount++;
                        $details[] = "Gagal isolir: " . ($customer['pppoe_username'] ?? $customer['nama_pelanggan']) . " - " . $isolirResult['message'];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $details[] = "Error isolir: " . ($customer['pppoe_username'] ?? $customer['nama_pelanggan']) . " - " . $e->getMessage();
                    log_message('error', 'Auto isolir error for customer ' . $customer['id_customers'] . ': ' . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'message' => "Auto isolir selesai untuk router ini. Berhasil: {$isolatedCount}, Gagal: {$failedCount}",
                'isolated_count' => $isolatedCount,
                'failed_count' => $failedCount,
                'details' => $details
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in runAutoIsolirForSpecificRouter: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menjalankan auto isolir: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Simple setup isolir profile and pool with timeout protection
     */
    private function setupIsolirProfileAndPoolSimple($routerId, $isolirIp)
    {
        try {
            // Quick database update first
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $configModel->where('router_id', $routerId)->set([
                'pool_name' => 'pool-isolir',
                'profile_name' => 'profile-isolir'
            ])->update();

            // Return success immediately - MikroTik setup can be done manually if needed
            return [
                'success' => true,
                'message' => 'Konfigurasi disimpan (setup MikroTik dapat dilakukan manual jika diperlukan)'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fast setup isolir profile and pool in MikroTik (optimized for speed)
     */
    private function setupIsolirProfileAndPoolFast($routerId, $isolirIp, $isolirPageUrl)
    {
        try {
            log_message('info', "Starting setup isolir for router ID: {$routerId}, IP: {$isolirIp}");

            // Ambil data router
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);

            if (!$router) {
                log_message('error', "Router not found for ID: {$routerId}");
                return ['success' => false, 'message' => 'Router tidak ditemukan'];
            }

            log_message('info', "Router found: {$router['name']} at {$router['ip_router']}");

            // Koneksi ke MikroTik dengan timeout singkat
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            log_message('info', "Connecting to MikroTik: {$connectionDetails['host']}:{$connectionDetails['port']}");

            try {
                $mt = new Mikrotik();
                $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

                if (!$connected) {
                    log_message('error', 'MikroTik connection failed in setupIsolirProfileAndPoolFast');
                    return ['success' => false, 'message' => 'Koneksi MikroTik gagal: Connection failed'];
                }
                log_message('info', "MikroTik connection established");
            } catch (\Exception $connError) {
                log_message('error', "MikroTik connection failed: " . $connError->getMessage());
                return ['success' => false, 'message' => 'Koneksi MikroTik gagal: ' . $connError->getMessage()];
            }
            $setupCount = 0;
            $setupMessages = [];

            // 1. Setup IP Pool Isolir (with retry mechanism)
            $poolName = 'pool-isolir';
            $poolRange = $isolirIp . '-' . $isolirIp;

            log_message('info', "Creating IP pool: {$poolName} with range: {$poolRange}");

            $poolSuccess = false;
            $retryCount = 2;

            for ($i = 0; $i < $retryCount && !$poolSuccess; $i++) {
                try {
                    if ($i > 0) {
                        log_message('info', "Retrying IP pool setup (attempt " . ($i + 1) . ")");
                        sleep(1); // Short delay before retry
                    }

                    // Try to create pool directly (faster than checking first)
                    $result = $mt->comm('/ip/pool/add', [
                        'name' => $poolName,
                        'ranges' => $poolRange
                    ]);
                    log_message('info', "IP pool created successfully");
                    $setupMessages[] = "Pool '{$poolName}' dibuat";
                    $setupCount++;
                    $poolSuccess = true;
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'already have') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                        // Pool already exists, try to update it
                        try {
                            $existingPools = $mt->comm('/ip/pool/print', ['?name' => $poolName]);
                            if (!empty($existingPools)) {
                                $mt->comm('/ip/pool/set', [
                                    '.id' => $existingPools[0]['.id'],
                                    'ranges' => $poolRange
                                ]);
                                log_message('info', "IP pool updated successfully");
                                $setupMessages[] = "Pool '{$poolName}' diupdate";
                                $setupCount++;
                                $poolSuccess = true;
                            }
                        } catch (\Exception $updateError) {
                            log_message('error', "IP pool update failed: " . $updateError->getMessage());
                            if ($i == $retryCount - 1) {
                                $setupMessages[] = "Pool error: " . $updateError->getMessage();
                            }
                        }
                    } else {
                        log_message('error', "IP pool setup failed: " . $e->getMessage());
                        if ($i == $retryCount - 1) {
                            $setupMessages[] = "Pool timeout/error - akan skip";
                        }
                    }
                }
            }            // 2. Setup PPPoE Profile Isolir
            $profileName = 'profile-isolir';

            log_message('info', "Creating PPPoE profile: {$profileName}");

            try {
                // Check if profile already exists first
                $existingProfiles = $mt->comm('/ppp/profile/print', ['?name' => $profileName]);

                if (empty($existingProfiles)) {
                    // Create new profile
                    $result = $mt->comm('/ppp/profile/add', [
                        'name' => $profileName,
                        'local-address' => $isolirIp,
                        'remote-address' => $poolName,
                        'rate-limit' => '1M/1M',
                        'session-timeout' => '0',
                        'idle-timeout' => '0'
                    ]);
                    log_message('info', "PPPoE profile created successfully");
                    $setupMessages[] = "Profile '{$profileName}' dibuat";
                    $setupCount++;
                } else {
                    // Update existing profile
                    $mt->comm('/ppp/profile/set', [
                        '.id' => $existingProfiles[0]['.id'],
                        'local-address' => $isolirIp,
                        'remote-address' => $poolName,
                        'rate-limit' => '1M/1M',
                        'session-timeout' => '0',
                        'idle-timeout' => '0'
                    ]);
                    log_message('info', "PPPoE profile updated successfully");
                    $setupMessages[] = "Profile '{$profileName}' diupdate";
                    $setupCount++;
                }
            } catch (\Exception $e) {
                log_message('error', "PPPoE profile setup failed: " . $e->getMessage());
                $setupMessages[] = "Profile error: " . $e->getMessage();
            }

            // Update database dengan setup info (quick update)
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $configModel->where('router_id', $routerId)->set([
                'pool_name' => $poolName,
                'profile_name' => $profileName,
                'setup_completed' => 1,
                'last_setup_at' => date('Y-m-d H:i:s')
            ])->update();

            log_message('info', "Setup completed. Count: {$setupCount}, Messages: " . implode(', ', $setupMessages));

            $resultMessage = "Setup berhasil ({$setupCount}/2 item): " . implode(', ', $setupMessages);

            return [
                'success' => true,
                'message' => $resultMessage,
                'details' => [
                    'pool_name' => $poolName,
                    'profile_name' => $profileName,
                    'setup_count' => $setupCount,
                    'messages' => $setupMessages
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in setupIsolirProfileAndPoolFast: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Setup gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test MikroTik connection and setup for debugging
     */
    public function testMikrotikSetup()
    {
        try {
            $routerId = $this->request->getPost('router_id');
            $isolirIp = $this->request->getPost('isolir_ip') ?? '192.168.100.1';

            if (!$routerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router ID diperlukan'
                ]);
            }

            // Test setup
            $result = $this->setupIsolirProfileAndPoolFast($routerId, $isolirIp, 'https://isolir.kimonet.my.id/');

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Test error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Setup isolir profile and pool in MikroTik (full version)
     */
    private function setupIsolirProfileAndPool($routerId, $isolirIp, $isolirPageUrl)
    {
        try {
            // Ambil data router
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);

            if (!$router) {
                return ['success' => false, 'message' => 'Router tidak ditemukan'];
            }

            // Koneksi ke MikroTik
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed in setupIsolirProfileAndPool');
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke MikroTik: Connection failed'
                ];
            }

            $setupMessages = [];

            // 1. Create IP Pool Isolir
            $poolName = 'pool-isolir';
            $poolRange = $isolirIp . '-' . $isolirIp; // Single IP pool

            try {
                // Check if pool exists
                $existingPools = $mt->comm('/ip/pool/print', ['?name' => $poolName]);

                if (empty($existingPools)) {
                    // Create new pool
                    $mt->comm('/ip/pool/add', [
                        'name' => $poolName,
                        'ranges' => $poolRange
                    ]);
                    $setupMessages[] = "Pool isolir '{$poolName}' berhasil dibuat";
                } else {
                    // Update existing pool
                    $mt->comm('/ip/pool/set', [
                        '.id' => $existingPools[0]['.id'],
                        'ranges' => $poolRange
                    ]);
                    $setupMessages[] = "Pool isolir '{$poolName}' berhasil diupdate";
                }
            } catch (\Exception $e) {
                $setupMessages[] = "Pool isolir error: " . $e->getMessage();
            }

            // 2. Create PPPoE Profile Isolir
            $profileName = 'profile-isolir';

            try {
                // Check if profile exists
                $existingProfiles = $mt->comm('/ppp/profile/print', ['?name' => $profileName]);

                $profileData = [
                    'name' => $profileName,
                    'local-address' => $isolirIp,
                    'remote-address' => $poolName,
                    'rate-limit' => '1M/1M', // Limited speed for isolir
                    'session-timeout' => '0',
                    'idle-timeout' => '0'
                ];

                if (empty($existingProfiles)) {
                    // Create new profile
                    $mt->comm('/ppp/profile/add', $profileData);
                    $setupMessages[] = "Profile isolir '{$profileName}' berhasil dibuat";
                } else {
                    // Update existing profile
                    $profileData['.id'] = $existingProfiles[0]['.id'];
                    $mt->comm('/ppp/profile/set', $profileData);
                    $setupMessages[] = "Profile isolir '{$profileName}' berhasil diupdate";
                }
            } catch (\Exception $e) {
                $setupMessages[] = "Profile isolir error: " . $e->getMessage();
            }

            // 3. Setup Hotspot Walled Garden (if needed)
            if (!empty($isolirPageUrl)) {
                try {
                    $domain = parse_url($isolirPageUrl, PHP_URL_HOST);
                    if ($domain) {
                        // Check if walled garden exists
                        $existingWalledGarden = $mt->comm('/ip/hotspot/walled-garden/print', ['?dst-host' => $domain]);

                        if (empty($existingWalledGarden)) {
                            $mt->comm('/ip/hotspot/walled-garden/add', [
                                'dst-host' => $domain,
                                'action' => 'allow',
                                'comment' => 'Auto Isolir - ' . $domain
                            ]);
                            $setupMessages[] = "Walled garden untuk '{$domain}' berhasil dibuat";
                        }
                    }
                } catch (\Exception $e) {
                    $setupMessages[] = "Walled garden error: " . $e->getMessage();
                }
            }

            // Update database with setup info
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $configModel->where('router_id', $routerId)->set([
                'pool_name' => $poolName,
                'profile_name' => $profileName,
                'setup_completed' => 1,
                'last_setup_at' => date('Y-m-d H:i:s')
            ])->update();

            return [
                'success' => true,
                'message' => 'Setup isolir berhasil: ' . implode(', ', $setupMessages),
                'details' => $setupMessages
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in setupIsolirProfileAndPool: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal setup isolir: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process isolir for individual customer
     */
    private function processCustomerIsolir($customer, $config)
    {
        try {
            // Ambil data router
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($config['router_id']);

            if (!$router) {
                return ['success' => false, 'message' => 'Router tidak ditemukan'];
            }

            // Koneksi ke MikroTik
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed in processCustomerIsolir');
                return ['success' => false, 'message' => 'Koneksi MikroTik gagal: Connection failed'];
            }

            // Cari PPPoE secret customer
            $pppoeUsername = $customer['pppoe_username'] ?? '';

            if (empty($pppoeUsername)) {
                return ['success' => false, 'message' => 'Username PPPoE tidak ditemukan'];
            }

            // Update PPPoE secret ke profile isolir
            $pppoeSecrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);

            if (empty($pppoeSecrets)) {
                return ['success' => false, 'message' => 'PPPoE secret tidak ditemukan di MikroTik'];
            }

            $secretId = $pppoeSecrets[0]['.id'];
            $oldProfile = $pppoeSecrets[0]['profile'] ?? '';

            // Update ke profile isolir
            $isolirProfileName = $config['profile_name'] ?? 'profile-isolir';
            $mt->comm('/ppp/secret/set', [
                'numbers' => $secretId,
                'profile' => $isolirProfileName,
                'comment' => 'AUTO-ISOLIR - ' . date('Y-m-d H:i:s') . ' - Backup: ' . $oldProfile
            ]);

            // Log isolir
            $isolirLogModel = new \App\Models\IsolirLogModel();
            $logData = [
                'customer_id' => $customer['id_customers'],
                'router_id' => $config['router_id'],
                'username' => $pppoeUsername,
                'action' => 'isolir',
                'old_profile' => $oldProfile,
                'new_profile' => $isolirProfileName,
                'isolir_ip' => $config['isolir_ip'],
                'reason' => 'Auto isolir - Telat bayar (Due: ' . $customer['tgl_tempo'] . ')',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $isolirLogModel->insert($logData);

            // Update status customer
            $customerModel = new \App\Models\CustomerModel();
            $customerModel->update($customer['id_customers'], [
                'isolir_status' => 1,
                'isolir_date' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Berhasil diisolir customer ' . $pppoeUsername
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process customer isolir with fast timeout settings
     */
    private function processCustomerIsolirFast($customer, $config)
    {
        try {
            // Ambil data router
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($config['router_id']);

            if (!$router) {
                return ['success' => false, 'message' => 'Router tidak ditemukan'];
            }

            // Koneksi ke MikroTik dengan timeout sangat cepat
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik();
            $connected = $mt->connect($connectionDetails['host'], $router['username'], $router['password_router'], $connectionDetails['port']);

            if (!$connected) {
                log_message('error', 'MikroTik connection failed in processCustomerIsolirFast');
                return ['success' => false, 'message' => 'Koneksi MikroTik gagal: Connection failed'];
            }

            // Cari PPPoE secret customer
            $pppoeUsername = $customer['pppoe_username'] ?? '';

            if (empty($pppoeUsername)) {
                return ['success' => false, 'message' => 'Username PPPoE tidak ditemukan'];
            }

            // Update PPPoE secret ke profile isolir dengan timeout protection
            try {
                $pppoeSecrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);

                if (empty($pppoeSecrets)) {
                    return ['success' => false, 'message' => 'PPPoE secret tidak ditemukan di MikroTik'];
                }

                $secretId = $pppoeSecrets[0]['.id'];
                $oldProfile = $pppoeSecrets[0]['profile'] ?? '';

                // Update ke profile isolir
                $isolirProfileName = $config['profile_name'] ?? 'profile-isolir';
                $mt->comm('/ppp/secret/set', [
                    'numbers' => $secretId,
                    'profile' => $isolirProfileName,
                    'comment' => 'AUTO-ISOLIR - ' . date('Y-m-d H:i:s') . ' - Backup: ' . $oldProfile
                ]);

                // Log isolir
                $isolirLogModel = new \App\Models\IsolirLogModel();
                if (class_exists('\App\Models\IsolirLogModel')) {
                    $logData = [
                        'customer_id' => $customer['id_customers'],
                        'router_id' => $config['router_id'],
                        'username' => $pppoeUsername,
                        'action' => 'isolir',
                        'old_profile' => $oldProfile,
                        'new_profile' => $isolirProfileName,
                        'isolir_ip' => $config['isolir_ip'],
                        'reason' => 'Auto isolir - Telat bayar (Due: ' . $customer['tgl_tempo'] . ')',
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    $isolirLogModel->insert($logData);
                }

                // Update status customer
                $customerModel = new \App\Models\CustomerModel();
                $customerModel->update($customer['id_customers'], [
                    'isolir_status' => 1,
                    'isolir_date' => date('Y-m-d H:i:s'),
                    'isolir_reason' => 'Auto isolir - Telat bayar'
                ]);

                return [
                    'success' => true,
                    'message' => 'Berhasil diisolir customer ' . $pppoeUsername
                ];
            } catch (\Exception $e) {
                // Jika MikroTik timeout, update database saja
                log_message('warning', 'MikroTik timeout for customer ' . $pppoeUsername . ', updating database only');

                $customerModel = new \App\Models\CustomerModel();
                $customerModel->update($customer['id_customers'], [
                    'isolir_status' => 1,
                    'isolir_date' => date('Y-m-d H:i:s'),
                    'isolir_reason' => 'Auto isolir - Telat bayar (MikroTik timeout)'
                ]);

                return [
                    'success' => false,
                    'message' => 'Database updated, MikroTik timeout: ' . $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // GET: /routers (index page)
    public function index()
    {
        $model = new \App\Models\LokasiServerModel();
        $routers = $model->findAll();
        return view('routers/index', ['routers' => $routers]);
    }

    // PUT: /routers/{id}/update
    public function update($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Router tidak ditemukan'
            ]);
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'ip_router' => $this->request->getPost('ip_router'),
            'username' => $this->request->getPost('username'),
            'password_router' => $this->request->getPost('password_router'),
            'port_api' => $this->request->getPost('port_api'),
            'domain_name' => $this->request->getPost('domain_name'),
            'type' => $this->request->getPost('type'),
            'status' => $this->request->getPost('status'),
            'district' => $this->request->getPost('district'),
            'village' => $this->request->getPost('village'),
        ];


        try {
            $model->update($id, $data);

            return $this->response->setJSON([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data router berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error updating router: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal memperbarui router: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/{id}/details
    public function details($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Router tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $router
        ]);
    }

    // GET: /routers/{id}/status
    public function status($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Router tidak ditemukan'
            ]);
        }

        // Test connection to check current status
        $isConnected = false;
        try {
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 5,
            ]);

            // Simple test query to check if connection works
            $result = $mt->comm('/system/identity/print');
            $isConnected = !empty($result);
        } catch (\Exception $e) {
            $isConnected = false;
            log_message('debug', 'Router status check failed for router ID ' . $id . ': ' . $e->getMessage());
        }

        // Update connection status in database
        try {
            $model->update($id, [
                'is_connected' => $isConnected ? 1 : 0,
                'last_ping_check' => date('Y-m-d H:i:s'),
                'ping_status' => $isConnected ? 'online' : 'offline'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to update router status in database for router ID ' . $id . ': ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'router_id' => $id,
                'is_connected' => $isConnected ? 1 : 0,
                'ping_status' => $isConnected ? 'online' : 'offline',
                'last_checked' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // POST: /routers/{id}/toggle-status
    public function toggleStatus($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Router tidak ditemukan'
            ]);
        }

        $newStatus = $router['status'] === 'active' ? 'inactive' : 'active';

        try {
            $model->update($id, ['status' => $newStatus]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Status router berhasil diubah',
                'data' => ['new_status' => $newStatus]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengubah status router: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/{id}/logs
    public function logs($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Router tidak ditemukan'
            ]);
        }

        // Get logs from database or log files related to this router
        // This is a placeholder - implement according to your logging system
        $logs = [
            [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => 'info',
                'message' => 'Router connection test successful',
                'details' => 'Test connection to ' . $router['ip_router']
            ]
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    // GET: /routers/{id}/monitoring
    public function monitoring($id)
    {
        return view('routers/monitoring', ['router_id' => $id]);
    }

    // POST: /routers/{id}/clear-logs
    public function clearLogs($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Router tidak ditemukan'
            ]);
        }

        try {
            // Clear logs related to this router
            // Implement log clearing logic here

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Log router berhasil dibersihkan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal membersihkan log: ' . $e->getMessage()
            ]);
        }
    }    // GET: /routers/isolir-config-modal
    public function isolirConfigModal()
    {
        $model = new \App\Models\LokasiServerModel();
        $routers = $model->findAll();
        return view('routers/isolir_config_modal', ['routers' => $routers]);
    }

    // POST: /routers/setup-isolir-profile
    public function setupIsolirProfile()
    {
        try {
            $routerId = $this->request->getPost('router_id');
            $profileName = $this->request->getPost('profile_name') ?: 'Profile-Isolir';
            $isolirSubnet = $this->request->getPost('isolir_subnet') ?: '172.16.100.0/24';
            $isolirStartIp = $this->request->getPost('isolir_start_ip') ?: '172.16.100.1';
            $isolirEndIp = $this->request->getPost('isolir_end_ip') ?: '172.16.100.254';
            $bandwidth = $this->request->getPost('bandwidth') ?: '1M/1M';

            if (!$routerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router ID wajib dipilih'
                ]);
            }

            $model = new \App\Models\LokasiServerModel();
            $router = $model->find($routerId);

            if (!$router) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router tidak ditemukan'
                ]);
            }

            // Koneksi ke MikroTik
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 10,
            ]);

            // 1. Buat IP Pool untuk isolir
            $poolName = 'pool-isolir';
            $poolData = [
                'name' => $poolName,
                'ranges' => $isolirStartIp . '-' . $isolirEndIp
            ];

            // Hapus pool lama jika ada
            $existingPools = $mt->comm('/ip/pool/print', ['?name=' . $poolName]);
            if (!empty($existingPools)) {
                $mt->comm('/ip/pool/remove', ['numbers' => $existingPools[0]['.id']]);
            }

            // Tambah pool baru
            $mt->comm('/ip/pool/add', $poolData);

            // 2. Buat PPP Profile untuk isolir
            $profileData = [
                'name' => $profileName,
                'local-address' => '172.16.100.1',
                'remote-address' => $poolName,
                'rate-limit' => $bandwidth,
                'comment' => 'Profile untuk pelanggan isolir - Auto Generated'
            ];

            // Hapus profile lama jika ada
            $existingProfiles = $mt->comm('/ppp/profile/print', ['?name=' . $profileName]);
            if (!empty($existingProfiles)) {
                $mt->comm('/ppp/profile/remove', ['numbers' => $existingProfiles[0]['.id']]);
            }

            // Tambah profile baru
            $mt->comm('/ppp/profile/add', $profileData);

            // 3. Buat Address List untuk redirect isolir
            $addressListData = [
                'list' => 'isolir-redirect',
                'address' => $isolirSubnet,
                'comment' => 'Subnet isolir untuk redirect'
            ];

            // Hapus address list lama jika ada
            $existingAddressList = $mt->comm('/ip/firewall/address-list/print', [
                '?list=isolir-redirect',
                '?address=' . $isolirSubnet
            ]);
            if (!empty($existingAddressList)) {
                $mt->comm('/ip/firewall/address-list/remove', ['numbers' => $existingAddressList[0]['.id']]);
            }

            // Tambah address list baru
            $mt->comm('/ip/firewall/address-list/add', $addressListData);

            // 4. Simpan konfigurasi isolir ke database
            $isolirConfigModel = new \App\Models\AutoIsolirConfigModel();

            // Hapus konfigurasi lama untuk router ini
            $isolirConfigModel->where('router_id', $routerId)->delete();

            // Simpan konfigurasi baru
            $configData = [
                'router_id' => $routerId,
                'profile_name' => $profileName,
                'isolir_subnet' => $isolirSubnet,
                'isolir_start_ip' => $isolirStartIp,
                'isolir_end_ip' => $isolirEndIp,
                'bandwidth' => $bandwidth,
                'is_enabled' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $isolirConfigModel->insert($configData);

            return $this->response->setJSON([
                'success' => true,
                'title' => 'Berhasil',
                'message' => 'Konfigurasi isolir berhasil dibuat! Profile: ' . $profileName . ', Pool: ' . $poolName,
                'data' => [
                    'profile_name' => $profileName,
                    'pool_name' => $poolName,
                    'ip_range' => $isolirStartIp . ' - ' . $isolirEndIp,
                    'bandwidth' => $bandwidth
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error setting up isolir profile: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Gagal membuat konfigurasi isolir: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/auto-isolir-customer
    public function autoIsolirCustomer()
    {
        try {
            $customerId = $this->request->getPost('customer_id');
            $routerId = $this->request->getPost('router_id');

            if (!$customerId || !$routerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer ID dan Router ID wajib diisi'
                ]);
            }

            // Ambil data customer
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }            // Ambil konfigurasi isolir
            $isolirConfigModel = new \App\Models\AutoIsolirConfigModel();
            $config = $isolirConfigModel->where('router_id', $routerId)
                ->where('is_enabled', 1)
                ->first();

            if (!$config) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Konfigurasi isolir belum disetup untuk router ini'
                ]);
            }

            // Ambil data router
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);

            if (!$router) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router tidak ditemukan'
                ]);
            }

            // Koneksi ke MikroTik
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 10,
            ]);

            // Cari PPPoE secret customer
            $pppoeUsername = $customer['username'] ?? $customer['pppoe_username'] ?? '';

            if (empty($pppoeUsername)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Username PPPoE customer tidak ditemukan'
                ]);
            }

            // Update PPPoE secret ke profile isolir
            $pppoeSecrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);

            if (empty($pppoeSecrets)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PPPoE secret dengan username "' . $pppoeUsername . '" tidak ditemukan di MikroTik'
                ]);
            }

            $secretId = $pppoeSecrets[0]['.id'];
            $oldProfile = $pppoeSecrets[0]['profile'] ?? '';

            // Update ke profile isolir
            $mt->comm('/ppp/secret/set', [
                'numbers' => $secretId,
                'profile' => $config['profile_name'],
                'comment' => 'ISOLIR - ' . date('Y-m-d H:i:s') . ' - Backup Profile: ' . $oldProfile
            ]);

            // Log isolir
            $isolirLogModel = new \App\Models\IsolirLogModel();
            $logData = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'username' => $pppoeUsername,
                'action' => 'isolir',
                'old_profile' => $oldProfile,
                'new_profile' => $config['profile_name'],
                'isolir_ip' => 'Pool: ' . $config['isolir_start_ip'] . ' - ' . $config['isolir_end_ip'],
                'reason' => 'Telat bayar - Auto Isolir',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $isolirLogModel->insert($logData);

            // Update status customer
            $customerModel->update($customerId, [
                'status' => 'isolir',
                'isolir_date' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'title' => 'Berhasil',
                'message' => 'Customer ' . $pppoeUsername . ' berhasil diisolir ke profile ' . $config['profile_name'],
                'data' => [
                    'username' => $pppoeUsername,
                    'old_profile' => $oldProfile,
                    'new_profile' => $config['profile_name'],
                    'isolir_subnet' => $config['isolir_subnet']
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error auto isolir customer: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Gagal melakukan isolir: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/restore-customer/{id}
    public function restoreCustomer($customerId)
    {
        try {
            if (!$customerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer ID wajib diisi'
                ]);
            }

            // Ambil data customer
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Ambil log isolir terakhir
            $isolirLogModel = new \App\Models\IsolirLogModel();
            $lastIsolirLog = $isolirLogModel->where('customer_id', $customerId)
                ->where('action', 'isolir')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$lastIsolirLog) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data isolir tidak ditemukan'
                ]);
            }

            $routerId = $lastIsolirLog['router_id'];
            $pppoeUsername = $lastIsolirLog['username'];
            $originalProfile = $lastIsolirLog['old_profile'];

            // Ambil data router
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);

            if (!$router) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router tidak ditemukan'
                ]);
            }

            // Koneksi ke MikroTik
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new Mikrotik([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 10,
            ]);

            // Cari PPPoE secret customer
            $pppoeSecrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);

            if (empty($pppoeSecrets)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PPPoE secret tidak ditemukan di MikroTik'
                ]);
            }

            $secretId = $pppoeSecrets[0]['.id'];

            // Restore ke profile asli
            $mt->comm('/ppp/secret/set', [
                'numbers' => $secretId,
                'profile' => $originalProfile,
                'comment' => 'RESTORED - ' . date('Y-m-d H:i:s') . ' - From Isolir'
            ]);

            // Log restore
            $logData = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'username' => $pppoeUsername,
                'action' => 'restore',
                'old_profile' => $lastIsolirLog['new_profile'], // Profile isolir
                'new_profile' => $originalProfile, // Profile asli
                'reason' => 'Manual restore - Pembayaran lunas',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $isolirLogModel->insert($logData);

            // Update status customer
            $customerModel->update($customerId, [
                'status' => 'active',
                'isolir_date' => null,
                'restore_date' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'title' => 'Berhasil',
                'message' => 'Customer ' . $pppoeUsername . ' berhasil dipulihkan ke profile ' . $originalProfile,
                'data' => [
                    'username' => $pppoeUsername,
                    'restored_profile' => $originalProfile
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error restore customer: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'title' => 'Gagal',
                'message' => 'Gagal melakukan restore: ' . $e->getMessage()
            ]);
        }
    }

    // GET: /routers/isolir-status/{router_id}
    public function isolirStatus($routerId)
    {
        try {            // Ambil konfigurasi isolir
            $isolirConfigModel = new \App\Models\AutoIsolirConfigModel();
            $config = $isolirConfigModel->where('router_id', $routerId)
                ->where('is_enabled', 1)
                ->first();

            // Ambil statistik isolir
            $isolirLogModel = new \App\Models\IsolirLogModel();

            $totalIsolir = $isolirLogModel->where('router_id', $routerId)
                ->where('action', 'isolir')
                ->countAllResults();

            $totalRestore = $isolirLogModel->where('router_id', $routerId)
                ->where('action', 'restore')
                ->countAllResults();

            $recentLogs = $isolirLogModel->where('router_id', $routerId)
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'config' => $config,
                    'stats' => [
                        'total_isolir' => $totalIsolir,
                        'total_restore' => $totalRestore,
                        'current_isolated' => $totalIsolir - $totalRestore
                    ],
                    'recent_logs' => $recentLogs
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting isolir status: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil status isolir: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/auto-isolir-config/setup-profiles - Setup isolir profiles, pools, and address-lists
    public function setupIsolirProfiles()
    {
        try {
            $routerId = $this->request->getPost('router_id');
            $routerModel = new \App\Models\LokasiServerModel();

            // Jika router_id tidak diberikan, ambil dari konfigurasi auto isolir yang ada
            if (!$routerId) {
                $configModel = new \App\Models\AutoIsolirConfigModel();
                $configs = $configModel->findAll();

                if (empty($configs)) {
                    // Jika belum ada konfigurasi, ambil router pertama yang tersedia
                    $routers = $routerModel->findAll();
                    if (empty($routers)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Tidak ada router yang tersedia. Tambahkan router terlebih dahulu.'
                        ]);
                    }
                    $routerId = $routers[0]['id_lokasi'];
                } else {
                    // Setup untuk semua router yang memiliki konfigurasi auto isolir
                    $results = [];
                    $errors = [];

                    foreach ($configs as $config) {
                        $result = $this->setupProfilesForRouter($config['router_id']);
                        if ($result['success']) {
                            $results[] = $result['message'];
                        } else {
                            $errors[] = $result['message'];
                        }
                    }

                    if (!empty($errors)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Setup selesai dengan error: ' . implode(', ', $errors)
                        ]);
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Setup berhasil untuk semua router: ' . implode(', ', $results)
                    ]);
                }
            }

            // Setup untuk router tunggal
            $result = $this->setupProfilesForRouter($routerId);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error in setupIsolirProfiles: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function setupProfilesForRouter($routerId)
    {
        try {
            // Get router details
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);

            if (!$router) {
                return [
                    'success' => false,
                    'message' => 'Router tidak ditemukan (ID: ' . $routerId . ')'
                ];
            }

            // Initialize Mikrotik connection
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => 8728
            ];
            $mikrotik = new \App\Libraries\MikrotikAPI($mikrotikConfig);

            if (!$mikrotik->isConnected()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal terhubung ke router MikroTik: ' . ($mikrotik->getLastError() ?? 'Unknown error')
                ]);
            }

            $connection = $mikrotik->getClient();

            $results = [];
            $errors = [];

            // Configuration
            $poolName = 'poolexpiredkimonet';
            $poolStart = '172.35.32.2';
            $poolEnd = '172.35.32.254';
            $gateway = '172.35.32.1';
            $profileName = 'expiredbillingku';
            $addressListName = 'isolir-redirect';
            $addressSubnet = '172.35.32.0/24';
            $bandwidth = '1M/1M';
            $comment = 'added by billingku';

            // 1. Create IP Pool
            try {
                // Check if pool exists
                $poolExists = false;
                $pools = $connection->comm('/ip/pool/print');
                foreach ($pools as $pool) {
                    if ($pool['name'] === $poolName) {
                        $poolExists = true;
                        break;
                    }
                }

                if (!$poolExists) {
                    $connection->comm('/ip/pool/add', [
                        'name' => $poolName,
                        'ranges' => $poolStart . '-' . $poolEnd,
                        'comment' => $comment
                    ]);
                    $results[] = "✅ IP Pool '$poolName' berhasil dibuat";
                } else {
                    $results[] = "ℹ️ IP Pool '$poolName' sudah ada";
                }
            } catch (\Exception $e) {
                $errors[] = "❌ Gagal membuat IP Pool: " . $e->getMessage();
            }

            // 2. Create PPP Profile
            try {
                // Check if profile exists
                $profileExists = false;
                $profiles = $connection->comm('/ppp/profile/print');
                foreach ($profiles as $profile) {
                    if ($profile['name'] === $profileName) {
                        $profileExists = true;
                        break;
                    }
                }
                if (!$profileExists) {
                    $connection->comm('/ppp/profile/add', [
                        'name' => $profileName,
                        'remote-address' => $poolName,
                        'local-address' => $gateway,
                        'rate-limit' => $bandwidth,
                        'comment' => $comment
                    ]);
                    $results[] = "✅ PPP Profile '$profileName' berhasil dibuat (rate-limit: $bandwidth)";
                } else {
                    $results[] = "ℹ️ PPP Profile '$profileName' sudah ada";
                }
            } catch (\Exception $e) {
                $errors[] = "❌ Gagal membuat PPP Profile: " . $e->getMessage();
            }

            // 3. Create Address List
            try {
                // Check if address list exists
                $addressListExists = false;
                $addressLists = $connection->comm('/ip/firewall/address-list/print');
                foreach ($addressLists as $addressList) {
                    if ($addressList['list'] === $addressListName) {
                        $addressListExists = true;
                        break;
                    }
                }
                if (!$addressListExists) {
                    $connection->comm('/ip/firewall/address-list/add', [
                        'list' => $addressListName,
                        'address' => $addressSubnet,
                        'comment' => 'Subnet isolir untuk redirect'
                    ]);
                    $results[] = "✅ Address List '$addressListName' berhasil dibuat ($addressSubnet)";
                } else {
                    $results[] = "ℹ️ Address List '$addressListName' sudah ada";
                }
            } catch (\Exception $e) {
                $errors[] = "❌ Gagal membuat Address List: " . $e->getMessage();
            }

            // 4. Update auto isolir config
            try {
                $configModel = new \App\Models\AutoIsolirConfigModel();
                $config = $configModel->getByRouter($routerId);
                $configData = [
                    'router_id' => $routerId,
                    'isolir_ip' => $gateway,
                    'isolir_page_url' => 'https://isolir.kimonet.my.id',
                    'grace_period_days' => 0,
                    'is_enabled' => 1,
                    'pool_name' => $poolName,
                    'profile_name' => $profileName,
                    'address_list_name' => $addressListName,
                    'setup_completed' => 1,
                    'last_setup_at' => date('Y-m-d H:i:s')
                ];

                if ($config) {
                    $configModel->update($config['id'], $configData);
                } else {
                    $configModel->insert($configData);
                }

                $results[] = "✅ Konfigurasi auto isolir berhasil diupdate";
            } catch (\Exception $e) {
                $errors[] = "❌ Gagal update konfigurasi: " . $e->getMessage();
            }

            $connection->disconnect();            // Prepare response
            $allResults = array_merge($results, $errors);
            $success = empty($errors);

            return [
                'success' => $success,
                'message' => $success ? 'Setup isolir berhasil diselesaikan untuk ' . $router['name'] : 'Setup isolir selesai dengan beberapa error untuk ' . $router['name'],
                'details' => $allResults,
                'router_name' => $router['name']
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in setupProfilesForRouter: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan pada router ' . ($router['name'] ?? $routerId) . ': ' . $e->getMessage()
            ];
        }
    }    // POST: /routers/auto-isolir-config/verify-setup - Verify isolir setup
    public function verifyIsolirSetup()
    {
        try {
            $routerId = $this->request->getPost('router_id');

            // Jika router_id tidak diberikan, ambil dari konfigurasi auto isolir yang ada
            if (!$routerId) {
                $configModel = new \App\Models\AutoIsolirConfigModel();
                $configs = $configModel->findAll();

                if (empty($configs)) {
                    // Jika belum ada konfigurasi, ambil router pertama yang tersedia
                    $routerModel = new \App\Models\LokasiServerModel();
                    $routers = $routerModel->findAll();
                    if (empty($routers)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Tidak ada router yang tersedia. Tambahkan router terlebih dahulu.'
                        ]);
                    }
                    $routerId = $routers[0]['id_lokasi'];
                } else {
                    // Verifikasi untuk semua router yang memiliki konfigurasi auto isolir
                    $results = [];
                    $errors = [];

                    foreach ($configs as $config) {
                        $result = $this->verifySetupForRouter($config['router_id']);
                        if ($result['success']) {
                            $results[] = $result['message'];
                        } else {
                            $errors[] = $result['message'];
                        }
                    }

                    if (!empty($errors)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Verifikasi selesai dengan error: ' . implode(', ', $errors)
                        ]);
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Verifikasi berhasil untuk semua router: ' . implode(', ', $results)
                    ]);
                }
            }

            // Verifikasi untuk router tunggal
            $result = $this->verifySetupForRouter($routerId);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Error in verifyIsolirSetup: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function verifySetupForRouter($routerId)
    {
        try {
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($routerId);
            if (!$router) {
                return [
                    'success' => false,
                    'message' => 'Router tidak ditemukan (ID: ' . $routerId . ')'
                ];
            }

            // Initialize Mikrotik connection
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => (int)($router['port_api'] ?? 8728)
            ];
            $mikrotik = new \App\Libraries\MikrotikAPI($mikrotikConfig);
            if (!$mikrotik->isConnected()) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke router MikroTik: ' . ($mikrotik->getLastError() ?? 'Unknown error')
                ];
            }

            $connection = $mikrotik->getClient();

            $verification = [];
            $poolName = 'poolexpiredkimonet';
            $profileName = 'expiredbillingku';
            $addressListName = 'isolir-redirect';

            // Verify IP Pool
            $pools = $connection->comm('/ip/pool/print');
            $poolFound = false;
            foreach ($pools as $pool) {
                if ($pool['name'] === $poolName) {
                    $poolFound = true;
                    $verification['pool'] = [
                        'status' => 'found',
                        'name' => $pool['name'],
                        'ranges' => $pool['ranges'] ?? 'N/A',
                        'comment' => $pool['comment'] ?? 'N/A'
                    ];
                    break;
                }
            }
            if (!$poolFound) {
                $verification['pool'] = ['status' => 'not_found'];
            }

            // Verify PPP Profile
            $profiles = $connection->comm('/ppp/profile/print');
            $profileFound = false;
            foreach ($profiles as $profile) {
                if ($profile['name'] === $profileName) {
                    $profileFound = true;
                    $verification['profile'] = [
                        'status' => 'found',
                        'name' => $profile['name'],
                        'remote_address' => $profile['remote-address'] ?? 'N/A',
                        'local_address' => $profile['local-address'] ?? 'N/A',
                        'comment' => $profile['comment'] ?? 'N/A'
                    ];
                    break;
                }
            }
            if (!$profileFound) {
                $verification['profile'] = ['status' => 'not_found'];
            }

            // Verify Address List
            $addressLists = $connection->comm('/ip/firewall/address-list/print');
            $addressListFound = false;
            foreach ($addressLists as $addressList) {
                if ($addressList['list'] === $addressListName) {
                    $addressListFound = true;
                    $verification['address_list'] = [
                        'status' => 'found',
                        'list' => $addressList['list'],
                        'address' => $addressList['address'] ?? 'N/A',
                        'comment' => $addressList['comment'] ?? 'N/A'
                    ];
                    break;
                }
            }
            if (!$addressListFound) {
                $verification['address_list'] = ['status' => 'not_found'];
            }

            // Check database config
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $config = $configModel->getByRouter($routerId);
            $verification['database'] = [
                'status' => $config ? 'found' : 'not_found',
                'config' => $config
            ];

            $connection->disconnect();
            return [
                'success' => true,
                'message' => 'Verifikasi setup isolir berhasil untuk ' . $router['name'],
                'verification' => $verification,
                'router_name' => $router['name']
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in verifySetupForRouter: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan pada router ' . ($router['name'] ?? $routerId) . ': ' . $e->getMessage()
            ];
        }
    }

    // GET: /routers/auto-isolir-config/preview - Preview customers that will be isolated
    public function autoIsolirPreview()
    {
        try {
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $configs = $configModel->where('is_enabled', 1)->findAll();

            if (empty($configs)) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada konfigurasi auto isolir yang aktif'
                ]);
            }

            $customersToIsolate = [];
            foreach ($configs as $config) {
                $routerId = $config['router_id'];

                // Get customers that are overdue (tgl_tempo <= today)
                $today = date('Y-m-d');

                // Get customers that are overdue
                $customerModel = new \App\Models\CustomerModel();
                $overdueCustomers = $customerModel
                    ->select('id_customers, nama_pelanggan as nama, pppoe_username as username, tgl_tempo, isolir_status')
                    ->where('id_lokasi_server', $routerId)
                    ->where('tgl_tempo <=', $today)
                    ->where('isolir_status !=', 1)
                    ->where('pppoe_username IS NOT NULL')
                    ->where('pppoe_username !=', '')
                    ->findAll();

                foreach ($overdueCustomers as $customer) {
                    $daysOverdue = (strtotime('now') - strtotime($customer['tgl_tempo'])) / (60 * 60 * 24);
                    $customer['days_overdue'] = floor($daysOverdue);
                    $customersToIsolate[] = $customer;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $customersToIsolate,
                'count' => count($customersToIsolate)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in preview auto isolir: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // POST: /routers/auto-isolir-config/run - Run auto isolir process
    public function autoIsolirRun()
    {
        try {
            $isolatedCount = 0;
            $failedCount = 0;
            $details = [];

            // Get all active auto isolir configurations
            $configModel = new \App\Models\AutoIsolirConfigModel();
            $activeConfigs = $configModel->where('is_enabled', 1)->findAll();

            if (empty($activeConfigs)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada konfigurasi auto isolir yang aktif'
                ]);
            }
            foreach ($activeConfigs as $config) {
                $routerId = $config['router_id'];

                // Get customers that are overdue (tgl_tempo <= today)
                $today = date('Y-m-d');

                // Get customers that are overdue
                $customerModel = new \App\Models\CustomerModel();
                $overdueCustomers = $customerModel
                    ->where('id_lokasi_server', $routerId)
                    ->where('tgl_tempo <=', $today)
                    ->where('isolir_status !=', 1)
                    ->where('pppoe_username IS NOT NULL')
                    ->where('pppoe_username !=', '')
                    ->findAll();

                foreach ($overdueCustomers as $customer) {
                    try {
                        $result = $this->processCustomerAutoIsolir($customer, $config);
                        if ($result['success']) {
                            $isolatedCount++;
                            $details[] = "✅ {$customer['pppoe_username']}: Berhasil diisolir";
                        } else {
                            $failedCount++;
                            $details[] = "❌ {$customer['pppoe_username']}: {$result['message']}";
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $details[] = "❌ {$customer['pppoe_username']}: Error - {$e->getMessage()}";
                        log_message('error', 'Auto isolir failed for customer ' . $customer['id_customers'] . ': ' . $e->getMessage());
                    }
                }

                // Update last run time for this config
                $configModel->update($config['id'], [
                    'last_run' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Auto isolir selesai. Berhasil: {$isolatedCount}, Gagal: {$failedCount}",
                'isolated_count' => $isolatedCount,
                'failed_count' => $failedCount,
                'details' => implode('; ', array_slice($details, 0, 10)) // Limit details
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in run auto isolir: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process auto isolir for individual customer
     */
    private function processCustomerAutoIsolir($customer, $config)
    {
        try {
            // Get router details
            $routerModel = new \App\Models\LokasiServerModel();
            $router = $routerModel->find($config['router_id']);

            if (!$router) {
                return ['success' => false, 'message' => 'Router tidak ditemukan'];
            }

            // Initialize Mikrotik connection
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => (int)($router['port_api'] ?? 8728)
            ];
            $mikrotik = new \App\Libraries\MikrotikAPI($mikrotikConfig);

            if (!$mikrotik->isConnected()) {
                return ['success' => false, 'message' => 'Gagal terhubung ke MikroTik'];
            }

            $connection = $mikrotik->getClient();
            $pppoeUsername = $customer['pppoe_username'];

            // Find PPPoE secret
            $secrets = $connection->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);
            if (empty($secrets)) {
                return ['success' => false, 'message' => 'PPPoE secret tidak ditemukan'];
            }

            $secretId = $secrets[0]['.id'];
            $oldProfile = $secrets[0]['profile'] ?? 'default';

            // Update to isolir profile
            $isolirProfile = $config['profile_name'] ?? 'expiredbillingku';
            $connection->comm('/ppp/secret/set', [
                'numbers' => $secretId,
                'profile' => $isolirProfile,
                'comment' => 'AUTO-ISOLIR ' . date('Y-m-d H:i:s') . ' - Backup: ' . $oldProfile
            ]);

            // Disconnect active session if exists
            $activeSessions = $connection->comm('/ppp/active/print', ['?name' => $pppoeUsername]);
            if (!empty($activeSessions)) {
                foreach ($activeSessions as $session) {
                    $connection->comm('/ppp/active/remove', ['numbers' => $session['.id']]);
                }
            }

            $connection->disconnect();

            // Update customer status
            $customerModel = new \App\Models\CustomerModel();
            $customerModel->update($customer['id_customers'], [
                'isolir_status' => 1,
                'isolir_date' => date('Y-m-d H:i:s'),
                'isolir_reason' => 'Auto isolir - Telat bayar'
            ]);

            // Log the isolir action
            $this->logIsolirAction(
                $customer['id_customers'],
                $config['router_id'],
                'auto_isolir',
                'Telat bayar - Isolir langsung di hari tempo',
                'success'
            );

            return [
                'success' => true,
                'message' => 'Berhasil diisolir'
            ];
        } catch (\Exception $e) {
            // Log the failed isolir action
            $this->logIsolirAction(
                $customer['id_customers'] ?? null,
                $config['router_id'] ?? null,
                'auto_isolir',
                'Telat bayar - Isolir langsung di hari tempo',
                'failed',
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Simple method to get router data for dropdowns
    public function getData()
    {
        // Disable output buffering
        @ob_clean();

        try {
            $model = new \App\Models\LokasiServerModel();
            $routers = $model->findAll();

            // Format data for dropdown use
            $formattedRouters = [];
            foreach ($routers as $router) {
                $formattedRouters[] = [
                    'id' => $router['id_lokasi'],
                    'nama' => $router['nama'] ?? $router['name'] ?? 'Router-' . $router['id_lokasi'],
                    'ip_router' => $router['ip_router'] ?? 'N/A',
                    'label' => ($router['nama'] ?? $router['name'] ?? 'Router-' . $router['id_lokasi']) . ' (' . ($router['ip_router'] ?? 'N/A') . ')'
                ];
            }

            // Set proper headers for JSON response
            $this->response->setContentType('application/json');

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $formattedRouters,
                'message' => 'Router data loaded successfully',
                'count' => count($formattedRouters)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in getData: ' . $e->getMessage());

            // Set proper headers for error response
            $this->response->setContentType('application/json');

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Failed to load router data: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    // POST: /routers/{id}/update-connection-status
    public function updateConnectionStatus($id)
    {
        $model = new \App\Models\LokasiServerModel();
        $router = $model->find($id);

        if (!$router) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Router tidak ditemukan'
            ]);
        }

        $isConnected = $this->request->getPost('is_connected');
        $isConnected = $isConnected ? 1 : 0;

        try {
            $model->update($id, [
                'is_connected' => $isConnected,
                'last_ping_check' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Router connection status updated',
                'data' => [
                    'router_id' => $id,
                    'is_connected' => $isConnected,
                    'last_checked' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update router connection status: ' . $e->getMessage()
            ]);
        }
    }
}
