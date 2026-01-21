<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CustomerModel;
use App\Models\AutoIsolirConfigModel;
use App\Models\ServerLocationModel;
use App\Libraries\MikrotikNew;

class AutoIsolirVPN extends BaseCommand
{
    protected $group = 'Billing';
    protected $name = 'auto:isolir:vpn';
    protected $description = 'Auto isolir customers dengan konfigurasi VPN yang berbeda untuk setiap lokasi';

    public function run(array $params)
    {
        CLI::write('Starting Auto Isolir VPN Process...', 'yellow');

        $customerModel = new CustomerModel();
        $configModel = new AutoIsolirConfigModel();
        $routerModel = new ServerLocationModel();

        // Get active auto isolir configurations with VPN settings
        $configs = $this->getVPNConfigs();

        if (empty($configs)) {
            CLI::write('No active VPN auto isolir configurations found.', 'red');
            return;
        }

        $totalProcessed = 0;
        $totalIsolated = 0;
        $errors = [];

        foreach ($configs as $config) {
            CLI::write("Processing VPN router: {$config['name']} (ID: {$config['router_id']})", 'cyan');

            // Setup VPN routing for this location
            $vpnResult = $this->setupVPNRouting($config);
            if (!$vpnResult['success']) {
                $errors[] = "VPN setup failed for {$config['name']}: {$vpnResult['message']}";
                CLI::write("✗ VPN setup failed: {$vpnResult['message']}", 'red');
                continue;
            }

            CLI::write("✓ VPN routing established for {$config['name']}", 'green');

            // Get router details
            $router = $routerModel->find($config['router_id']);
            if (!$router) {
                $errors[] = "Router ID {$config['router_id']} not found";
                continue;
            }

            // Get overdue customers for this router
            $overdueCustomers = $this->getOverdueCustomers($customerModel, $config);

            if (empty($overdueCustomers)) {
                CLI::write("No overdue customers found for router: {$router['name']}", 'green');
                continue;
            }

            CLI::write("Found " . count($overdueCustomers) . " overdue customers", 'yellow');

            // Process each overdue customer through VPN
            foreach ($overdueCustomers as $customer) {
                $totalProcessed++;

                try {
                    // Check if already isolated
                    if ($customer['isolir_status'] == 1) {
                        CLI::write("Customer {$customer['nama_pelanggan']} already isolated", 'blue');
                        continue;
                    }

                    // Perform auto isolir via VPN
                    $result = $this->performAutoIsolirVPN($router, $customer, $config);

                    if ($result['success']) {
                        // Update customer status
                        $customerModel->update($customer['id_customers'], [
                            'isolir_status' => 1,
                            'isolir_date' => date('Y-m-d H:i:s'),
                            'isolir_reason' => 'AUTO ISOLIR VPN - Melebihi jatuh tempo',
                            'isolir_method' => 'vpn_' . $config['vpn_profile']
                        ]);

                        // Log the action
                        $this->logIsolirAction($customer['id_customers'], $config['router_id'], 'auto_isolir_vpn', $config['vpn_profile']);

                        CLI::write("✓ Isolated via VPN: {$customer['nama_pelanggan']} ({$customer['pppoe_username']})", 'green');
                        $totalIsolated++;
                    } else {
                        $errors[] = "Failed to isolate {$customer['nama_pelanggan']}: {$result['message']}";
                        CLI::write("✗ Failed: {$customer['nama_pelanggan']} - {$result['message']}", 'red');
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing {$customer['nama_pelanggan']}: " . $e->getMessage();
                    CLI::write("✗ Error: {$customer['nama_pelanggan']} - " . $e->getMessage(), 'red');
                }
            }

            // Update last run time
            $this->updateVPNConfigLastRun($config['router_id']);
        }

        // Summary
        CLI::write('', 'white');
        CLI::write('=== AUTO ISOLIR VPN SUMMARY ===', 'yellow');
        CLI::write("Total customers processed: {$totalProcessed}", 'cyan');
        CLI::write("Total customers isolated: {$totalIsolated}", 'green');

        if (!empty($errors)) {
            CLI::write("Errors encountered: " . count($errors), 'red');
            foreach ($errors as $error) {
                CLI::write("  - {$error}", 'red');
            }
        }

        CLI::write('Auto Isolir VPN Process completed.', 'green');
    }

    /**
     * Get VPN configurations for auto isolir
     */
    private function getVPNConfigs()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                ls.id_lokasi as router_id,
                ls.name,
                ls.ip_router,
                ls.username,
                ls.password_router,
                ls.port_api,
                ls.vpn_config,
                ls.vpn_profile,
                ls.vpn_gateway,
                aic.grace_period_days,
                aic.isolir_ip,
                aic.isolir_page_url,
                aic.is_active
            FROM lokasi_server ls 
            LEFT JOIN auto_isolir_config aic ON ls.id_lokasi = aic.router_id
            WHERE ls.status = 'active' 
            AND ls.vpn_config IS NOT NULL 
            AND (aic.is_active = 1 OR aic.is_active IS NULL)
            ORDER BY ls.id_lokasi
        ");

        return $query->getResultArray();
    }

