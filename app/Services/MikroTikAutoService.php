<?php

namespace App\Services;

use App\Models\ServerLocationModel;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Libraries\MikrotikAdvanced;
use App\Libraries\MikrotikNew;

class MikroTikAutoService
{
    protected $lokasiServerModel;
    protected $customerModel;
    protected $invoiceModel;

    public function __construct()
    {
        $this->lokasiServerModel = new ServerLocationModel();
        $this->customerModel = new CustomerModel();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Get MikroTik connection using new library (compatible with VPN)
     */
    private function getMikrotikConnection($routerId)
    {
        try {
            $router = $this->lokasiServerModel->find($routerId);
            if (!$router) {
                throw new \Exception('Router not found');
            }

            // Parse connection details for VPN tunnel
            $actualHost = $router['ip_router'];
            $actualPort = intval($router['port_api'] ?: 8728);

            if (strpos($router['ip_router'], ':') !== false) {
                $parts = explode(':', $router['ip_router']);
                if (count($parts) == 2) {
                    $actualHost = $parts[0];
                    $actualPort = intval($parts[1]);
                }
            }

            $config = [
                'host' => $actualHost,
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $actualPort,
                'timeout' => 60 // VPN needs longer timeout
            ];

            log_message('info', "Connecting to MikroTik: {$actualHost}:{$actualPort}");

            return [
                'success' => true,
                'client' => new MikrotikNew($config),
                'advanced' => new MikrotikAdvanced($config),
                'router' => $router
            ];
        } catch (\Exception $e) {
            log_message('error', 'Failed to connect to MikroTik: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Otomatis membuat PPPoE secret di MikroTik ketika customer baru ditambahkan
     */
    public function createPPPoESecret($customerId)
    {
        try {
            // Get customer data
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Get router connection
            $connection = $this->getMikrotikConnection($customer['id_lokasi_server']);
            if (!$connection['success']) {
                throw new \Exception($connection['message']);
            }

            // Ambil default profile dari package yang dipilih customer
            $packageModel = new \App\Models\PackageProfileModel();
            $package = $packageModel->find($customer['id_paket']);

            $profileToUse = isset($package['default_profile_mikrotik']) && $package['default_profile_mikrotik']
                ? $package['default_profile_mikrotik']
                : 'default';

            log_message('info', 'Profile yang akan digunakan: ' . $profileToUse);

            $secretData = [
                'username' => $customer['pppoe_username'],
                'password' => $customer['pppoe_password'] ?? 'password123',
                'service' => 'pppoe',
                'profile' => $profileToUse,
            ];

            // Use MikrotikAdvanced for adding secret
            $result = $connection['advanced']->addPPPoESecret($secretData);

            if ($result['success']) {
                $this->customerModel->update($customerId, [
                    'pppoe_created' => 1
                ]);

                log_message('info', "PPPoE secret pelanggan {$customer['nama_pelanggan']} ({$customer['pppoe_username']}) berhasil ditambahkan ke Mikrotik.");

                return [
                    'success' => true,
                    'message' => 'PPPoE secret pelanggan berhasil dibuat di Mikrotik.'
                ];
            } else {
                throw new \Exception($result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to create PPPoE secret: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat PPPoE secret: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Isolir customer dengan mengubah profile ke profile isolir
     */
    public function isolateCustomer($customerId, $reason = 'Telat bayar tagihan')
    {
        try {
            // Get customer data
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Skip if already isolated
            if ($customer['isolir_status'] == 1) {
                return [
                    'success' => true,
                    'message' => 'Customer sudah dalam status isolir'
                ];
            }

            // Get router connection
            $connection = $this->getMikrotikConnection($customer['id_lokasi_server']);
            if (!$connection['success']) {
                throw new \Exception($connection['message']);
            }

            $client = $connection['client'];
            $router = $connection['router'];

            // Find PPPoE secret
            $secrets = $client->comm('/ppp/secret/print', [
                '?name=' . $customer['pppoe_username']
            ]);

            if (empty($secrets)) {
                throw new \Exception('PPPoE secret not found in MikroTik');
            }

            $secret = $secrets[0];
            $originalProfile = $secret['profile'] ?? '';

            // Ambil nama profile isolir dari database router jika ada
            $isolirProfile = $router['isolir_profile'] ?? 'expiredbillingku';

            // Cek apakah profile isolir ada di router
            $profiles = $client->comm('/ppp/profile/print', []);
            $profileNames = array_column($profiles, 'name');
            if (!in_array($isolirProfile, $profileNames)) {
                throw new \Exception("Profile isolir '$isolirProfile' tidak ditemukan di MikroTik");
            }

            // Eksekusi perubahan profile ke isolir
            $client->comm('/ppp/secret/set', [
                '.id' => $secret['.id'],
                '=profile' => $isolirProfile
            ]);

            // Disconnect active connections to force profile change
            $activeConnections = $client->comm('/ppp/active/print', [
                '?name=' . $customer['pppoe_username']
            ]);
            foreach ($activeConnections as $connection) {
                $client->comm('/ppp/active/remove', ['.id' => $connection['.id']]);
            }

            // Update customer status di database
            $this->customerModel->update($customerId, [
                'isolir_status' => 1,
                'isolir_date' => date('Y-m-d H:i:s'),
                'isolir_reason' => $reason,
                'original_profile' => $originalProfile
            ]);

            // Log isolir action
            $this->logIsolirAction($customerId, 'isolir', $customer['pppoe_username'], $originalProfile, $isolirProfile, $reason);

            log_message('info', "Customer {$customer['nama_pelanggan']} ({$customer['pppoe_username']}) has been isolated");

            return [
                'success' => true,
                'message' => 'Customer berhasil diisolir'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Failed to isolate customer: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengisolir customer: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Un-isolir customer dengan mengembalikan profile asli
     */
    public function unIsolateCustomer($customerId, $reason = 'Pembayaran diterima')
    {
        try {
            // Get customer data
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Skip if not isolated
            if ($customer['isolir_status'] != 1) {
                return [
                    'success' => true,
                    'message' => 'Customer tidak dalam status isolir'
                ];
            }

            // Get router connection
            $connection = $this->getMikrotikConnection($customer['id_lokasi_server']);
            if (!$connection['success']) {
                throw new \Exception($connection['message']);
            }

            $client = $connection['client'];

            // Find PPPoE secret
            $secrets = $client->comm('/ppp/secret/print', [
                '?name=' . $customer['pppoe_username']
            ]);

            if (empty($secrets)) {
                throw new \Exception('PPPoE secret not found in MikroTik');
            }

            $secret = $secrets[0];
            $currentProfile = $secret['profile'] ?? '';

            // Get original profile, fallback to package profile if not available
            $originalProfile = $customer['original_profile'];
            if (empty($originalProfile)) {
                // Get from package profile
                $db = \Config\Database::connect();
                $package = $db->table('package_profiles')
                    ->where('id', $customer['id_paket'])
                    ->get()
                    ->getRowArray();

                // Use default_profile_mikrotik first, then name, then 'default'
                $originalProfile = $package['default_profile_mikrotik'] ?? $package['name'] ?? 'default';
            }

            // Update profile PPPoE di Mikrotik ke profile asli
            $client->comm('/ppp/secret/set', [
                '.id' => $secret['.id'],
                '=profile' => $originalProfile
            ]);

            // Disconnect active connections to force profile change
            $activeConnections = $client->comm('/ppp/active/print', [
                '?name=' . $customer['pppoe_username']
            ]);
            foreach ($activeConnections as $conn) {
                $client->comm('/ppp/active/remove', ['.id' => $conn['.id']]);
            }

            // Update status isolir di database
            $this->customerModel->update($customerId, [
                'isolir_status' => 0,
                'isolir_date' => null,
                'isolir_reason' => null,
                'original_profile' => null
            ]);

            // Log aksi un-isolir
            $this->logIsolirAction($customerId, 'un-isolir', $customer['pppoe_username'], $currentProfile, $originalProfile, $reason);

            log_message('info', "Customer {$customer['nama_pelanggan']} ({$customer['pppoe_username']}) profile PPPoE telah dikembalikan ke profile semula setelah pembayaran.");

            return [
                'success' => true,
                'message' => 'Customer berhasil diaktifkan kembali dan profile PPPoE sudah dikembalikan.'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Failed to un-isolate customer: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengaktifkan customer: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check customers with overdue invoices and auto isolate
     */
    public function autoIsolateOverdueCustomers($maxDaysOverdue = 7)
    {
        try {
            $results = [];

            // Get customers with unpaid invoices that are overdue
            $db = \Config\Database::connect();
            $builder = $db->table('customers c');
            $overdueCustomers = $builder
                ->select('c.*, ci.due_date, COUNT(ci.id) as unpaid_count')
                ->join('customer_invoices ci', 'ci.customer_id = c.id_customers')
                ->where('ci.status', 'unpaid')
                ->where('ci.due_date <', date('Y-m-d', strtotime("-{$maxDaysOverdue} days")))
                ->where('c.isolir_status', 0) // Only non-isolated customers
                ->groupBy('c.id_customers')
                ->get()
                ->getResultArray();

            foreach ($overdueCustomers as $customer) {
                $result = $this->isolateCustomer(
                    $customer['id_customers'],
                    "Auto isolir - Telat bayar {$customer['unpaid_count']} tagihan lebih dari {$maxDaysOverdue} hari"
                );

                $results[] = [
                    'customer_id' => $customer['id_customers'],
                    'customer_name' => $customer['nama_pelanggan'],
                    'customer_number' => $customer['nomor_layanan'],
                    'unpaid_count' => $customer['unpaid_count'],
                    'result' => $result
                ];
            }

            log_message('info', 'Auto isolir completed. Processed ' . count($results) . ' customers');

            return [
                'success' => true,
                'message' => 'Auto isolir completed',
                'data' => $results
            ];
        } catch (\Exception $e) {
            log_message('error', 'Auto isolir failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Auto isolir failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Auto un-isolate customers when payment is received
     */
    public function autoUnIsolateOnPayment($customerId)
    {
        try {
            // Cek apakah customer masih punya tagihan yang belum dibayar
            $unpaidCount = $this->invoiceModel
                ->where('customer_id', $customerId)
                ->where('status', 'unpaid')
                ->countAllResults();

            if ($unpaidCount == 0) {
                // Semua tagihan lunas, lakukan un-isolir
                $result = $this->unIsolateCustomer($customerId, 'Auto un-isolir - Semua tagihan sudah lunas');
                if ($result['success']) {
                    log_message('info', "Customer $customerId berhasil di-unisolir setelah pembayaran lunas.");
                } else {
                    log_message('error', "Gagal un-isolir customer $customerId: " . $result['message']);
                }
                return $result;
            }

            // Masih ada tagihan, tidak bisa un-isolir
            return [
                'success' => false,
                'message' => 'Customer masih memiliki tagihan yang belum dibayar'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Auto un-isolir failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Auto un-isolir failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log isolir actions
     */
    private function logIsolirAction($customerId, $action, $username, $oldProfile, $newProfile, $reason)
    {
        $db = \Config\Database::connect();

        // Check if isolir_logs table exists
        if (!$db->tableExists('isolir_logs')) {
            return; // Skip logging if table doesn't exist
        }

        $logData = [
            'customer_id' => $customerId,
            'action' => $action,
            'username' => $username,
            'old_profile' => $oldProfile,
            'new_profile' => $newProfile,
            'reason' => $reason,
            'router_id' => null, // Will be filled if needed
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $db->table('isolir_logs')->insert($logData);
    }

    /**
     * Sync all customers PPPoE secrets to MikroTik
     */
    public function syncAllCustomersToMikroTik($routerId = null)
    {
        try {
            $results = [];

            // Get customers
            $builder = $this->customerModel;
            if ($routerId) {
                $builder->where('id_lokasi_server', $routerId);
            }

            $customers = $builder->where('pppoe_username !=', '')
                ->where('pppoe_username IS NOT NULL')
                ->findAll();

            foreach ($customers as $customer) {
                $result = $this->createPPPoESecret($customer['id_customers']);
                $results[] = [
                    'customer_id' => $customer['id_customers'],
                    'customer_name' => $customer['nama_pelanggan'],
                    'pppoe_username' => $customer['pppoe_username'],
                    'result' => $result
                ];
            }

            return [
                'success' => true,
                'message' => 'Sync completed',
                'data' => $results
            ];
        } catch (\Exception $e) {
            log_message('error', 'Sync all customers failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }
}
