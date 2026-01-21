<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriKasModel extends Model
{
    protected $table = 'kategori_kas';
    protected $primaryKey = 'id_category';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['nama', 'jenis_kas', 'keterangan'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama' => 'required|min_length[3]',
        'jenis_kas' => 'required|in_list[revenue,expenditure]',
        'keterangan' => 'required|min_length[5]'
    ];

    protected $validationMessages = [
        'nama' => [
            'required' => 'Nama kategori kas harus diisi',
            'min_length' => 'Nama kategori kas minimal 3 karakter'
        ],
        'jenis_kas' => [
            'required' => 'Jenis kas harus dipilih',
            'in_list' => 'Jenis kas harus berupa pendapatan atau pengeluaran'
        ],
        'keterangan' => [
            'required' => 'Keterangan harus diisi',
            'min_length' => 'Keterangan minimal 5 karakter'
        ]
    ];

    public function getDataTables($start, $length, $search = '')
    {
        $builder = $this->db->table($this->table);

        // Total records without filtering
        $total = $builder->countAllResults(false);

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('nama', $search)
                ->orLike('jenis_kas', $search)
                ->orLike('keterangan', $search)
                ->groupEnd();
        }

        // Total records with filtering
        $filtered = $builder->countAllResults(false);

        // Fetch records
        $query = $builder->orderBy('nama', 'ASC')
            ->limit($length, $start)
            ->get();

        $data = $query->getResultArray();

        // Add row index
        foreach ($data as $key => $value) {
            $data[$key]['DT_RowIndex'] = $start + $key + 1;
        }

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }
}
