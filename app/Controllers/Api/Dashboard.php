<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Libraries\MikrotikAPI; // Correct import for MikrotikAPI.php in app/Libraries
// use App\Libraries\MikrotikAPI; // Not needed, use fully qualified name

class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    public function statistics()
    {
        try {
            // Initialize models
            $customerModel = new CustomerModel();
            $invoiceModel = new InvoiceModel();

            // Get dashboard statistics from database
            $stats = $this->getDashboardStatistics($customerModel, $invoiceModel);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get dashboard statistics: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    // New endpoint for customer statistics only (without payment methods)
    public function customerStats()
    {
        try {
            // Initialize models
            $customerModel = new CustomerModel();
            $invoiceModel = new InvoiceModel();

            // Get customer statistics only
            $stats = $this->getCustomerStatisticsOnly($customerModel, $invoiceModel);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get customer statistics: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    // New endpoint for payment methods only
    public function paymentStats()
    {
        try {
            // Initialize models
            $invoiceModel = new InvoiceModel();

            // Get current month for filtering
            $currentMonth = date('Y-m');

            // Debug: Cek payment methods yang ada
            $db = \Config\Database::connect();
            $paymentMethodsQuery = $db->query("
                SELECT 
                    payment_method, 
                    COUNT(*) as count,
                    SUM(paid_amount) as total_amount
                FROM customer_invoices 
                WHERE status = 'paid' 
                AND payment_method IS NOT NULL 
                AND payment_method != ''
                AND (
                    DATE_FORMAT(payment_date, '%Y-%m') = '{$currentMonth}'
                    OR periode LIKE '{$currentMonth}%'
                )
                GROUP BY payment_method
            ");
            $paymentMethodsDebug = $paymentMethodsQuery->getResultArray();

            log_message('info', 'Payment methods for ' . $currentMonth . ': ' . json_encode($paymentMethodsDebug));

            // Get payment method statistics
            $paymentMethods = $this->getPaymentMethodStats($invoiceModel, $currentMonth);

            // Calculate total for percentage
            $totalCount = 0;
            foreach ($paymentMethodsDebug as $method) {
                $totalCount += $method['count'];
            }

            // Build chart data for frontend
            $chartData = [];
            $colors = [
                'MANUAL' => '#ff6384',
                'TUNAI' => '#ff6384',
                'TRANSFER' => '#36a2eb',
                'BNI_VA' => '#ffcd56',
                'VIRTUAL_ACCOUNT' => '#ffcd56',
                'QRIS' => '#4bc0c0',
                'FLIP' => '#9966ff',
                'MIDTRANS' => '#ff9f40',
                'XENDIT' => '#c9cbcf',
                'SHOPEE_PAY' => '#ff6384',
                'OVO' => '#4bc0c0',
                'GOPAY' => '#36a2eb',
                'DANA' => '#ffcd56'
            ];

            $colorIndex = 0;
            $defaultColors = ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff9f40', '#c9cbcf'];

            foreach ($paymentMethodsDebug as $method) {
                $methodName = strtoupper($method['payment_method']);
                $count = (int)$method['count'];
                $percentage = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;

                $chartData[] = [
                    'label' => $methodName,
                    'value' => $count,
                    'percentage' => $percentage,
                    'color' => $colors[$methodName] ?? $defaultColors[$colorIndex % count($defaultColors)]
                ];
                $colorIndex++;
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'payment_methods' => $paymentMethods,
                    'chart_data' => $chartData,
                    'total_transactions' => $totalCount,
                    'current_month' => $currentMonth,
                    'query_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Payment stats error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get payment statistics: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function systemInfo()
    {
        try {
            // Check if lokasi_server table exists
            $db = \Config\Database::connect();
            if (!$db->tableExists('lokasi_server')) {
                log_message('warning', 'lokasi_server table does not exist. Returning dummy data.');

                // Return dummy data with helpful message
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => [
                        'board_name' => 'Not Configured',
                        'version' => 'N/A',
                        'cpu_usage' => '0%',
                        'memory_usage' => '0%',
                        'memory_used' => '0 MB',
                        'memory_total' => '0 MB',
                        'disk_usage' => '0%',
                        'uptime' => 'N/A',
                        'router_name' => 'MikroTik Not Configured',
                        'router_location' => 'Please run SQL_CREATE_LOKASI_SERVER_TABLE.sql',
                        'is_fallback' => true,
                        'source' => 'table_not_exists',
                        'setup_required' => true,
                        'setup_instructions' => 'Import SQL_CREATE_LOKASI_SERVER_TABLE.sql via phpMyAdmin'
                    ],
                    'message' => 'MikroTik configuration table not found. Please import SQL_CREATE_LOKASI_SERVER_TABLE.sql'
                ]);
            }

            // Forward to realSystemInfo to ensure we only use real data
            return $this->realSystemInfo();
        } catch (\Exception $e) {
            log_message('error', 'System info API error: ' . $e->getMessage());

            // Return graceful fallback instead of error
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'board_name' => 'Error',
                    'version' => 'N/A',
                    'cpu_usage' => '0%',
                    'memory_usage' => '0%',
                    'memory_used' => '0 MB',
                    'memory_total' => '0 MB',
                    'disk_usage' => '0%',
                    'uptime' => 'N/A',
                    'router_name' => 'Connection Error',
                    'router_location' => 'Check error logs',
                    'is_fallback' => true,
                    'source' => 'error_fallback',
                    'error_message' => $e->getMessage()
                ],
                'message' => 'Failed to get system information. Using fallback data.'
            ]);
        }
    }

    /**
     * Kebab-case endpoint for system info (real-system-info)
     * This is a wrapper for the realSystemInfo method to support kebab-case URLs
     */
    public function realSystemInfoKebab()
    {
        return $this->realSystemInfo();
    }

    private function getDashboardStatistics($customerModel, $invoiceModel)
    {
        try {
            // Total customers (reset query builder untuk pastikan count fresh)
            $totalCustomers = $customerModel->countAll();
            log_message('info', 'Total Customers: ' . $totalCustomers);

            // Get current month for filtering invoices
            $currentMonth = date('Y-m');

            // Gunakan format tanggal lengkap agar cocok dengan DATETIME
            $startOfMonth = date('Y-m-01 00:00:00');
            $endOfMonth = date('Y-m-t 23:59:59');


            // Count invoices paid in the current month (by payment_date)
            // Gunakan fungsi DATE() agar robust untuk tipe DATETIME
            // Pembayaran bulan ini (pastikan filter tanggal sesuai format di database)
            $paidInvoices = (clone $invoiceModel)->where('status', 'paid')
                ->where('payment_date >=', $startOfMonth)
                ->where('payment_date <=', $endOfMonth)
                ->countAllResults();
            log_message('info', 'Paid Invoices This Month: ' . $paidInvoices);

            // Count unpaid invoices for current month only (try multiple date field options)
            // Invoice belum bayar bulan ini
            $unpaidCurrentMonth = (clone $invoiceModel)->where('status', 'unpaid')
                ->where('periode', $currentMonth)
                ->countAllResults();
            log_message('info', 'Unpaid Invoices Current Month: ' . $unpaidCurrentMonth);

            // Jika bulan ini tidak ada invoice, tampilkan total keseluruhan
            $unpaidInvoices = $unpaidCurrentMonth > 0 ? $unpaidCurrentMonth : (clone $invoiceModel)->where('status', 'unpaid')->countAllResults();
            log_message('info', 'Unpaid Invoices Display: ' . $unpaidInvoices);

            // Count overdue invoices for current month only
            // Invoice overdue bulan ini
            $overdueInvoices = (clone $invoiceModel)->where('status', 'unpaid')
                ->where('due_date <', date('Y-m-d'))
                ->countAllResults();
            log_message('info', 'Overdue Invoices: ' . $overdueInvoices);

            // Alternative: Count current month unpaid for comparison
            $currentMonthUnpaid = (clone $invoiceModel)
                ->where('status', 'unpaid')
                ->where('periode', $currentMonth)
                ->countAllResults();

            $currentMonthOverdue = (clone $invoiceModel)
                ->where('status', 'overdue')
                ->where('periode', $currentMonth)
                ->countAllResults();

            // Count suspended customers (assuming status_tagihan field indicates suspension)
            // Suspended customers (isolir_status = 1)
            $suspendedCustomers = (clone $customerModel)->where('isolir_status', 1)->countAllResults();
            log_message('info', 'Suspended Customers: ' . $suspendedCustomers);

            // Count customers who haven't been installed yet (no installation date)
            // Belum terpasang (tgl_pasang NULL atau kosong)
            $notInstalledCustomers = (clone $customerModel)->groupStart()
                ->where('tgl_pasang IS NULL')
                ->orWhere('tgl_pasang', '')
                ->groupEnd()
                ->countAllResults();
            log_message('info', 'Not Installed Customers: ' . $notInstalledCustomers);

            // Get today's payments
            $todayPayments = (clone $invoiceModel)
                ->where('payment_date >=', date('Y-m-d'))
                ->where('payment_date <', date('Y-m-d', strtotime('+1 day')))
                ->where('status', 'paid')
                ->countAllResults();            // Calculate today's revenue
            $todayRevenue = (clone $invoiceModel)
                ->selectSum('paid_amount')
                ->where('payment_date >=', date('Y-m-d'))
                ->where('payment_date <', date('Y-m-d', strtotime('+1 day')))
                ->where('status', 'paid')
                ->get()
                ->getRow()
                ->paid_amount ?? 0;

            // Get payment method statistics for current month
            $paymentMethods = $this->getPaymentMethodStats($invoiceModel, $currentMonth);

            // Get detailed breakdown
            $details = [
                'total_customers' => $totalCustomers,
                'paid_invoices' => $paidInvoices,
                'unpaid_invoices' => $unpaidInvoices, // Total semua invoice belum bayar
                'overdue_invoices' => $overdueInvoices, // Total semua invoice overdue
                'suspended_customers' => $suspendedCustomers,
                'not_installed_customers' => $notInstalledCustomers,
                'today_payments' => $todayPayments,
                'today_revenue' => $todayRevenue,
                'current_month' => $currentMonth,
                'current_month_unpaid' => $currentMonthUnpaid, // Unpaid bulan ini saja
                'current_month_overdue' => $currentMonthOverdue, // Overdue bulan ini saja
                'payment_methods' => $paymentMethods, // Payment method breakdown
                'query_time' => date('Y-m-d H:i:s')
            ];
            return $details;
        } catch (\Exception $e) {
            log_message('error', 'Error getting dashboard statistics: ' . $e->getMessage());

            // Return default values if database query fails
            return [
                'total_customers' => 0,
                'paid_invoices' => 0,
                'unpaid_invoices' => 0,
                'overdue_invoices' => 0,
                'suspended_customers' => 0,
                'not_installed_customers' => 0,
                'today_payments' => 0,
                'today_revenue' => 0,
                'payment_methods' => [
                    'total_transactions' => 0,
                    'methods' => [
                        'TUNAI' => ['count' => 0, 'percentage' => 0],
                        'TRANSFER' => ['count' => 0, 'percentage' => 0],
                        'QRIS' => ['count' => 0, 'percentage' => 0],
                        'VIRTUAL_ACCOUNT' => ['count' => 0, 'percentage' => 0]
                    ]
                ],
                'error' => $e->getMessage()
            ];
        }
    }

    // Get customer statistics only (without payment methods)
    private function getCustomerStatisticsOnly($customerModel, $invoiceModel)
    {
        try {
            // Total customers - gunakan countAll() agar tidak terpengaruh query sebelumnya
            $totalCustomers = $customerModel->countAll();

            // Get current month for filtering invoices
            $currentMonth = date('Y-m');

            // Count invoices paid in current month (by payment_date)
            // Menggunakan payment_date agar invoice yang dibayar bulan ini terhitung
            $currentMonthStart = date('Y-m-01');
            $currentMonthEnd = date('Y-m-t');
            $paidInvoices = $invoiceModel->where('status', 'paid')
                ->where('payment_date >=', $currentMonthStart)
                ->where('payment_date <=', $currentMonthEnd . ' 23:59:59')
                ->countAllResults();

            // Count unpaid invoices for current month only (try multiple date field options)
            $unpaidInvoices = $invoiceModel->where('status', 'unpaid')
                ->groupStart()
                ->where('periode', $currentMonth)
                ->orWhere('created_at >=', $currentMonthStart)
                ->where('created_at <=', $currentMonthEnd . ' 23:59:59')
                ->groupEnd()
                ->countAllResults();

            // Count overdue invoices for current month only
            $overdueInvoices = $invoiceModel->where('status', 'overdue')
                ->groupStart()
                ->where('periode', $currentMonth)
                ->orWhere('created_at >=', $currentMonthStart)
                ->where('created_at <=', $currentMonthEnd . ' 23:59:59')
                ->groupEnd()
                ->countAllResults();

            // Suspended customers - include both actually isolated and overdue customers
            // Customer yang sudah diisolir secara teknis (isolir_status = 1)
            $actuallyIsolated = $customerModel->where('isolir_status', 1)->countAllResults();

            // Customer yang overdue dan belum bayar (seharusnya suspended)
            $overdueCustomers = $customerModel->where('status_tagihan', 'Belum Lunas')
                ->where('tgl_tempo <=', date('Y-m-d'))
                ->where('isolir_status', 0)
                ->countAllResults();

            // Total suspended (isolated + overdue)
            $suspendedCustomers = $actuallyIsolated + $overdueCustomers;

            // For current month only (filtering untuk bulan ini)
            $currentMonthUnpaid = $invoiceModel->where('status', 'unpaid')
                ->like('periode', $currentMonth)
                ->countAllResults();

            $currentMonthOverdue = $invoiceModel->where('status', 'unpaid')
                ->like('periode', $currentMonth)
                ->where('due_date <', date('Y-m-d'), false)
                ->countAllResults();

            // Today's payments count
            $todayPayments = $invoiceModel->where('payment_date', date('Y-m-d'))
                ->where('status', 'paid')
                ->countAllResults();

            // Today's revenue (sum of payment amounts for today)
            $todayRevenue = $invoiceModel->selectSum('paid_amount')
                ->where('payment_date >=', date('Y-m-d'))
                ->where('payment_date <', date('Y-m-d', strtotime('+1 day')))
                ->where('status', 'paid')
                ->get()
                ->getRow()
                ->paid_amount ?? 0;

            // Count customers who haven't been installed yet (no installation date)
            $notInstalledCustomers = $customerModel->where('tgl_pasang IS NULL')
                ->orWhere('tgl_pasang', '')
                ->countAllResults();

            return [
                'total_customers' => $totalCustomers,
                'paid_invoices' => $paidInvoices,
                'unpaid_invoices' => $unpaidInvoices,
                'overdue_invoices' => $overdueInvoices,
                'suspended_customers' => $suspendedCustomers,
                'not_installed_customers' => $notInstalledCustomers,
                'today_payments' => $todayPayments,
                'today_revenue' => $todayRevenue,
                'current_month' => $currentMonth,
                'current_month_unpaid' => $currentMonthUnpaid,
                'current_month_overdue' => $currentMonthOverdue,
                'query_time' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting customer statistics: ' . $e->getMessage());

            // Return default values if database query fails
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
                'query_time' => date('Y-m-d H:i:s')
            ];
        }
    }

    // Financial Chart endpoint
    public function financialChart()
    {
        try {
            // Initialize models
            $invoiceModel = new InvoiceModel();

            // Get financial chart data
            $chartData = $this->getFinancialChartData($invoiceModel);

            // Log the data for debugging
            log_message('info', 'Financial Chart Data: ' . json_encode($chartData));

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $chartData['data'],
                'period' => $chartData['period'],
                'debug' => [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data_source' => 'real_database',
                    'total_revenue' => array_sum($chartData['data']['revenue']),
                    'total_expense' => array_sum($chartData['data']['expense'])
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Financial Chart Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get financial chart data: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    private function getFinancialChartData($invoiceModel)
    {
        try {
            // Get last 3 months of data (sesuai menu mutasi keuangan)
            $months = [];
            $revenueData = [];
            $expenseData = [];
            $emptyData = true;
            // Ambil data 3 bulan sekaligus untuk cash_flow
            $cashFlowIncome = [];
            $cashFlowExpense = [];
            if ($this->db->tableExists('cash_flow')) {
                $query = $this->db->query("SELECT DATE_FORMAT(transaction_date, '%b %Y') as month, SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type='expenditure' THEN amount ELSE 0 END) as expense FROM cash_flow WHERE transaction_date >= ? AND transaction_date <= ? GROUP BY month ORDER BY month ASC", [date('Y-m-01', strtotime('-2 months')), date('Y-m-t')]);
                foreach ($query->getResultArray() as $row) {
                    $cashFlowIncome[$row['month']] = (float)$row['income'];
                    $cashFlowExpense[$row['month']] = (float)$row['expense'];
                }
            }

            // DEBUG: Log invoicePayments dan onlinePayments
            $debugInvoicePayments = [];
            $debugOnlinePayments = [];

            // Ambil data 3 bulan sekaligus untuk invoices
            $invoicePayments = [];
            if ($this->db->tableExists('customer_invoices')) {
                log_message('error', '[CHART] DEBUG: Blok query customer_invoices dijalankan!');
                $sql = "SELECT DATE_FORMAT(payment_date, '%b %Y') as month, SUM(paid_amount) as paid FROM customer_invoices WHERE status='paid' AND payment_date >= ? AND payment_date <= ? GROUP BY month ORDER BY month ASC";
                $param1 = date('Y-m-01', strtotime('-2 months'));
                $param2 = date('Y-m-t');
                $query = $this->db->query($sql, [$param1, $param2]);
                foreach ($query->getResultArray() as $row) {
                    $invoicePayments[$row['month']] = (float)$row['paid'];
                    $debugInvoicePayments[] = $row;
                }
                if (empty($debugInvoicePayments)) {
                    // Coba query manual tanpa GROUP BY
                    $manual = $this->db->query("SELECT invoice_no, payment_date, paid_amount, status FROM customer_invoices WHERE status='paid' AND payment_date >= ? AND payment_date <= ? ORDER BY payment_date", [$param1, $param2]);
                    $manualRows = $manual->getResultArray();
                    log_message('error', '[CHART] invoicePayments EMPTY! Query invoices tidak mengembalikan data. SQL: ' . $sql . ' param1=' . $param1 . ' param2=' . $param2 . ' | Manual: ' . json_encode($manualRows));
                } else {
                    log_message('info', '[CHART] invoicePayments: ' . json_encode($debugInvoicePayments));
                }
            }

            // Ambil data 3 bulan sekaligus untuk payment_transactions
            $onlinePayments = [];
            $onlineAdminFees = [];
            if ($this->db->tableExists('payment_transactions')) {
                $query = $this->db->query("SELECT DATE_FORMAT(paid_at, '%b %Y') as month, SUM(amount) as total_amount, SUM(admin_fee) as total_admin FROM payment_transactions WHERE status='sukses' AND paid_at >= ? AND paid_at <= ? GROUP BY month ORDER BY month ASC", [date('Y-m-01', strtotime('-2 months')), date('Y-m-t')]);
                foreach ($query->getResultArray() as $row) {
                    $onlinePayments[$row['month']] = (float)$row['total_amount'];
                    $onlineAdminFees[$row['month']] = (float)$row['total_admin'];
                    $debugOnlinePayments[] = $row;
                }
                log_message('debug', '[CHART] onlinePayments: ' . json_encode($debugOnlinePayments));
            }

            for ($i = 2; $i >= 0; $i--) {
                $monthName = date('M Y', strtotime("-$i months"));
                $months[] = $monthName;
                $monthlyRevenue = 0;
                $monthlyExpense = 0;
                // Gabungkan semua sumber
                $monthlyRevenue += $cashFlowIncome[$monthName] ?? 0;
                $monthlyExpense += $cashFlowExpense[$monthName] ?? 0;
                $monthlyRevenue += $invoicePayments[$monthName] ?? 0;
                $monthlyRevenue += ($onlinePayments[$monthName] ?? 0) - ($onlineAdminFees[$monthName] ?? 0);
                $monthlyExpense += $onlineAdminFees[$monthName] ?? 0;
                if ($monthlyRevenue > 0 || $monthlyExpense > 0) {
                    $emptyData = false;
                }
                $revenueData[] = $monthlyRevenue;
                $expenseData[] = $monthlyExpense;
            }
            $emptyDataMessage = $emptyData ? 'Data pendapatan dan pengeluaran tidak tersedia untuk 3 bulan terakhir.' : '';
            return [
                'data' => [
                    'months' => $months,
                    'revenue' => $revenueData,
                    'expense' => $expenseData
                ],
                'period' => date('d M Y', strtotime('-2 months')) . ' - ' . date('d M Y')
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting financial chart data: ' . $e->getMessage());                // Return empty data for last 3 months instead of fallback data
            $months = [];
            for ($i = 2; $i >= 0; $i--) {
                $months[] = date('M Y', strtotime("-$i months"));
            }

            return [
                'data' => [
                    'months' => $months,
                    'revenue' => [0, 0, 0], // Empty revenue data
                    'expense' => [0, 0, 0]  // Empty expense data
                ],
                'period' => date('d M Y', strtotime('-2 months')) . ' - ' . date('d M Y')
            ];
        }
    }

    private function getPaymentMethodStats($invoiceModel, $currentMonth)
    {
        try {
            log_message('info', 'Getting payment method stats for month: ' . $currentMonth);

            // Get all payment methods dynamically from database
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(paid_amount) as total_amount
                FROM customer_invoices 
                WHERE status = 'paid' 
                AND payment_method IS NOT NULL 
                AND payment_method != ''
                AND (
                    DATE_FORMAT(payment_date, '%Y-%m') = ?
                    OR periode LIKE ?
                )
                GROUP BY payment_method
                ORDER BY count DESC
            ", [$currentMonth, $currentMonth . '%']);

            $results = $query->getResultArray();
            log_message('info', 'Payment methods found: ' . json_encode($results));

            // Calculate total
            $totalTransactions = 0;
            foreach ($results as $row) {
                $totalTransactions += $row['count'];
            }

            // Build methods array with percentages
            $methods = [];
            foreach ($results as $row) {
                $methodName = strtoupper($row['payment_method']);
                $count = (int)$row['count'];
                $percentage = $totalTransactions > 0 ? round(($count / $totalTransactions) * 100, 1) : 0;

                $methods[$methodName] = [
                    'count' => $count,
                    'percentage' => $percentage,
                    'amount' => (float)$row['total_amount']
                ];
            }

            $result = [
                'total_transactions' => $totalTransactions,
                'methods' => $methods
            ];

            log_message('info', 'Payment method stats result: ' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error getting payment method stats: ' . $e->getMessage());

            return [
                'total_transactions' => 0,
                'methods' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    private function getMikrotikSystemInfo()
    {
        try {
            // Get the primary location server
            $lokasiServerModel = new \App\Models\ServerLocationModel();
            $primaryServer = $lokasiServerModel->where('is_connected', '1')->first();

            if (!$primaryServer) {
                log_message('warning', 'No connected server found in database - using fallback data');
                // Return fallback data if no server is configured
                $fallbackData = $this->getFallbackSystemInfo();
                $fallbackData['is_fallback'] = true;
                return $fallbackData;
            }

            // Continue with the existing code...
            log_message('info', 'Primary server found for realSystemInfo: ' . $primaryServer['name'] . ' (' . $primaryServer['ip_router'] . ')');

            // Parse host and port from ip_router correctly
            $host = $primaryServer['ip_router'];
            $apiPort = $primaryServer['port_api'] ?? 8728; // Default API port

            // If ip_router contains port (format: host:port), extract them
            if (strpos($host, ':') !== false) {
                $hostParts = explode(':', $host);
                $host = $hostParts[0]; // Hostname only

                // If port_api is empty or default (8728), use the port from ip_router
                if (empty($primaryServer['port_api']) || $primaryServer['port_api'] == 8728) {
                    $apiPort = isset($hostParts[1]) ? (int)$hostParts[1] : $apiPort;
                }
                // Otherwise, always use port_api from database (it's the actual API port)
            }

            $config = [
                'host' => $host,
                'user' => $primaryServer['username'],
                'pass' => $primaryServer['password'],
                'port' => (int)$apiPort
            ];

            // Log config for debugging (hide password)
            log_message('debug', 'MikroTik API Config: host=' . $host . ', port=' . $apiPort . ', user=' . $primaryServer['username']);

            // Initialize MikroTik connection
            $mikrotikAPI = new MikrotikAPI($config);

            // Test connection first
            $connectionTest = $mikrotikAPI->testConnection();
            if (!$connectionTest['success']) {
                log_message('debug', 'Failed to connect to MikroTik for system info: ' . $connectionTest['message']);
                $fallbackData = $this->getFallbackSystemInfo();
                $fallbackData['is_fallback'] = true;
                return $fallbackData;
            }

            log_message('info', 'MikroTik connection successful, retrieving system resource...');

            // Get system resource information
            $resource = $mikrotikAPI->getSystemResource();

            if ($resource && is_array($resource)) {
                $systemInfo = [];

                // Board name
                $systemInfo['board_name'] = $resource['board-name'] ?? 'Unknown';

                // Version - ensure we get the version data
                $systemInfo['version'] = $resource['version'] ?? 'Unknown Version';

                // Log the version to debug
                log_message('debug', 'MikroTik version detected: ' . $systemInfo['version']);

                // Memory usage
                $totalMemory = isset($resource['total-memory']) ? $resource['total-memory'] : 0;
                $freeMemory = isset($resource['free-memory']) ? $resource['free-memory'] : 0;
                $usedMemory = $totalMemory - $freeMemory;

                $systemInfo['memory_used'] = $this->formatBytes($usedMemory);
                $systemInfo['memory_total'] = $this->formatBytes($totalMemory);
                $systemInfo['memory_usage'] = $totalMemory > 0 ? round(($usedMemory / $totalMemory) * 100, 2) : 0;

                // CPU load - MikroTik returns as percentage, extract number only
                $cpuLoad = isset($resource['cpu-load']) ? $resource['cpu-load'] : '0%';
                $cpuLoadValue = intval(str_replace('%', '', $cpuLoad));
                $systemInfo['cpu_load'] = is_numeric($cpuLoadValue) ? $cpuLoadValue : 0;

                // Disk usage (HDD space)
                $totalDisk = isset($resource['total-hdd-space']) ? $resource['total-hdd-space'] : 0;
                $freeDisk = isset($resource['free-hdd-space']) ? $resource['free-hdd-space'] : 0;
                $usedDisk = $totalDisk - $freeDisk;

                $systemInfo['disk_used'] = $this->formatBytes($usedDisk);
                $systemInfo['disk_total'] = $this->formatBytes($totalDisk);
                $systemInfo['disk_usage'] = $totalDisk > 0 ? round(($usedDisk / $totalDisk) * 100, 2) : 0;

                // Uptime
                $systemInfo['uptime'] = isset($resource['uptime']) ? $this->formatUptime($resource['uptime']) : 'Unknown';

                // Format for API response (JavaScript expects different keys)
                $apiResponse = [
                    'board_name' => $systemInfo['board_name'],
                    'version' => $systemInfo['version'],
                    'cpu_usage' => $systemInfo['cpu_load'] . '%',
                    'memory_usage' => $systemInfo['memory_usage'] . '%',
                    'memory_used' => $systemInfo['memory_used'],
                    'memory_total' => $systemInfo['memory_total'],
                    'disk_usage' => $systemInfo['disk_usage'] . '%',
                    'disk_used' => $systemInfo['disk_used'],
                    'disk_total' => $systemInfo['disk_total'],
                    'uptime' => $systemInfo['uptime'],
                    'is_fallback' => false
                ];

                log_message('info', 'Successfully retrieved real MikroTik system info with version: ' . $systemInfo['version']);
                return $apiResponse;
            } else {
                log_message('warning', 'Empty or invalid system resource data from MikroTik - using fallback data');
                $fallbackData = $this->getFallbackSystemInfo();
                $fallbackData['is_fallback'] = true;
                return $fallbackData;
            }
        } catch (\Exception $e) {
            log_message('debug', 'Error getting MikroTik system info: ' . $e->getMessage());
            $fallbackData = $this->getFallbackSystemInfo();
            $fallbackData['is_fallback'] = true;
            return $fallbackData;
        }
    }

    private function getFallbackSystemInfo()
    {
        log_message('info', 'Connection to MikroTik router failed - providing fallback system info based on typical values');

        // Using similar values to what a real MikroTik would return, but marking as fallback
        return [
            'board_name' => 'RB750Gr3',
            'version' => '6.49.7 (stable)',
            'cpu_usage' => '15%',
            'memory_usage' => '42%',
            'memory_used' => '48.64 MB',
            'memory_total' => '128.00 MB',
            'disk_usage' => '35%',
            'disk_used' => '35.2 MB',
            'disk_total' => '100 MB',
            'uptime' => '3 Hari, 6 Jam, 42 Menit, 18 Detik',
            'is_fallback' => true
        ];
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if (!is_numeric($bytes) || $bytes < 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Format uptime to human readable format
     */
    private function formatUptime($uptime)
    {
        // MikroTik uptime format examples: "1w2d3h4m5s", "5d10h30m", "2h45m30s"
        if (empty($uptime)) {
            return 'Unknown';
        }

        // Parse MikroTik uptime format
        $formatted = [];

        if (preg_match('/(\d+)w/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' week' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' day' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' hour' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' minute' . ($matches[1] > 1 ? 's' : '');
        }
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $formatted[] = $matches[1] . ' second' . ($matches[1] > 1 ? 's' : '');
        }

        return !empty($formatted) ? implode(', ', $formatted) : $uptime;
    }

    /**
     * Get real system information from MikroTik without fallback data
     * Uses existing data from lokasi_server table without modifying it
     */
    public function realSystemInfo($routerId = null)
    {
        try {
            // Get the router by ID
            $lokasiServerModel = new \App\Models\ServerLocationModel();

            if ($routerId === null) {
                // If no ID provided, get the primary connected router
                $router = $lokasiServerModel->where('is_connected', '1')->first();

                if (!$router) {
                    // Try to get any router
                    $router = $lokasiServerModel->first();

                    if (!$router) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'No MikroTik routers configured in the system.',
                            'data' => null
                        ])->setStatusCode(404);
                    }
                }
            } else {
                // Get specific router by ID
                $router = $lokasiServerModel->find($routerId);

                if (!$router) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Router with ID ' . $routerId . ' not found.',
                        'data' => null
                    ])->setStatusCode(404);
                }
            }

            // USE SAME APPROACH AS ROUTER LIST - MikrotikNew class
            // Use ip_router as-is for host (can be hostname:port or just hostname)
            $host = $router['ip_router']; // Use full ip_router (e.g., "us-1.hostddns.us:31014")
            $username = $router['username'];
            $password = $router['password'];
            $connectionPort = $router['port_api'] ?? 8728;

            // Log connection attempt (hide password for security)
            log_message('info', 'Connecting to MikroTik: host=' . $host . ', port=' . $connectionPort . ', user=' . $username);

            // Initialize MikroTik connection using MikrotikNew (same as router list)
            $mt = new \App\Libraries\MikrotikNew();
            $connected = $mt->connect($host, $username, $password, intval($connectionPort));

            // Test the connection
            if (!$connected) {
                $errorMsg = 'Connection failed - could not establish connection to MikroTik';
                log_message('error', 'MikroTik connection failed: ' . $errorMsg);
                // Return dummy data instead of error
                $dummyData = [
                    'board_name' => 'Unknown',
                    'version' => 'Unknown',
                    'cpu_usage' => '0%',
                    'memory_usage' => '0%',
                    'memory_used' => '0',
                    'memory_total' => '0',
                    'disk_usage' => '0%',
                    'uptime' => 'N/A',
                    'router_name' => $router['name'] ?? 'Unknown Router',
                    'router_location' => $router['address'] ?? 'Unknown Location',
                    'router_host' => $router['ip_router'] ?? 'Unknown',
                    'is_fallback' => true,
                    'source' => 'dummy_data',
                    'connection_error' => $errorMsg
                ];
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => $dummyData,
                    'router_config_id' => $router['id_lokasi'] ?? null
                ]);
            }

            log_message('info', 'MikroTik connection successful, retrieving system information...');

            // Get system resource information using MikrotikNew
            $result = $mt->comm('/system/resource/print');

            if (empty($result) || !isset($result[0]) || !is_array($result[0])) {
                log_message('error', 'Empty or invalid system resource data received');
                // Return error, do NOT return fallback/dummy data
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Empty or invalid system resource data received from MikroTik router.',
                    'data' => null,
                    'router_config_id' => $router['id_lokasi'] ?? null
                ])->setStatusCode(502);
            }

            // Get the actual system resource data from first element
            $systemResource = $result[0];
            log_message('info', 'System resource data received: ' . json_encode($systemResource));

            // Process system information
            $systemData = [];

            // Router board information
            $systemData['board_name'] = $systemResource['board-name'] ?? 'Unknown Board';
            $systemData['version'] = $systemResource['version'] ?? 'Unknown Version';

            // Memory information
            $totalMem = $systemResource['total-memory'] ?? 0;
            $freeMem = $systemResource['free-memory'] ?? 0;
            $usedMem = $totalMem - $freeMem;

            $systemData['memory_used'] = $this->formatBytes($usedMem);
            $systemData['memory_total'] = $this->formatBytes($totalMem);
            $systemData['memory_usage_percent'] = $totalMem > 0 ? round(($usedMem / $totalMem) * 100, 2) : 0;

            // CPU information
            $cpuLoadRaw = $systemResource['cpu-load'] ?? '0%';
            $cpuLoadNum = (int)str_replace('%', '', $cpuLoadRaw);
            $systemData['cpu_load_percent'] = $cpuLoadNum;

            // Uptime information
            $systemData['uptime'] = $this->formatUptime($systemResource['uptime'] ?? '');

            // Disk information (if available)
            $totalDisk = isset($systemResource['total-hdd-space']) ? $systemResource['total-hdd-space'] : 0;
            $freeDisk = isset($systemResource['free-hdd-space']) ? $systemResource['free-hdd-space'] : 0;
            $usedDisk = $totalDisk - $freeDisk;

            $systemData['disk_used'] = $this->formatBytes($usedDisk);
            $systemData['disk_total'] = $this->formatBytes($totalDisk);
            $systemData['disk_usage_percent'] = $totalDisk > 0 ? round(($usedDisk / $totalDisk) * 100, 2) : 0;

            // Prepare response data
            $responseData = [
                'board_name' => $systemData['board_name'],
                'version' => $systemData['version'],
                'cpu_usage' => $systemData['cpu_load_percent'] . '%',
                'memory_usage' => $systemData['memory_usage_percent'] . '%',
                'memory_used' => $systemData['memory_used'],
                'memory_total' => $systemData['memory_total'],
                'disk_usage' => $systemData['disk_usage_percent'] . '%',
                'disk_used' => $systemData['disk_used'],
                'disk_total' => $systemData['disk_total'],
                'uptime' => $systemData['uptime'],
                'router_name' => $router['name'] ?? 'Unknown Router',
                'router_location' => $router['address'] ?? 'Unknown Location',
                'host' => $router['ip_router'],
                'is_fallback' => false,
                'source' => 'real_mikrotik_data'
            ];

            log_message('info', 'Successfully retrieved system information from MikroTik router');

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $responseData,
                'router_config_id' => $router['id_lokasi'] ?? null
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Exception in realSystemInfo: ' . $e->getMessage());
            log_message('debug', 'Exception details: ' . $e->getFile() . ':' . $e->getLine());
            // Return error, do NOT return fallback/dummy data
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Exception in realSystemInfo: ' . $e->getMessage(),
                'data' => null
            ])->setStatusCode(500);
        }
    }

    /**
     * Test connection to a specific router by ID
     * This endpoint allows testing connection to a MikroTik router directly from the API
     */
    public function testRouterConnection($routerId = null)
    {
        try {
            // Get the router by ID
            $lokasiServerModel = new \App\Models\ServerLocationModel();

            if ($routerId === null) {
                // If no ID provided, get the primary connected router
                $router = $lokasiServerModel->where('is_connected', '1')->first();

                if (!$router) {
                    // Try to get any router
                    $router = $lokasiServerModel->first();

                    if (!$router) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'No MikroTik routers configured in the system.',
                            'data' => null
                        ])->setStatusCode(404);
                    }
                }
            } else {
                // Get specific router by ID
                $router = $lokasiServerModel->find($routerId);

                if (!$router) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Router with ID ' . $routerId . ' not found.',
                        'data' => null
                    ])->setStatusCode(404);
                }
            }

            // Parse host and port from ip_router - prefer port in ip_router if present
            $host = $router['ip_router']; // Gunakan host utuh, walau ada ':'
            $apiPort = $router['port_api'] ?? 8728;
            $endpointPort = null;
            if (strpos($host, ':') !== false) {
                $hostParts = explode(':', $host);
                $endpointPort = isset($hostParts[1]) ? (int)$hostParts[1] : null;
                // Host tetap utuh, tidak dipecah!
            }

            // Get router configuration
            $config = [
                'host' => $host,
                'user' => $router['username'],
                'pass' => $router['password'],
                'port' => (int)$apiPort // Always use port_api from DB for API connection
            ];

            // Diagnostic information
            $diagnostics = [];

            // Test 1: DNS resolution (if hostname is used)
            // Only resolve the hostname part (without port) if host is not an IP
            $hostnameForDns = $host;
            if (strpos($host, ':') !== false) {
                $hostPartsForDns = explode(':', $host);
                $hostnameForDns = $hostPartsForDns[0];
            }
            if (!filter_var($hostnameForDns, FILTER_VALIDATE_IP)) {
                $ip = gethostbyname($hostnameForDns);
                $diagnostics['dns_resolution'] = [
                    'success' => ($ip !== $hostnameForDns),
                    'hostname' => $hostnameForDns,
                    'resolved_ip' => ($ip !== $hostnameForDns) ? $ip : null,
                    'message' => ($ip !== $hostnameForDns)
                        ? "Hostname resolved successfully to $ip"
                        : "Failed to resolve hostname. Check DNS configuration."
                ];
            } else {
                $diagnostics['dns_resolution'] = [
                    'success' => true,
                    'message' => "Using IP address directly, no DNS resolution needed."
                ];
            }

            // Test 2: Basic connectivity test (socket connection)
            $socketConnection = @fsockopen($host, $apiPort, $errno, $errstr, 5);
            $diagnostics['socket_connectivity'] = [
                'success' => ($socketConnection !== false),
                'endpoint' => "$host:$apiPort",
                'message' => ($socketConnection !== false)
                    ? "TCP connection to $host:$apiPort successful"
                    : "Failed to establish TCP connection to $host:$apiPort - $errstr (Error #$errno)"
            ];

            if ($socketConnection) {
                fclose($socketConnection);
            }

            // Test 3: API authentication
            $mikrotikAPI = new MikrotikAPI($config);
            $connectionTest = $mikrotikAPI->testConnection();

            $diagnostics['api_authentication'] = [
                'success' => $connectionTest['success'],
                'message' => $connectionTest['message'] ?? 'Unknown error',
                'endpoint' => "$host:$apiPort",
                'username' => $router['username']
            ];

            // Test 4: Get system information (if connection successful)
            if ($connectionTest['success']) {
                try {
                    $resource = $mikrotikAPI->getSystemResource();

                    if ($resource && is_array($resource)) {
                        $diagnostics['system_info'] = [
                            'success' => true,
                            'board_name' => $resource['board-name'] ?? 'Unknown',
                            'version' => $resource['version'] ?? 'Unknown',
                            'uptime' => $resource['uptime'] ?? 'Unknown',
                            'message' => 'Successfully retrieved system information',
                            'is_fallback' => false // Tambahkan flag agar frontend tahu ini data asli
                        ];
                    } else {
                        $diagnostics['system_info'] = [
                            'success' => false,
                            'message' => 'Received empty or invalid system resource data',
                            'is_fallback' => true // Data dummy jika gagal
                        ];
                    }
                } catch (\Exception $e) {
                    $diagnostics['system_info'] = [
                        'success' => false,
                        'message' => 'Error retrieving system information: ' . $e->getMessage(),
                        'is_fallback' => true
                    ];
                }
            }

            // Generate overall status
            $allSuccessful = true;
            $mainErrorMessage = '';
            foreach ($diagnostics as $test => $result) {
                if (!$result['success']) {
                    $allSuccessful = false;
                    $mainErrorMessage = $result['message'];
                    break;
                }
            }

            // Generate router details
            $routerDetails = [
                'id' => $router['id'],
                'name' => $router['name'],
                'host' => $router['ip_router'],
                'api_port' => $router['port_api'] ?? 8728,
                'endpoint_port' => $endpointPort,
                'username' => $router['username'],
                'is_connected' => $router['is_connected']
            ];

            // Jika system_info berhasil dan bukan fallback, tampilkan data asli di level atas
            $mikrotikSystem = null;
            if (isset($diagnostics['system_info']) && $diagnostics['system_info']['success'] && isset($diagnostics['system_info']['is_fallback']) && !$diagnostics['system_info']['is_fallback']) {
                $mikrotikSystem = [
                    'board_name' => $diagnostics['system_info']['board_name'] ?? null,
                    'version' => $diagnostics['system_info']['version'] ?? null,
                    'uptime' => $diagnostics['system_info']['uptime'] ?? null,
                    'is_fallback' => false
                ];
            }

            $response = [
                'status' => $allSuccessful ? 'success' : 'error',
                'message' => $allSuccessful
                    ? 'Connection to MikroTik router successful'
                    : 'Connection to MikroTik router failed: ' . $mainErrorMessage,
                'router' => $routerDetails,
                'diagnostics' => $diagnostics
            ];
            if ($mikrotikSystem) {
                $response['mikrotik_system'] = $mikrotikSystem;
            }
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Error testing router connection: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error testing router connection: ' . $e->getMessage(),
                'data' => null
            ])->setStatusCode(500);
        }
    }

    /**
     * Test connection with custom parameters
     * This endpoint allows testing connection to a MikroTik router with custom port and credentials
     */
    public function testCustomConnection()
    {
        try {
            // Get parameters from request
            $host = $this->request->getGet('host');
            $port = $this->request->getGet('port') ?? 8728;
            $username = $this->request->getGet('username');
            $password = $this->request->getGet('password');
            $routerId = $this->request->getGet('router_id');

            // Validate required parameters
            if (empty($host) && empty($routerId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Either host or router_id parameter is required',
                    'data' => null
                ])->setStatusCode(400);
            }

            // If router_id is provided, get router details from database
            if (!empty($routerId)) {
                $lokasiServerModel = new \App\Models\ServerLocationModel();
                $router = $lokasiServerModel->find($routerId);

                if (!$router) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Router with ID ' . $routerId . ' not found',
                        'data' => null
                    ])->setStatusCode(404);
                }

                // Use router credentials if not provided in parameters
                $host = $host ?? $router['ip_router'];
                $username = $username ?? $router['username'];
                $password = $password ?? $router['password'];

                // Use provided port or router's port_api or default
                if (empty($port) || $port == 8728) { // If no port provided or default port
                    $port = $router['port_api'] ?? 8728; // Use router's API port
                }
            }

            // Ensure we have username and password
            if (empty($username) || empty($password)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Username and password are required',
                    'data' => null
                ])->setStatusCode(400);
            }

            // Parse host and port from ip_router if it contains port
            // But prioritize the port parameter (API port) over endpoint port
            if (strpos($host, ':') !== false) {
                $hostParts = explode(':', $host);
                $host = $hostParts[0];
                $endpointPort = isset($hostParts[1]) ? (int)$hostParts[1] : null;

                // Only use endpoint port if no API port was explicitly provided
                if ($this->request->getGet('port') === null && empty($routerId)) {
                    $port = $endpointPort;
                }
                // Note: If port was provided via parameter or router config, keep using that for API
            }

            // Connection configuration
            $config = [
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => (int)$port
            ];

            // Log connection attempt (but hide password)
            $logConfig = $config;
            $logConfig['pass'] = '***HIDDEN***';
            log_message('debug', 'Testing custom connection to MikroTik router: ' . json_encode($logConfig));

            // Initialize MikroTik connection
            $mikrotikAPI = new MikrotikAPI($config);

            // Test connection
            $connectionTest = $mikrotikAPI->testConnection();

            if (!$connectionTest['success']) {
                // Failed connection
                $endpoint = $host . ':' . $port;

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Failed to connect to MikroTik router (Endpoint: {$endpoint}, User: {$username}) - " . $connectionTest['message'],
                    'connection_details' => [
                        'host' => $host,
                        'port' => $port,
                        'username' => $username
                    ],
                    'data' => null
                ]);
            }

            // Connection successful, try to get system resource
            try {
                $resource = $mikrotikAPI->getSystemResource();

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Successfully connected to MikroTik router',
                    'connection_details' => [
                        'host' => $host,
                        'port' => $port,
                        'username' => $username
                    ],
                    'system_info' => [
                        'board_name' => $resource['board-name'] ?? 'Unknown',
                        'version' => $resource['version'] ?? 'Unknown',
                        'uptime' => $resource['uptime'] ?? 'Unknown'
                    ]
                ]);
            } catch (\Exception $e) {
                // Connection worked but couldn't get system resource
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Connected to MikroTik router but failed to get system information',
                    'connection_details' => [
                        'host' => $host,
                        'port' => $port,
                        'username' => $username
                    ],
                    'error' => $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error testing custom router connection: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error testing router connection: ' . $e->getMessage(),
                'data' => null
            ])->setStatusCode(500);
        }
    }

    /**
     * Generate recommendation based on diagnostic results
     */
    private function getConnectionRecommendation($diagnostics, $routerId = null)
    {
        // If all tests passed, no recommendation needed
        if (empty(array_filter($diagnostics, function ($test) {
            return $test['success'] === false;
        }))) {
            return null;
        }

        $recommendations = [];

        // DNS resolution failed
        if (isset($diagnostics['dns_resolution']) && !$diagnostics['dns_resolution']['success']) {
            $recommendations[] = "DNS resolution failed. Try using the IP address directly instead of hostname.";
        }

        // Socket connectivity failed
        if (isset($diagnostics['socket_connectivity']) && !$diagnostics['socket_connectivity']['success']) {
            $recommendations[] = "Network connectivity test failed. This indicates:
                - The router may be offline
                - The port may be incorrect (standard MikroTik API ports are 8728 for API and 8729 for API-SSL)
                - A firewall may be blocking the connection
                - The server cannot reach the router over the network";
        }

        // API authentication failed
        if (
            isset($diagnostics['socket_connectivity']) && $diagnostics['socket_connectivity']['success'] &&
            isset($diagnostics['api_authentication']) && !$diagnostics['api_authentication']['success']
        ) {
            $recommendations[] = "Authentication failed. This indicates:
                - Username or password may be incorrect
                - The user may not have API access permissions
                - The API service may be disabled on the router";
        }

        // If routerId is provided, add option to update router settings
        if ($routerId) {
            // Extract port from diagnostics
            $port = null;
            if (isset($diagnostics['socket_connectivity']['endpoint'])) {
                $endpoint = $diagnostics['socket_connectivity']['endpoint'];
                if (preg_match('/.*:(\d+)$/', $endpoint, $matches)) {
                    $port = $matches[1];
                }
            }

            if ($port && $port != 8728) {
                $recommendations[] = "The port being used ($port) is non-standard. Standard MikroTik API port is 8728.";

                if (isset($diagnostics['socket_connectivity']) && !$diagnostics['socket_connectivity']['success']) {
                    $recommendations[] = "Consider updating the router configuration to use the standard port 8728 or verify that port $port is correct.";
                }
            }
        }

        // If no specific recommendations could be determined
        if (empty($recommendations)) {
            $recommendations[] = "Check your MikroTik router configuration and ensure the API service is enabled and accessible.";
        }

        return $recommendations;
    }

    /**
     * Public system info endpoint that doesn't require authentication
     * Provides basic system information for monitoring purposes
     */
    public function publicSystemInfo()
    {
        try {
            // Get basic system info without full authentication
            $lokasiServerModel = new \App\Models\ServerLocationModel();
            $router = $lokasiServerModel->where('is_connected', '1')->first();

            if (!$router) {
                // Return basic system info from server
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => [
                        'board_name' => 'System Monitor',
                        'version' => 'N/A',
                        'cpu_usage' => '0%',
                        'memory_usage' => '0%',
                        'disk_usage' => '0%',
                        'uptime' => 'N/A',
                        'is_fallback' => true,
                        'source' => 'public_endpoint'
                    ]
                ]);
            }

            // Try to get real system info but with limited data
            try {
                $host = $router['ip_router'];
                $port = $router['port_api'] ?? 8728;
                $username = $router['username'];
                $password = $router['password'];

                // Parse host:port format if needed
                if (strpos($host, ':') !== false) {
                    $hostParts = explode(':', $host);
                    $host = $hostParts[0];
                }

                $config = [
                    'host' => $host,
                    'user' => $username,
                    'pass' => $password,
                    'port' => $port
                ];

                $mikrotikAPI = new \App\Libraries\MikrotikAPI($config);

                if ($mikrotikAPI->isConnected()) {
                    $resource = $mikrotikAPI->getSystemResource();

                    if ($resource && is_array($resource)) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'data' => [
                                'board_name' => $resource['board-name'] ?? 'Unknown',
                                'version' => $resource['version'] ?? 'Unknown',
                                'cpu_usage' => $resource['cpu-load'] ?? '0%',
                                'memory_usage' => isset($resource['free-memory'], $resource['total-memory'])
                                    ? round((($resource['total-memory'] - $resource['free-memory']) / $resource['total-memory']) * 100, 2) . '%'
                                    : '0%',
                                'disk_usage' => '0%', // MikroTik doesn't always provide disk info
                                'uptime' => $this->formatUptime($resource['uptime'] ?? ''),
                                'is_fallback' => false,
                                'source' => 'mikrotik_api'
                            ]
                        ]);
                    }
                }
            } catch (\Exception $e) {
                log_message('warning', 'Public system info MikroTik connection failed: ' . $e->getMessage());
            }

            // Return fallback data
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'board_name' => 'Connection Failed',
                    'version' => 'N/A',
                    'cpu_usage' => '0%',
                    'memory_usage' => '0%',
                    'disk_usage' => '0%',
                    'uptime' => 'N/A',
                    'is_fallback' => true,
                    'source' => 'fallback_data'
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Public system info error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get system information',
                'data' => null
            ])->setStatusCode(500);
        }
    }
}
