<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFlipPaymentGateway extends Migration
{
    public function up()
    {
        // Check if payment_gateways table exists, if not create it
        if (!$this->db->tableExists('payment_gateways')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'gateway_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'gateway_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'api_key' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'api_secret' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'merchant_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'private_key' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'callback_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'environment' => [
                    'type' => 'ENUM',
                    'constraint' => ['sandbox', 'production'],
                    'default' => 'sandbox',
                ],
                'settings' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'admin_fees' => [
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
            $this->forge->addUniqueKey('gateway_type');
            $this->forge->createTable('payment_gateways', true);

            echo "Table payment_gateways created successfully.\n";
        }

        // Insert Flip configuration (will be replaced with actual credentials later)
        $data = [
            'gateway_name' => 'Flip',
            'gateway_type' => 'flip',
            'is_active' => 0, // Disabled by default until credentials are set
            'api_key' => '', // Will be set via flip-config.php
            'api_secret' => '', // Will be set via flip-config.php
            'environment' => 'production',
            'settings' => json_encode([]),
            'admin_fees' => json_encode([
                'flip_va' => 4000,
                'flip_qris' => 0,
                'flip_ewallet' => 0,
                'flip_retail' => 2500,
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Check if Flip already exists
        $existing = $this->db->table('payment_gateways')
            ->where('gateway_type', 'flip')
            ->get()
            ->getRowArray();

        if (!$existing) {
            $this->db->table('payment_gateways')->insert($data);
            echo "Flip payment gateway configuration added successfully.\n";
            echo "Please configure credentials using: /flip-config.php\n";
        } else {
            echo "Flip payment gateway already exists, skipping insert.\n";
        }
    }

    public function down()
    {
        // Remove Flip configuration
        $this->db->table('payment_gateways')
            ->where('gateway_type', 'flip')
            ->delete();

        echo "Flip payment gateway configuration removed.\n";

        // Note: We don't drop the payment_gateways table as it might contain other gateways
        // If you want to completely remove the table, uncomment the line below:
        // $this->forge->dropTable('payment_gateways', true);
    }
}
