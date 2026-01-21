<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CashFlowSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Pendapatan (Income)
            [
                'name' => 'Pembayaran Internet - Pelanggan A',
                'amount' => 150000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 1, // Pembayaran Internet
                'type' => 'income',
                'description' => 'Pembayaran bulanan pelanggan A',
                'expenditure' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Pembayaran Internet - Pelanggan B',
                'amount' => 200000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 1, // Pembayaran Internet
                'type' => 'income',
                'description' => 'Pembayaran bulanan pelanggan B',
                'expenditure' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Instalasi Baru - Pelanggan C',
                'amount' => 500000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 4, // Instalasi Baru
                'type' => 'income',
                'description' => 'Biaya instalasi pelanggan baru',
                'expenditure' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Layanan Tambahan - VPN',
                'amount' => 100000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 6, // Layanan Tambahan
                'type' => 'income',
                'description' => 'Layanan VPN premium',
                'expenditure' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // Pengeluaran (Expenditure)
            [
                'name' => 'Pembelian Peralatan',
                'amount' => 1500000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 2, // Biaya Operasional
                'type' => 'expenditure',
                'description' => 'Pembelian router dan switch',
                'expenditure' => 1500000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Maintenance Server',
                'amount' => 750000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 3, // Maintenance
                'type' => 'expenditure',
                'description' => 'Biaya maintenance server bulanan',
                'expenditure' => 750000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Biaya Marketing',
                'amount' => 300000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 5, // Marketing
                'type' => 'expenditure',
                'description' => 'Biaya promosi dan iklan',
                'expenditure' => 300000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Listrik Kantor',
                'amount' => 400000.00,
                'transaction_date' => date('Y-m-d'),
                'category_id' => 8, // Listrik & Utilitas
                'type' => 'expenditure',
                'description' => 'Tagihan listrik bulanan',
                'expenditure' => 400000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert the data
        $this->db->table('cash_flow')->insertBatch($data);
    }
}
