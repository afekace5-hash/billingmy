<?php

namespace App\Models;

use CodeIgniter\Model;

class CashFlowModel extends Model
{
    protected $table = 'cash_flow';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'amount',
        'transaction_date',
        'category_id',
        'type',
        'description',
        'deleted_at',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = false;

    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    /**
     * Get only income records
     */
    public function income()
    {
        $this->builder()->where('type', 'income');
        return $this;
    }

    /**
     * Get only expenditure records
     */
    public function expenditure()
    {
        $this->builder()->where('type', 'expenditure');
        return $this;
    }

    /**
     * Get summary data for widgets
     */
    public function getWidgetData($month, $year)
    {
        $builder = $this->db->table($this->table);
        $builder->select('
            SUM(CASE WHEN type = "revenue" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "expenditure" THEN amount ELSE 0 END) as total_expenditure
        ');

        if ($month) {
            $builder->where('MONTH(transaction_date)', $month);
        }
        if ($year) {
            $builder->where('YEAR(transaction_date)', $year);
        }

        $result = $builder->get()->getRowArray();

        // Calculate balance
        $result['balance'] = $result['total_income'] - $result['total_expenditure'];

        return $result;
    }
}
