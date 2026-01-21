<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TestApiController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function testFinancialChart()
    {
        try {
            // Test revenue calculation
            $revenueQuery = "
                SELECT 
                    MONTH(p.tanggal_bayar) as month,
                    YEAR(p.tanggal_bayar) as year,
                    SUM(p.jumlah_bayar) as total_revenue
                FROM payment_transactions p
                JOIN customers c ON p.customer_id = c.id_customer
                WHERE p.status_bayar = 'Paid'
                    AND p.tanggal_bayar >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY YEAR(p.tanggal_bayar), MONTH(p.tanggal_bayar)
                ORDER BY year ASC, month ASC
            ";

            $revenueResult = $this->db->query($revenueQuery)->getResultArray();

            // Test expense calculation
            $expenseQuery = "
                SELECT 
                    MONTH(tanggal) as month,
                    YEAR(tanggal) as year,
                    SUM(ABS(jumlah)) as total_expense
                FROM cash_flow
                WHERE jenis = 'keluar'
                    AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY YEAR(tanggal), MONTH(tanggal)
                ORDER BY year ASC, month ASC
            ";

            $expenseResult = $this->db->query($expenseQuery)->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'revenue_data' => $revenueResult,
                'expense_data' => $expenseResult,
                'revenue_count' => count($revenueResult),
                'expense_count' => count($expenseResult)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }

    public function testTables()
    {
        try {
            // Check if tables exist and their structure
            $tables = [
                'payment_transactions',
                'customers',
                'cash_flow',
                'invoices'
            ];

            $results = [];
            foreach ($tables as $table) {
                try {
                    $count = $this->db->query("SELECT COUNT(*) as count FROM {$table}")->getRow()->count;
                    $columns = $this->db->query("DESCRIBE {$table}")->getResultArray();
                    $results[$table] = [
                        'exists' => true,
                        'count' => $count,
                        'columns' => array_column($columns, 'Field')
                    ];
                } catch (\Exception $e) {
                    $results[$table] = [
                        'exists' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'tables' => $results
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function index()
    {
        //
    }
}
