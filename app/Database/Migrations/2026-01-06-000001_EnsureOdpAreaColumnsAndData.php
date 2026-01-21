<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureOdpAreaColumnsAndData extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        // Pastikan kolom odp_id dan area_id ada di tabel customers
        if (!$db->fieldExists('odp_id', 'customers')) {
            $forge->addColumn('customers', [
                'odp_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'id_lokasi_server'
                ]
            ]);
            echo "Added odp_id column to customers table\n";
        }

        if (!$db->fieldExists('area_id', 'customers')) {
            $forge->addColumn('customers', [
                'area_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'odp_id'
                ]
            ]);
            echo "Added area_id column to customers table\n";
        }

        // Pastikan tabel odps ada
        if (!$db->tableExists('odps')) {
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'odp_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'odp_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'area_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'latitude' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'longitude' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'address' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'capacity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 16,
                ],
                'used_ports' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['active', 'inactive', 'maintenance'],
                    'default' => 'active',
                ],
                'notes' => [
                    'type' => 'TEXT',
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
            $forge->addKey('id', true);
            $forge->createTable('odps');
            echo "Created odps table\n";
        }

        // Pastikan tabel areas ada
        if (!$db->tableExists('areas')) {
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'area_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'area_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'branch_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'latitude' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'longitude' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['active', 'inactive'],
                    'default' => 'active',
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
            $forge->createTable('areas');
            echo "Created areas table\n";
        }

        // Tambahkan data sample areas jika tabel kosong
        $areaCount = $db->table('areas')->countAll();
        if ($areaCount == 0) {
            $areas = [
                [
                    'area_name' => 'Area Pusat',
                    'area_code' => 'AP-001',
                    'description' => 'Area pusat kota',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'area_name' => 'Area Utara',
                    'area_code' => 'AU-001',
                    'description' => 'Area utara kota',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'area_name' => 'Area Selatan',
                    'area_code' => 'AS-001',
                    'description' => 'Area selatan kota',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ];
            $db->table('areas')->insertBatch($areas);
            echo "Added sample area data\n";
        }

        // Tambahkan data sample ODPs jika tabel kosong
        $odpCount = $db->table('odps')->countAll();
        if ($odpCount == 0) {
            $odps = [
                [
                    'odp_name' => 'ODP-Pusat-01',
                    'odp_code' => 'ODP-P-001',
                    'area_id' => 1,
                    'capacity' => 16,
                    'used_ports' => 0,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'odp_name' => 'ODP-Pusat-02',
                    'odp_code' => 'ODP-P-002',
                    'area_id' => 1,
                    'capacity' => 16,
                    'used_ports' => 0,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'odp_name' => 'ODP-Utara-01',
                    'odp_code' => 'ODP-U-001',
                    'area_id' => 2,
                    'capacity' => 16,
                    'used_ports' => 0,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'odp_name' => 'ODP-Selatan-01',
                    'odp_code' => 'ODP-S-001',
                    'area_id' => 3,
                    'capacity' => 16,
                    'used_ports' => 0,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ];
            $db->table('odps')->insertBatch($odps);
            echo "Added sample ODP data\n";
        }
    }

    public function down()
    {
        // Optional: Drop columns if needed
        // $forge = \Config\Database::forge();
        // $forge->dropColumn('customers', ['odp_id', 'area_id']);
    }
}