    /**
     * Setup VPN routing for specific location
     */
    private function setupVPNRouting($config)
    {
        try {
            // Check if VPN is already connected
            $vpnStatus = $this->checkVPNStatus($config['vpn_profile']);

            if (!$vpnStatus['connected']) {
                // Connect to VPN
                $connectResult = $this->connectVPN($config);
                if (!$connectResult['success']) {
                    return $connectResult;
                }
            }

            // Add routing rules for this VPN
            $routingResult = $this->addVPNRouting($config);
            if (!$routingResult['success']) {
                return $routingResult;
            }

            return [
                'success' => true,
                'message' => 'VPN routing established successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'VPN setup error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check VPN connection status
     */
    private function checkVPNStatus($vpnProfile)
    {
        $output = [];
        $returnCode = 0;

        // Check if exec() is available
        if (!function_exists('exec')) {
            log_message('warning', 'exec() function not available - VPN check disabled');
            return [
                'connected' => false,
                'profile' => $vpnProfile,
                'error' => 'exec() disabled on server'
            ];
        }

        // Check if OpenVPN process is running for this profile (Windows only)
        if (PHP_OS_FAMILY === 'Windows') {
            @exec("tasklist /fi \"imagename eq openvpn.exe\" /fo csv", $output, $returnCode);

            $connected = false;
            foreach ($output as $line) {
                if (strpos($line, $vpnProfile) !== false) {
                    $connected = true;
                    break;
                }
            }
        } else {
            // Linux - check via process list
            @exec("ps aux | grep openvpn | grep -v grep", $output, $returnCode);
            $connected = !empty($output);
        }

        return [
            'connected' => $connected,
            'profile' => $vpnProfile
        ];
    }

    /**
     * Connect to VPN
     */
    private function connectVPN($config)
    {
        try {
            $vpnConfigPath = APPPATH . '../vpn-configs/' . $config['vpn_config'];
            $logPath = WRITEPATH . 'logs/vpn-' . $config['vpn_profile'] . '.log';

            if (!file_exists($vpnConfigPath)) {
                return [
                    'success' => false,
                    'message' => 'VPN config file not found: ' . $config['vpn_config']
                ];
            }

            // Check if exec() is available
            if (!function_exists('exec')) {
                log_message('warning', 'exec() not available - VPN commands disabled');
                return [
                    'success' => false,
                    'message' => 'exec() function disabled on server - VPN not available'
                ];
            }

            // Kill existing connection (Windows only)
            if (PHP_OS_FAMILY === 'Windows') {
                @exec("taskkill /f /im openvpn.exe /fi \"WINDOWTITLE eq {$config['vpn_profile']}*\" 2>nul");
            } else {
                // Linux
                @exec("pkill -f openvpn");
            }

            // Wait a moment
            sleep(2);

            // Start new VPN connection
            $openvpnPath = 'C:\Program Files\OpenVPN\bin\openvpn.exe';
            $command = "start /min \"\" \"{$openvpnPath}\" --config \"{$vpnConfigPath}\" --log \"{$logPath}\"";

            exec($command);

            // Wait for connection to establish
            sleep(10);

            // Verify connection
            $status = $this->checkVPNStatus($config['vpn_profile']);

            return [
                'success' => $status['connected'],
                'message' => $status['connected'] ? 'VPN connected successfully' : 'VPN connection failed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'VPN connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add VPN specific routing
     */
    private function addVPNRouting($config)
    {
        try {
            // Add route for MikroTik access through VPN gateway
            if (!empty($config['vpn_gateway'])) {
                $routeCommand = "route add {$config['ip_router']} mask 255.255.255.255 {$config['vpn_gateway']} metric 1";
                exec($routeCommand, $output, $returnCode);

                if ($returnCode !== 0) {
                    return [
                        'success' => false,
                        'message' => 'Failed to add VPN route'
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'VPN routing configured successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'VPN routing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get overdue customers for specific router
     */
    private function getOverdueCustomers($customerModel, $config)
    {
        $gracePeriod = $config['grace_period_days'] ?? 3;
        $overdueDate = date('Y-m-d', strtotime("-{$gracePeriod} days"));

        return $customerModel->select('id_customers, nama_pelanggan, pppoe_username, tgl_tempo, isolir_status')
            ->where('id_lokasi_server', $config['router_id'])
            ->where('pppoe_username IS NOT NULL')
            ->where('pppoe_username !=', '')
            ->where('isolir_status', 0) // Not already isolated
            ->where('tgl_tempo <', $overdueDate)
            ->where('status_tagihan !=', 'Lunas') // Not paid
            ->findAll();
    }

    /**
     * Perform auto isolir through VPN connection
     */
    private function performAutoIsolirVPN($router, $customer, $config)
    {
        try {
            // Use VPN-aware connection parsing
            $connectionDetails = $this->parseVPNConnectionDetails($router, $config);

            $mt = new MikrotikNew([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 60, // VPN needs longer timeout
            ]);

            // Step 1: Find and disable PPPoE secret
            $secrets = $mt->comm('/ppp/secret/print', ['?name' => $customer['pppoe_username']]);
            if (empty($secrets)) {
                return [
                    'success' => false,
                    'message' => 'PPPoE secret not found'
                ];
            }

            $secretId = $secrets[0]['.id'];

            // Step 2: Disconnect active sessions
            $activeConnections = $mt->comm('/ppp/active/print', ['?name' => $customer['pppoe_username']]);
            foreach ($activeConnections as $connection) {
                $mt->comm('/ppp/active/remove', ['.id' => $connection['.id']]);
            }

            // Step 3: Update PPPoE secret with isolir IP redirect
            $isolirIp = $config['isolir_ip'] ?? '192.168.100.1';

            $updateData = [
                '.id' => $secretId,
                'disabled' => 'no', // Keep enabled but redirect
                'local-address' => $isolirIp, // Redirect to isolir IP
                'comment' => 'VPN_AUTO_ISOLIR_' . $config['vpn_profile'] . '_' . date('Y-m-d H:i:s')
            ];

            // If isolir page URL is configured, add to comment
            if (!empty($config['isolir_page_url'])) {
                $updateData['comment'] .= ' - Redirect: ' . $config['isolir_page_url'];
            }

            $result = $mt->comm('/ppp/secret/set', $updateData);

            return [
                'success' => true,
                'message' => 'Customer redirected to isolir IP via VPN',
                'data' => $result,
                'vpn_profile' => $config['vpn_profile']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'MikroTik VPN error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse VPN connection details
     */
    private function parseVPNConnectionDetails($router, $config)
    {
        $actualHost = $router['ip_router'];
        $actualPort = intval($router['port_api'] ?? 8728);

        // For VPN connections, we might need to use different addressing
        if (!empty($config['vpn_profile'])) {
            // Check if we have tunnel-style connection
            if (strpos($actualHost, ':') !== false) {
                $parts = explode(':', $actualHost);
                if (count($parts) == 2) {
                    $actualHost = $parts[0];
                    // Keep the original port for VPN connections
                }
            }
        }

        return [
            'host' => $actualHost,
            'port' => $actualPort,
            'vpn_profile' => $config['vpn_profile'] ?? null
        ];
    }

    /**
     * Log isolir action with VPN info
     */
    private function logIsolirAction($customerId, $routerId, $action, $vpnProfile, $reason = 'AUTO ISOLIR VPN - Melebihi jatuh tempo')
    {
        try {
            $db = \Config\Database::connect();

            $data = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'action' => $action,
                'reason' => $reason,
                'vpn_profile' => $vpnProfile,
                'status' => 'success',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->table('isolir_log')->insert($data);

            CLI::write("Logged isolir action for customer {$customerId} via VPN {$vpnProfile}", 'blue');
        } catch (\Exception $e) {
            CLI::write("Log error: " . $e->getMessage(), 'red');
        }
    }

    /**
     * Update VPN config last run time
     */
    private function updateVPNConfigLastRun($routerId)
    {
        try {
            $db = \Config\Database::connect();

            $db->table('lokasi_server')
                ->where('id_lokasi', $routerId)
                ->update(['last_isolir_run' => date('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            CLI::write("Update last run error: " . $e->getMessage(), 'red');
        }
    }
}
