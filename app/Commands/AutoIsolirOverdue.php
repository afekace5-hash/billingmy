<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\MikroTikAutoService;

class AutoIsolirOverdue extends BaseCommand
{
    protected $group       = 'Auto';
    protected $name        = 'auto:isolir-overdue';
    protected $description = 'Automatically isolate customers with overdue payments';

    protected $usage = 'auto:isolir-overdue [days_overdue]';
    protected $arguments = [
        'days_overdue' => 'Number of days overdue before isolation (default: 7)'
    ];

    protected $options = [
        '--dry-run' => 'Show what would be isolated without actually doing it',
        '--force'   => 'Force isolation even if already isolated'
    ];

    public function run(array $params)
    {
        $daysOverdue = $params[0] ?? 7;
        $isDryRun = CLI::getOption('dry-run');
        $force = CLI::getOption('force');

        CLI::write('=== AUTO ISOLIR OVERDUE CUSTOMERS ===', 'yellow');
        CLI::write('Days overdue threshold: ' . $daysOverdue);
        CLI::write('Dry run mode: ' . ($isDryRun ? 'YES' : 'NO'));
        CLI::write('Force mode: ' . ($force ? 'YES' : 'NO'));
        CLI::write('');

        try {
            $mikrotikService = new MikroTikAutoService();

            // Get overdue customers
            $db = \Config\Database::connect();

            $builder = $db->table('customers c');
            $query = $builder
                ->select('c.*, ci.due_date, COUNT(ci.id) as unpaid_count, 
                         MAX(ci.due_date) as latest_due_date,
                         MIN(ci.due_date) as earliest_due_date')
                ->join('customer_invoices ci', 'ci.customer_id = c.id_customers')
                ->where('ci.status', 'unpaid')
                ->where('ci.due_date <', date('Y-m-d', strtotime("-{$daysOverdue} days")))
                ->where('c.pppoe_username IS NOT NULL')
                ->where('c.pppoe_username !=', '')
                ->where('c.id_lokasi_server IS NOT NULL')
                ->groupBy('c.id_customers');

            // Add isolation status filter if not forcing
            if (!$force) {
                $query->where('c.isolir_status', 0);
            }

            $overdueCustomers = $query->get()->getResultArray();

            if (empty($overdueCustomers)) {
                CLI::write('No overdue customers found for isolation.', 'green');
                return;
            }

            CLI::write('Found ' . count($overdueCustomers) . ' overdue customers:', 'cyan');
            CLI::write('');

            $processed = 0;
            $success = 0;
            $failed = 0;

            foreach ($overdueCustomers as $customer) {
                $processed++;

                // Calculate overdue days
                $overdueDays = ceil((time() - strtotime($customer['earliest_due_date'])) / (60 * 60 * 24));

                CLI::write(sprintf(
                    '[%d/%d] Customer: %s (%s)',
                    $processed,
                    count($overdueCustomers),
                    $customer['nama_pelanggan'],
                    $customer['nomor_layanan']
                ), 'white');

                CLI::write(sprintf(
                    '  Unpaid: %d invoices | Overdue: %d days | Status: %s',
                    $customer['unpaid_count'],
                    $overdueDays,
                    $customer['isolir_status'] ? 'ISOLATED' : 'ACTIVE'
                ), 'yellow');

                if ($isDryRun) {
                    CLI::write('  [DRY RUN] Would isolate this customer', 'cyan');
                    $success++;
                    continue;
                }

                // Perform isolation
                $reason = "Auto-isolir: {$customer['unpaid_count']} tagihan telat lebih dari {$daysOverdue} hari";
                $result = $mikrotikService->isolateCustomer($customer['id_customers'], $reason);

                if ($result['success']) {
                    CLI::write('  ✓ Successfully isolated', 'green');
                    $success++;
                } else {
                    CLI::write('  ✗ Failed to isolate: ' . $result['message'], 'red');
                    $failed++;
                }

                // Small delay to prevent overwhelming the system
                usleep(500000); // 0.5 second
            }

            CLI::write('');
            CLI::write('=== SUMMARY ===', 'yellow');
            CLI::write('Total processed: ' . $processed);
            CLI::write('Successful: ' . $success, 'green');
            if ($failed > 0) {
                CLI::write('Failed: ' . $failed, 'red');
            }
            CLI::write('');

            if ($isDryRun) {
                CLI::write('This was a dry run. No actual changes were made.', 'cyan');
                CLI::write('Run without --dry-run to actually isolate customers.', 'cyan');
            } else {
                CLI::write('Auto-isolation completed!', 'green');
            }
        } catch (\Exception $e) {
            CLI::error('Error during auto-isolation: ' . $e->getMessage());
            CLI::write('Stack trace:', 'red');
            CLI::write($e->getTraceAsString(), 'red');
        }
    }
}
