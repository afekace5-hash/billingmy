<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\MikrotikAPI;
use App\Models\ServerLocationModel;

class SetupIsolirProfile extends BaseCommand
{
    protected $group = 'Setup';
    protected $name = 'setup:isolir-profile';
    protected $description = 'Setup isolir profile and pool in MikroTik router';

    public function run(array $params)
    {
        CLI::write('=== SETUP ISOLIR PROFILE & POOL ===', 'yellow');

        // Get router
        $routerModel = new ServerLocationModel();
        $router = $routerModel->find(12); // KIMONET router

        if (!$router) {
            CLI::write('Router not found', 'red');
            return;
        }

        CLI::write("Router: {$router['name']}", 'cyan');
        CLI::write("IP: {$router['ip_router']}", 'cyan');

        try {
            // Initialize MikroTik connection
            $mikrotikConfig = [
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => (int)($router['port_api'] ?? 8728)
            ];

            $mikrotik = new MikrotikAPI($mikrotikConfig);

            if (!$mikrotik->isConnected()) {
                CLI::write("❌ Failed to connect to MikroTik", 'red');
                CLI::write("Error: " . ($mikrotik->getLastError() ?? 'Unknown error'), 'red');
                return;
            }

            CLI::write("✅ Connected to MikroTik", 'green');
            $connection = $mikrotik->getClient();

            // Step 1: Create IP Pool for isolir
            CLI::write("", 'white');
            CLI::write("Step 1: Setting up IP Pool for isolir...", 'yellow');

            $isolirPoolName = 'isolir-pool';
            $isolirPoolRange = '192.168.100.100-192.168.100.200'; // Range untuk isolir

            try {
                // Check if pool already exists
                $existingPools = $connection->comm('/ip/pool/print', ['?name' => $isolirPoolName]);

                if (!empty($existingPools)) {
                    CLI::write("⚠️ Pool '{$isolirPoolName}' already exists", 'yellow');
                } else {
                    // Create new pool
                    $connection->comm('/ip/pool/add', [
                        'name' => $isolirPoolName,
                        'ranges' => $isolirPoolRange,
                        'comment' => 'Pool untuk customer yang diisolir - Created by Auto Isolir System'
                    ]);
                    CLI::write("✅ Created IP Pool: {$isolirPoolName} ({$isolirPoolRange})", 'green');
                }
            } catch (\Exception $e) {
                CLI::write("❌ Failed to create IP Pool: " . $e->getMessage(), 'red');
            }

            // Step 2: Create PPP Profile for isolir
            CLI::write("", 'white');
            CLI::write("Step 2: Setting up PPP Profile for isolir...", 'yellow');

            $isolirProfileName = 'expiredbillingku';

            try {
                // Check if profile already exists
                $existingProfiles = $connection->comm('/ppp/profile/print', ['?name' => $isolirProfileName]);

                if (!empty($existingProfiles)) {
                    CLI::write("⚠️ Profile '{$isolirProfileName}' already exists", 'yellow');

                    // Show current profile settings
                    $profile = $existingProfiles[0];
                    CLI::write("Current profile settings:", 'cyan');
                    CLI::write("  - Local Address: " . ($profile['local-address'] ?? 'N/A'), 'cyan');
                    CLI::write("  - Remote Address: " . ($profile['remote-address'] ?? 'N/A'), 'cyan');
                    CLI::write("  - Rate Limit: " . ($profile['rate-limit'] ?? 'N/A'), 'cyan');
                    CLI::write("  - Session Timeout: " . ($profile['session-timeout'] ?? 'N/A'), 'cyan');

                    // Ask if want to update
                    $update = CLI::prompt('Update existing profile? (y/N)', ['y', 'n', 'Y', 'N']);

                    if (strtolower($update) === 'y') {
                        // Update existing profile
                        $connection->comm('/ppp/profile/set', [
                            'numbers' => $profile['.id'],
                            'local-address' => '192.168.1.1',
                            'remote-address' => $isolirPoolName,
                            'rate-limit' => '64k/64k', // Limit bandwidth untuk isolir
                            'session-timeout' => '1h', // Auto disconnect setelah 1 jam
                            'comment' => 'Profile isolir untuk customer telat bayar - Updated ' . date('Y-m-d H:i:s')
                        ]);
                        CLI::write("✅ Updated existing profile: {$isolirProfileName}", 'green');
                    }
                } else {
                    // Create new profile
                    $connection->comm('/ppp/profile/add', [
                        'name' => $isolirProfileName,
                        'local-address' => '192.168.1.1',
                        'remote-address' => $isolirPoolName,
                        'rate-limit' => '64k/64k', // Limit bandwidth untuk isolir
                        'session-timeout' => '1h', // Auto disconnect setelah 1 jam
                        'comment' => 'Profile isolir untuk customer telat bayar - Created by Auto Isolir System'
                    ]);
                    CLI::write("✅ Created PPP Profile: {$isolirProfileName}", 'green');
                    CLI::write("   - Bandwidth: 64k/64k (limited)", 'cyan');
                    CLI::write("   - Session Timeout: 1 hour", 'cyan');
                    CLI::write("   - IP Pool: {$isolirPoolName}", 'cyan');
                }
            } catch (\Exception $e) {
                CLI::write("❌ Failed to create PPP Profile: " . $e->getMessage(), 'red');
            }

            // Step 3: Create simple queue for additional bandwidth control
            CLI::write("", 'white');
            CLI::write("Step 3: Setting up Simple Queue for isolir control...", 'yellow');

            $isolirQueueName = 'isolir-queue';

            try {
                // Check if queue already exists
                $existingQueues = $connection->comm('/queue/simple/print', ['?name' => $isolirQueueName]);

                if (!empty($existingQueues)) {
                    CLI::write("⚠️ Queue '{$isolirQueueName}' already exists", 'yellow');
                } else {
                    // Create simple queue for isolir pool
                    $connection->comm('/queue/simple/add', [
                        'name' => $isolirQueueName,
                        'target' => $isolirPoolRange,
                        'max-limit' => '128k/128k', // Total bandwidth limit untuk semua isolir
                        'comment' => 'Queue untuk membatasi bandwidth customer isolir - Created by Auto Isolir System'
                    ]);
                    CLI::write("✅ Created Simple Queue: {$isolirQueueName}", 'green');
                    CLI::write("   - Target: {$isolirPoolRange}", 'cyan');
                    CLI::write("   - Max Limit: 128k/128k", 'cyan');
                }
            } catch (\Exception $e) {
                CLI::write("❌ Failed to create Simple Queue: " . $e->getMessage(), 'red');
            }

            // Step 4: Test the setup
            CLI::write("", 'white');
            CLI::write("Step 4: Testing the setup...", 'yellow');

            try {
                // List all created resources
                CLI::write("✅ Setup verification:", 'green');

                // Check pools
                $pools = $connection->comm('/ip/pool/print');
                $isolirPoolFound = false;
                foreach ($pools as $pool) {
                    if (($pool['name'] ?? '') === $isolirPoolName) {
                        $isolirPoolFound = true;
                        CLI::write("  ✓ IP Pool: {$isolirPoolName} - {$pool['ranges']}", 'green');
                        break;
                    }
                }
                if (!$isolirPoolFound) {
                    CLI::write("  ❌ IP Pool not found", 'red');
                }

                // Check profiles
                $profiles = $connection->comm('/ppp/profile/print');
                $isolirProfileFound = false;
                foreach ($profiles as $profile) {
                    if (($profile['name'] ?? '') === $isolirProfileName) {
                        $isolirProfileFound = true;
                        CLI::write("  ✓ PPP Profile: {$isolirProfileName}", 'green');
                        CLI::write("    - Remote Address: " . ($profile['remote-address'] ?? 'N/A'), 'cyan');
                        CLI::write("    - Rate Limit: " . ($profile['rate-limit'] ?? 'N/A'), 'cyan');
                        break;
                    }
                }
                if (!$isolirProfileFound) {
                    CLI::write("  ❌ PPP Profile not found", 'red');
                }

                // Check queues
                $queues = $connection->comm('/queue/simple/print');
                $isolirQueueFound = false;
                foreach ($queues as $queue) {
                    if (($queue['name'] ?? '') === $isolirQueueName) {
                        $isolirQueueFound = true;
                        CLI::write("  ✓ Simple Queue: {$isolirQueueName}", 'green');
                        CLI::write("    - Target: " . ($queue['target'] ?? 'N/A'), 'cyan');
                        CLI::write("    - Max Limit: " . ($queue['max-limit'] ?? 'N/A'), 'cyan');
                        break;
                    }
                }
                if (!$isolirQueueFound) {
                    CLI::write("  ❌ Simple Queue not found", 'red');
                }
            } catch (\Exception $e) {
                CLI::write("❌ Failed to verify setup: " . $e->getMessage(), 'red');
            }

            CLI::write("", 'white');
            CLI::write("🎉 ISOLIR PROFILE & POOL SETUP COMPLETED! 🎉", 'green');
            CLI::write("", 'white');
            CLI::write("Ringkasan konfigurasi:", 'yellow');
            CLI::write("✅ IP Pool: {$isolirPoolName} ({$isolirPoolRange})", 'green');
            CLI::write("✅ PPP Profile: {$isolirProfileName}", 'green');
            CLI::write("✅ Simple Queue: {$isolirQueueName}", 'green');
            CLI::write("", 'white');
            CLI::write("Sekarang script auto isolir sudah bisa digunakan!", 'green');
        } catch (\Exception $e) {
            CLI::write("❌ Setup failed: " . $e->getMessage(), 'red');
            CLI::write("Stack trace:", 'yellow');
            CLI::write($e->getTraceAsString(), 'red');
        }
    }
}
