<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToInvoices extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        // Check which fields exist
        $fields = $db->getFieldNames('customer_invoices');

        // Add payment_code if not exists
        if (!in_array('payment_code', $fields)) {
            $forge->addColumn('customer_invoices', [
                'payment_code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'payment_method'
                ]
            ]);
        }

        // Add payment_transaction_id if not exists
        if (!in_array('payment_transaction_id', $fields)) {
            $forge->addColumn('customer_invoices', [
                'payment_transaction_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'payment_url'
                ]
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('customer_invoices', ['payment_code', 'payment_url', 'payment_transaction_id']);
    }
}
