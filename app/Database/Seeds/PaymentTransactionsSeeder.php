<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class PaymentTransactionsSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('id_ID');
        $db = \Config\Database::connect();

        // Clear existing data
        $db->table('payment_transactions')->truncate();

        $paymentMethods = [
            'QRIS',
            'VIRTUAL_AKUN_BRI',
            'VIRTUAL_AKUN_BCA',
            'VIRTUAL_AKUN_BNI',
            'VIRTUAL_AKUN_MANDIRI'
        ];

        $channels = [
            'QRIS' => 'QRIS',
            'VIRTUAL_AKUN_BRI' => 'VIRTUAL AKUN BRI',
            'VIRTUAL_AKUN_BCA' => 'VIRTUAL AKUN BCA',
            'VIRTUAL_AKUN_BNI' => 'VIRTUAL AKUN BNI',
            'VIRTUAL_AKUN_MANDIRI' => 'VIRTUAL AKUN MANDIRI'
        ];

        $billers = ['KIMONET', 'MIBER'];
        $statuses = ['sukses', 'pending', 'failed', 'expired'];
        $statusWeights = [70, 15, 10, 5]; // 70% sukses, 15% pending, etc.

        $data = [];

        // Generate 100 sample transactions
        for ($i = 1; $i <= 100; $i++) {
            $paymentMethod = $faker->randomElement($paymentMethods);
            $amount = $faker->numberBetween(50000, 500000);
            $adminFee = $faker->numberBetween(2500, 7500);
            $totalAmount = $amount + $adminFee;

            // Weighted random status selection
            $rand = $faker->numberBetween(1, 100);
            if ($rand <= 70) {
                $status = 'sukses';
            } elseif ($rand <= 85) {
                $status = 'pending';
            } elseif ($rand <= 95) {
                $status = 'failed';
            } else {
                $status = 'expired';
            }

            $createdAt = $faker->dateTimeBetween('-30 days', 'now');
            $updatedAt = $status === 'sukses' ?
                $faker->dateTimeBetween($createdAt, 'now') :
                $createdAt;

            $data[] = [
                'transaction_code' => 'TRX' . str_pad($i, 6, '0', STR_PAD_LEFT) . date('Ymd'),
                'customer_number' => $faker->numerify('####-####-####'),
                'customer_name' => $faker->name(),
                'payment_method' => $paymentMethod,
                'channel' => $channels[$paymentMethod],
                'biller' => $faker->randomElement($billers),
                'amount' => $amount,
                'admin_fee' => $adminFee,
                'total_amount' => $totalAmount,
                'status' => $status,
                'payment_code' => $paymentMethod === 'QRIS' ? null : $faker->numerify('##########'),
                'expired_at' => $faker->dateTimeBetween($createdAt, '+1 day'),
                'paid_at' => $status === 'sukses' ? $updatedAt->format('Y-m-d H:i:s') : null,
                'callback_data' => json_encode([
                    'gateway_transaction_id' => $faker->uuid(),
                    'reference_number' => $faker->numerify('REF##########'),
                    'callback_time' => $updatedAt->format('Y-m-d H:i:s')
                ]),
                'notes' => $status === 'failed' ? 'Payment timeout' : null,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
            ];
        }

        // Insert data in batches
        $batchSize = 25;
        $batches = array_chunk($data, $batchSize);

        foreach ($batches as $batch) {
            $db->table('payment_transactions')->insertBatch($batch);
        }

        echo "PaymentTransactionsSeeder: Inserted " . count($data) . " sample payment transactions.\n";
    }
}
