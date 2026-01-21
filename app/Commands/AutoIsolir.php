<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CustomerModel;
use App\Models\AutoIsolirConfigModel;
use App\Models\ServerLocationModel;
use App\Libraries\MikrotikNew;
use App\Services\WhatsAppService;

class AutoIsolir extends BaseCommand
{
    protected $group = 'Billing';
    protected $name = 'auto:isolir';
    protected $description = 'Auto isolir customers yang melebihi jatuh tempo';

    public function run(array $params)
    {
        CLI::write('Starting Auto Isolir Process...', 'yellow');

        $customerModel = new CustomerModel();
        $configModel = new AutoIsolirConfigModel();
        $routerModel = new ServerLocationModel();

        // Get active auto isolir configurations
        $configs = $configModel->getActiveConfigs();

        if (empty($configs)) {
            CLI::write('No active auto isolir configurations found.', 'red');
            return;
        }

        $totalProcessed = 0;
        $totalIsolated = 0;
        $errors = [];

        foreach ($configs as $config) {
            CLI::write("Processing router ID: {$config['router_id']}", 'cyan');

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

            // Process each overdue customer
            foreach ($overdueCustomers as $customer) {
                $totalProcessed++;

                try {
                    // Check if already isolated
                    if ($customer['isolir_status'] == 1) {
                        CLI::write("Customer {$customer['nama_pelanggan']} already isolated", 'blue');
                        continue;
                    }

                    // Perform auto isolir
                    $result = $this->performAutoIsolir($router, $customer, $config);

                    if ($result['success']) {
                        // Update customer status
                        $isolirDate = date('Y-m-d H:i:s');
                        $customerModel->update($customer['id_customers'], [
                            'isolir_status' => 1,
                            'isolir_date' => $isolirDate,
                            'isolir_reason' => 'AUTO ISOLIR - Melebihi jatuh tempo'
                        ]);

                        // Log the action
                        $this->logIsolirAction($customer['id_customers'], $config['router_id'], 'auto_isolir');

                        // Send WhatsApp notification to customer
                        $this->sendIsolirNotification($customer, $isolirDate);

                        CLI::write("✓ Isolated: {$customer['nama_pelanggan']} ({$customer['pppoe_username']})", 'green');
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
            $configModel->updateLastRun($config['id']);
        }

        // Summary
        CLI::write('', 'white');
        CLI::write('=== AUTO ISOLIR SUMMARY ===', 'yellow');
        CLI::write("Total customers processed: {$totalProcessed}", 'cyan');
        CLI::write("Total customers isolated: {$totalIsolated}", 'green');

        if (!empty($errors)) {
            CLI::write("Errors encountered: " . count($errors), 'red');
            foreach ($errors as $error) {
                CLI::write("  - {$error}", 'red');
            }
        }

        CLI::write('Auto Isolir Process completed.', 'green');
    }

    /**
     * Get overdue customers for specific router
     * 
     * ✅ FIX (2026-01-11): Sekarang mengecek status_tagihan != 'Lunas'
     * Kondisi ini akan terpenuhi karena:
     * 1. Saat invoice baru dibuat di GenerateInvoices.php, status_tagihan otomatis direset ke 'Belum Bayar'
     * 2. Status_tagihan hanya berubah ke 'Lunas' saat pembayaran berhasil
     * 3. Jika customer tidak bayar sebelum tgl_tempo, status_tagihan tetap 'Belum Bayar'
     * 4. Maka auto isolir dapat mendeteksi dan mengisolir customer yang sudah jatuh tempo
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
            ->where('status_tagihan !=', 'Lunas') // Not paid - akan tercapai setelah fix GenerateInvoices
            ->findAll();
    }

    /**
     * Perform auto isolir with IP redirect
     */
    private function performAutoIsolir($router, $customer, $config)
    {
        try {
            // Parse connection details
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            $mt = new MikrotikNew([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 60,
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
            $updateData = [
                '.id' => $secretId,
                'disabled' => 'no', // Keep enabled but redirect
                'local-address' => $config['isolir_ip'], // Redirect to isolir IP
                'comment' => 'AUTO_ISOLIR_' . date('Y-m-d H:i:s')
            ];

            // If isolir page URL is configured, add to comment
            if (!empty($config['isolir_page_url'])) {
                $updateData['comment'] .= ' - Redirect: ' . $config['isolir_page_url'];
            }

            $result = $mt->comm('/ppp/secret/set', $updateData);

            return [
                'success' => true,
                'message' => 'Customer redirected to isolir IP',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'MikroTik error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse connection details for tunnel connections
     */
    private function parseConnectionDetails($ipRouter, $portApi)
    {
        $actualHost = $ipRouter;
        $actualPort = intval($portApi);

        if (strpos($ipRouter, ':') !== false) {
            $parts = explode(':', $ipRouter);
            if (count($parts) == 2) {
                $actualHost = $parts[0];
                $actualPort = intval($parts[1]);
            }
        }

        return ['host' => $actualHost, 'port' => $actualPort];
    }

    /**
     * Send isolir notification via WhatsApp
     */
    private function sendIsolirNotification($customer, $isolirDate)
    {
        try {
            $whatsappService = new WhatsAppService();

            // Tambah field isolir_date untuk notification message
            $customerData = array_merge($customer, [
                'isolir_date' => $isolirDate,
                'isolir_reason' => 'AUTO ISOLIR - Melebihi jatuh tempo'
            ]);

            $result = $whatsappService->sendIsolirNotification($customerData);

            if ($result['success']) {
                CLI::write("  ✓ WhatsApp notification sent to {$customer['nama_pelanggan']}", 'blue');
            } else {
                CLI::write("  ⚠ WhatsApp notification failed: {$result['message']}", 'yellow');
            }
        } catch (\Exception $e) {
            CLI::write("  ✗ Error sending WhatsApp: " . $e->getMessage(), 'red');
        }
    }

    /**
     * Log isolir action
     */
    private function logIsolirAction($customerId, $routerId, $action, $reason = 'AUTO ISOLIR - Melebihi jatuh tempo')
    {
        try {
            $db = \Config\Database::connect();

            $data = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'action' => $action,
                'reason' => $reason,
                'status' => 'success',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->table('isolir_log')->insert($data);
        } catch (\Exception $e) {
            CLI::write("Log error: " . $e->getMessage(), 'red');
        }
    }
}
