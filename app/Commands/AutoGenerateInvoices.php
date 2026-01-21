<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\GenerateInvoices;

class AutoGenerateInvoices extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'invoices:autogenerate';
    protected $description = 'Generate invoices automatically on the 1st of every month.';
    public function run(array $params)
    {
        CLI::write('Starting auto-generate invoices...', 'yellow');

        // Gunakan parameter periode dari command line atau default bulan ini
        $periode = $params[0] ?? date('Y-m'); // Format: 2025-06

        CLI::write('Generating invoices for periode: ' . $periode, 'cyan');

        try {
            // Gunakan controller GenerateInvoices yang sudah ada
            $generateController = new GenerateInvoices();

            // Panggil method generate dengan parameter periode
            $result = $generateController->generate($periode);

            // Parse hasil response - jika berupa array langsung, gunakan itu
            if (is_array($result)) {
                $data = $result;
            } else {
                // Jika berupa response object, ambil body
                $response = $result->getBody();
                $data = json_decode($response, true);
            }

            if ($data && isset($data['status']) && $data['status'] === 'success') {
                CLI::write('✓ Invoices generated successfully for periode ' . $periode, 'green');
                CLI::write('✓ Created: ' . ($data['created'] ?? 0) . ' invoices', 'green');
                if (isset($data['skipped']) && $data['skipped'] > 0) {
                    CLI::write('ℹ Skipped: ' . $data['skipped'] . ' invoices (already exist)', 'cyan');
                }
            } else {
                CLI::write('✗ Failed to generate invoices for periode ' . $periode, 'red');
                if ($data && isset($data['message'])) {
                    CLI::write('Error: ' . $data['message'], 'red');
                }
            }
        } catch (\Exception $e) {
            CLI::write('✗ Exception occurred: ' . $e->getMessage(), 'red');
            CLI::write('Stack trace: ' . $e->getTraceAsString(), 'red');
        }

        CLI::write('Auto-generate invoices command completed.', 'yellow');
    }
}
