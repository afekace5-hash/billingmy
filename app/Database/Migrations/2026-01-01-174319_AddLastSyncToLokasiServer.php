<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastSyncToLokasiServer extends Migration
{
    public function up()
    {
        // Check if column already exists
        if (!$this->db->fieldExists('last_sync', 'lokasi_server')) {
            $fields = [
                'last_sync' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'is_connected'
                ],
            ];
            $this->forge->addColumn('lokasi_server', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('lokasi_server', 'last_sync');
    }
}
