<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBranchesTable extends Migration
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
            'branch_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'payment_type' => [
                'type' => 'ENUM',
                'constraint' => ['Bayar diawal', 'Bayar diakhir'],
                'default' => 'Bayar diawal',
                'null' => false,
            ],
            'due_date' => [
                'type' => 'INT',
                'constraint' => 2,
                'null' => false,
                'comment' => 'Tanggal jatuh tempo (1-31)',
            ],
            'day_before_due_date' => [
                'type' => 'INT',
                'constraint' => 2,
                'null' => false,
                'default' => 0,
                'comment' => 'Hari sebelum jatuh tempo untuk reminder',
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('branch_name');
        $this->forge->addKey('city');
        $this->forge->createTable('branches');

        // Insert demo data
        $data = [
            [
                'branch_name' => 'Jakarta Timur (Demo)',
                'city' => 'KOTA ADM. JAKARTA TIMUR',
                'payment_type' => 'Bayar diawal',
                'due_date' => 10,
                'day_before_due_date' => 9,
                'address' => 'Jl. Contoh No. 123, Jakarta Timur',
                'description' => 'Demo branch untuk testing',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('branches')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('branches');
    }
}
