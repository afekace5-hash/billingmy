<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerAppFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $columnsToAdd = [];

        // Password hash untuk login
        if (!$db->fieldExists('password_hash', 'customers')) {
            $columnsToAdd['password_hash'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Encrypted password for customer app login'
            ];
        }

        // API token untuk keep login
        if (!$db->fieldExists('api_token', 'customers')) {
            $columnsToAdd['api_token'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'JWT token for API authentication'
            ];
        }

        // FCM token untuk push notification
        if (!$db->fieldExists('fcm_token', 'customers')) {
            $columnsToAdd['fcm_token'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Firebase Cloud Messaging token for push notifications'
            ];
        }

        // Last login tracking
        if (!$db->fieldExists('last_login', 'customers')) {
            $columnsToAdd['last_login'] = [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Last login timestamp'
            ];
        }

        // Password reset token
        if (!$db->fieldExists('password_reset_token', 'customers')) {
            $columnsToAdd['password_reset_token'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Token for password reset'
            ];
        }

        // Password reset expiry
        if (!$db->fieldExists('password_reset_expires', 'customers')) {
            $columnsToAdd['password_reset_expires'] = [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Password reset token expiration time'
            ];
        }

        // Account activation status
        if (!$db->fieldExists('is_activated', 'customers')) {
            $columnsToAdd['is_activated'] = [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Whether customer has activated their app account'
            ];
        }

        // Activation token
        if (!$db->fieldExists('activation_token', 'customers')) {
            $columnsToAdd['activation_token'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Token for account activation'
            ];
        }

        // Device info
        if (!$db->fieldExists('device_info', 'customers')) {
            $columnsToAdd['device_info'] = [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON data of device information'
            ];
        }

        // Add all missing columns
        if (!empty($columnsToAdd)) {
            $this->forge->addColumn('customers', $columnsToAdd);
            echo "Added " . count($columnsToAdd) . " columns for customer app functionality\n";
        } else {
            echo "All customer app columns already exist\n";
        }

        // Add index for api_token for faster lookup
        if (!$db->query("SHOW INDEXES FROM customers WHERE Key_name = 'api_token'")->getResult()) {
            $this->db->query("ALTER TABLE customers ADD INDEX api_token (api_token)");
            echo "Added index for api_token\n";
        }
    }

    public function down()
    {
        $columnsToRemove = [
            'password_hash',
            'api_token',
            'fcm_token',
            'last_login',
            'password_reset_token',
            'password_reset_expires',
            'is_activated',
            'activation_token',
            'device_info'
        ];

        foreach ($columnsToRemove as $column) {
            if ($this->db->fieldExists($column, 'customers')) {
                $this->forge->dropColumn('customers', $column);
            }
        }
    }
}
