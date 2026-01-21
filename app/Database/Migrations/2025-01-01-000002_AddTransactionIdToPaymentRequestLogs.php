<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransactionIdToPaymentRequestLogs extends Migration
{
    public function up()
    {
        $fields = [
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'payment_code',
                'comment' => 'Transaction ID from payment gateway (link_id from Flip, transaction_id from Midtrans/Duitku)'
            ]
        ];

        $this->forge->addColumn('payment_request_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('payment_request_logs', 'transaction_id');
    }
}
