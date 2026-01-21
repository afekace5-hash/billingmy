<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ProrateModel;
use App\Models\CustomerModel;

class AutoGenerateProrate extends BaseCommand
{
    protected $group = 'Billing';
    protected $name = 'prorate:generate';
    protected $description = 'Auto-generate prorate for new customers installed between 1st-10th of the month';

    public function run(array $params)
    {
        CLI::write('Starting auto-generate prorate...', 'yellow');

        $db = \Config\Database::connect();
        $prorateModel = new ProrateModel();

        // Get current month
        $currentMonth = date('Y-m');
        $currentYear = date('Y');
        $currentMonthNum = date('m');

        // Get customers yang dipasang di bulan ini antara tanggal 1-10
        $builder = $db->table('customers c');
        $builder->select('c.id_customers, c.nama_pelanggan, c.tgl_tempo, c.id_paket, 
                         pk.name as nama_paket, pk.price as harga, c.nomor_layanan');
        $builder->join('package_profiles pk', 'pk.id = c.id_paket', 'left');
        $builder->where('YEAR(c.tgl_tempo)', $currentYear);
        $builder->where('MONTH(c.tgl_tempo)', $currentMonthNum);
        $builder->where('DAY(c.tgl_tempo) <=', 10);
        $builder->where('c.status_tagihan !=', '');

        // Check if prorate already exists for this customer this month
        $builder->where("NOT EXISTS (
            SELECT 1 FROM prorate p 
            WHERE p.customer_id = c.id_customers 
            AND p.invoice_month = '{$currentMonth}'
        )", null, false);

        $customers = $builder->get()->getResultArray();

        if (empty($customers)) {
            CLI::write('No customers found for prorate generation.', 'green');
            return;
        }

        CLI::write('Found ' . count($customers) . ' customers for prorate generation.', 'cyan');

        $generated = 0;
        foreach ($customers as $customer) {
            try {
                // Parse installation date
                $installDate = new \DateTime($customer['tgl_tempo']);
                $startDay = $installDate->format('d');

                // Calculate end date (last day of month)
                $lastDay = date('t', strtotime($currentMonth . '-01'));
                $endDate = new \DateTime($currentYear . '-' . $currentMonthNum . '-' . $lastDay);

                // Calculate prorate days
                $prorateDays = $lastDay - $startDay + 1;

                // Calculate prorate amount
                $dailyRate = $customer['harga'] / $lastDay;
                $prorateAmount = $dailyRate * $prorateDays;

                // Format description in Indonesian
                $installDateObj = new \DateTime($customer['tgl_tempo']);
                $formatter = new \IntlDateFormatter(
                    'id_ID',
                    \IntlDateFormatter::LONG,
                    \IntlDateFormatter::NONE,
                    'Asia/Jakarta',
                    \IntlDateFormatter::GREGORIAN,
                    'MMMM yyyy'
                );
                $monthYearName = $formatter->format($installDateObj);

                $description = "Prorate dari tgl " . str_pad($startDay, 2, '0', STR_PAD_LEFT) .
                    " sampai " . $lastDay . " bulan " . $monthYearName;

                // Insert prorate
                $prorateData = [
                    'customer_id' => $customer['id_customers'],
                    'invoice_month' => $currentMonth,
                    'start_date' => $installDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'prorate_amount' => round($prorateAmount, 0),
                    'description' => $description,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $prorateModel->insert($prorateData);
                $generated++;

                CLI::write("✓ Generated prorate for: {$customer['nama_pelanggan']} (Rp. " . number_format($prorateAmount, 0, ',', '.') . ")", 'green');
            } catch (\Exception $e) {
                CLI::error("✗ Failed to generate prorate for {$customer['nama_pelanggan']}: " . $e->getMessage());
            }
        }

        CLI::write("\n" . str_repeat('=', 50), 'white');
        CLI::write("Prorate generation completed!", 'green');
        CLI::write("Successfully generated: {$generated} records", 'cyan');
        CLI::write(str_repeat('=', 50) . "\n", 'white');
    }
}
