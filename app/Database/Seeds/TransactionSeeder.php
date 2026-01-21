<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'branch' => 'Jakarta Timur [Demo]',
                'date' => '2025-09-09',
                'transaction_name' => 'Biaya Teknisi - Paket Internet',
                'payment_method' => 'Cash',
                'category' => 'Operasional - Instalasi',
                'description' => '',
                'type' => 'out',
                'amount' => 100000,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'branch' => 'Jakarta Timur [Demo]',
                'date' => '2025-09-09',
                'transaction_name' => 'Biaya Sales - Paket Internet',
                'payment_method' => 'Cash',
                'category' => 'Operasional - Instalasi',
                'description' => '',
                'type' => 'out',
                'amount' => 80000,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'branch' => 'Jakarta Timur [Demo]',
                'date' => '2025-09-15',
                'transaction_name' => 'Pembayaran Invoice #INV-001',
                'payment_method' => 'Transfer',
                'category' => 'Pendapatan - Pembayaran',
                'description' => 'Pembayaran pelanggan untuk bulan September',
                'type' => 'in',
                'amount' => 500000,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'branch' => 'Jakarta Timur [Demo]',
                'date' => '2025-09-20',
                'transaction_name' => 'Biaya Marketing - Banner Promosi',
                'payment_method' => 'Cash',
                'category' => 'Operasional - Marketing',
                'description' => 'Cetak banner untuk promosi paket baru',
                'type' => 'out',
                'amount' => 250000,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data
        $this->db->table('transactions')->insertBatch($data);
    }
}
