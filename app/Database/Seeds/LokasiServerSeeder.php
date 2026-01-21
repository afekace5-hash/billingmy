<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LokasiServerSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Router Pusat - Jakarta',
                'ip_router' => '192.168.1.1',
                'username' => 'admin',
                'password_router' => 'admin123',
                'port_api' => 8728,
                'address' => 'Jl. Medan Merdeka Utara No. 1, Jakarta Pusat',
                'due_date' => '2025-12-31',
                'tax' => 11.00,
                'tax_amount' => 0.00,
                'is_connected' => 0,
                'ping_status' => 'unknown',
                'auto_ping_enabled' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Router Cabang - Bandung',
                'ip_router' => '192.168.2.1',
                'username' => 'admin',
                'password_router' => 'admin123',
                'port_api' => 8728,
                'address' => 'Jl. Asia Afrika No. 1, Bandung',
                'due_date' => '2025-12-31',
                'tax' => 11.00,
                'tax_amount' => 0.00,
                'is_connected' => 0,
                'ping_status' => 'unknown',
                'auto_ping_enabled' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Router Test - Local',
                'ip_router' => '192.168.100.1',
                'username' => 'admin',
                'password_router' => 'password123',
                'port_api' => 8728,
                'address' => 'Test location for development',
                'due_date' => '2025-12-31',
                'tax' => 11.00,
                'tax_amount' => 0.00,
                'is_connected' => 0,
                'ping_status' => 'unknown',
                'auto_ping_enabled' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert sample data (but don't override existing data)
        foreach ($data as $serverData) {
            // Check if server with this name already exists
            $existing = $this->db->table('lokasi_server')
                ->where('name', $serverData['name'])
                ->get()
                ->getRow();

            if (!$existing) {
                $this->db->table('lokasi_server')->insert($serverData);
                echo "Added server: " . $serverData['name'] . "\n";
            } else {
                echo "Server already exists: " . $serverData['name'] . "\n";
            }
        }
    }
}
