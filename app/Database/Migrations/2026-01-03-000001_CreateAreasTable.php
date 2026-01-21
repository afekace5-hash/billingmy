<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAreasTable extends Migration
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
            'area_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => false,
                'comment' => 'Koordinat latitude',
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => false,
                'comment' => 'Koordinat longitude',
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
        $this->forge->addKey('area_name');

        // Add foreign key constraint
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('areas');

        // Insert demo data
        $data = [
            [
                'branch_id' => 1,
                'area_name' => 'Area Kelapa Dua Wetan',
                'latitude' => -6.33945073,
                'longitude' => 106.88302721,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'branch_id' => 1,
                'area_name' => 'Area Cibubur',
                'latitude' => -6.34988203,
                'longitude' => 106.87602334,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'branch_id' => 1,
                'area_name' => 'Area Arundina',
                'latitude' => -6.35175477,
                'longitude' => 106.88409346,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('areas')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('areas');
    }
}
