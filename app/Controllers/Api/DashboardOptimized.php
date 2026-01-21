<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;

class DashboardOptimized extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Optimized customer statistics endpoint with better performance
     */
    public function customerStats()
    {
        try {
            // Get customer statistics using optimized queries
            $stats = $this->getOptimizedCustomerStatistics();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting optimized customer statistics: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get customer statistics: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    private function getOptimizedCustomerStatistics()
    {
        try {
            // Get current month for filtering
            $currentMonth = date('Y-m');
            $currentMonthStart = date('Y-m-01');
            $currentMonthEnd = date('Y-m-t');

            $stats = [];

            // 1. Get total customers count (simple query)
            $customerBuilder = $this->db->table('customers');
            $stats['total_customers'] = $customerBuilder->countAllResults();

            // 2. Get all invoice statistics in a single query with aggregation
            $invoiceBuilder = $this->db->table('invoices');
            $invoiceStats = $invoiceBuilder
                ->select([
                    'COUNT(CASE WHEN status = "paid" AND payment_date >= "' . $currentMonthStart . '" AND payment_date <= "' . $currentMonthEnd . ' 23:59:59" THEN 1 END) as paid_invoices',
                    'COUNT(CASE WHEN status = "unpaid" AND (periode LIKE "' . $currentMonth . '%" OR (created_at >= "' . $currentMonthStart . '" AND created_at <= "' . $currentMonthEnd . ' 23:59:59")) THEN 1 END) as unpaid_invoices',
                    'COUNT(CASE WHEN status = "overdue" AND (periode LIKE "' . $currentMonth . '%" OR (created_at >= "' . $currentMonthStart . '" AND created_at <= "' . $currentMonthEnd . ' 23:59:59")) THEN 1 END) as overdue_invoices',
                    'COUNT(CASE WHEN status = "paid" AND payment_date = "' . date('Y-m-d') . '" THEN 1 END) as today_payments',
                    'SUM(CASE WHEN status = "paid" AND payment_date >= "' . date('Y-m-d') . '" AND payment_date < "' . date('Y-m-d', strtotime('+1 day')) . '" THEN paid_amount ELSE 0 END) as today_revenue'
                ])
                ->get()
                ->getRow();

            $stats['paid_invoices'] = (int)$invoiceStats->paid_invoices;
            $stats['unpaid_invoices'] = (int)$invoiceStats->unpaid_invoices;
            $stats['overdue_invoices'] = (int)$invoiceStats->overdue_invoices;
            $stats['today_payments'] = (int)$invoiceStats->today_payments;
            $stats['today_revenue'] = (float)($invoiceStats->today_revenue ?? 0);

            // 3. Get customer status statistics in a single query
            $customerStatsBuilder = $this->db->table('customers');
            $customerStats = $customerStatsBuilder
                ->select([
                    'COUNT(CASE WHEN isolir_status = 1 THEN 1 END) as isolated_customers',
                    'COUNT(CASE WHEN (tgl_pasang IS NULL OR tgl_pasang = "") THEN 1 END) as not_installed_customers',
                    'COUNT(CASE WHEN status_tagihan = "Belum Lunas" AND tgl_tempo <= "' . date('Y-m-d') . '" AND isolir_status = 0 THEN 1 END) as overdue_customers'
                ])
                ->get()
                ->getRow();

            $actuallyIsolated = (int)$customerStats->isolated_customers;
            $overdueCustomers = (int)$customerStats->overdue_customers;
            $stats['suspended_customers'] = $actuallyIsolated + $overdueCustomers;
            $stats['not_installed_customers'] = (int)$customerStats->not_installed_customers;

            // 4. Get current month specific counts
            $currentMonthBuilder = $this->db->table('invoices');
            $currentMonthStats = $currentMonthBuilder
                ->select([
                    'COUNT(CASE WHEN status = "unpaid" AND periode LIKE "' . $currentMonth . '%" THEN 1 END) as current_month_unpaid',
                    'COUNT(CASE WHEN status = "unpaid" AND periode LIKE "' . $currentMonth . '%" AND due_date < "' . date('Y-m-d') . '" THEN 1 END) as current_month_overdue'
                ])
                ->get()
                ->getRow();

            $stats['current_month_unpaid'] = (int)$currentMonthStats->current_month_unpaid;
            $stats['current_month_overdue'] = (int)$currentMonthStats->current_month_overdue;

            // Add metadata
            $stats['current_month'] = $currentMonth;
            $stats['query_time'] = date('Y-m-d H:i:s');

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'Error in optimized customer statistics: ' . $e->getMessage());

            // Return default values
            return [
                'total_customers' => 0,
                'paid_invoices' => 0,
                'unpaid_invoices' => 0,
                'overdue_invoices' => 0,
                'suspended_customers' => 0,
                'not_installed_customers' => 0,
                'today_payments' => 0,
                'today_revenue' => 0,
                'current_month' => date('Y-m'),
                'current_month_unpaid' => 0,
                'current_month_overdue' => 0,
                'query_time' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ];
        }
    }
}
