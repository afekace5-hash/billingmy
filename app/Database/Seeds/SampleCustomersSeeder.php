<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleCustomersSeeder extends Seeder
{
    public function run()
    {
        // Sample customer data with different tempo dates for testing
        $sampleCustomers = [
            [
                'nomor_layanan' => '1414370001',
                'nama_pelanggan' => 'John Doe',
                'telepphone' => '08123456789',
                'email' => 'john@example.com',
                'address' => 'Jl. Contoh No. 123',
                'status_tagihan' => 'Belum Lunas',
                'tgl_tempo' => '2025-08-05', // Overdue (past due)
                'tgl_pasang' => '2025-07-01',
                'id_paket' => 1,
                'id_lokasi_server' => 1,
                'created_at' => '2025-07-01 10:30:15',
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nomor_layanan' => '1414370002',
                'nama_pelanggan' => 'Jane Smith',
                'telepphone' => '08123456788',
                'email' => 'jane@example.com',
                'address' => 'Jl. Sample No. 456',
                'status_tagihan' => 'Lunas',
                'tgl_tempo' => '2025-08-15', // Future due date
                'tgl_pasang' => '2025-07-15',
                'id_paket' => 1,
                'id_lokasi_server' => 1,
                'created_at' => '2025-07-15 14:20:30',
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nomor_layanan' => '1414370003',
                'nama_pelanggan' => 'Bob Wilson',
                'telepphone' => '08123456787',
                'email' => 'bob@example.com',
                'address' => 'Jl. Test No. 789',
                'status_tagihan' => 'Belum Lunas',
                'tgl_tempo' => '2025-08-12', // Coming due soon
                'tgl_pasang' => '2025-07-12',
                'id_paket' => 1,
                'id_lokasi_server' => 1,
                'created_at' => '2025-07-12 09:15:45',
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert sample data
        foreach ($sampleCustomers as $customer) {
            // Check if customer with same nomor_layanan already exists
            $existing = $this->db->table('customers')
                ->where('nomor_layanan', $customer['nomor_layanan'])
                ->get()
                ->getRow();

            if (!$existing) {
                $this->db->table('customers')->insert($customer);
                echo "Inserted customer: " . $customer['nama_pelanggan'] . "\n";
            } else {
                echo "Customer already exists: " . $customer['nama_pelanggan'] . "\n";
            }
        }
    }
}
