<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaidAtToPaymentTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'expired_at'
            ]
        ];

        $this->forge->addColumn('payment_transactions', $fields);

        log_message('info', 'Migration: Added paid_at column to payment_transactions table');
    }

    public function down()
    {
        $this->forge->dropColumn('payment_transactions', 'paid_at');

        log_message('info', 'Migration: Dropped paid_at column from payment_transactions table');
    }
}
