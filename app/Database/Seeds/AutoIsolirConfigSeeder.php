<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AutoIsolirConfigSeeder extends Seeder
{
    public function run()
    {
        // Get all lokasi_server records
        $lokasiServerModel = new \App\Models\ServerLocationModel();
        $servers = $lokasiServerModel->findAll();

        if (empty($servers)) {
            echo "No lokasi_server records found. Please add server locations first.\n";
            return;
        }

        $data = [];
        foreach ($servers as $server) {
            $data[] = [
                'router_id' => $server['id_lokasi'],
                'isolir_ip' => '10.10.10.1', // Default isolir IP
                'isolir_page_url' => 'http://isolir.local/blocked.html', // Default isolir page
                'grace_period_days' => 3, // Default 3 days grace period
                'is_enabled' => 0, // Disabled by default for safety
                'last_run' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // Insert the data
        $this->db->table('auto_isolir_config')->insertBatch($data);

        echo "Added auto isolir configuration for " . count($data) . " router(s).\n";
    }
}
