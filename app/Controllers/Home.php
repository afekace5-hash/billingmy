<?php

namespace App\Controllers;

use App\Models\TpenerimaanModel;
use App\Models\CashFlowModel;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Models\ServerLocationModel;

class Home extends BaseController
{
	public function index()
	{
		$startTime = microtime(true);

		log_message('info', '=== HOME CONTROLLER INDEX CALLED ===');

		// Initialize models
		$customerModel = new CustomerModel();
		$invoiceModel = new InvoiceModel();

		// Get dashboard statistics from database (fast queries only)
		$dashboardData = $this->getDashboardStatistics($customerModel, $invoiceModel);

		// Don't get MikroTik system info on initial load - make it async
		// Add dummy system info for initial page load, real data will load via AJAX
		$dashboardData['systemInfo'] = $this->getDummySystemInfo();

		$endTime = microtime(true);
		$loadTime = round(($endTime - $startTime) * 1000, 2);
		log_message('info', "Dashboard loaded in {$loadTime}ms (optimized - no MikroTik connection on initial load)");

		return view('dashboard', $dashboardData);
	}

	/**
	 * AJAX endpoint for system info (async loading)
	 */
	public function systemInfo()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
		}

		try {
			$systemInfo = $this->getMikrotikSystemInfo();
			return $this->response->setJSON([
				'status' => 'success',
				'data' => $systemInfo
			]);
		} catch (\Exception $e) {
			log_message('error', 'Error in systemInfo endpoint: ' . $e->getMessage());
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Failed to get system info',
				'data' => $this->getDummySystemInfo()
			]);
		}
	}

	private function getDashboardStatistics($customerModel, $invoiceModel)
	{
		try {
			// Get database connection
			$db = \Config\Database::connect();

			// Total customers
			$totalCustomers = $customerModel->countAll();
			log_message('debug', "Dashboard: totalCustomers = {$totalCustomers}");			// Get current month for filtering invoices
			$currentMonth = date('Y-m');

			// Count invoices paid in current month (by payment_date)
			// Menggunakan payment_date agar invoice yang dibayar bulan ini terhitung
			$currentMonthStart = date('Y-m-01');
			$currentMonthEnd = date('Y-m-t');
			$paidInvoices = (clone $invoiceModel)->where('status', 'paid')
				->where('payment_date >=', $currentMonthStart)
				->where('payment_date <=', $currentMonthEnd . ' 23:59:59')
				->countAllResults();

			// Count unpaid invoices for current month only (by periode field)
			$unpaidInvoices = (clone $invoiceModel)->where('status', 'unpaid')
				->where('periode', $currentMonth)
				->countAllResults();
			log_message('debug', "Dashboard: unpaidInvoices = {$unpaidInvoices}");

			// Count overdue invoices for current month only (by periode field)
			$overdueInvoices = (clone $invoiceModel)->where('status', 'overdue')
				->where('periode', $currentMonth)
				->countAllResults();

			// Count suspended customers using isolir_status field
			$suspendedCustomers = (clone $customerModel)->where('isolir_status', 1)
				->countAllResults();

			// Count not installed - skip this to avoid DATE errors, set to 0
			$notInstalledCustomers = 0;			// Get today's payments
			$todayPayments = (clone $invoiceModel)->where('payment_date >=', date('Y-m-d'))
				->where('payment_date <', date('Y-m-d', strtotime('+1 day')))
				->where('status', 'paid')
				->countAllResults();

			// Calculate today's revenue
			$todayRevenue = (clone $invoiceModel)->selectSum('paid_amount')
				->where('payment_date >=', date('Y-m-d'))
				->where('payment_date <', date('Y-m-d', strtotime('+1 day')))
				->where('status', 'paid')
				->get()
				->getRow()
				->paid_amount ?? 0;

			// Calculate total revenue and expenses from cash_flow + paid invoices
			// Menggunakan query PERSIS SAMA seperti di ArusKas.php data() method
			$totalRevenue = 0;
			$totalExpenses = 0;

			// Get all data from cash_flow table + paid invoices (NO payment_transactions)
			if ($db->tableExists('cash_flow')) {
				$cashFlowModel = new CashFlowModel();
				$useSoftDeletes = $cashFlowModel->useSoftDeletes;
				$softDeleteCondition = $useSoftDeletes ? "cf.deleted_at IS NULL" : "1=1";

				// Build combined query for cash flow and invoices only (no payment_transactions)
				// PERSIS SAMA seperti di ArusKas::data() line 330-368
				$allDataQuery = "
					SELECT 
						'cash_flow' as source_type,
						cf.id,
						cf.name,
						cf.transaction_date,
						cf.amount,
						cf.type,
						cf.description,
						k.nama as category_name,
						'Manual Entry' as source_description
					FROM cash_flow cf
					LEFT JOIN kategori_kas k ON k.id_category = cf.category_id
					WHERE $softDeleteCondition
					
					UNION ALL
					
					SELECT 
						'invoice' as source_type,
						ci.id,
						CONCAT('Invoice #', ci.invoice_no, ' - ', COALESCE(c.nama_pelanggan, 'Unknown Customer')) as name,
						ci.payment_date as transaction_date,
						CASE 
							WHEN ci.paid_amount IS NULL OR ci.paid_amount = '' THEN ci.bill 
							ELSE ci.paid_amount 
						END as amount,
						'income' as type,
						CONCAT('Pembayaran invoice ', ci.periode, ' untuk ', COALESCE(c.nama_pelanggan, 'Unknown Customer')) as description,
						'Pembayaran Invoice' as category_name,
						CONCAT('Invoice Payment - ', COALESCE(ci.payment_method, 'Manual')) as source_description
					FROM customer_invoices ci
					LEFT JOIN customers c ON c.id_customers = ci.customer_id
					WHERE ci.status = 'paid' 
					  AND (
						(ci.paid_amount IS NOT NULL AND ci.paid_amount != '' AND ci.paid_amount > 0) 
						OR 
						((ci.paid_amount IS NULL OR ci.paid_amount = '') AND ci.bill > 0)
					  )
					  AND ci.payment_date IS NOT NULL
				";

				// Calculate GRAND TOTAL summary (sama seperti di ArusKas::data() line 404-413)
				$grandSummaryQuery = "
					SELECT 
						SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as grand_total_income,
						SUM(CASE WHEN type = 'expenditure' THEN amount ELSE 0 END) as grand_total_expenditure
					FROM ($allDataQuery) as summary_data
				";

				$summary = $db->query($grandSummaryQuery)->getRowArray();
				$totalRevenue = isset($summary['grand_total_income']) ? (float)$summary['grand_total_income'] : 0;
				$totalExpenses = isset($summary['grand_total_expenditure']) ? (float)$summary['grand_total_expenditure'] : 0;
			}

			// Calculate net balance (saldo bersih)
			$netBalance = $totalRevenue - $totalExpenses;

			// Get financial data for charts
			$financialData = $this->getFinancialChartData($invoiceModel);

			log_message('debug', 'Dashboard statistics calculated: ' . json_encode([
				'totalCustomers' => $totalCustomers,
				'unpaidInvoices' => $unpaidInvoices,
				'paidInvoices' => $paidInvoices
			]));

			return [
				'totalCustomers' => $totalCustomers,
				'paidInvoices' => $paidInvoices,
				'unpaidInvoices' => $unpaidInvoices,
				'overdueInvoices' => $overdueInvoices,
				'suspendedCustomers' => $suspendedCustomers,
				'notInstalledCustomers' => $notInstalledCustomers,
				'todayPayments' => $todayPayments,
				'todayRevenue' => $todayRevenue,
				'totalRevenue' => $totalRevenue,
				'netBalance' => $netBalance,
				'revenueChartData' => $financialData['revenue'],
				'expenseChartData' => $financialData['expense'],
				'revenueLabels' => $financialData['labels'],
				'paymentMethodData' => $financialData['paymentMethods'],
				'chartPeriod' => $financialData['period']
			];
		} catch (\Exception $e) {
			log_message('error', 'Error getting dashboard statistics: ' . $e->getMessage());

			// Return default values if database query fails
			return [
				'totalCustomers' => 0,
				'paidInvoices' => 0,
				'unpaidInvoices' => 0,
				'overdueInvoices' => 0,
				'suspendedCustomers' => 0,
				'notInstalledCustomers' => 0,
				'todayPayments' => 0,
				'todayRevenue' => 0,
				'totalRevenue' => 0,
				'netBalance' => 0,
				'revenueChartData' => [],
				'expenseChartData' => [],
				'revenueLabels' => [],
				'paymentMethodData' => [
					'labels' => ['TUNAI', 'TRANSFER', 'QRIS', 'VIRTUAL AKUN'],
					'data' => [0, 0, 0, 0],
					'percentages' => [0, 0, 0, 0],
					'total' => 0
				],
				'chartPeriod' => date('d-m-Y') . ' s/d ' . date('d-m-Y')
			];
		}
	}

	private function getFinancialChartData($invoiceModel)
	{
		try {
			// Initialize models for expenses
			$cashFlowModel = new CashFlowModel();

			// Get last 30 days data
			$startDate = date('Y-m-d', strtotime('-29 days'));
			$endDate = date('Y-m-d');

			$revenueData = [];
			$expenseData = [];
			$labels = [];

			// Get daily revenue and expense for last 30 days
			for ($i = 29; $i >= 0; $i--) {
				$date = date('Y-m-d', strtotime("-$i days"));

				// Get daily revenue (paid invoices)
				$dailyRevenue = (clone $invoiceModel)->selectSum('paid_amount')
					->where('payment_date', $date)
					->where('status', 'paid')
					->get()
					->getRow()
					->paid_amount ?? 0;

				// Get daily expenses
				$dailyExpense = (clone $cashFlowModel)->selectSum('amount')
					->where('DATE(transaction_date)', $date)
					->where('type', 'expenditure')
					->get()
					->getRow()
					->amount ?? 0;
				$revenueData[] = (int)$dailyRevenue;
				$expenseData[] = (int)$dailyExpense;
				$labels[] = date('d M', strtotime($date));
			}

			// Get payment method statistics for current month
			$paymentMethods = $this->getPaymentMethodStats($invoiceModel);

			return [
				'revenue' => $revenueData,
				'expense' => $expenseData,
				'labels' => $labels,
				'paymentMethods' => $paymentMethods,
				'period' => date('d-m-Y', strtotime($startDate)) . ' s/d ' . date('d-m-Y', strtotime($endDate))
			];
		} catch (\Exception $e) {
			log_message('error', 'Error getting financial chart data: ' . $e->getMessage());
			return [
				'revenue' => [],
				'expense' => [],
				'labels' => [],
				'paymentMethods' => [],
				'period' => date('d-m-Y') . ' s/d ' . date('d-m-Y')
			];
		}
	}

	private function getPaymentMethodStats($invoiceModel)
	{
		try {
			$currentMonth = date('Y-m');
			$startDate = $currentMonth . '-01';
			$endDate = date('Y-m-t', strtotime($startDate)); // Last day of current month

			// Get payment method counts for current month based on payment_date
			$db = \Config\Database::connect();

			// Get all payment methods dynamically
			$query = $db->query("
				SELECT 
					payment_method,
					COUNT(*) as count
				FROM customer_invoices 
				WHERE status = 'paid' 
				AND payment_method IS NOT NULL 
				AND payment_method != ''
				AND payment_date >= ?
				AND payment_date <= ?
				GROUP BY payment_method
				ORDER BY count DESC
			", [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

			$results = $query->getResultArray();

			// Calculate total
			$totalTransactions = 0;
			foreach ($results as $row) {
				$totalTransactions += $row['count'];
			}

			if ($totalTransactions == 0) {
				return [
					'labels' => [],
					'data' => [],
					'percentages' => [],
					'total' => 0
				];
			}

			// Build labels and data arrays dynamically
			$labels = [];
			$data = [];
			$percentages = [];

			foreach ($results as $row) {
				$labels[] = strtoupper($row['payment_method']);
				$data[] = (int)$row['count'];
				$percentages[] = round(($row['count'] / $totalTransactions) * 100, 1);
			}

			return [
				'labels' => $labels,
				'data' => $data,
				'percentages' => $percentages,
				'total' => $totalTransactions
			];
		} catch (\Exception $e) {
			log_message('error', 'Error getting payment method stats: ' . $e->getMessage());
			return [
				'labels' => [],
				'data' => [],
				'percentages' => [],
				'total' => 0
			];
		}
	}

	private function getMikrotikSystemInfo()
	{
		try {
			// Get the primary location server (you might want to modify this logic)
			$lokasiServerModel = new ServerLocationModel();
			$primaryServer = $lokasiServerModel->where('is_connected', '1')->first();

			if (!$primaryServer) {
				log_message('warning', 'No connected server found in database - using dummy data');
				// Return dummy data if no server is configured
				return $this->getDummySystemInfo();
			}
			log_message('info', 'Primary server found: ' . $primaryServer['name'] . ' (' . $primaryServer['ip_router'] . ')');

			// Parse host dan port dengan benar dari ip_router
			$host = $primaryServer['ip_router'];
			$port = $primaryServer['port_api'] ?? 8728;

			// Jika ip_router berformat "host:port", pisahkan
			if (strpos($host, ':') !== false) {
				$hostParts = explode(':', $host);
				$host = $hostParts[0];
				$portFromHost = isset($hostParts[1]) ? (int)$hostParts[1] : null;
				if ($portFromHost) {
					$port = $portFromHost;
				}
			}

			$config = [
				'host' => $host,
				'user' => $primaryServer['username'],
				'pass' => $primaryServer['password_router'],
				'port' => (int)$port
			];

			// Debug log configuration (tanpa password)
			log_message('info', 'MikroTik API config for system info: ' . json_encode([
				'host' => $config['host'],
				'user' => $config['user'],
				'port' => $config['port'],
				'original_ip_router' => $primaryServer['ip_router']
			]));

			// Initialize MikroTik connection
			$mikrotikAPI = new \App\Libraries\MikrotikAPI($config);

			// Test connection first
			$connectionTest = $mikrotikAPI->testConnection();
			if (!$connectionTest['success']) {
				log_message('debug', 'Failed to connect to MikroTik for system info: ' . $connectionTest['message']);
				return $this->getDummySystemInfo();
			}

			log_message('info', 'MikroTik connection successful, retrieving system resource...');

			// Get system resource information
			$resource = $mikrotikAPI->getSystemResource();

			// Debug: Log raw data untuk melihat format sebenarnya
			log_message('info', 'Raw MikroTik system resource data: ' . json_encode($resource));

			if ($resource && is_array($resource)) {
				// Debug: Log individual fields
				log_message('info', 'Processing system resource data - Board: ' . ($resource['board-name'] ?? 'not found') .
					', Version: ' . ($resource['version'] ?? 'not found') .
					', CPU: ' . ($resource['cpu-load'] ?? 'not found') .
					', Uptime: ' . ($resource['uptime'] ?? 'not found'));

				$systemInfo = [];

				// Board name
				$systemInfo['board_name'] = $resource['board-name'] ?? 'Unknown';

				// Version
				$systemInfo['version'] = $resource['version'] ?? 'Unknown';

				// Memory usage
				$totalMemory = isset($resource['total-memory']) ? $resource['total-memory'] : 0;
				$freeMemory = isset($resource['free-memory']) ? $resource['free-memory'] : 0;
				$usedMemory = $totalMemory - $freeMemory;

				$systemInfo['memory_used'] = $this->formatBytes($usedMemory);
				$systemInfo['memory_total'] = $this->formatBytes($totalMemory);
				$systemInfo['memory_usage'] = $totalMemory > 0 ? round(($usedMemory / $totalMemory) * 100, 2) : 0;

				// CPU load - MikroTik returns as percentage, extract number only
				$cpuLoad = isset($resource['cpu-load']) ? $resource['cpu-load'] : '0%';
				// Remove % sign if present and convert to integer, ensure it's numeric
				$cpuLoadValue = intval(str_replace('%', '', $cpuLoad));
				$systemInfo['cpu_load'] = is_numeric($cpuLoadValue) ? $cpuLoadValue : 0;

				// Uptime
				$systemInfo['uptime'] = isset($resource['uptime']) ? $this->formatUptime($resource['uptime']) : 'Unknown';

				log_message('info', 'Successfully retrieved real MikroTik system info: ' . json_encode($systemInfo));
				return $systemInfo;
			} else {
				log_message('warning', 'Empty or invalid system resource data from MikroTik - using dummy data');
				return $this->getDummySystemInfo();
			}
		} catch (\Exception $e) {
			log_message('debug', 'Error getting MikroTik system info: ' . $e->getMessage());
			log_message('debug', 'Exception trace: ' . $e->getTraceAsString());
			return $this->getDummySystemInfo();
		}
	}

	private function getDummySystemInfo()
	{
		log_message('info', 'Using dummy system info data (MikroTik connection failed or not configured)');
		return [
			'board_name' => 'Loading... (Connecting to MikroTik)',
			'version' => 'Loading...',
			'memory_used' => '0 B',
			'memory_total' => '0 B',
			'memory_usage' => 0, // Ensure this is numeric
			'disk_used' => '0 B',
			'disk_total' => '0 B',
			'disk_usage' => 0, // Ensure this is numeric
			'cpu_load' => 0, // Ensure this is numeric
			'uptime' => 'Loading system information...'
		];
	}

	private function formatBytes($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		} elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		} elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	private function formatUptime($uptime)
	{
		// MikroTik uptime bisa dalam berbagai format: 
		// - "2w3d4h5m6s" 
		// - "3d4h5m6s"
		// - "4h5m6s"
		// - atau format lain seperti "2 days, 1:28:31"

		$result = [];

		// Jika format sudah dalam bahasa Indonesia atau format yang diinginkan, return as is
		if (preg_match('/\d+\s*(Hari|Jam|Menit|Detik|Minggu)/', $uptime)) {
			return $uptime;
		}

		// Handle format seperti "2 days, 1:28:31"
		if (preg_match('/(\d+)\s*days?,\s*(\d+):(\d+):(\d+)/', $uptime, $matches)) {
			$days = intval($matches[1]);
			$hours = intval($matches[2]);
			$minutes = intval($matches[3]);
			$seconds = intval($matches[4]);

			if ($days > 0) $result[] = $days . ' Hari';
			if ($hours > 0) $result[] = $hours . ' Jam';
			if ($minutes > 0) $result[] = $minutes . ' Menit';
			if ($seconds > 0) $result[] = $seconds . ' Detik';

			return !empty($result) ? implode(', ', $result) : $uptime;
		}

		// Handle format MikroTik standar: 2w3d4h5m6s
		if (preg_match('/(\d+)w/', $uptime, $matches)) {
			$weeks = intval($matches[1]);
			if ($weeks > 0) {
				$result[] = $weeks . ' Minggu';
			}
		}

		if (preg_match('/(\d+)d/', $uptime, $matches)) {
			$days = intval($matches[1]);
			if ($days > 0) {
				$result[] = $days . ' Hari';
			}
		}

		if (preg_match('/(\d+)h/', $uptime, $matches)) {
			$hours = intval($matches[1]);
			if ($hours > 0) {
				$result[] = $hours . ' Jam';
			}
		}

		if (preg_match('/(\d+)m/', $uptime, $matches)) {
			$minutes = intval($matches[1]);
			if ($minutes > 0) {
				$result[] = $minutes . ' Menit';
			}
		}

		if (preg_match('/(\d+)s/', $uptime, $matches)) {
			$seconds = intval($matches[1]);
			if ($seconds > 0) {
				$result[] = $seconds . ' Detik';
			}
		}

		return !empty($result) ? implode(', ', $result) : $uptime;
	}

	public function generate()
	{
		// echo password_hash('12345', PASSWORD_BCRYPT);
	}

	public function refreshSystemInfo()
	{
		try {
			$systemInfo = $this->getMikrotikSystemInfo();

			return $this->response->setJSON([
				'success' => true,
				'data' => $systemInfo
			]);
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Failed to refresh system information: ' . $e->getMessage()
			]);
		}
	}

	public function testSystemInfo()
	{
		try {
			$systemInfo = $this->getMikrotikSystemInfo();

			// Also get raw data for debugging
			$lokasiServerModel = new ServerLocationModel();
			$primaryServer = $lokasiServerModel->where('is_connected', '1')->first();
			$rawData = null;
			if ($primaryServer) {
				// Parse host dan port dengan benar dari ip_router
				$host = $primaryServer['ip_router'];
				$port = $primaryServer['port_api'] ?? 8728;

				// Jika ip_router berformat "host:port", pisahkan
				if (strpos($host, ':') !== false) {
					$hostParts = explode(':', $host);
					$host = $hostParts[0];
					$portFromHost = isset($hostParts[1]) ? (int)$hostParts[1] : null;
					if ($portFromHost) {
						$port = $portFromHost;
					}
				}

				$config = [
					'host' => $host,
					'user' => $primaryServer['username'],
					'pass' => $primaryServer['password_router'],
					'port' => (int)$port
				];

				$mikrotikAPI = new \App\Libraries\MikrotikAPI($config);
				$connectionTest = $mikrotikAPI->testConnection();

				if ($connectionTest['success']) {
					$rawData = $mikrotikAPI->getSystemResource();
				}
			}

			return $this->response->setJSON([
				'success' => true,
				'formatted_data' => $systemInfo,
				'raw_data' => $rawData,
				'server_config' => [
					'host' => $primaryServer['ip_router'] ?? 'Not configured',
					'connected' => $primaryServer ? true : false
				]
			]);
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
		}
	}

	private function parseHostAndPort($hostString)
	{
		// Handle format like "id-14.hostddns.us:8211"
		if (strpos($hostString, ':') !== false) {
			$parts = explode(':', $hostString);
			return [
				'host' => $parts[0],
				'port' => intval($parts[1])
			];
		}

		// Return original host without port
		return [
			'host' => $hostString,
			'port' => null
		];
	}
}
