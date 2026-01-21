<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\RESTful\ResourceController;
use DateTime;
use CodeIgniter\Database\BaseBuilder;

class Invoices extends ResourceController
{
    /** @var \App\Models\CustomerModel */
    protected $customerModel;

    /** @var \App\Models\InvoiceModel */
    protected $invoiceModel;

    /** @var \App\Models\PackageProfileModel */
    protected $packageModel;

    /** @var \App\Models\BankModel */
    protected $BankModel;

    public function __construct()
    {
        log_message('debug', '==== INVOICES CONTROLLER LOADED ====');
        $this->customerModel = model('CustomerModel');
        $this->invoiceModel = model('InvoiceModel');
        $this->packageModel = model('PackageProfileModel');
        $this->BankModel = model('BankModel');
    }

    /**
     * Generate single bill for a customer (dummy implementation)
     * POST: customer_id, periode, amount, etc.
     */
    public function generateBillSingle()
    {
        // Ambil data dari POST (contoh: customer_id, periode, amount)
        $customer_id = $this->request->getPost('customer_id');
        $periode = $this->request->getPost('periode');
        $amount = $this->request->getPost('amount');
        // Validasi sederhana
        if (!$customer_id || !$periode || !$amount) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Parameter tidak lengkap.',
                'debug' => [
                    'customer_id' => $customer_id,
                    'periode' => $periode,
                    'amount' => $amount
                ]
            ]);
        }

        // Cek apakah customer_id valid di database
        $customer = $this->customerModel->find($customer_id);
        if (!$customer) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID customer tidak ditemukan.',
                'debug' => [
                    'customer_id' => $customer_id
                ]
            ]);
        }

        // Cek duplikat invoice sebelum insert
        $existing = $this->invoiceModel->where([
            'customer_id' => $customer_id,
            'periode' => $periode
        ])->first();
        if ($existing) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tagihan sudah ada untuk periode ini!'
            ]);
        }
        // Insert invoice jika belum ada
        $invoice_no = 'INV-' . date('Ymd') . '-' . $customer_id . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        // Hitung biaya tambahan untuk customer ini
        $additional_fee = $this->calculateAdditionalFees($customer);

        // Periksa apakah ini tagihan pertama (biaya pemasangan)
        $existing_invoices = $this->invoiceModel->where('customer_id', $customer_id)->countAllResults();
        if ($existing_invoices == 0 && !empty($customer['biaya_pasang'])) {
            $additional_fee += (float) str_replace(['.', ','], ['', ''], $customer['biaya_pasang']);
        }

        $this->invoiceModel->insert([
            'customer_id' => $customer_id,
            'invoice_no' => $invoice_no,
            'periode' => $periode,
            'bill' => $amount,
            'additional_fee' => $additional_fee,
            'arrears' => 0,
            'status' => 'unpaid',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tagihan single berhasil digenerate untuk customer: ' . $customer['nama_pelanggan'],
            'data' => [
                'customer_id' => $customer_id,
                'customer_name' => $customer['nama_pelanggan'],
                'periode' => $periode,
                'amount' => $amount
            ]
        ]);
    }
    /**
     * Generate invoices for all active customers for a given period (month-year).
     * POST: periode=YYYY-MM
     */
    public function generate()
    {
        try {
            $customerModel = $this->customerModel;
            $invoiceModel = $this->invoiceModel;
            $db = \Config\Database::connect();

            // Support: generate untuk semua customer (periode) atau per customer (customer_slug, month)
            $customer_slug = $this->request->getPost('customer_slug');
            $month = $this->request->getPost('month');
            // Jika tidak ada input periode, gunakan bulan saat ini (YYYY-MM)
            $periode = $this->request->getPost('periode') ?: date('Y-m');

            // Validasi format periode
            if (!preg_match('/^\d{4}-\d{2}$/', $periode)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Format periode tidak valid. Gunakan format YYYY-MM (contoh: 2025-06)'
                ]);
            }

            if ($customer_slug && $month) {
                // Generate invoice untuk customer tertentu sejumlah month ke depan
                $customer = $customerModel->find($customer_slug);
                if (!$customer) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Pelanggan tidak ditemukan.']);
                }
                $created = 0;
                for ($i = 0; $i < $month; $i++) {
                    $periodeGen = date('Y-m', strtotime(">{$i} month"));
                    // Cek jika sudah ada invoice untuk periode ini
                    $exists = $invoiceModel->where([
                        'customer_id' => $customer['id_customers'],
                        'periode' => $periodeGen
                    ])->first();
                    if ($exists) continue;
                    $paket = \Config\Database::connect()->table('package_profiles')->where('id', $customer['id_paket'])->get()->getRowArray();
                    $bill = $paket ? $paket['price'] : 0;
                    $package = $paket ? ($paket['name'] . ' | ' . $paket['bandwidth_profile']) : '-';
                    $invoice_no = 'INV-' . date('Ymd') . '-' . $customer['id_customers'] . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

                    // Hitung biaya tambahan
                    $additional_fee = $this->calculateAdditionalFees($customer);

                    // Tambahkan biaya pemasangan untuk customer baru (bulan pertama tagihan)
                    if ($i == 0 && !empty($customer['biaya_pasang'])) {
                        $additional_fee += (float) str_replace(['.', ','], ['', ''], $customer['biaya_pasang']);
                    }

                    $invoiceData = [
                        'customer_id' => $customer['id_customers'],
                        'invoice_no' => $invoice_no,
                        'periode' => $periodeGen,
                        'bill' => $bill,
                        'arrears' => 0,
                        'status' => 'unpaid',
                        'package' => $package,
                        'additional_fee' => $additional_fee,
                        'server' => $customer['id_lokasi_server'] ?? null,
                        'due_date' => $customer['tgl_tempo'] ?? null,
                        'district' => $customer['district'] ?? null,
                        'village' => $customer['village'] ?? null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $invoiceModel->insert($invoiceData);
                    $created++;
                }
                // Ambil data invoice yang baru saja dibuat untuk response tabel (khusus pelanggan ini)
                $newInvoices = $invoiceModel
                    ->where('customer_id', $customer['id_customers'])
                    ->orderBy('id', 'desc')
                    ->findAll($month);
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Generate tagihan untuk customer selesai. $created tagihan berhasil dibuat.",
                    'created' => $created,
                    'data' => $newInvoices
                ]);
            } elseif ($periode) {
                // Generate untuk semua customer pada periode tertentu
                $customers = $customerModel->where('status_tagihan', 1)->findAll();
                if (empty($customers)) {
                    return $this->response->setJSON([
                        'status' => 'warning',
                        'message' => 'Tidak ada pelanggan aktif yang ditemukan.'
                    ]);
                }

                $created = 0;
                $skipped = 0;
                foreach ($customers as $cust) {
                    // Cek jika sudah ada invoice untuk periode ini
                    $exists = $invoiceModel->where([
                        'customer_id' => $cust['id_customers'],
                        'periode' => $periode
                    ])->first();
                    if ($exists) {
                        $skipped++;
                        continue;
                    }                    // Ambil info paket
                    $paket = \Config\Database::connect()->table('package_profiles')->where('id', $cust['id_paket'])->get()->getRowArray();
                    $bill = $paket ? $paket['price'] : 0;
                    $package = $paket ? ($paket['name'] . ' | ' . $paket['bandwidth_profile']) : '-';

                    // Generate nomor invoice unik
                    $invoice_no = 'INV-' . date('Ymd') . '-' . $cust['id_customers'] . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

                    // Hitung biaya tambahan
                    $additional_fee = $this->calculateAdditionalFees($cust);

                    // Periksa apakah ini tagihan pertama (biaya pemasangan)
                    $existing_invoices = $invoiceModel->where('customer_id', $cust['id_customers'])->countAllResults();
                    if ($existing_invoices == 0 && !empty($cust['biaya_pasang'])) {
                        $additional_fee += (float) str_replace(['.', ','], ['', ''], $cust['biaya_pasang']);
                    }

                    $invoiceData = [
                        'customer_id' => $cust['id_customers'],
                        'invoice_no' => $invoice_no,
                        'periode' => $periode,
                        'bill' => $bill,
                        'arrears' => 0,
                        'status' => 'unpaid',
                        'package' => $package,
                        'additional_fee' => $additional_fee,
                        'server' => $cust['id_lokasi_server'] ?? null,
                        'due_date' => $cust['tgl_tempo'] ?? null,
                        'district' => $cust['district'] ?? null,
                        'village' => $cust['village'] ?? null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $invoiceModel->insert($invoiceData);
                    $created++;
                }
                // Ambil data invoice yang baru saja dibuat untuk response tabel
                $newInvoices = $invoiceModel
                    ->where('periode', $periode)
                    ->orderBy('id', 'desc')
                    ->findAll();

                $message = "Generate tagihan selesai. $created tagihan berhasil dibuat untuk periode $periode.";
                if ($skipped > 0) {
                    $message .= " $skipped tagihan dilewati karena sudah ada.";
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => $message,
                    'created' => $created,
                    'skipped' => $skipped,
                    'data' => $newInvoices
                ]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Periode atau parameter customer_slug & month wajib diisi.']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Generate Invoice Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate tagihan: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Generate prorate invoices (prorata) for customers marked as new customers.
     * This calculates partial billing for the first month based on installation date.
     */
    public function generateProrates()
    {
        try {
            $db = \Config\Database::connect();
            $customerModel = $this->customerModel;
            $invoiceModel = $this->invoiceModel;

            // Get request parameters
            $request = service('request');
            $customerId = $request->getPost('customer_id');
            $periode = $request->getPost('periode') ?: date('Y-m');

            if ($customerId) {
                // Generate prorata untuk customer tertentu
                $customer = $customerModel->where('id_customers', $customerId)->first();
                if (!$customer) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Pelanggan tidak ditemukan.']);
                }

                // Cek apakah customer sudah diset sebagai new customer (is_new_customer = 1)
                if (!isset($customer['is_new_customer']) || $customer['is_new_customer'] != 1) {
                    return $this->response->setJSON([
                        'status' => 'warning',
                        'message' => 'Pelanggan ini tidak diset sebagai pelanggan baru untuk tagihan prorata.'
                    ]);
                }

                // Cek apakah sudah ada invoice prorata untuk periode ini
                $existingInvoice = $invoiceModel->where([
                    'customer_id' => $customer['id_customers'],
                    'periode' => $periode,
                    'is_prorata' => 1
                ])->first();

                if ($existingInvoice) {
                    return $this->response->setJSON([
                        'status' => 'warning',
                        'message' => 'Tagihan prorata untuk periode ini sudah ada.'
                    ]);
                }

                $prorataInvoice = $this->createProrataInvoice($customer, $periode);

                if ($prorataInvoice['success']) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Tagihan prorata berhasil dibuat.',
                        'data' => $prorataInvoice['invoice']
                    ]);
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $prorataInvoice['message']
                    ]);
                }
            } else {
                // Generate prorata untuk semua new customers
                $newCustomers = $customerModel->where([
                    'is_new_customer' => 1,
                    'status_tagihan' => 1
                ])->findAll();

                if (empty($newCustomers)) {
                    return $this->response->setJSON([
                        'status' => 'warning',
                        'message' => 'Tidak ada pelanggan baru yang membutuhkan tagihan prorata.'
                    ]);
                }

                $created = 0;
                $skipped = 0;
                $errors = [];

                foreach ($newCustomers as $customer) {
                    // Cek apakah sudah ada invoice prorata
                    $existingInvoice = $invoiceModel->where([
                        'customer_id' => $customer['id_customers'],
                        'periode' => $periode,
                        'is_prorata' => 1
                    ])->first();

                    if ($existingInvoice) {
                        $skipped++;
                        continue;
                    }

                    $prorataInvoice = $this->createProrataInvoice($customer, $periode);

                    if ($prorataInvoice['success']) {
                        $created++;
                    } else {
                        $errors[] = "Customer {$customer['nama_pelanggan']}: {$prorataInvoice['message']}";
                    }
                }

                $message = "Tagihan prorata selesai dibuat. $created tagihan berhasil dibuat, $skipped dilewati.";
                if (!empty($errors)) {
                    $message .= " Errors: " . implode("; ", $errors);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => $message,
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in generateProrates: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate tagihan prorata: ' . $e->getMessage()
            ]);
        }
    }
    // AJAX: Widget data for invoices
    public function getWidgetInvoice()
    {
        // Real data for widget
        $customerModel = $this->customerModel;
        $invoiceModel = $this->invoiceModel;
        $activeCounts = $customerModel->countByStatus();

        // Get selected period from GET (default: current month)
        $request = service('request');
        $periode = $request->getGet('filterPeriode') ?: date('Y-m');

        // Count invoices for the selected period (Tagihan Terbit)
        $totalInvoice = $invoiceModel->where('periode', $periode)->countAllResults();

        // Tagihan Belum Terbit: active customers - invoices terbit for this period
        $invoiceNotGenerated = $activeCounts['active'] - $totalInvoice;

        // Tagihan Lunas: count invoices for this period with status 'paid'
        $paidInvoice = $invoiceModel->where(['periode' => $periode, 'status' => 'paid'])->countAllResults();

        // Tagihan Belum Lunas: count invoices for this period with status 'unpaid'
        $unpaidInvoice = $invoiceModel->where(['periode' => $periode, 'status' => 'unpaid'])->countAllResults();


        // Total Tagihan Lunas (sum bill for paid invoices this period, formatted as Rupiah)
        $paidInvoiceAmountRaw = $invoiceModel->selectSum('bill')->where(['periode' => $periode, 'status' => 'paid'])->first()['bill'] ?? 0;
        $paidInvoiceAmount = 'Rp ' . number_format($paidInvoiceAmountRaw, 0, ',', '.');

        // Total Tagihan Belum Lunas (sum bill for unpaid invoices this period, formatted as Rupiah)
        $unpaidInvoiceAmountRaw = $invoiceModel->selectSum('bill')->where(['periode' => $periode, 'status' => 'unpaid'])->first()['bill'] ?? 0;
        $unpaidInvoiceAmount = 'Rp ' . number_format($unpaidInvoiceAmountRaw, 0, ',', '.');

        // Total tagihan: jumlah semua invoice untuk periode yang dipilih (bukan semua periode)
        $totalTagihan = $totalInvoice; // Sama dengan totalInvoice karena sudah filtered by periode

        $data = [
            'activeCustomers' => $activeCounts['active'],
            'totalInvoice' => $totalInvoice,
            'total' => $totalTagihan,
            'invoiceNotGenerated' => $invoiceNotGenerated,
            'paidInvoice' => $paidInvoice,
            'unpaidInvoice' => $unpaidInvoice,
            'paidInvoiceAmount' => $paidInvoiceAmount,
            'unpaidInvoiceAmount' => $unpaidInvoiceAmount,
        ];
        return $this->response->setJSON($data);
    }

    // AJAX: Difference invoice widget
    public function getDifferenceInvoice($value = null)
    {
        // Dummy HTML for modal, replace with real logic as needed
        $html = '<div>Belum terbit: ' . htmlspecialchars($value) . '</div>';
        return $this->response->setBody($html);
    }

    // AJAX: Get available periods from invoices
    public function getAvailablePeriods()
    {
        $invoiceModel = $this->invoiceModel;
        $periods = $invoiceModel->select('periode')
            ->distinct()
            ->where('periode IS NOT NULL')
            ->where('periode !=', '')
            ->orderBy('periode', 'DESC')
            ->findAll();

        $periodOptions = [];
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        // Always add current and next month
        $now = date('Y-m');
        $next = date('Y-m', strtotime('+1 month'));
        $customPeriods = [$now, $next];

        foreach ($customPeriods as $periode) {
            if (preg_match('/^(\d{4})-(\d{2})$/', $periode, $matches)) {
                $year = $matches[1];
                $month = $matches[2];
                $monthName = $monthNames[$month] ?? $month;
                $periodOptions[] = [
                    'value' => $periode,
                    'text' => $monthName . ' ' . $year
                ];
            }
        }

        // Add periods from database, skip duplicates
        $existing = array_column($periodOptions, 'value');
        foreach ($periods as $period) {
            $periode = $period['periode'];
            if (preg_match('/^(\d{4})-(\d{2})$/', $periode, $matches)) {
                if (!in_array($periode, $existing)) {
                    $year = $matches[1];
                    $month = $matches[2];
                    $monthName = $monthNames[$month] ?? $month;
                    $periodOptions[] = [
                        'value' => $periode,
                        'text' => $monthName . ' ' . $year
                    ];
                }
            }
        }

        return $this->response->setJSON($periodOptions);
    }
    // Display a listing of the resource.
    public function index()
    {
        return view('invoices/index');
    }

    // Invoice standalone page (different from transaction/invoices)
    public function invoiceStandalone()
    {
        return view('invoice/index');
    }

    // Show the form for creating a new resource.
    public function new()
    {
        return 'Form create invoice (resource controller)';
    }

    // Store a newly created resource in storage.
    public function create()
    {
        return 'Store new invoice (resource controller)';
    }

    // DataTables Ajax handler
    public function getData()
    {
        try {
            $db = \Config\Database::connect();
            $request = service('request');

            // DataTables parameters
            $draw = intval($request->getPost('draw'));
            $start = intval($request->getPost('start'));
            $length = intval($request->getPost('length'));

            // Filtering (tambahkan filter lain jika perlu)
            $filterStatus = $request->getPost('filterStatus');
            $filterPeriode = $request->getPost('filterPeriode');
            /** @var BaseBuilder $builder */
            $builder = \Config\Database::connect()->table('customer_invoices ci');
            $builder->select('ci.*, ci.payment_button_used, ci.payment_button_used_at, ci.next_payment_available_date,
                         COALESCE(c.nama_pelanggan, CONCAT("Customer ", ci.customer_id)) as customer_name, 
                         COALESCE(c.nomor_layanan, ci.invoice_no, CONCAT("INV-", ci.id)) as service_number, 
                         ls.name as server_name, pp.name as package_name,
                         prl.payment_method as log_payment_method,
                         prl.payment_gateway as log_payment_gateway,
                         prl.payment_code as log_payment_code,
                         prl.transaction_id as log_transaction_id,
                         prl.status as log_status,
                         prl.created_at as log_created_at,
                         pt.transaction_id as payment_transaction_id,
                         pt.payment_code as payment_transaction_payment_code,
                         pt.status as payment_transaction_status');
            $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
            $builder->join('lokasi_server ls', 'ls.id_lokasi = ci.server', 'left');
            $builder->join('package_profiles pp', 'pp.id = c.id_paket', 'left');
            $builder->join('(SELECT invoice_id, payment_method, payment_gateway, payment_code, transaction_id, status, created_at, 
                           ROW_NUMBER() OVER (PARTITION BY invoice_id ORDER BY created_at DESC) as rn 
                           FROM payment_request_logs) prl', 'prl.invoice_id = ci.id AND prl.rn = 1', 'left');
            $builder->join('payment_transactions pt', 'pt.invoice_id = ci.id', 'left');

            if ($filterStatus && $filterStatus != '0') {
                $builder->where('ci.status', $filterStatus);
            }
            if ($filterPeriode && $filterPeriode != '0') {
                $builder->where('ci.periode', $filterPeriode);
            }

            $total = $builder->countAllResults(false);
            $data = $builder->orderBy('ci.periode', 'desc')->limit($length, $start)->get()->getResultArray();
            $result = [];
            $no = $start + 1;
            foreach ($data as $row) {
                $id = htmlspecialchars($row['id']);
                $invoice_no = htmlspecialchars($row['invoice_no'] ?? '');
                $service_number = isset($row['service_number']) ? htmlspecialchars($row['service_number']) : '';

                // Add prorated indicator to package field
                $package = $row['package'] ?? '';
                $isProrata = isset($row['is_prorata']) && $row['is_prorata'] == 1;
                if ($isProrata) {
                    $package .= ' <span class="badge bg-info ms-1">PRORATA</span>';
                }

                // Tentukan status pembayaran dalam bentuk teks
                $statusText = 'BELUM LUNAS';
                if (isset($row['status']) && (strtolower($row['status']) === 'paid' || strtolower($row['status']) === 'lunas')) {
                    $statusText = 'LUNAS';
                }

                // Cek status tombol bayar - apakah bisa diklik atau tidak
                $canPayment = true;
                $paymentDisabledReason = '';
                $currentDate = date('Y-m-d');

                // Jika tombol sudah digunakan, cek apakah sudah bisa aktif lagi
                if (isset($row['payment_button_used']) && $row['payment_button_used'] == 1) {
                    if (isset($row['next_payment_available_date']) && $row['next_payment_available_date']) {
                        if ($currentDate < $row['next_payment_available_date']) {
                            $canPayment = false;
                            $paymentDisabledReason = 'Tombol akan aktif pada: ' . date('d M Y', strtotime($row['next_payment_available_date']));
                        }
                    }
                }

                // Jika invoice sudah lunas, disable tombol
                if ($statusText === 'LUNAS') {
                    $canPayment = false;
                    $paymentDisabledReason = 'Invoice sudah lunas';
                }

                $paymentButton = '';
                if ($canPayment) {
                    $paymentButton = '<button class="btn btn-action btn-info payInvoice" title="Pembayaran" data-id="' . $id . '" data-customer_id="' . ($row['customer_id'] ?? '') . '" data-invoice_no="' . $invoice_no . '" data-bill="' . ($row['bill'] ?? 0) . '" data-package="' . ($row['package'] ?? '') . '" data-status="' . $row['status'] . '" data-customer="' . ($row['customer_name'] ?? '') . '" data-service_no="' . $service_number . '" data-periode="' . $row['periode'] . '"><i class="bx bx-money"></i></button>';
                } else {
                    $paymentButton = '<button class="btn btn-action btn-secondary" title="' . $paymentDisabledReason . '" disabled><i class="bx bx-money"></i></button>';
                }

                $action = '<div class="action-buttons">'
                    . '<button class="btn btn-sm btn-info payInvoice" title="View" data-id="' . $id . '"><i class="bx bx-show"></i></button>'
                    . '</div>';

                $result[] = [
                    'action' => $action,
                    'DT_RowIndex' => $no,
                    'id' => $row['id'],
                    'customer_id' => $row['customer_id'] ?? '',
                    'customer_name' => $row['customer_name'] ?? '',
                    'package' => $row['package_name'] ?? $package,
                    'invoice_no' => $row['invoice_no'] ?? '',
                    'periode' => $row['periode'],
                    'payment_method' => $row['log_payment_method'] ?? $row['payment_method'] ?? '-',
                    'payment_id' => $row['payment_transaction_id'] ?? $row['log_transaction_id'] ?? $row['transaction_id'] ?? $row['payment_reference'] ?? '-',
                    'bill' => $row['bill'],
                    'status' => $row['status'],
                    'payment_status' => $row['payment_transaction_status'] ?? $row['log_status'] ?? '-',
                    'paid_amount' => $row['paid_amount'] ?? 0,
                    'payment_date' => $row['payment_date'] ?? null,
                ];
                $no++;
            }
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Invoice getData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading invoice data: ' . $e->getMessage()
            ]);
        }
    } // Get invoice history for a customer/service (for DataTables) - OPTIMIZED
    public function getHistory($id = null)
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'ID pelanggan/layanan wajib diisi.'
            ]);
        }

        $db = \Config\Database::connect();
        $request = service('request');

        // DataTables parameters - handle both GET and POST
        $draw = intval($request->getGet('draw') ?: $request->getPost('draw') ?: 1);
        $start = intval($request->getGet('start') ?: $request->getPost('start') ?: 0);
        $length = intval($request->getGet('length') ?: $request->getPost('length') ?: 10);

        // Filtering
        $filterStatus = $request->getGet('filterStatus') ?: $request->getPost('filterStatus');
        $filterPeriode = $request->getGet('filterPeriode') ?: $request->getPost('filterPeriode');

        try {
            // ✅ PERFORMANCE OPTIMIZATION: Optimized query with proper indexing hints
            $builder = \Config\Database::connect()->table('customer_invoices ci');
            $builder->select('ci.id, ci.invoice_no, ci.periode, ci.bill, ci.additional_fee, ci.discount, ci.arrears, ci.status, ci.package, ci.due_date, c.nama_pelanggan as customer_name');
            $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
            $builder->where('ci.customer_id', $id);

            // Apply filters
            if ($filterStatus && $filterStatus != '0' && $filterStatus != '') {
                if ($filterStatus === 'paid') {
                    $builder->where('ci.status', 'paid');
                } elseif ($filterStatus === 'unpaid') {
                    $builder->where('ci.status !=', 'paid');
                }
            }

            if ($filterPeriode && $filterPeriode != '0' && $filterPeriode != '') {
                $builder->where('ci.periode', $filterPeriode);
            }

            // ✅ Count total first (more efficient)
            $total = $builder->countAllResults(false);

            // ✅ Add efficient ordering and pagination
            $data = $builder->orderBy('ci.periode', 'DESC')
                ->orderBy('ci.created_at', 'DESC')
                ->limit($length, $start)
                ->get()
                ->getResultArray();

            // Debug log for monitoring
            if (ENVIRONMENT === 'development') {
                log_message('info', "Invoice History - Customer: {$id}, Total: {$total}, Returned: " . count($data));
            }

            // ✅ Efficient data formatting
            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                $result[] = [
                    'DT_RowIndex' => $no,
                    'order' => $no,
                    'customer_name' => $row['customer_name'] ?? '',
                    'periode' => $row['periode'] ?? '',
                    'package' => $row['package'] ?? '',
                    'additional_fee' => (int) ($row['additional_fee'] ?? 0),
                    'discount' => (int) ($row['discount'] ?? 0),
                    'arrears' => (int) ($row['arrears'] ?? 0),
                    'bill' => (int) ($row['bill'] ?? 0),
                    'status' => $row['status'] ?? 'unpaid',
                    'due_date' => $row['due_date'] ?? '',
                    'invoice_no' => $row['invoice_no'] ?? '',
                ];
                $no++;
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Invoice History Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Terjadi kesalahan saat memuat data riwayat pembayaran.',
                'debug' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ]);
        }
    }
    public function generateInvoice()
    {
        // Pastikan customer_slug dan jumlah tagihan yang akan di-generate sudah di-set
        $customer_slug = $this->request->getPost('customer_slug');
        $month = $this->request->getPost('month');

        if ($customer_slug && $month) {
            // Ambil data pelanggan berdasarkan customer_slug
            $customer = $this->customerModel->find($customer_slug);

            if ($customer) {
                // Logika untuk generate invoice
                for ($i = 0; $i < $month; $i++) {
                    // Buat invoice baru
                    $data_invoice = [
                        'customer_id' => $customer['id_customers'],
                        'periode' => date('Y-m', strtotime("+$i month")),
                        'amount' => $this->calculateInvoiceAmount($customer), // Fungsi untuk menghitung jumlah tagihan
                        'status' => 'unpaid',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    // Simpan invoice ke database
                    $this->invoiceModel->insert($data_invoice);
                }

                return $this->response->setJSON(['status' => 'success', 'message' => 'Invoice berhasil dibuat.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Pelanggan tidak ditemukan.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        }
    }

    // Fungsi untuk menghitung jumlah tagihan
    private function calculateInvoiceAmount($customer)
    {
        // Logika untuk menghitung jumlah tagihan berdasarkan paket dan biaya lainnya
        $base_amount = 100000; // Contoh jumlah dasar
        $additional_fee = 0; // Biaya tambahan jika ada

        // Misalnya, jika pelanggan memiliki paket tertentu
        if (isset($customer['id_paket'])) {
            $paket = $this->packageModel->find($customer['id_paket']);
            if ($paket) {
                $base_amount = $paket->harga;
            }
        }
        return $base_amount + $additional_fee;
    }

    /**
     * Hitung biaya tambahan untuk customer
     */
    private function calculateAdditionalFees($customer)
    {
        $total_additional_fee = 0;

        // Gunakan relasi customer_biaya_tambahan yang baru
        $customerBiayaTambahanModel = model('CustomerBiayaTambahanModel');
        $customerId = $customer['id_customers'] ?? $customer['customer_id'] ?? null;

        if ($customerId) {
            $total_additional_fee = $customerBiayaTambahanModel->getTotalBiayaTambahanByCustomer($customerId);
        }

        // Fallback ke method lama jika tidak ada data di tabel relasi
        if ($total_additional_fee == 0 && !empty($customer['additional_fee_id'])) {
            $biayaTambahanModel = model('BiayaTambahanModel');

            // Parse additional_fee_id jika berupa string JSON atau comma-separated
            $additional_fee_ids = [];
            if (is_string($customer['additional_fee_id'])) {
                // Coba parse sebagai JSON dulu
                $parsed = json_decode($customer['additional_fee_id'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $additional_fee_ids = $parsed;
                } else {
                    // Fallback ke comma-separated
                    $additional_fee_ids = explode(',', $customer['additional_fee_id']);
                }
            } elseif (is_array($customer['additional_fee_id'])) {
                $additional_fee_ids = $customer['additional_fee_id'];
            }

            // Ambil total biaya tambahan
            foreach ($additional_fee_ids as $fee_id) {
                $fee_id = trim($fee_id);
                if (!empty($fee_id) && is_numeric($fee_id)) {
                    $biaya = $biayaTambahanModel->find($fee_id);
                    if ($biaya && $biaya['status'] == 1) {
                        $total_additional_fee += (float) $biaya['jumlah'];
                    }
                }
            }
        }

        return $total_additional_fee;
    }

    /**
     * Handle payment confirmation for invoices
     */
    public function paymentConfirmation()
    {
        try {
            $invoiceModel = $this->invoiceModel;            // Get form data
            $invoice_no = $this->request->getPost('invoice_no');
            $inputPayment = $this->request->getPost('inputPayment');
            $paymentMethod = $this->request->getPost('paymentMethod');
            $bank = $this->request->getPost('bank');
            $receiver = $this->request->getPost('receiver');
            $arrears = $this->request->getPost('arrears');
            $paymentDate = $this->request->getPost('paymentDate');

            // Debug log
            log_message('info', 'Payment confirmation attempt - Invoice: ' . $invoice_no . ', Payment: ' . $inputPayment . ', Payment Date: ' . $paymentDate);
            $arrears = $this->request->getPost('arrears') ?? 0;
            // Validate required fields
            if (!$invoice_no || !$inputPayment || !$paymentDate) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Nomor invoice, jumlah pembayaran, dan tanggal pembayaran wajib diisi.'
                ]);
            }

            // Validate payment date format
            if (!DateTime::createFromFormat('Y-m-d', $paymentDate)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Format tanggal pembayaran tidak valid. Gunakan format YYYY-MM-DD.'
                ]);
            }

            // Validate payment method
            if (!$paymentMethod) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Metode pembayaran wajib dipilih.'
                ]);
            }            // Find the invoice
            $invoice = $invoiceModel->where('invoice_no', $invoice_no)->first();
            if (!$invoice) {
                log_message('error', 'Invoice not found: ' . $invoice_no);
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Invoice tidak ditemukan.'
                ]);
            }

            // Debug log the invoice data
            log_message('info', 'Found invoice: ' . json_encode($invoice));

            // Clean and convert payment amount
            $paymentAmount = (int) str_replace(['.', ','], '', $inputPayment);
            $invoiceBill = (int) ($invoice['bill'] ?? 0);
            $invoiceArrears = (int) ($invoice['arrears'] ?? 0);
            $totalBill = $invoiceBill + $invoiceArrears;

            if ($paymentAmount <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Jumlah pembayaran harus lebih besar dari 0.'
                ]);
            }

            $updateData = [
                'payment_date' => $paymentDate . ' 00:00:00', // For internal tracking
                'paid_at' => date('Y-m-d H:i:s'), // waktu konfirmasi aktual
                'payment_method' => $paymentMethod,
                'paid_amount' => $paymentAmount,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Pembayaran bulan berjalan tetap bisa dikonfirmasi walaupun ada tunggakan
            if ($paymentAmount >= $invoiceBill) {
                // Jika pembayaran >= tagihan bulan ini, status invoice bulan ini jadi paid
                $updateData['status'] = 'paid';
                // Sisa pembayaran digunakan untuk mengurangi tunggakan
                $sisaPembayaran = $paymentAmount - $invoiceBill;
                $updateData['arrears'] = max(0, $invoiceArrears - $sisaPembayaran);
            } else {
                // Jika pembayaran < tagihan bulan ini, status tetap unpaid
                $updateData['status'] = 'unpaid';
                // Tunggakan bertambah karena tagihan bulan ini belum lunas
                $updateData['arrears'] = $invoiceArrears + ($invoiceBill - $paymentAmount);
            }

            log_message('info', 'UpdateData sebelum update invoice: ' . json_encode($updateData));
            $resultUpdate = $invoiceModel->update($invoice['id'], $updateData);
            log_message('info', 'Hasil update invoice: ' . json_encode($resultUpdate));
            $updatedInvoice = $invoiceModel->find($invoice['id']);
            log_message('info', 'Invoice setelah update: ' . json_encode($updatedInvoice));

            // Update customer status and due date if payment is complete
            if ($updateData['status'] === 'paid') {
                $this->updateCustomerAfterPayment($invoice['customer_id'], $paymentDate);

                // Send WhatsApp payment confirmation
                log_message('info', 'Attempting to send payment confirmation WhatsApp for invoice: ' . $invoice['id']);
                $whatsappSent = $this->sendPaymentConfirmationWhatsApp($invoice['id']);
                if ($whatsappSent) {
                    log_message('info', 'WhatsApp payment confirmation sent successfully for invoice: ' . $invoice['id']);
                } else {
                    log_message('warning', 'WhatsApp payment confirmation failed or disabled for invoice: ' . $invoice['id']);
                }

                // Tambahkan URL print invoice
                $printInvoiceUrl = base_url('invoices/view/' . $invoice['id']);
            }

            $response = [
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Konfirmasi pembayaran berhasil disimpan.'
            ];
            if (isset($printInvoiceUrl)) {
                $response['print_invoice_url'] = $printInvoiceUrl;
            }
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Payment confirmation error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Resend WhatsApp invoice message
     */
    public function resendWhatsApp($invoiceId)
    {
        try {
            $invoiceModel = new \App\Models\InvoiceModel();
            $customerModel = new \App\Models\CustomerModel();

            // Get invoice data
            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice tidak ditemukan'
                ]);
            }

            // Get customer data
            $customer = $customerModel->find($invoice['customer_id']);
            if (!$customer || empty($customer['telepphone'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Nomor telepon pelanggan tidak tersedia'
                ]);
            }

            // Build invoice message
            $companyData = getCompanyData();
            $message = $this->buildInvoiceMessage($invoice, $customer, $companyData);

            // Send via WhatsApp
            $result = $this->sendWhatsAppMessage($customer['telepphone'], $message);

            if ($result) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Pesan WhatsApp berhasil dikirim'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengirim pesan WhatsApp. Pastikan konfigurasi WhatsApp sudah benar.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Resend WhatsApp error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Build invoice message for WhatsApp
     */
    private function buildInvoiceMessage($invoice, $customer, $companyData)
    {
        $message = "*TAGIHAN INTERNET*\n\n";
        $message .= $companyData['name'] . "\n";
        $message .= $companyData['tagline'] . "\n";
        $message .= $companyData['phone'] . "\n";
        $message .= $companyData['address'] . "\n";
        $message .= $companyData['city'] . "\n\n";

        $message .= "TANGGAL   : " . date('d-m-Y H:i:s') . "\n";
        $message .= "NOPEL     : " . $customer['nomor_layanan'] . "\n";
        $message .= "NAMA      : " . $customer['nama_pelanggan'] . "\n";
        $message .= "TELP      : " . $customer['telepphone'] . "\n";
        $message .= "PAKET     : " . $invoice['package'] . "\n";
        $message .= "PERIODE   : " . strtoupper(date('F Y', strtotime($invoice['periode'] . '-01'))) . "\n";
        $message .= "PEMAKAIAN : " . $invoice['usage_period'] . "\n";
        $message .= "TARIF/BLN : Rp " . number_format($invoice['bill'], 0, ',', '.') . "\n";

        if (!empty($invoice['additional_fee']) && $invoice['additional_fee'] > 0) {
            $message .= "BIAYA LAI : Rp " . number_format($invoice['additional_fee'], 0, ',', '.') . "\n";
        }

        $totalTagihan = $invoice['bill'] + ($invoice['additional_fee'] ?? 0) - ($invoice['discount'] ?? 0);
        $message .= "TOTAL TAG : Rp " . number_format($totalTagihan, 0, ',', '.') . "\n";

        if (!empty($invoice['keterangan'])) {
            $message .= "CATATAN   : " . $invoice['keterangan'] . "\n";
        }

        $message .= "\nPembayaran Online:\n";
        $message .= "Kamu bisa menggunakan QRIS, VIRTUAL AKUN, INDOMARET dan ALFAMART\n";
        $message .= "Kunjungi: " . base_url('public/billing/' . $customer['nomor_layanan']) . "\n";

        $message .= "\nTerima kasih";

        return $message;
    }

    /**
     * Send WhatsApp payment confirmation message
     */
    private function sendPaymentConfirmationWhatsApp($invoiceId)
    {
        try {
            log_message('info', 'sendPaymentConfirmationWhatsApp called for invoice: ' . $invoiceId);

            // Get invoice details with customer info
            $invoiceModel = model('InvoiceModel');
            $customerModel = model('CustomerModel');

            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                log_message('error', 'Invoice not found for WhatsApp notification: ' . $invoiceId);
                return false;
            }

            log_message('info', 'Invoice found: ' . $invoice['invoice_no'] . ' for customer: ' . $invoice['customer_id']);

            $customer = $customerModel->find($invoice['customer_id']);
            if (!$customer || empty($customer['telepphone'])) {
                log_message('info', 'Customer has no phone number for WhatsApp notification: ' . $invoice['customer_id']);
                return false;
            }

            log_message('info', 'Customer phone found: ' . $customer['telepphone']);

            // Check if WhatsApp notification is enabled
            $notifModel = new \App\Models\WhatsappNotifSettingModel();
            $settings = $notifModel->orderBy('id', 'desc')->first();
            if (!$settings || !$settings['notif_payment']) {
                log_message('info', 'WhatsApp payment notifications are disabled in settings');
                return false;
            }

            log_message('info', 'WhatsApp payment notification is enabled');

            // Get payment confirmation template
            $template = $this->getPaymentConfirmationTemplate();
            if (!$template) {
                log_message('error', 'Payment confirmation template not found');
                return false;
            }

            // Get company info
            $companyModel = new \App\Models\CompanyModel();
            $company = $companyModel->first();

            if (!$company) {
                log_message('warning', 'Company data not found, using default name');
                $companyName = 'Nama Perusahaan';
            } else {
                // Field name is 'name' not 'nama_perusahaan'
                $companyName = $company['name'] ?? 'Nama Perusahaan';
                log_message('info', 'Company name loaded: ' . $companyName);
            }

            // Calculate PPN and other fees
            $tarif = (float)($invoice['bill'] ?? 0);
            $ppnRate = 0; // Default 0%, can be configured
            $totalPpn = $tarif * ($ppnRate / 100);
            $diskon = (float)($invoice['discount'] ?? 0);
            $biayaLain = (float)($invoice['additional_fee'] ?? 0);
            $totalTagihan = $tarif + $totalPpn - $diskon + $biayaLain;

            // Replace template variables
            $message = str_replace(
                ['{company}', '{customer}', '{no_invoice}', '{tanggal}', '{total}', '{periode}', '{no_layanan}', '{tunggakan}', '{metode_pembayaran}', '{paket}', '{tarif}', '{ppn}', '{totalppn}', '{diskon}', '{biaya}'],
                [
                    $companyName,
                    $customer['nama_pelanggan'] ?? 'Pelanggan',
                    $invoice['invoice_no'],
                    date('d/m/Y'),
                    'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                    $this->formatPeriode($invoice['periode']),
                    $customer['nomor_layanan'] ?? $invoice['invoice_no'],
                    'Rp 0', // Assuming payment clears all arrears
                    'Manual Payment', // Default payment method
                    $invoice['package'] ?? 'Paket',
                    'Rp ' . number_format($tarif, 0, ',', '.'),
                    $ppnRate . '%',
                    'Rp ' . number_format($totalPpn, 0, ',', '.'),
                    'Rp ' . number_format($diskon, 0, ',', '.'),
                    'Rp ' . number_format($biayaLain, 0, ',', '.')
                ],
                $template
            );

            log_message('info', 'Template variables replaced - Company: ' . $companyName . ', Customer: ' . ($customer['nama_pelanggan'] ?? 'N/A'));
            log_message('info', 'Payment confirmation message prepared, length: ' . strlen($message));

            // Log message to database
            $messageLogModel = new \App\Models\WhatsappMessageLogModel();
            $logId = $messageLogModel->insert([
                'customer_id' => $invoice['customer_id'],
                'customer_name' => $customer['nama_pelanggan'],
                'phone_number' => $customer['telepphone'],
                'template_type' => 'bill_paid',
                'message_content' => $message,
                'notification_type' => 'payment_confirmation',
                'status' => 'pending',
                'invoice_id' => $invoiceId
            ]);

            log_message('info', 'Message logged to database with ID: ' . $logId);

            // Send via WhatsApp API
            $sendResult = $this->sendWhatsAppMessage($customer['telepphone'], $message);

            // Update message log status
            if ($sendResult) {
                $messageLogModel->update($logId, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
                log_message('info', 'WhatsApp message sent successfully to: ' . $customer['telepphone']);
            } else {
                $messageLogModel->update($logId, ['status' => 'failed']);
                log_message('error', 'WhatsApp message failed to send to: ' . $customer['telepphone']);
            }

            return $sendResult;
        } catch (\Exception $e) {
            log_message('error', 'Failed to send payment confirmation WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment confirmation template
     */
    private function getPaymentConfirmationTemplate()
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT bill_paid FROM whatsapp_templates WHERE id = 1");
            $result = $query->getRow();

            return $result->bill_paid ?? null;
        } catch (\Exception $e) {
            log_message('error', 'Failed to get payment confirmation template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format periode YYYY-MM menjadi "Bulan YYYY"
     */
    private function formatPeriode($periode)
    {
        if (!$periode || !preg_match('/^\d{4}-\d{2}$/', $periode)) {
            return $periode;
        }

        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $parts = explode('-', $periode);
        $year = $parts[0];
        $month = $parts[1];

        return ($monthNames[$month] ?? $month) . ' ' . $year;
    }

    /**
     * Send WhatsApp message
     */
    private function sendWhatsAppMessage($phoneNumber, $message)
    {
        try {
            // Get WhatsApp device configuration
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $device = $deviceModel->orderBy('id', 'desc')->first();

            if (!$device) {
                log_message('error', 'No WhatsApp device configured');
                return false;
            }            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Check if demo mode is enabled
            $whatsappConfig = new \Config\WhatsApp();
            if (isset($whatsappConfig->demoMode) && $whatsappConfig->demoMode) {
                log_message('info', "WhatsApp DEMO: Payment confirmation would be sent to {$phoneNumber}");
                log_message('info', "WhatsApp DEMO Message: " . substr($message, 0, 100) . "...");
                return true; // Simulate success in demo mode
            }
            // Ambil API URL dari database settings
            $apiUrl = '';
            try {
                $db = \Config\Database::connect();
                $query = $db->query("SELECT value FROM settings WHERE name = 'whatsapp_api_url' LIMIT 1");
                $row = $query->getRow();
                if ($row && !empty($row->value)) {
                    $apiUrl = $row->value;
                }
            } catch (\Exception $e) {
                log_message('error', 'Gagal mengambil API URL WhatsApp dari database: ' . $e->getMessage());
            }
            if (!$apiUrl) {
                $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.kimonet.my.id';
                $apiUrl = $baseUrl . '/send-message'; // fallback default
            }
            $data = [
                'api_key' => $device['api_key'],
                'sender' => $device['number'],
                'number' => $phoneNumber,
                'message' => $message
            ];

            // Use GET request like the working API format
            $queryParams = http_build_query($data);
            $fullUrl = $apiUrl . '?' . $queryParams;

            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                log_message('error', "WhatsApp API CURL error: {$curlError}");
                return false;
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['status']) && $result['status'] === true) {
                    log_message('info', "WhatsApp payment confirmation sent successfully to {$phoneNumber}");
                    return true;
                } else {
                    log_message('error', "WhatsApp API error response: " . ($result['msg'] ?? $result['message'] ?? 'Unknown error'));
                }
            }
            log_message('error', "WhatsApp API error: HTTP {$httpCode}, Response: {$response}");
            return false;
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if missing
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        return $phone;
    }

    /**
     * Delete an invoice
     * DELETE: invoices/{id}
     */
    public function processPaymentButton()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'ID tagihan tidak valid.',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // Check if invoice exists
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Tagihan tidak ditemukan.',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(404);
        }

        // Check if payment button is still available
        $currentDate = date('Y-m-d');
        if ($invoice['payment_button_used'] == 1) {
            if ($invoice['next_payment_available_date'] && $currentDate < $invoice['next_payment_available_date']) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Tombol Tidak Aktif',
                    'message' => 'Tombol pembayaran akan aktif kembali pada tanggal ' . date('d M Y', strtotime($invoice['next_payment_available_date'])),
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }
        }

        // Check if invoice is already paid
        if ($invoice['status'] === 'paid' || $invoice['status'] === 'lunas') {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Sudah Lunas',
                'message' => 'Invoice ini sudah dibayar.',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // Update payment button status
        $nextMonth = date('Y-m-01', strtotime('+1 month')); // Tanggal 1 bulan depan
        $updateData = [
            'payment_button_used' => 1,
            'payment_button_used_at' => date('Y-m-d H:i:s'),
            'next_payment_available_date' => $nextMonth
        ];

        $this->invoiceModel->update($id, $updateData);

        return $this->response->setJSON([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Tombol pembayaran telah digunakan. Akan aktif kembali pada ' . date('d M Y', strtotime($nextMonth)),
            'next_available_date' => date('d M Y', strtotime($nextMonth)),
            'csrfHash' => csrf_hash()
        ]);
    }

    public function delete($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request', 400);
        }

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'ID tagihan tidak valid.',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // Check if invoice exists
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Tagihan tidak ditemukan.',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(404);
        }

        // Check if invoice is already paid
        if ($invoice['status'] === 'paid' || $invoice['status'] === 'lunas') {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Tidak dapat menghapus tagihan yang sudah dibayar.',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        // Delete the invoice
        $this->invoiceModel->delete($id);

        return $this->response->setJSON([
            'status' => 'success',
            'title' => 'Berhasil',
            'message' => 'Tagihan berhasil dihapus.',
            'csrfHash' => csrf_hash()
        ]);
    }
    /**
     * Delete selected invoice by id (POST: id)
     */
    public function deleteSelected()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID tagihan tidak valid.'
            ]);
        }
        $invoiceModel = model('InvoiceModel');
        $deleted = $invoiceModel->delete($id);
        if ($deleted) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Tagihan berhasil dihapus.',
                'deleted' => $id
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus tagihan. ID tidak ditemukan.'
            ]);
        }
    }
    /**
     * Delete all invoices for a given period (POST: periode)
     */
    public function deleteAll()
    {
        $periode = $this->request->getPost('periode');
        if (!$periode) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Periode wajib diisi.'
            ]);
        }
        $invoiceModel = model('InvoiceModel');
        $deleted = $invoiceModel->where('periode', $periode)->delete();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Semua tagihan untuk periode ' . $periode . ' berhasil dihapus.',
            'deleted' => $deleted
        ]);
    }
    /**
     * Create a prorated invoice for a new customer
     * @param array $customer Customer data
     * @param string $periode Billing period (Y-m format)
     * @return array Result with success status and invoice data or error message
     */
    public function createProrataInvoice($customer, $periode)
    {
        try {
            $db = \Config\Database::connect();
            $invoiceModel = model('InvoiceModel');

            // Get package information
            $paket = \Config\Database::connect()->table('package_profiles')->where('id', $customer['id_paket'])->get()->getRowArray();
            if (!$paket) {
                return ['success' => false, 'message' => 'Paket tidak ditemukan untuk pelanggan ini.'];
            }
            $fullPrice = $paket['price'];
            $packageName = $paket['name'] . ' | ' . $paket['bandwidth_profile']; // Calculate prorated amount based on installation date
            $installationDate = $customer['tgl_pasang'] ?? date('Y-m-d');
            $prorataData = $this->calculateProrataAmount($installationDate, $periode, $fullPrice);

            if ($prorataData['days_remaining'] <= 0) {
                return ['success' => false, 'message' => 'Tidak ada hari tersisa untuk periode ini.'];
            }

            // Generate invoice number
            $invoice_no = 'INV-PRORATA-' . date('Ymd') . '-' . $customer['id_customers'] . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

            // Create invoice data
            $invoiceData = [
                'customer_id' => $customer['id_customers'],
                'invoice_no' => $invoice_no,
                'periode' => $periode,
                'bill' => $prorataData['prorata_amount'],
                'arrears' => 0,
                'status' => 'unpaid',
                'package' => $packageName . ' (Prorata ' . $prorataData['days_remaining'] . ' hari)',
                'additional_fee' => 0,
                'server' => $customer['id_lokasi_server'] ?? null,
                'due_date' => $customer['tgl_tempo'] ?? null,
                'district' => $customer['district'] ?? null,
                'village' => $customer['village'] ?? null,
                'is_prorata' => 1,
                'prorata_days' => $prorataData['days_remaining'],
                'prorata_start_date' => $installationDate,
                'full_amount' => $fullPrice,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $invoiceModel->insert($invoiceData);

            if ($result) {
                return [
                    'success' => true,
                    'invoice' => $invoiceData,
                    'prorata_info' => $prorataData
                ];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan tagihan prorata ke database.'];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in createProrataInvoice: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    /**
     * Calculate prorated amount based on installation date and billing period
     * @param string $installationDate Installation date (Y-m-d format)
     * @param string $periode Billing period (Y-m format)
     * @param float $fullPrice Full monthly price
     * @return array Calculation results
     */
    public function calculateProrataAmount($installationDate, $periode, $fullPrice)
    {
        // Parse dates
        $installDate = new \DateTime($installationDate);
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // First day of the billing month
        $monthStart = new \DateTime("{$year}-{$month}-01");

        // Last day of the billing month
        $monthEnd = new \DateTime($monthStart->format('Y-m-t'));

        // If installation date is before the billing month, start from month start
        if ($installDate < $monthStart) {
            $installDate = $monthStart;
        }

        // If installation date is after the billing month, no billing for this month
        if ($installDate > $monthEnd) {
            return [
                'days_in_month' => $monthEnd->format('j'),
                'days_remaining' => 0,
                'prorata_amount' => 0,
                'calculation' => 'Installation date is after billing month'
            ];
        }

        // Calculate days
        $daysInMonth = (int)$monthEnd->format('j');
        $dayOfInstallation = (int)$installDate->format('j');
        $daysRemaining = $daysInMonth - $dayOfInstallation + 1; // +1 to include installation day

        // Calculate prorated amount
        $dailyRate = $fullPrice / $daysInMonth;
        $prorataAmount = round($dailyRate * $daysRemaining);

        return [
            'days_in_month' => $daysInMonth,
            'days_remaining' => $daysRemaining,
            'daily_rate' => $dailyRate,
            'prorata_amount' => $prorataAmount,
            'installation_date' => $installationDate,
            'billing_period' => $periode,
            'calculation' => "({$fullPrice} / {$daysInMonth}) * {$daysRemaining} = {$prorataAmount}"
        ];
    }

    /**
     * Display the specified invoice
     */
    public function view($id = null)
    {
        log_message('debug', '==== INVOICES::VIEW CALLED ==== ID: ' . $id);

        if (!$id) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice tidak ditemukan');
        }

        $db = \Config\Database::connect();

        // Get invoice with customer data using LEFT JOIN
        $builder = \Config\Database::connect()->table('customer_invoices ci');
        $builder->select('ci.*, c.nama_pelanggan, c.nomor_layanan, c.address, c.telepphone, c.village, c.district');
        $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
        $builder->where('ci.id', $id);
        $invoice = $builder->get()->getRowArray();

        if (!$invoice) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice tidak ditemukan');
        }

        // Get customer data with fallback
        $customer = [
            'nama_pelanggan' => $invoice['nama_pelanggan'] ?? 'Nama Pelanggan',
            'nomor_layanan' => $invoice['nomor_layanan'] ?? 'N/A',
            'address' => $invoice['address'] ?? 'Alamat tidak tersedia',
            'telepphone' => $invoice['telepphone'] ?? 'Tidak tersedia',
            'village' => $invoice['village'] ?? 'Desa Lebo',
            'district' => $invoice['district'] ?? 'Gringsing',
            'kabupaten' => 'Batang' // Default value
        ];

        // Add customer info to invoice array for easier access in view
        $invoice['customer_name'] = $customer['nama_pelanggan'];
        $invoice['customer_no'] = $customer['nomor_layanan'];
        $invoice['customer_address'] = $customer['address'];
        $invoice['customer_phone'] = $customer['telepphone'];

        // Get company data
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();

        if (!$company) {
            $company = [
                'name' => 'PT. KIMONET DIGITAL SYNERGY',
                'tagline' => 'Dari Kita, Untuk Konektivitas Nusantara',
                'address' => 'Dusun Lebo Kulon Rt02/rw08',
                'city' => 'Batang',
                'phone' => '085183112127',
                'website' => 'www.kimonet.my.id'
            ];
        }

        // Get active bank accounts
        $bankModel = new \App\Models\BankModel();
        $activeBanks = $bankModel->where('is_active', 1)->findAll();

        // Get payment history for this invoice
        $paymentTransactionModel = model('PaymentTransactionModel');
        $paymentHistory = $paymentTransactionModel
            ->where('invoice_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Debug log
        log_message('debug', '==== PAYMENT HISTORY DEBUG ====');
        log_message('debug', 'Invoice ID: ' . $id);
        log_message('debug', 'Payment History Variable Type: ' . gettype($paymentHistory));
        log_message('debug', 'Payment History Count: ' . count($paymentHistory));
        log_message('debug', 'Payment History JSON: ' . json_encode($paymentHistory));
        if (!empty($paymentHistory)) {
            foreach ($paymentHistory as $payment) {
                log_message('debug', 'Payment Code: ' . ($payment['payment_code'] ?? 'NULL'));
            }
        }
        log_message('debug', '==== END PAYMENT HISTORY DEBUG ====');

        $viewData = [
            'invoice' => $invoice,
            'customer' => $customer,
            'company' => $company,
            'activeBanks' => $activeBanks,
            'paymentHistory' => $paymentHistory
        ];

        log_message('debug', 'View Data Keys: ' . implode(', ', array_keys($viewData)));
        log_message('debug', 'paymentHistory in viewData: ' . (isset($viewData['paymentHistory']) ? 'YES' : 'NO'));

        return view('invoice/view', $viewData);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf($id = null)
    {
        if (!$id) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice tidak ditemukan');
        }

        // Get invoice data (same as view method)
        $db = \Config\Database::connect();

        $builder = \Config\Database::connect()->table('customer_invoices ci');
        $builder->select('ci.*, c.nama_pelanggan, c.nomor_layanan, c.address, c.telepphone, c.village, c.district');
        $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
        $builder->where('ci.id', $id);
        $invoice = $builder->get()->getRowArray();

        if (!$invoice) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice tidak ditemukan');
        }

        // Get customer data with fallback
        $customer = [
            'nama_pelanggan' => $invoice['nama_pelanggan'] ?? 'Nama Pelanggan',
            'nomor_layanan' => $invoice['nomor_layanan'] ?? 'N/A',
            'address' => $invoice['address'] ?? 'Alamat tidak tersedia',
            'telepphone' => $invoice['telepphone'] ?? 'Tidak tersedia',
            'village' => $invoice['village'] ?? 'Desa Lebo',
            'district' => $invoice['district'] ?? 'Gringsing',
            'kabupaten' => 'Batang'
        ];

        // Get company data
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first() ?? [
            'name' => 'PT. KIMONET DIGITAL SYNERGY',
            'tagline' => 'Dari Kita, Untuk Konektivitas Nusantara',
            'address' => 'Dusun Lebo Kulon Rt02/rw08',
            'city' => 'Batang',
            'phone' => '085183112127',
            'website' => 'www.kimonet.my.id'
        ];

        // Get active bank accounts
        $bankModel = new \App\Models\BankModel();
        $activeBanks = $bankModel->where('is_active', 1)->findAll();

        // Generate PDF using mPDF
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        // Get HTML content
        $html = view('invoices/view', [
            'invoice' => $invoice,
            'customer' => $customer,
            'company' => $company,
            'activeBanks' => $activeBanks
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'Invoice-' . $invoice['invoice_no'] . '.pdf';
        $mpdf->Output($filename, 'D'); // 'D' = download
    }

    /**
     * Download thermal receipt
     */
    public function downloadThermal($id = null)
    {
        if (!$id) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice tidak ditemukan');
        }

        // Get invoice data (same as view method)
        $db = \Config\Database::connect();

        $builder = \Config\Database::connect()->table('customer_invoices ci');
        $builder->select('ci.*, c.nama_pelanggan, c.nomor_layanan, c.address, c.telepphone, c.village, c.district');
        $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
        $builder->where('ci.id', $id);
        $invoice = $builder->get()->getRowArray();

        if (!$invoice) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice tidak ditemukan');
        }

        // Get customer data with fallback
        $customer = [
            'nama_pelanggan' => $invoice['nama_pelanggan'] ?? 'Nama Pelanggan',
            'nomor_layanan' => $invoice['nomor_layanan'] ?? 'N/A',
            'address' => $invoice['address'] ?? 'Alamat tidak tersedia',
            'telepphone' => $invoice['telepphone'] ?? 'Tidak tersedia',
            'village' => $invoice['village'] ?? 'Desa Lebo',
            'district' => $invoice['district'] ?? 'Gringsing'
        ];

        // Get company data from database
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->first();

        // If no company data, use empty array
        if (!$company) {
            $company = [];
        }

        return view('invoices/thermal', [
            'invoice' => $invoice,
            'customer' => $customer,
            'company' => $company
        ]);
    }

    /**
     * Print invoice view
     */
    public function print($invoice_no)
    {
        $invoiceModel = $this->invoiceModel;
        $invoice = $invoiceModel
            ->where('invoice_no', $invoice_no)
            ->first();
        if (!$invoice) {
            return 'Invoice tidak ditemukan';
        }
        // Ambil data customer
        $customerModel = model('CustomerModel');
        $customer = $customerModel->find($invoice['customer_id']);

        // Ambil rekening bank aktif
        $bankModel = new \App\Models\BankModel();
        $activeBanks = $bankModel->where('is_active', 1)->findAll();

        // Siapkan data untuk view
        $data = [
            'invoice' => (object) [
                'invoice_no' => $invoice['invoice_no'],
                'periode' => $invoice['periode'],
                'bill' => (float)($invoice['bill'] ?? 0),
                'additional_fee' => (float)($invoice['additional_fee'] ?? 0),
                'discount' => (float)($invoice['discount'] ?? 0),
                'package' => $invoice['package'],
                'status' => $invoice['status'],
                'paid_at' => $invoice['updated_at'],
                'customer_no' => $customer['nomor_layanan'] ?? '-',
                'customer_name' => $customer['nama_pelanggan'] ?? '-',
                'customer_address' => $customer['address'] ?? '-',
                'customer_phone' => $customer['telepphone'] ?? '-',
                'usage_period' => $this->getUsagePeriod($invoice['periode']),
                'keterangan' => $invoice['keterangan'] ?? '-',
                'payment_url' => base_url($customer['nomor_layanan'] ?? '')
            ],
            'activeBanks' => $activeBanks
        ];
        return view('invoices/print', $data);
    }

    // Helper untuk periode pemakaian
    private function getUsagePeriod($periode)
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $periode, $m)) {
            $year = (int)$m[1];
            $month = (int)$m[2];
            $start = date('d F Y', strtotime("$year-$month-10"));
            $end = date('d F Y', strtotime("$year-$month-10 +1 month -1 day"));
            return "$start - $end";
        }
        return '-';
    }

    /**
     * Multi Payment Handler
     * Proses pembayaran banyak invoice sekaligus
     * POST: invoices (array of invoice_no), inputPayment (array of nominal), paymentMethod, bank, receiver
     */
    public function multiPayment()
    {
        try {
            $invoiceModel = $this->invoiceModel;
            $invoiceNos = $this->request->getPost('invoices'); // array of invoice_no
            $inputPayments = $this->request->getPost('inputPayment'); // array of nominal
            $paymentMethod = $this->request->getPost('paymentMethod');
            $bank = $this->request->getPost('bank');
            $receiver = $this->request->getPost('receiver');

            // Ambil nama bank jika ID dikirim
            $bankName = null;
            if ($bank) {
                $bankModel = new \App\Models\BankModel();
                $bankData = $bankModel->find($bank);
                $bankName = $bankData['nama_bank'] ?? $bankData['name'] ?? $bank;
            }

            if (!is_array($invoiceNos)) {
                $invoiceNos = $invoiceNos ? [$invoiceNos] : [];
            }
            if (!is_array($inputPayments)) {
                $inputPayments = $inputPayments ? [$inputPayments] : [];
            }
            if (count($invoiceNos) !== count($inputPayments)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data invoice atau nominal tidak valid.'
                ]);
            }

            $results = [];
            foreach ($invoiceNos as $idx => $invoice_no) {
                $invoice = $invoiceModel->where('invoice_no', $invoice_no)->first();
                if (!$invoice) {
                    $results[] = [
                        'invoice_no' => $invoice_no,
                        'status' => 'error',
                        'message' => 'Invoice tidak ditemukan.'
                    ];
                    continue;
                }
                $paymentAmount = (int) str_replace(['.', ','], '', $inputPayments[$idx]);
                if ($paymentAmount <= 0) {
                    $results[] = [
                        'invoice_no' => $invoice_no,
                        'status' => 'error',
                        'message' => 'Nominal pembayaran harus lebih dari 0.'
                    ];
                    continue;
                }
                $updateData = [
                    'arrears' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $totalBill = $invoice['bill'] ?? 0;
                if ($paymentAmount >= $totalBill) {
                    $updateData['status'] = 'paid';
                } else {
                    $updateData['status'] = 'partial';
                }
                $invoiceModel->update($invoice['id'], $updateData);
                // Update customer status and due date if payment is complete
                if ($updateData['status'] === 'paid') {
                    $this->updateCustomerAfterPayment($invoice['customer_id'], date('Y-m-d'));
                    $this->sendPaymentConfirmationWhatsApp($invoice['id']);
                }
                $results[] = [
                    'invoice_no' => $invoice_no,
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil diproses.',
                    'bank' => $bankName
                ];
            }
            return $this->response->setJSON([
                'status' => 'success',
                'results' => $results,
                'bank' => $bankName
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update customer status and due date after successful payment
     */
    private function updateCustomerAfterPayment($customerId, $paymentDate)
    {
        try {
            log_message('info', "updateCustomerAfterPayment START - Customer ID: {$customerId}, Payment Date: {$paymentDate}");

            $customerModel = model('CustomerModel');
            $customer = $customerModel->find($customerId);

            if (!$customer) {
                log_message('error', 'Customer not found for payment update: ' . $customerId);
                return false;
            }

            log_message('info', "Customer found - Name: {$customer['nama_pelanggan']}, isolir_status: " . ($customer['isolir_status'] ?? 'NULL'));

            // Calculate new due date (add 1 month from payment date)
            $paymentDateTime = new \DateTime($paymentDate);
            $newDueDate = clone $paymentDateTime;
            $newDueDate->add(new \DateInterval('P1M')); // Add 1 month

            // Update customer data
            $updateCustomerData = [
                'status_tagihan' => 'Lunas',
                'tgl_tempo' => $newDueDate->format('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Check if customer was isolated and needs to be un-isolated
            $wasIsolated = $customer['isolir_status'] == 1;
            log_message('info', "Customer isolation check - wasIsolated: " . ($wasIsolated ? 'YES' : 'NO'));

            if ($wasIsolated) {
                // Add un-isolir data to customer update
                $updateCustomerData['isolir_status'] = 0;
                $updateCustomerData['isolir_date'] = null;
                $updateCustomerData['isolir_reason'] = null;
                log_message('info', "Preparing to un-isolir customer {$customerId}");
            }

            $result = $customerModel->update($customerId, $updateCustomerData);
            log_message('info', "Customer update result: " . ($result ? 'SUCCESS' : 'FAILED'));

            if ($result) {
                log_message('info', "Customer {$customerId} updated after payment - New due date: " . $newDueDate->format('Y-m-d'));

                // If customer was isolated, perform automatic un-isolir
                if ($wasIsolated) {
                    log_message('info', "Customer {$customerId} was isolated, performing un-isolir on MikroTik");
                    $unIsolirResult = $this->performAutoUnIsolir($customer);
                    log_message('info', "performAutoUnIsolir result: " . ($unIsolirResult ? 'SUCCESS' : 'FAILED'));
                } else {
                    log_message('info', "Customer {$customerId} was NOT isolated, skipping MikroTik un-isolir");
                }

                return true;
            } else {
                log_message('error', "Failed to update customer {$customerId} after payment");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating customer after payment: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Perform automatic un-isolir after successful payment
     */
    private function performAutoUnIsolir($customer)
    {
        try {
            // Check if customer has PPPoE username and router
            if (empty($customer['pppoe_username']) || empty($customer['id_lokasi_server'])) {
                log_message('warning', "Customer {$customer['id_customers']} cannot be un-isolated: missing PPPoE username or router");
                return false;
            }

            // Get router data
            $routerModel = new \App\Models\ServerLocationModel();
            $router = $routerModel->find($customer['id_lokasi_server']);

            if (!$router) {
                log_message('error', "Router not found for customer {$customer['id_customers']} un-isolir");
                return false;
            }

            // Execute un-isolir in MikroTik
            $result = $this->executeMikrotikUnIsolir($router, $customer['pppoe_username']);

            if ($result['success']) {
                // Log successful auto un-isolir
                $this->logAutoIsolirAction($customer['id_customers'], $customer['id_lokasi_server'], 'auto_unisolir', 'Automatic un-isolir after manual payment confirmation', 'success');
                log_message('info', "Auto un-isolir successful for customer {$customer['id_customers']} ({$customer['nama_pelanggan']})");
                return true;
            } else {
                // Log failed auto un-isolir
                $this->logAutoIsolirAction($customer['id_customers'], $customer['id_lokasi_server'], 'auto_unisolir', 'Automatic un-isolir after manual payment confirmation', 'failed', $result['message']);
                log_message('error', "Auto un-isolir failed for customer {$customer['id_customers']}: " . $result['message']);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in performAutoUnIsolir: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute MikroTik un-isolir command
     */
    private function executeMikrotikUnIsolir($router, $pppoeUsername)
    {
        try {
            log_message('info', "Starting un-isolir for PPPoE user: $pppoeUsername on router: " . $router['name']);

            // Parse connection details
            $connectionDetails = $this->parseConnectionDetails($router['ip_router'], $router['port_api']);

            // Initialize MikroTik connection
            $mt = new \App\Libraries\MikrotikNew([
                'host' => $connectionDetails['host'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => $connectionDetails['port'],
                'timeout' => 60,
            ]);

            // Find PPPoE secret
            log_message('info', "Querying PPPoE secret for: $pppoeUsername");
            $secrets = $mt->comm('/ppp/secret/print', ['?name' => $pppoeUsername]);
            if (empty($secrets)) {
                log_message('error', "PPPoE secret tidak ditemukan di router untuk user: $pppoeUsername");
                return [
                    'success' => false,
                    'message' => 'PPPoE secret tidak ditemukan di router'
                ];
            }

            $secretId = $secrets[0]['.id'];
            $currentProfile = $secrets[0]['profile'] ?? '';
            log_message('info', "Found PPPoE secret ID: $secretId, current profile: $currentProfile");

            // Get original profile from isolir log
            $originalProfile = $this->getOriginalProfileFromLog($pppoeUsername, $router['id_lokasi']);
            log_message('info', "Original profile from log: " . ($originalProfile ?? 'null'));

            if (!$originalProfile) {
                // If no log found, try to get from customer database
                $originalProfile = $this->getCustomerOriginalProfile($pppoeUsername);
                log_message('info', "Original profile from customer: " . ($originalProfile ?? 'null'));
            }

            if (!$originalProfile) {
                log_message('warning', "No original profile found for $pppoeUsername, using default");
                $originalProfile = 'default';
            }

            // Enable PPPoE secret and restore original profile
            log_message('info', "Updating PPPoE secret - Setting profile to: $originalProfile, disabled to: no");

            $result = $mt->comm('/ppp/secret/set', [
                'numbers' => $secretId,
                'disabled' => 'no',
                'profile' => $originalProfile
            ]);

            log_message('info', "MikroTik API response for /ppp/secret/set: " . json_encode($result));

            log_message('info', "MikroTik API response for /ppp/secret/set: " . json_encode($result));
            log_message('info', "PPPoE $pppoeUsername restored: profile changed from '$currentProfile' to '$originalProfile'");

            return [
                'success' => true,
                'message' => "PPPoE user berhasil dibuka isolirnya dan profile dikembalikan ke '$originalProfile'",
                'data' => [
                    'original_profile' => $originalProfile,
                    'previous_profile' => $currentProfile,
                    'mikrotik_result' => $result
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'MikroTik auto un-isolir error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Koneksi MikroTik gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get original profile from isolir log
     */
    private function getOriginalProfileFromLog($pppoeUsername, $routerId)
    {
        try {
            $db = \Config\Database::connect();

            // Find the last isolir action for this username
            // Note: status field may not exist in all records, so don't filter by it
            $query = $db->table('isolir_log')
                ->where('username', $pppoeUsername)
                ->where('router_id', $routerId)
                ->where('action', 'isolir')
                ->orderBy('created_at', 'DESC')
                ->limit(1);

            $result = $query->get()->getRowArray();

            if ($result && isset($result['old_profile'])) {
                log_message('info', "Found original profile for $pppoeUsername: " . $result['old_profile']);
                return $result['old_profile'];
            }

            log_message('warning', "No isolir log found for username: $pppoeUsername, router: $routerId");
            return null;
        } catch (\Exception $e) {
            log_message('error', 'Error getting original profile from log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get customer original profile from database or default
     */
    private function getCustomerOriginalProfile($pppoeUsername)
    {
        try {
            // Get customer data
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->where('pppoe_username', $pppoeUsername)->first();

            if ($customer && !empty($customer['group_profile_id'])) {
                // Get profile name from group_profile table if exists
                $db = \Config\Database::connect();
                $profile = \Config\Database::connect()->table('group_profile')
                    ->select('profile_name')
                    ->where('id', $customer['group_profile_id'])
                    ->get()
                    ->getRowArray();

                if ($profile && !empty($profile['profile_name'])) {
                    return $profile['profile_name'];
                }
            }

            // Default profile based on customer package or use default
            return 'default';
        } catch (\Exception $e) {
            log_message('error', 'Error getting customer original profile: ' . $e->getMessage());
            return 'default';
        }
    }

    /**
     * Parse connection details from router IP and port
     */
    private function parseConnectionDetails($ipRouter, $portApi)
    {
        $port = !empty($portApi) ? (int)$portApi : 8728; // Default MikroTik API port

        // Remove protocol prefix if present
        $host = preg_replace('/^https?:\/\//', '', $ipRouter);

        // Remove port if already in IP string
        $host = preg_replace('/:\d+$/', '', $host);

        return [
            'host' => $host,
            'port' => $port
        ];
    }

    /**
     * Log auto isolir action
     */
    private function logAutoIsolirAction($customerId, $routerId, $action, $reason, $status, $errorMessage = null)
    {
        try {
            $db = \Config\Database::connect();

            // Check if isolir_log table exists, create if not
            if (!\Config\Database::connect()->tableExists('isolir_log')) {
                $forge = \Config\Database::forge();

                $forge->addField([
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true,
                    ],
                    'customer_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'router_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'action' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                    ],
                    'reason' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'status' => [
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                    ],
                    'error_message' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
                $forge->addPrimaryKey('id');
                $forge->createTable('isolir_log');
            }

            // Insert log record
            $logData = [
                'customer_id' => $customerId,
                'router_id' => $routerId,
                'action' => $action,
                'reason' => $reason,
                'status' => $status,
                'error_message' => $errorMessage,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            \Config\Database::connect()->table('isolir_log')->insert($logData);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log auto isolir action: ' . $e->getMessage());
        }
    }

    /**
     * Get available periods for generation dropdown
     */
    public function availablePeriods()
    {
        try {
            $periods = [];

            // Current month
            $currentMonth = date('Y-m');
            $periods[] = [
                'periode' => $currentMonth,
                'label' => 'Bulan ' . $this->formatPeriodeIndonesian($currentMonth)
            ];

            // Next month
            $nextMonth = date('Y-m', strtotime('+1 month'));
            $periods[] = [
                'periode' => $nextMonth,
                'label' => 'Bulan ' . $this->formatPeriodeIndonesian($nextMonth)
            ];

            // Previous month (for catch-up)
            $prevMonth = date('Y-m', strtotime('-1 month'));
            $periods[] = [
                'periode' => $prevMonth,
                'label' => 'Bulan ' . $this->formatPeriodeIndonesian($prevMonth)
            ];

            return $this->response->setJSON($periods);
        } catch (\Exception $e) {
            log_message('error', 'Error getting available periods: ' . $e->getMessage());
            return $this->response->setJSON([]);
        }
    }

    /**
     * Format periode YYYY-MM to Indonesian month name
     */
    private function formatPeriodeIndonesian($periode)
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $periode, $matches)) {
            return $periode;
        }

        $year = $matches[1];
        $month = (int)$matches[2];

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $monthNames[$month] . ' ' . $year;
    }

    /**
     * Auto-generate invoices endpoint (can be called from cron or manually)
     */
    public function autoGenerate()
    {
        try {
            $periode = $this->request->getGet('periode') ?: date('Y-m');
            $force = $this->request->getGet('force') === 'true';

            // Use the GenerateInvoices controller
            $generateController = new \App\Controllers\GenerateInvoices();
            $result = $generateController->generate($periode);

            // If result is array (from CLI), convert to JSON response
            if (is_array($result)) {
                return $this->response->setJSON($result);
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Auto Generate Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check if auto-generation is needed
     */
    public function checkAutoGeneration()
    {
        try {
            $customerModel = model('CustomerModel');
            $invoiceModel = $this->invoiceModel;

            $currentPeriode = date('Y-m');
            $nextPeriode = date('Y-m', strtotime('+1 month'));

            // Check current month
            $totalCustomers = $customerModel->where('status_tagihan', 1)->countAllResults();
            $currentInvoices = $invoiceModel->where('periode', $currentPeriode)->countAllResults();
            $nextInvoices = $invoiceModel->where('periode', $nextPeriode)->countAllResults();

            $currentNeeded = $totalCustomers - $currentInvoices;
            $nextNeeded = $totalCustomers - $nextInvoices;

            $response = [
                'status' => 'success',
                'current_periode' => $currentPeriode,
                'next_periode' => $nextPeriode,
                'total_customers' => $totalCustomers,
                'current' => [
                    'periode' => $currentPeriode,
                    'existing' => $currentInvoices,
                    'needed' => $currentNeeded,
                    'is_needed' => $currentNeeded > 0
                ],
                'next' => [
                    'periode' => $nextPeriode,
                    'existing' => $nextInvoices,
                    'needed' => $nextNeeded,
                    'is_needed' => $nextNeeded > 0
                ],
                'recommendation' => $this->getGenerationRecommendation($currentNeeded, $nextNeeded)
            ];

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Check Auto Generation Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get generation recommendation based on current state
     */
    private function getGenerationRecommendation($currentNeeded, $nextNeeded)
    {
        $day = (int)date('j');

        if ($currentNeeded > 0 && $nextNeeded > 0) {
            return "Generate untuk bulan sekarang ($currentNeeded tagihan) dan bulan depan ($nextNeeded tagihan)";
        } elseif ($currentNeeded > 0) {
            return "Generate untuk bulan sekarang ($currentNeeded tagihan diperlukan)";
        } elseif ($nextNeeded > 0 && $day >= 25) {
            return "Siap generate untuk bulan depan ($nextNeeded tagihan)";
        } else {
            return "Semua tagihan sudah tersedia";
        }
    }

    /**
     * Get total unpaid invoices for a customer (for calculating arrears)
     */
    public function getUnpaidTotal()
    {
        try {
            $customerId = $this->request->getPost('customer_id');
            $excludeInvoiceId = $this->request->getPost('exclude_invoice_id');

            if (!$customerId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Customer ID tidak ditemukan',
                    'total_unpaid' => 0
                ]);
            }

            // Query untuk menghitung total tagihan yang belum dibayar
            $builder = $this->invoiceModel->builder();
            $builder->selectSum('bill', 'total_bill');
            $builder->selectSum('additional_fee', 'total_additional');
            $builder->where('customer_id', $customerId);
            $builder->where('status', 'unpaid');

            // Exclude current invoice if specified
            if ($excludeInvoiceId) {
                $builder->where('id !=', $excludeInvoiceId);
            }

            $result = $builder->get()->getRowArray();

            $totalBill = floatval($result['total_bill'] ?? 0);
            $totalAdditional = floatval($result['total_additional'] ?? 0);
            $totalUnpaid = $totalBill + $totalAdditional;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Tunggakan berhasil dihitung',
                'total_unpaid' => $totalUnpaid,
                'total_bill' => $totalBill,
                'total_additional' => $totalAdditional,
                'customer_id' => $customerId
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get Unpaid Total Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'total_unpaid' => 0
            ]);
        }
    }

    /**
     * Search customers for new payment
     */
    public function searchCustomers()
    {
        try {
            $search = $this->request->getPost('search');

            if (!$search || strlen($search) < 3) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Minimal 3 karakter untuk pencarian',
                    'customers' => []
                ]);
            }

            $customerModel = model('CustomerModel');

            // Search customers by name, service number, or phone
            $customers = $customerModel
                ->groupStart()
                ->like('nama_pelanggan', $search)
                ->orLike('nomor_layanan', $search)
                ->orLike('telepphone', $search)
                ->groupEnd()
                ->limit(20)
                ->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data pelanggan ditemukan',
                'customers' => $customers
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Search customers error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'customers' => []
            ]);
        }
    }

    /**
     * Get invoices by customer ID
     */
    public function getInvoicesByCustomer()
    {
        try {
            $customerId = $this->request->getPost('customer_id');
            $status = $this->request->getPost('status'); // unpaid, paid, or all

            log_message('info', 'getInvoicesByCustomer called with customer_id: ' . $customerId . ', status: ' . $status);

            if (!$customerId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Customer ID tidak ditemukan',
                    'data' => []
                ]);
            }

            $builder = $this->invoiceModel->builder();
            $builder->select('id, invoice_no, periode, bill, additional_fee, status, payment_method, payment_date');
            $builder->where('customer_id', $customerId);

            // Filter by status if specified
            if ($status && $status !== 'all') {
                if ($status === 'paid') {
                    // Include both 'paid' and 'lunas' status
                    $builder->groupStart()
                        ->where('status', 'paid')
                        ->orWhere('status', 'lunas')
                        ->groupEnd();
                } elseif ($status === 'unpaid') {
                    // Exclude paid invoices (not equal to 'paid' and not 'lunas')
                    $builder->where('status !=', 'paid');
                    $builder->where('status !=', 'lunas');
                }
            }

            $builder->orderBy('periode', 'DESC');

            // Log the query
            $query = $builder->getCompiledSelect(false);
            log_message('info', 'Query: ' . $query);

            $invoices = $builder->get()->getResultArray();

            log_message('info', 'Found ' . count($invoices) . ' invoices for customer ' . $customerId);

            // Calculate total for each invoice (bill + additional_fee)
            foreach ($invoices as &$invoice) {
                $invoice['total'] = floatval($invoice['bill']) + floatval($invoice['additional_fee'] ?? 0);
                log_message('debug', 'Invoice: ' . $invoice['invoice_no'] . ' - Status: ' . $invoice['status'] . ' - Total: ' . $invoice['total']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data invoice berhasil diambil',
                'data' => $invoices,
                'count' => count($invoices)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get invoices by customer error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Manual paid invoice - force mark as paid
     */
    public function manualPaid()
    {
        try {
            $invoiceId = $this->request->getPost('invoice_id');
            $customerId = $this->request->getPost('customer_id');

            if (!$invoiceId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice ID tidak ditemukan'
                ]);
            }

            // Get invoice data
            $invoice = $this->invoiceModel->find($invoiceId);

            if (!$invoice) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice tidak ditemukan'
                ]);
            }

            // Check if already paid
            if ($invoice['status'] === 'paid' || $invoice['status'] === 'lunas') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice sudah lunas'
                ]);
            }

            // Get customer data for transaction record
            $customerModel = model('CustomerModel');
            $customer = $customerModel->find($customerId);

            // Calculate total amount
            $totalAmount = $invoice['bill'] + ($invoice['additional_fee'] ?? 0);

            // Update invoice status to paid
            $updateData = [
                'status' => 'paid',
                'payment_method' => 'manual',
                'payment_date' => date('Y-m-d H:i:s'),
                'paid_amount' => $totalAmount
            ];

            $updated = $this->invoiceModel->update($invoiceId, $updateData);

            if ($updated) {
                // Create transaction record
                $transactionModel = model('TransactionModel');
                $transactionData = [
                    'branch' => $customer['cabang'] ?? 'default',
                    'date' => date('Y-m-d'),
                    'transaction_name' => 'Pembayaran Invoice ' . $invoice['invoice_no'],
                    'payment_method' => 'manual',
                    'category' => 'billing',
                    'description' => 'Manual paid - Customer: ' . ($customer['nama_pelanggan'] ?? '') . ' - Periode: ' . $invoice['periode'],
                    'type' => 'in',
                    'amount' => $totalAmount,
                    'created_by' => session()->get('id_user') ?? 1
                ];

                $transactionModel->insert($transactionData);

                // Update customer status if needed
                if ($customerId) {
                    log_message('info', "Manual payment: Calling updateCustomerAfterPayment for customer {$customerId}");
                    $updateResult = $this->updateCustomerAfterPayment($customerId, date('Y-m-d'));
                    log_message('info', "Manual payment: updateCustomerAfterPayment result: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Invoice berhasil dibayar secara manual dan transaksi telah dicatat'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate invoice'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Manual paid error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get invoice detail with transaction info
     */
    public function getDetail()
    {
        try {
            $invoiceId = $this->request->getPost('invoice_id');

            if (!$invoiceId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice ID tidak ditemukan'
                ]);
            }

            // Get invoice with customer data
            $db = \Config\Database::connect();
            $builder = $db->table('customer_invoices ci');
            $builder->select('ci.*, c.nama_pelanggan as customer_name, c.nomor_layanan as service_number');
            $builder->join('customers c', 'c.id_customers = ci.customer_id', 'left');
            $builder->where('ci.id', $invoiceId);
            $invoice = $builder->get()->getRowArray();

            if (!$invoice) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice tidak ditemukan'
                ]);
            }

            // Try to get related transaction if exists
            $transactionModel = model('TransactionModel');
            $transaction = $transactionModel
                ->where('description LIKE', '%' . $invoice['invoice_no'] . '%')
                ->orWhere('transaction_name LIKE', '%' . $invoice['invoice_no'] . '%')
                ->orderBy('created_at', 'DESC')
                ->first();

            // Generate transaction code if not exists
            if ($transaction) {
                $transaction['code'] = date('YmdHis', strtotime($transaction['created_at'])) . '-' . $invoice['invoice_no'];
            } else {
                // Create dummy transaction data for display
                $transaction = [
                    'code' => date('Ymd') . '-' . $invoice['invoice_no'],
                    'category' => 'Pembayaran Invoice Manual',
                    'branch' => $invoice['branch'] ?? '-',
                    'description' => 'Manual paid - ' . $invoice['periode']
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Detail invoice berhasil diambil',
                'data' => $invoice,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get invoice detail error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}
