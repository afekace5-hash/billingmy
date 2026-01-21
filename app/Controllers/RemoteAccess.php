<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class RemoteAccess extends BaseController
{
    protected $customerModel;
    protected $geniacsModel;

    public function __construct()
    {
        $this->customerModel = model('CustomerModel');
        helper(['form', 'url']);
    }

    /**
     * Main page - List of customers with remote access
     */
    public function index()
    {
        // Get Geniacs configuration from database or .env
        $db = \Config\Database::connect();
        $geniacsUrl = getenv('GENIACS_SERVER_URL') ?: '';

        // Try to get URL from database first
        if ($db->tableExists('geniacs_settings')) {
            $builder = $db->table('geniacs_settings');
            $config = $builder->where('is_active', 1)->get()->getRowArray();

            if ($config) {
                $geniacsUrl = $config['server_url'];
            }
        }

        // Get credentials from .env (use default if not set)
        $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
        $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

        $data = [
            'title' => 'Remote Access - Geniacs',
            'geniacs_url' => $geniacsUrl,
            'geniacs_configured' => !empty($geniacsUrl)
        ];

        return view('remote-access/index', $data);
    }

    /**
     * Get devices data from GenieACS for DataTable
     */
    public function getData()
    {
        try {
            $request = \Config\Services::request();
            $draw = $request->getPost('draw') ?? 1;
            $start = $request->getPost('start') ?? 0;
            $length = $request->getPost('length') ?? 10;
            $searchValue = $request->getPost('search')['value'] ?? '';

            // Get Geniacs configuration
            $db = \Config\Database::connect();
            $geniacsUrl = getenv('GENIACS_SERVER_URL');

            if ($db->tableExists('geniacs_settings')) {
                $builder = $db->table('geniacs_settings');
                $config = $builder->where('is_active', 1)->get()->getRowArray();
                if ($config) {
                    $geniacsUrl = $config['server_url'];
                }
            }

            if (empty($geniacsUrl)) {
                return $this->response->setJSON([
                    'draw' => intval($draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'GenieACS not configured'
                ]);
            }

            // Get credentials from .env
            $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
            $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

            // Fetch devices from GenieACS
            $devices = $this->fetchDevicesFromGenieACS($geniacsUrl, $geniacsUsername, $geniacsPassword);

            // Handle false (error) vs empty array (no devices)
            if ($devices === false) {
                return $this->response->setJSON([
                    'draw' => intval($draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to connect to GenieACS. Check logs for details.'
                ]);
            }

            // Empty array is valid (no devices connected)
            if (empty($devices)) {
                return $this->response->setJSON([
                    'draw' => intval($draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }

            // Apply search filter
            if (!empty($searchValue)) {
                $devices = array_filter($devices, function ($device) use ($searchValue) {
                    $searchLower = strtolower($searchValue);
                    return stripos($device['serial_number'], $searchValue) !== false ||
                        stripos($device['model'], $searchValue) !== false ||
                        stripos($device['pppoe_username'], $searchValue) !== false;
                });
            }

            $totalRecords = count($devices);
            $filteredDevices = array_slice($devices, $start, $length);

            $data = [];
            foreach ($filteredDevices as $device) {
                // Status badge
                $statusBadge = $device['online']
                    ? '<span class="badge bg-success">Online</span>'
                    : '<span class="badge bg-danger">Offline</span>';

                // Signal badge
                $signal = $device['signal'];
                $signalClass = 'secondary';
                if ($signal > -20) $signalClass = 'success';
                elseif ($signal > -25) $signalClass = 'warning';
                elseif ($signal > -30) $signalClass = 'danger';

                $signalBadge = $signal !== '-'
                    ? '<span class="badge bg-' . $signalClass . '">' . $signal . ' dBm</span>'
                    : '<span class="badge bg-secondary">-</span>';

                $actions = '<div class="btn-group">';
                $actions .= '<button type="button" class="btn btn-sm btn-primary remote-access-btn" 
                                data-id="' . esc($device['device_id']) . '" 
                                data-serial="' . esc($device['serial_number']) . '" 
                                title="Remote Access">
                                <i class="bx bx-desktop"></i> Remote
                            </button>';
                $actions .= '<button type="button" class="btn btn-sm btn-warning wifi-settings-btn" 
                                data-id="' . esc($device['device_id']) . '" 
                                data-serial="' . esc($device['serial_number']) . '" 
                                title="WiFi Settings">
                                <i class="bx bx-wifi"></i>
                            </button>';
                $actions .= '<button type="button" class="btn btn-sm btn-info view-details-btn" 
                                data-id="' . esc($device['device_id']) . '" 
                                title="View Details">
                                <i class="bx bx-info-circle"></i>
                            </button>';
                $actions .= '</div>';

                $data[] = [
                    'serial_number' => esc($device['serial_number']),
                    'model' => esc($device['model']),
                    'pppoe_username' => esc($device['pppoe_username']),
                    'pppoe_mac' => esc($device['pppoe_mac']),
                    'status' => $statusBadge,
                    'signal' => $signalBadge,
                    'ssid' => esc($device['ssid']),
                    'actions' => $actions
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'RemoteAccess getData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get customer details for remote access
     */
    public function getCustomerDetail($id)
    {
        try {
            $customer = $this->customerModel->find($id);

            if (!$customer) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Customer not found'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Connect to Geniacs remote access
     */
    public function connectGeniacs()
    {
        try {
            $deviceId = $this->request->getPost('customer_id'); // This is actually device_id (serial number)

            if (empty($deviceId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Device ID tidak valid'
                ]);
            }

            // Get Geniacs configuration from database or .env
            $db = \Config\Database::connect();
            $geniacsUrl = getenv('GENIACS_SERVER_URL');

            // Try to get URL from database first
            if ($db->tableExists('geniacs_settings')) {
                $builder = $db->table('geniacs_settings');
                $config = $builder->where('is_active', 1)->get()->getRowArray();

                if ($config) {
                    $geniacsUrl = $config['server_url'];
                }
            }

            // Get credentials from .env (use default if not set)
            $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
            $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

            if (empty($geniacsUrl)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'GenieACS belum dikonfigurasi. Silakan konfigurasi URL server terlebih dahulu'
                ]);
            }

            // Fetch device from GenieACS by device ID
            $devices = $this->fetchDevicesFromGenieACS($geniacsUrl, $geniacsUsername, $geniacsPassword);

            if (!$devices) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data device dari GenieACS'
                ]);
            }

            // Debug: Log the deviceId being searched and available devices
            log_message('info', 'Looking for device with ID: ' . $deviceId);
            $availableIds = array_map(function ($dev) {
                return $dev['device_id'] ?? 'no-id';
            }, $devices);
            log_message('info', 'Available device IDs: ' . json_encode($availableIds));

            // Find the specific device by device_id (fetchDevicesFromGenieACS returns parsed data)
            $device = null;
            foreach ($devices as $dev) {
                if (isset($dev['device_id']) && $dev['device_id'] === $deviceId) {
                    $device = $dev;
                    break;
                }
            }

            if (!$device) {
                // Log for debugging
                log_message('error', 'Device not found. Looking for: ' . $deviceId);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Device tidak ditemukan di GenieACS. Device ID: ' . $deviceId
                ]);
            }

            // Get IP address from parsed device data
            $deviceIP = $device['ip_address'] ?? null;

            if (!$deviceIP) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak dapat menemukan IP address device'
                ]);
            }

            // Return device info for remote access
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Device ditemukan',
                'data' => [
                    'device_id' => $deviceId,
                    'ip_address' => $deviceIP,
                    'model' => $device['model'] ?? 'Unknown',
                    'serial_number' => $device['serial_number'] ?? $deviceId,
                    // For web interface access to device (if ONT has web UI)
                    'web_interface_url' => 'http://' . $deviceIP
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Geniacs connection error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Connection error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save Geniacs configuration
     */
    public function saveConfiguration()
    {
        try {
            $geniacsUrl = $this->request->getPost('geniacs_url');

            // Validate inputs
            if (empty($geniacsUrl)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'URL Server GenieACS harus diisi'
                ]);
            }

            // Get credentials from .env (use default if not set)
            $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
            $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

            // Test connection
            $testResult = $this->testGeniacsConnection($geniacsUrl, $geniacsUsername, $geniacsPassword);

            if (!$testResult['success']) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Connection test failed: ' . $testResult['message']
                ]);
            }

            // Save to database - create or update settings table
            $db = \Config\Database::connect();

            // Check if geniacs_settings table exists, if not create it
            if (!$db->tableExists('geniacs_settings')) {
                $forge = \Config\Database::forge();
                $forge->addField([
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true,
                    ],
                    'server_url' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                    ],
                    'is_active' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                    ],
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                    'updated_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
                $forge->addKey('id', true);
                $forge->createTable('geniacs_settings');
            }

            // Update or insert configuration
            $builder = $db->table('geniacs_settings');
            $existing = $builder->get()->getRowArray();

            if ($existing) {
                $builder->where('id', $existing['id'])->update([
                    'server_url' => $geniacsUrl,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $builder->insert([
                    'server_url' => $geniacsUrl,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Geniacs configuration saved successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test Geniacs connection
     */
    public function testConnection()
    {
        try {
            $geniacsUrl = $this->request->getPost('geniacs_url');

            // Get credentials from .env (use default if not set)
            $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
            $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

            $result = $this->testGeniacsConnection($geniacsUrl, $geniacsUsername, $geniacsPassword);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Call Geniacs API
     */
    private function callGeniacsAPI($url, $username, $password, $data)
    {
        try {
            // GenieACS typically uses port 7557 for API
            // Endpoint: /devices for listing devices
            $ch = curl_init($url . '/devices');
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new \Exception('CURL Error: ' . $error);
            }

            if ($httpCode !== 200) {
                throw new \Exception('HTTP Error: ' . $httpCode);
            }

            $result = json_decode($response, true);

            return [
                'success' => $result['status'] === 'success',
                'message' => $result['message'] ?? '',
                'session_id' => $result['session_id'] ?? null,
                'remote_url' => $result['remote_url'] ?? null,
                'data' => $result['data'] ?? []
            ];
        } catch (\Exception $e) {
            log_message('error', 'Geniacs API call error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test Geniacs connection
     */
    private function testGeniacsConnection($url, $username, $password)
    {
        try {
            // Test connection by querying /devices endpoint
            $ch = curl_init($url . '/devices?limit=1');
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'Connection failed: ' . $error
                ];
            }

            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Connection successful'
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed with HTTP code: ' . $httpCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch devices from GenieACS
     */
    private function fetchDevicesFromGenieACS($url, $username, $password)
    {
        try {
            // Get all device data - GenieACS returns full data by default
            $ch = curl_init($url . '/devices');
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                log_message('error', 'GenieACS fetch devices CURL error: ' . $error);
                return false;
            }

            if ($httpCode !== 200) {
                log_message('error', 'GenieACS fetch devices HTTP error: ' . $httpCode . ', Response: ' . substr($response, 0, 500));
                return false;
            }

            $devices = json_decode($response, true);

            // Log response untuk debugging
            log_message('info', 'GenieACS response: ' . substr($response, 0, 1000));

            if (!is_array($devices)) {
                log_message('error', 'GenieACS response is not array, type: ' . gettype($devices));
                return [];
            }

            // If empty array, return empty array instead of false
            if (empty($devices)) {
                log_message('info', 'GenieACS returned empty device list');
                return [];
            }

            // Parse device data from GenieACS format
            $parsedDevices = [];
            foreach ($devices as $device) {
                $deviceId = $device['_id'] ?? '';

                // Log device keys untuk debugging
                $deviceKeys = array_keys($device);
                log_message('info', 'Device ' . $deviceId . ' available keys: ' . implode(', ', array_slice($deviceKeys, 0, 20)));

                // Extract device information from GenieACS parameter structure
                $serialNumber = $device['_deviceId']['_SerialNumber'] ?? '-';
                $model = $device['_deviceId']['_ProductClass'] ?? '-';

                // Get connection status - handle timestamp properly
                $lastInform = $device['_lastInform'] ?? 0;

                // Parse timestamp - GenieACS returns ISO 8601 format string
                $lastInformTimestamp = 0;
                if (is_numeric($lastInform)) {
                    // If numeric, assume milliseconds
                    $lastInformTimestamp = (int)($lastInform / 1000);
                } elseif (is_string($lastInform) && !empty($lastInform)) {
                    // If string, use strtotime which handles ISO 8601
                    $lastInformTimestamp = strtotime($lastInform);
                }

                $currentTime = time();
                $timeDiff = $currentTime - $lastInformTimestamp;
                $online = $lastInformTimestamp > 0 && $timeDiff < 300; // Online if informed in last 5 minutes

                // Debug logging for device status
                if (strpos($deviceId, 'D49E04') !== false || strpos($deviceId, 'E48D8C') !== false) {
                    log_message('info', "$deviceId - Last Inform: $lastInform, Timestamp: $lastInformTimestamp, Current: $currentTime, Diff: $timeDiff sec, Online: " . ($online ? 'Yes' : 'No'));
                }

                // Get PPPoE username - support both TR-069 and TR-181 nested paths
                $pppoeUsername = '-';

                // Try TR-069 nested path (ONT) - loop through all possible indexes
                if (isset($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'])) {
                    $wanConnDevices = $device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'];
                    foreach ($wanConnDevices as $idx => $connDevice) {
                        if (isset($connDevice['WANPPPConnection'])) {
                            foreach ($connDevice['WANPPPConnection'] as $pppIdx => $pppConn) {
                                if (isset($pppConn['Username']['_value']) && !empty($pppConn['Username']['_value'])) {
                                    $pppoeUsername = $pppConn['Username']['_value'];
                                    break 2; // Exit both loops
                                }
                            }
                        }
                    }
                }

                // TR-181 nested path (MikroTik) - if still not found
                if ($pppoeUsername === '-' && isset($device['Device']['PPP']['Interface'])) {
                    $pppInterfaces = $device['Device']['PPP']['Interface'];
                    foreach ($pppInterfaces as $idx => $iface) {
                        if (isset($iface['Username']['_value']) && !empty($iface['Username']['_value'])) {
                            $pppoeUsername = $iface['Username']['_value'];
                            break;
                        }
                    }
                }

                // G665 XPON specific path - X_GponInterfaceConfig or X_EponInterfaceConfig
                if ($pppoeUsername === '-' && isset($device['InternetGatewayDevice']['WANDevice'])) {
                    foreach ($device['InternetGatewayDevice']['WANDevice'] as $wanIdx => $wanDevice) {
                        // Try X_GponInterfaceConfig
                        if (isset($wanDevice['X_GponInterfaceConfig']['Username']['_value'])) {
                            $pppoeUsername = $wanDevice['X_GponInterfaceConfig']['Username']['_value'];
                            break;
                        }
                        // Try X_EponInterfaceConfig
                        if ($pppoeUsername === '-' && isset($wanDevice['X_EponInterfaceConfig']['Username']['_value'])) {
                            $pppoeUsername = $wanDevice['X_EponInterfaceConfig']['Username']['_value'];
                            break;
                        }
                    }
                }

                // Try alternative path: WANPPPConnection with UserName (not Username)
                if ($pppoeUsername === '-' && isset($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'])) {
                    $wanConnDevices = $device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'];
                    foreach ($wanConnDevices as $idx => $connDevice) {
                        if (isset($connDevice['WANPPPConnection'])) {
                            foreach ($connDevice['WANPPPConnection'] as $pppIdx => $pppConn) {
                                // Some devices use "UserName" instead of "Username"
                                if (isset($pppConn['UserName']['_value']) && !empty($pppConn['UserName']['_value'])) {
                                    $pppoeUsername = $pppConn['UserName']['_value'];
                                    break 2;
                                }
                                // Try Name field
                                if ($pppoeUsername === '-' && isset($pppConn['Name']['_value']) && !empty($pppConn['Name']['_value'])) {
                                    $pppoeUsername = $pppConn['Name']['_value'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                // Get MAC Address - try multiple nested paths
                $pppoeMac = '-';

                // TR-069 WANPPPConnection MACAddress - loop through all possible indexes
                if (isset($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'])) {
                    $wanConnDevices = $device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'];
                    foreach ($wanConnDevices as $idx => $connDevice) {
                        // Try WANPPPConnection MACAddress
                        if (isset($connDevice['WANPPPConnection'])) {
                            foreach ($connDevice['WANPPPConnection'] as $pppIdx => $pppConn) {
                                if (isset($pppConn['MACAddress']['_value']) && !empty($pppConn['MACAddress']['_value'])) {
                                    $pppoeMac = $pppConn['MACAddress']['_value'];
                                    break 2;
                                }
                            }
                        }
                        // Try WANIPConnection MACAddress
                        if ($pppoeMac === '-' && isset($connDevice['WANIPConnection'])) {
                            foreach ($connDevice['WANIPConnection'] as $ipIdx => $ipConn) {
                                if (isset($ipConn['MACAddress']['_value']) && !empty($ipConn['MACAddress']['_value'])) {
                                    $pppoeMac = $ipConn['MACAddress']['_value'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                // Try LANDevice Ethernet MACAddress if not found
                if ($pppoeMac === '-' && isset($device['InternetGatewayDevice']['LANDevice'])) {
                    foreach ($device['InternetGatewayDevice']['LANDevice'] as $lanIdx => $lanDevice) {
                        if (isset($lanDevice['LANEthernetInterfaceConfig'])) {
                            foreach ($lanDevice['LANEthernetInterfaceConfig'] as $ethIdx => $ethConfig) {
                                if (isset($ethConfig['MACAddress']['_value']) && !empty($ethConfig['MACAddress']['_value'])) {
                                    $pppoeMac = $ethConfig['MACAddress']['_value'];
                                    break 2;
                                }
                            }
                        }
                        // Try LANHostConfigManagement
                        if ($pppoeMac === '-' && isset($lanDevice['LANHostConfigManagement']['MACAddress']['_value'])) {
                            $pppoeMac = $lanDevice['LANHostConfigManagement']['MACAddress']['_value'];
                            break;
                        }
                    }
                }

                // Try WANDevice WANEthernetInterfaceConfig if still not found
                if ($pppoeMac === '-' && isset($device['InternetGatewayDevice']['WANDevice'])) {
                    foreach ($device['InternetGatewayDevice']['WANDevice'] as $wanIdx => $wanDevice) {
                        if (isset($wanDevice['WANEthernetInterfaceConfig']['MACAddress']['_value'])) {
                            $pppoeMac = $wanDevice['WANEthernetInterfaceConfig']['MACAddress']['_value'];
                            break;
                        }
                    }
                }

                // TR-181 Ethernet.Interface - loop through all interfaces
                if ($pppoeMac === '-' && isset($device['Device']['Ethernet']['Interface'])) {
                    foreach ($device['Device']['Ethernet']['Interface'] as $idx => $iface) {
                        if (isset($iface['MACAddress']['_value']) && !empty($iface['MACAddress']['_value'])) {
                            $pppoeMac = $iface['MACAddress']['_value'];
                            break;
                        }
                    }
                }

                // TR-181 PPP Interface MAC
                if ($pppoeMac === '-' && isset($device['Device']['PPP']['Interface'])) {
                    foreach ($device['Device']['PPP']['Interface'] as $idx => $iface) {
                        if (isset($iface['MACAddress']['_value']) && !empty($iface['MACAddress']['_value'])) {
                            $pppoeMac = $iface['MACAddress']['_value'];
                            break;
                        }
                    }
                }

                // G665 XPON specific - try ManagementServer X_Config or DeviceInfo
                if ($pppoeMac === '-' && isset($device['InternetGatewayDevice']['DeviceInfo'])) {
                    // Try various DeviceInfo paths for G665
                    if (isset($device['InternetGatewayDevice']['DeviceInfo']['X_Config']['MACAddress']['_value'])) {
                        $pppoeMac = $device['InternetGatewayDevice']['DeviceInfo']['X_Config']['MACAddress']['_value'];
                    } elseif (isset($device['InternetGatewayDevice']['DeviceInfo']['MACAddress']['_value'])) {
                        $pppoeMac = $device['InternetGatewayDevice']['DeviceInfo']['MACAddress']['_value'];
                    }
                }

                // Try WANConnectionDevice X_Config paths
                if ($pppoeMac === '-' && isset($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'])) {
                    foreach ($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice'] as $idx => $connDevice) {
                        if (isset($connDevice['X_Config']['MACAddress']['_value'])) {
                            $pppoeMac = $connDevice['X_Config']['MACAddress']['_value'];
                            break;
                        }
                    }
                }

                // Debug logging for G665 device
                if ($model === 'G665' || strpos($model, 'G665') !== false || strpos($serialNumber, 'G665') !== false) {
                    log_message('info', "G665 Device $deviceId - PPPoE Username: $pppoeUsername, MAC: $pppoeMac");
                    log_message('info', "G665 Available top keys: " . json_encode(array_keys($device)));
                    if (isset($device['InternetGatewayDevice'])) {
                        log_message('info', "G665 IGD keys: " . json_encode(array_keys($device['InternetGatewayDevice'])));
                        if (isset($device['InternetGatewayDevice']['WANDevice']['1'])) {
                            log_message('info', "G665 WANDevice[1] keys: " . json_encode(array_keys($device['InternetGatewayDevice']['WANDevice']['1'])));
                        }
                        if (isset($device['InternetGatewayDevice']['DeviceInfo'])) {
                            log_message('info', "G665 DeviceInfo keys: " . json_encode(array_keys($device['InternetGatewayDevice']['DeviceInfo'])));
                        }
                    }
                }

                // Get WLAN SSID - nested paths
                $ssid = '-';
                // Try InternetGatewayDevice nested (ONT standard)
                if (isset($device['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration']['1']['SSID']['_value'])) {
                    $ssid = $device['InternetGatewayDevice']['LANDevice']['1']['WLANConfiguration']['1']['SSID']['_value'];
                }
                // Try Device.WiFi nested (TR-181 standard)
                elseif (isset($device['Device']['WiFi']['SSID']['1']['SSID']['_value'])) {
                    $ssid = $device['Device']['WiFi']['SSID']['1']['SSID']['_value'];
                }

                // Get signal strength - nested paths
                $signal = '-';

                // Try ONU EPON RxPower nested (X_TWSZ-COM)
                if (isset($device['InternetGatewayDevice']['WANDevice']['1']['X_TWSZ-COM_EponInterfaceConfig']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['WANDevice']['1']['X_TWSZ-COM_EponInterfaceConfig']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try ONU GPON RxPower nested (X_TWSZ-COM)
                elseif (isset($device['InternetGatewayDevice']['WANDevice']['1']['X_TWSZ-COM_GponInterfaceConfig']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['WANDevice']['1']['X_TWSZ-COM_GponInterfaceConfig']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try ONU EPON RxPower (X_CT-COM)
                elseif (isset($device['InternetGatewayDevice']['WANDevice']['1']['X_CT-COM_EponInterfaceConfig']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['WANDevice']['1']['X_CT-COM_EponInterfaceConfig']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try ONU GPON RxPower (X_CT-COM)
                elseif (isset($device['InternetGatewayDevice']['WANDevice']['1']['X_CT-COM_GponInterfaceConfig']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['WANDevice']['1']['X_CT-COM_GponInterfaceConfig']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try WANConnectionDevice[3] X_CT-COM_WANEponLinkConfig (for ONT F460)
                elseif (isset($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice']['3']['X_CT-COM_WANEponLinkConfig']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice']['3']['X_CT-COM_WANEponLinkConfig']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try WANConnectionDevice[4] X_CT-COM_WANEponLinkConfig
                elseif (isset($device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice']['4']['X_CT-COM_WANEponLinkConfig']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['WANDevice']['1']['WANConnectionDevice']['4']['X_CT-COM_WANEponLinkConfig']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try X_CT-COM_UserInfo (some ONT models)
                elseif (isset($device['InternetGatewayDevice']['X_CT-COM_UserInfo']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['X_CT-COM_UserInfo']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try DeviceInfo X_CT-COM (Fiberhome ONT)
                elseif (isset($device['InternetGatewayDevice']['DeviceInfo']['X_CT-COM_TelecommunicationServices']['RxPower']['_value'])) {
                    $rxPower = $device['InternetGatewayDevice']['DeviceInfo']['X_CT-COM_TelecommunicationServices']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower / 100, 2);
                    }
                }
                // Try generic Optical nested (TR-181)
                elseif (isset($device['Device']['Optical']['Interface']['1']['RxPower']['_value'])) {
                    $rxPower = $device['Device']['Optical']['Interface']['1']['RxPower']['_value'];
                    if (is_numeric($rxPower)) {
                        $signal = round($rxPower, 2);
                    }
                }

                // Debug log for device without signal
                if ($signal === '-' && strpos($deviceId, 'D49E04') !== false) {
                    log_message('info', "$deviceId - No signal found. Checking available paths...");

                    // Check InternetGatewayDevice top-level keys
                    if (isset($device['InternetGatewayDevice'])) {
                        $igdKeys = array_keys($device['InternetGatewayDevice']);
                        log_message('info', "$deviceId InternetGatewayDevice keys: " . json_encode($igdKeys));

                        // Check DeviceInfo
                        if (isset($device['InternetGatewayDevice']['DeviceInfo'])) {
                            $deviceInfoKeys = array_keys($device['InternetGatewayDevice']['DeviceInfo']);
                            log_message('info', "$deviceId DeviceInfo keys: " . json_encode($deviceInfoKeys));
                        }
                    }

                    if (isset($device['InternetGatewayDevice']['WANDevice']['1'])) {
                        $wanKeys = array_keys($device['InternetGatewayDevice']['WANDevice']['1']);
                        log_message('info', "$deviceId WANDevice[1] keys for signal: " . json_encode($wanKeys));
                    }
                }

                // Get device IP address from ConnectionRequestURL
                $ipAddress = null;
                // Try TR-181 path
                if (isset($device['Device']['ManagementServer']['ConnectionRequestURL']['_value'])) {
                    $url = $device['Device']['ManagementServer']['ConnectionRequestURL']['_value'];
                    if (preg_match('/http:\/\/([0-9.]+)/', $url, $matches)) {
                        $ipAddress = $matches[1];
                    }
                }
                // Try TR-069 path
                elseif (isset($device['InternetGatewayDevice']['ManagementServer']['ConnectionRequestURL']['_value'])) {
                    $url = $device['InternetGatewayDevice']['ManagementServer']['ConnectionRequestURL']['_value'];
                    if (preg_match('/http:\/\/([0-9.]+)/', $url, $matches)) {
                        $ipAddress = $matches[1];
                    }
                }

                $parsedDevices[] = [
                    'device_id' => $deviceId,
                    'serial_number' => $serialNumber,
                    'model' => $model,
                    'pppoe_username' => $pppoeUsername,
                    'pppoe_mac' => $pppoeMac,
                    'online' => $online,
                    'signal' => $signal,
                    'ssid' => $ssid,
                    'ip_address' => $ipAddress,
                    'last_inform' => $lastInformTimestamp > 0 ? date('Y-m-d H:i:s', $lastInformTimestamp) : '-'
                ];
            }

            return $parsedDevices;
        } catch (\Exception $e) {
            log_message('error', 'Fetch devices from GenieACS error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get WiFi settings for a device
     */
    public function getWifiSettings()
    {
        try {
            $deviceId = $this->request->getPost('device_id');

            if (empty($deviceId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Device ID tidak valid'
                ]);
            }

            // Get GenieACS configuration
            $db = \Config\Database::connect();
            $geniacsUrl = getenv('GENIACS_SERVER_URL');

            if ($db->tableExists('geniacs_settings')) {
                $builder = $db->table('geniacs_settings');
                $config = $builder->where('is_active', 1)->get()->getRowArray();
                if ($config) {
                    $geniacsUrl = $config['server_url'];
                }
            }

            if (empty($geniacsUrl)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'GenieACS belum dikonfigurasi'
                ]);
            }

            $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
            $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

            // Get WiFi settings from device directly from GenieACS
            $wifiSettings = $this->extractWifiSettings($deviceId, $geniacsUrl, $geniacsUsername, $geniacsPassword);

            if (isset($wifiSettings['error'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengambil WiFi settings: ' . $wifiSettings['error']
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $wifiSettings
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get WiFi settings error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update WiFi settings for a device
     */
    public function updateWifiSettings()
    {
        try {
            $deviceId = $this->request->getPost('device_id');
            $ssid = $this->request->getPost('ssid');
            $password = $this->request->getPost('password');

            if (empty($deviceId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Device ID tidak valid'
                ]);
            }

            if (empty($ssid)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'SSID tidak boleh kosong'
                ]);
            }

            if (!empty($password) && strlen($password) < 8) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password minimal 8 karakter'
                ]);
            }

            // Get GenieACS configuration
            $db = \Config\Database::connect();
            $geniacsUrl = getenv('GENIACS_SERVER_URL');

            if ($db->tableExists('geniacs_settings')) {
                $builder = $db->table('geniacs_settings');
                $config = $builder->where('is_active', 1)->get()->getRowArray();
                if ($config) {
                    $geniacsUrl = $config['server_url'];
                }
            }

            $geniacsUsername = getenv('GENIACS_USERNAME') ?: 'admin';
            $geniacsPassword = getenv('GENIACS_PASSWORD') ?: 'admin';

            // Update WiFi settings via GenieACS API
            $result = $this->setWifiSettingsGenieACS($deviceId, $ssid, $password, $geniacsUrl, $geniacsUsername, $geniacsPassword);

            if ($result) {
                // Log activity
                $this->logRemoteAccess($deviceId, 'Update WiFi Settings: SSID=' . $ssid);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'WiFi settings berhasil diupdate. Perubahan akan diterapkan saat device berikutnya terhubung ke GenieACS.'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate WiFi settings'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Update WiFi settings error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extract WiFi settings from device
     */
    private function extractWifiSettings($deviceId, $geniacsUrl, $username, $authPassword)
    {
        try {
            // Get device details from GenieACS
            $ch = curl_init($geniacsUrl . '/devices/' . urlencode($deviceId));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $authPassword);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Log for debugging
            log_message('info', "Extract WiFi - HTTP Code: $httpCode, Device: $deviceId");

            if ($httpCode !== 200) {
                log_message('error', "Extract WiFi failed: HTTP $httpCode, Error: $curlError, Response: " . substr($response, 0, 500));
                throw new \Exception('Failed to get device details from GenieACS (HTTP ' . $httpCode . ')');
            }

            $device = json_decode($response, true);

            if (!$device) {
                log_message('error', 'Extract WiFi: Failed to decode JSON response');
                throw new \Exception('Invalid response from GenieACS');
            }

            // Extract WiFi settings from various possible paths
            $ssid = '-';
            $wifiPassword = '';

            // Try InternetGatewayDevice nested (ONT standard) - loop all LANDevice
            if (isset($device['InternetGatewayDevice']['LANDevice'])) {
                foreach ($device['InternetGatewayDevice']['LANDevice'] as $lanIdx => $lanDevice) {
                    if (isset($lanDevice['WLANConfiguration'])) {
                        foreach ($lanDevice['WLANConfiguration'] as $wlanIdx => $wlanConfig) {
                            if (isset($wlanConfig['SSID']['_value']) && !empty($wlanConfig['SSID']['_value'])) {
                                $ssid = $wlanConfig['SSID']['_value'];

                                // Get password if available - try multiple paths
                                if (isset($wlanConfig['KeyPassphrase']['_value'])) {
                                    $wifiPassword = $wlanConfig['KeyPassphrase']['_value'];
                                } elseif (isset($wlanConfig['PreSharedKey']['1']['KeyPassphrase']['_value'])) {
                                    $wifiPassword = $wlanConfig['PreSharedKey']['1']['KeyPassphrase']['_value'];
                                } elseif (isset($wlanConfig['WEPKey']['1']['WEPKey']['_value'])) {
                                    $wifiPassword = $wlanConfig['WEPKey']['1']['WEPKey']['_value'];
                                }
                                break 2; // Exit both loops once found
                            }
                        }
                    }
                }
            }

            // Try Device.WiFi nested (TR-181 standard) if not found
            if ($ssid === '-' && isset($device['Device']['WiFi']['SSID'])) {
                foreach ($device['Device']['WiFi']['SSID'] as $ssidIdx => $ssidConfig) {
                    if (isset($ssidConfig['SSID']['_value']) && !empty($ssidConfig['SSID']['_value'])) {
                        $ssid = $ssidConfig['SSID']['_value'];
                        break;
                    }
                }

                // Get password from AccessPoint
                if (isset($device['Device']['WiFi']['AccessPoint'])) {
                    foreach ($device['Device']['WiFi']['AccessPoint'] as $apIdx => $apConfig) {
                        if (isset($apConfig['Security']['KeyPassphrase']['_value'])) {
                            $wifiPassword = $apConfig['Security']['KeyPassphrase']['_value'];
                            break;
                        }
                    }
                }
            }

            log_message('info', "Extract WiFi - Device $deviceId: SSID=$ssid, Password=" . ($wifiPassword ? 'Found' : 'Not found'));

            return [
                'ssid' => $ssid,
                'password' => $wifiPassword
            ];
        } catch (\Exception $e) {
            log_message('error', 'Extract WiFi settings error: ' . $e->getMessage());
            return [
                'ssid' => '-',
                'password' => '',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Set WiFi settings via GenieACS
     */
    private function setWifiSettingsGenieACS($deviceId, $ssid, $password, $geniacsUrl, $username, $geniacsPass)
    {
        try {
            // GenieACS uses "tasks" to set parameter values
            // We need to create tasks to update the WiFi settings

            $tasks = [];

            // Task to set SSID (try both TR-069 and TR-181 paths)
            $tasks[] = [
                'name' => 'setParameterValues',
                'parameterValues' => [
                    ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', $ssid, 'xsd:string']
                ]
            ];

            // Task to set password if provided
            if (!empty($password)) {
                $tasks[] = [
                    'name' => 'setParameterValues',
                    'parameterValues' => [
                        ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase', $password, 'xsd:string']
                    ]
                ];
            }

            // Send tasks to GenieACS
            foreach ($tasks as $task) {
                $ch = curl_init($geniacsUrl . '/devices/' . urlencode($deviceId) . '/tasks');
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $geniacsPass);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($task));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                log_message('info', "GenieACS set WiFi task response: HTTP $httpCode, Response: $response");

                // GenieACS returns 200 or 202 for successful task creation
                if ($httpCode !== 200 && $httpCode !== 202) {
                    log_message('error', "Failed to create GenieACS task: HTTP $httpCode, Error: $error, Response: $response");
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Set WiFi settings GenieACS error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log remote access activity
     */
    private function logRemoteAccess($customerId, $action, $sessionId = null)
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('remote_access_logs');

            $builder->insert([
                'customer_id' => $customerId,
                'user_id' => session()->get('id_user'),
                'action' => $action,
                'session_id' => $sessionId,
                'ip_address' => $this->request->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log remote access: ' . $e->getMessage());
        }
    }
}
