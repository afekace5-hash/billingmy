<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KategoriKasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama' => 'Pembayaran Internet',
                'jenis' => 'pemasukan',
                'deskripsi' => 'Pendapatan dari pembayaran layanan internet pelanggan'
            ],
            [
                'nama' => 'Biaya Operasional',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran untuk operasional harian'
            ],
            [
                'nama' => 'Gaji Karyawan',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran untuk gaji karyawan'
            ],
            [
                'nama' => 'Maintenance Perangkat',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Biaya pemeliharaan dan perbaikan perangkat'
            ],
            [
                'nama' => 'Instalasi Baru',
                'jenis' => 'pemasukan',
                'deskripsi' => 'Pendapatan dari biaya instalasi pelanggan baru'
            ],
            [
                'nama' => 'Pembelian Perangkat',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran untuk pembelian perangkat baru'
            ],
            [
                'nama' => 'Biaya Listrik',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran untuk tagihan listrik'
            ],
            [
                'nama' => 'Biaya Sewa',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran untuk sewa lokasi atau peralatan'
            ],
            [
                'nama' => 'Upgrade Layanan',
                'jenis' => 'pemasukan',
                'deskripsi' => 'Pendapatan dari upgrade paket layanan'
            ],
            [
                'nama' => 'Denda Keterlambatan',
                'jenis' => 'pemasukan',
                'deskripsi' => 'Pendapatan dari denda keterlambatan pembayaran'
            ],
            [
                'nama' => 'Marketing',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran untuk kegiatan pemasaran'
            ],
            [
                'nama' => 'Administrasi',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Biaya administrasi dan operasional kantor'
            ],
            [
                'nama' => 'Pendapatan Lain-lain',
                'jenis' => 'pemasukan',
                'deskripsi' => 'Pendapatan dari sumber lainnya'
            ],
            [
                'nama' => 'Pengeluaran Lain-lain',
                'jenis' => 'pengeluaran',
                'deskripsi' => 'Pengeluaran tidak terduga atau lainnya'
            ]
        ];

        foreach ($data as $category) {
            // Skip if category already exists
            $exists = $this->db->table('kategori_kas')
                ->where('nama', $category['nama'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('kategori_kas')->insert($category);
            }
        }
    }
}
