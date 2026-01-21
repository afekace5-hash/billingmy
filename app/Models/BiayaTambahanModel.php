<?php

namespace App\Models;

use CodeIgniter\Model;

class BiayaTambahanModel extends Model
{
    protected $table = 'biaya_tambahan';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'kategori',
        'nama_biaya',
        'jumlah',
        'tanggal',
        'deskripsi',
        'status',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get all biaya tambahan by kategori
     */
    public function getBiayaTambahanByKategori($kategori)
    {
        return $this->where('kategori', $kategori)
            ->where('status', 1)
            ->findAll();
    }

    /**
     * Get total biaya tambahan by kategori
     */
    public function getTotalBiayaTambahanByKategori($kategori)
    {
        $result = $this->selectSum('jumlah')
            ->where('kategori', $kategori)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        return $result['jumlah'] ?? 0;
    }

    /**
     * Get biaya tambahan for specific period
     */
    public function getBiayaTambahanForPeriod($month, $year)
    {
        return $this->where('status', 1)
            ->where('MONTH(tanggal)', $month)
            ->where('YEAR(tanggal)', $year)
            ->findAll();
    }

    /**
     * Get summary by kategori
     */
    public function getSummaryByKategori()
    {
        return $this->select('kategori, SUM(jumlah) as total, COUNT(*) as count')
            ->where('status', 1)
            ->groupBy('kategori')
            ->findAll();
    }
}
