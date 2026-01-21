<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CashFlowCategoriesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama' => 'Pembayaran Internet',
                'jenis_kas' => 'pemasukan',
                'keterangan' => 'Kategori untuk pembayaran layanan internet pelanggan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Biaya Operasional',
                'jenis_kas' => 'pengeluaran',
                'keterangan' => 'Kategori untuk biaya operasional harian',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Maintenance',
                'jenis_kas' => 'pengeluaran',
                'keterangan' => 'Kategori untuk biaya maintenance peralatan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Instalasi Baru',
                'jenis_kas' => 'pemasukan',
                'keterangan' => 'Kategori untuk pendapatan instalasi pelanggan baru',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Marketing',
                'jenis_kas' => 'pengeluaran',
                'keterangan' => 'Kategori untuk biaya promosi dan marketing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Layanan Tambahan',
                'jenis_kas' => 'pemasukan',
                'keterangan' => 'Kategori untuk pendapatan layanan tambahan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Administrasi',
                'jenis_kas' => 'pengeluaran',
                'keterangan' => 'Kategori untuk biaya administrasi kantor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama' => 'Listrik & Utilitas',
                'jenis_kas' => 'pengeluaran',
                'keterangan' => 'Kategori untuk biaya listrik dan utilitas',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert the data
        $this->db->table('kategori_kas')->insertBatch($data);
    }
}
