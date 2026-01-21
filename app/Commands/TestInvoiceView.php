<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestInvoiceView extends BaseCommand
{
    protected $group = 'Test';
    protected $name = 'test:invoiceview';
    protected $description = 'Test invoice view data loading';

    public function run(array $params)
    {
        $invoiceId = $params[0] ?? 330;

        CLI::write("Testing Invoice View for ID: {$invoiceId}", 'green');
        CLI::write(str_repeat('=', 80));

        // Simulate what controller does
        $paymentTransactionModel = model('PaymentTransactionModel');
        $paymentHistory = $paymentTransactionModel
            ->where('invoice_id', $invoiceId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        CLI::write("Payment History Count: " . count($paymentHistory), 'cyan');

        if (empty($paymentHistory)) {
            CLI::write('No payment history found', 'yellow');
            return;
        }

        foreach ($paymentHistory as $idx => $payment) {
            $num = $idx + 1;
            CLI::write("\n Payment #{$num}:", 'yellow');
            CLI::write("  - ID: {$payment['id']}");
            CLI::write("  - Method: " . ($payment['payment_method'] ?? 'NULL'));
            CLI::write("  - Code: " . ($payment['payment_code'] ?? 'NULL'));
            CLI::write("  - Status: {$payment['status']}");
            CLI::write("  - Created: {$payment['created_at']}");
        }
    }
}
