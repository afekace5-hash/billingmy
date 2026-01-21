<?php

namespace App\Models;

use CodeIgniter\Model;

class PromoModel extends Model
{
    protected $table            = 'promos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'description',
        'badge_text',
        'button_text',
        'button_action',
        'gradient_start',
        'gradient_end',
        'display_order',
        'is_active',
        'start_date',
        'end_date',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'title'          => 'required|max_length[255]',
        'badge_text'     => 'required|max_length[50]',
        'button_text'    => 'required|max_length[100]',
        'button_action'  => 'required|max_length[255]',
        'gradient_start' => 'permit_empty|max_length[7]',
        'gradient_end'   => 'permit_empty|max_length[7]',
    ];

    protected $validationMessages   = [
        'title' => [
            'required' => 'Judul promo harus diisi',
        ],
        'badge_text' => [
            'required' => 'Badge text harus diisi',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active promos yang sedang berlaku
     */
    public function getActivePromos()
    {
        return $this->where('is_active', 1)
            ->where('(start_date IS NULL OR start_date <= NOW())')
            ->where('(end_date IS NULL OR end_date >= NOW())')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Get all promos for admin (including inactive)
     */
    public function getAllPromos()
    {
        return $this->orderBy('display_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $promo = $this->find($id);
        if ($promo) {
            return $this->update($id, [
                'is_active' => $promo['is_active'] == 1 ? 0 : 1
            ]);
        }
        return false;
    }
}
