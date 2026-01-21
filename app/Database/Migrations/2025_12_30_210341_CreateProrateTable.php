<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProrateTable extends Migration
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
            'customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'invoice_month' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'comment' => 'Format: YYYY-MM'
            ],
            'start_date' => [
                'type' => 'DATE',
            ],
            'end_date' => [
                'type' => 'DATE',
            ],
            'prorate_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'description' => [
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('invoice_month');

        // Note: Foreign key constraint removed due to potential type mismatch
        // Ensure referential integrity is maintained in application logic
        // $this->forge->addForeignKey('customer_id', 'customers', 'id_customers', 'CASCADE', 'CASCADE');

        $this->forge->createTable('prorate');
    }

    public function down()
    {
        $this->forge->dropTable('prorate');
    }
}
