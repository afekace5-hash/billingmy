<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckPaymentData extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'check:payment';
    protected $description = 'Check payment transactions data';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $query = $db->query('SELECT id, invoice_id, payment_method, payment_code, status FROM payment_transactions ORDER BY id DESC LIMIT 10');
        $results = $query->getResultArray();

        CLI::write('Payment Transactions Data:', 'green');
        CLI::write(str_repeat('=', 80));

        if (empty($results)) {
            CLI::write('No data found', 'yellow');
            return;
        }

        foreach ($results as $row) {
            CLI::write("ID: {$row['id']}", 'cyan');
            CLI::write("Invoice ID: {$row['invoice_id']}");
            CLI::write("Payment Method: " . ($row['payment_method'] ?: 'NULL'));
            CLI::write("Payment Code: " . ($row['payment_code'] ?: 'NULL'));
            CLI::write("Status: {$row['status']}");
            CLI::write(str_repeat('-', 80));
        }
    }
}
