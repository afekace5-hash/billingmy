<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentRequestLogsTable extends Migration
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
            'invoice_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'invoice_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'payment_gateway' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'method_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'success', 'failed', 'expired'],
                'default' => 'pending',
            ],
            'payment_code' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'payment_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'request_data' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON request data',
            ],
            'response_data' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON response data',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('invoice_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('payment_gateway');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->createTable('payment_request_logs');
    }

    public function down()
    {
        $this->forge->dropTable('payment_request_logs');
    }
}
