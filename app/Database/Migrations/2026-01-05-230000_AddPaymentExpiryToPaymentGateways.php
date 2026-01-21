<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentExpiryToPaymentGateways extends Migration
{
    public function up()
    {
        // Add payment_expiry_hours column to payment_gateways table
        $fields = [
            'payment_expiry_hours' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 24,
                'null' => false,
                'comment' => 'Payment expiry time in hours (default 24 hours)',
                'after' => 'admin_fees'
            ],
        ];

        $this->forge->addColumn('payment_gateways', $fields);

        // Log migration
        log_message('info', 'Migration: Added payment_expiry_hours column to payment_gateways table');
    }

    public function down()
    {
        // Remove payment_expiry_hours column
        $this->forge->dropColumn('payment_gateways', 'payment_expiry_hours');

        log_message('info', 'Migration: Removed payment_expiry_hours column from payment_gateways table');
    }
}
