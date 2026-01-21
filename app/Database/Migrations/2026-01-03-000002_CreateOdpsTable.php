<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOdpsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'branch_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'area_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'odp_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'customer_active' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Jumlah customer aktif di ODP ini',
            ],
            'core' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'comment' => 'Jumlah core yang tersedia',
            ],
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
                'comment' => 'Koordinat latitude',
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
                'comment' => 'Koordinat longitude',
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Alamat lokasi ODP',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addKey('area_id');
        $this->forge->addKey('odp_name');

        // Add foreign key constraints
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('area_id', 'areas', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('odps');

        // Check if areas exist first
        $db = \Config\Database::connect();
        $areaCount = $db->table('areas')->countAll();

        // Only insert demo data if we have areas
        if ($areaCount >= 3) {
            // Insert demo data
            $data = [
                [
                    'branch_id' => 1,
                    'area_id' => 1,
                    'odp_name' => 'ODP 02-GTFH- DEPAN TOKO LISTRIK',
                    'customer_active' => 8,
                    'core' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 2,
                    'odp_name' => 'ODP 02-XAZJ- DEPAN TOKO BUAH',
                    'customer_active' => 8,
                    'core' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 2,
                    'odp_name' => 'ODP 03-V4SX-DEPAN WARUNG MADURA',
                    'customer_active' => 16,
                    'core' => 16,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 3,
                    'odp_name' => 'ODP 01-VVC4- DEPAN GG MUSYAWARAH',
                    'customer_active' => 8,
                    'core' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 1,
                    'odp_name' => 'ODP 01-BNHG- DEPAN TOKO ROTI',
                    'customer_active' => 16,
                    'core' => 16,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 3,
                    'odp_name' => 'ODP 02-FGA3- DEPAN GG MUSTOFA',
                    'customer_active' => 16,
                    'core' => 16,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 2,
                    'odp_name' => 'ODP 04-RRTF- DEPAN GG KLINIK',
                    'customer_active' => 8,
                    'core' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'branch_id' => 1,
                    'area_id' => 1,
                    'odp_name' => 'ODP 01-Z6S4- DEPAN PUSKESMAS',
                    'customer_active' => 8,
                    'core' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ];

            $db->table('odps')->insertBatch($data);
        }
    }

    public function down()
    {
        $this->forge->dropTable('odps');
    }
}
