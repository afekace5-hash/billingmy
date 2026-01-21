<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;

class GenerateInvoices extends Controller
{
    protected $invoiceModel;
    protected $customerModel;
    protected $db;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Generate invoices - enhanced with auto-generation logic
     */
    public function generate($periode = null)
    {
        try {
            // Jika tidak ada periode, gunakan bulan sekarang
            if (!$periode) {
                $periode = date('Y-m');
            }

            // Validasi format periode
            if (!preg_match('/^\d{4}-\d{2}$/', $periode)) {
                $result = [
                    'status' => 'error',
                    'message' => 'Format periode tidak valid. Gunakan format YYYY-MM (contoh: 2025-10)'
                ];

                return is_cli() ? $result : $this->response->setJSON($result);
            }

            // Log untuk tracking
            log_message('info', "Generate Invoice: Starting for periode $periode");

            // Generate untuk periode yang diminta
            $result = $this->generateForPeriod($periode);

            // AUTO-GENERATE LOGIC: Hanya generate bulan depan pada akhir bulan (tanggal 28-31)
            // untuk persiapan bulan baru
            $currentDay = (int)date('j');
            $currentMonth = date('Y-m');

            if ($periode === $currentMonth && $currentDay >= 28) {
                $nextPeriode = date('Y-m', strtotime('+1 month'));
                log_message('info', "End of month - Auto-generating for next month: $nextPeriode");

                $nextResult = $this->generateForPeriod($nextPeriode);

                // Gabungkan hasil
                $result['created'] += $nextResult['created'];
                $result['skipped'] += $nextResult['skipped'];
                $result['message'] .= " || PERSIAPAN BULAN DEPAN: Generate untuk periode $nextPeriode: {$nextResult['created']} tagihan dibuat, {$nextResult['skipped']} dilewati.";
            }            // Return response sesuai context
            return is_cli() ? $result : $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Generate Invoice Error: ' . $e->getMessage());

            $result = [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate tagihan: ' . $e->getMessage()
            ];

            return is_cli() ? $result : $this->response->setJSON($result);
        }
    }

    /**
     * Generate invoices for specific period
     */
    private function generateForPeriod($periode)
    {
        $customers = $this->customerModel->where('status_tagihan', 1)->findAll();

        if (empty($customers)) {
            return [
                'status' => 'warning',
                'message' => 'Tidak ada pelanggan aktif untuk periode ' . $periode,
                'created' => 0,
                'skipped' => 0
            ];
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($customers as $cust) {
            try {
                // Cek apakah sudah ada invoice untuk periode ini
                $exists = $this->invoiceModel->where([
                    'customer_id' => $cust['id_customers'],
                    'periode' => $periode
                ])->first();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Ambil info paket
                $paket = $this->db->table('package_profiles')
                    ->where('id', $cust['id_paket'])
                    ->get()
                    ->getRowArray();

                if (!$paket) {
                    $errors[] = "Customer {$cust['nama_pelanggan']}: Paket tidak ditemukan";
                    continue;
                }

                $bill = $paket['price'] ?? 0;
                $package = ($paket['name'] ?? '') . ' | ' . ($paket['bandwidth_profile'] ?? '');

                // Generate nomor invoice unik
                $invoice_no = $this->generateInvoiceNumber($cust['id_customers']);

                $invoiceData = [
                    'customer_id' => $cust['id_customers'],
                    'invoice_no' => $invoice_no,
                    'periode' => $periode,
                    'bill' => $bill,
                    'arrears' => 0,
                    'status' => 'unpaid',
                    'package' => $package,
                    'additional_fee' => 0,
                    'discount' => 0,
                    'server' => $cust['id_lokasi_server'] ?? null,
                    'due_date' => $cust['tgl_tempo'] ?? 15,
                    'district' => $cust['district'] ?? null,
                    'village' => $cust['village'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->invoiceModel->insert($invoiceData)) {
                    $created++;

                    // ✅ AUTO-RESET: Ubah status_tagihan menjadi "Belum Bayar" untuk bulan yang baru
                    // Ini penting agar auto-isolir dapat bekerja dengan benar
                    $this->customerModel->update($cust['id_customers'], [
                        'status_tagihan' => 'Belum Bayar'
                    ]);

                    log_message('info', "Invoice created for customer {$cust['nama_pelanggan']} - {$invoice_no} and status_tagihan reset to 'Belum Bayar'");
                } else {
                    $errors[] = "Gagal insert invoice untuk customer {$cust['nama_pelanggan']}";
                }
            } catch (\Exception $e) {
                $errors[] = "Error untuk customer {$cust['nama_pelanggan']}: " . $e->getMessage();
                log_message('error', "Invoice generation error for customer {$cust['id_customers']}: " . $e->getMessage());
            }
        }

        // Format message
        $message = "Generate tagihan periode $periode selesai. $created tagihan berhasil dibuat";

        if ($skipped > 0) {
            $message .= ", $skipped tagihan dilewati (sudah ada)";
        }

        if (!empty($errors)) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " (dan " . (count($errors) - 3) . " error lainnya)";
            }
        }

        return [
            'status' => $created > 0 ? 'success' : ($skipped > 0 ? 'warning' : 'error'),
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'periode' => $periode
        ];
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber($customerId)
    {
        $prefix = 'INV';
        // Generate 8 digit random number, leading zeros allowed
        $randomNumber = str_pad(strval(mt_rand(0, 99999999)), 8, '0', STR_PAD_LEFT);
        return "{$prefix}-{$randomNumber}";
    }

    /**
     * Check if invoice generation is needed for current period
     */
    public function checkGenerationNeeded()
    {
        $currentPeriode = date('Y-m');

        // Count total active customers
        $totalCustomers = $this->customerModel->where('status_tagihan', 1)->countAllResults();

        // Count existing invoices for current period
        $existingInvoices = $this->invoiceModel->where('periode', $currentPeriode)->countAllResults();

        $needed = $totalCustomers - $existingInvoices;

        return $this->response->setJSON([
            'status' => 'success',
            'periode' => $currentPeriode,
            'total_customers' => $totalCustomers,
            'existing_invoices' => $existingInvoices,
            'generation_needed' => $needed,
            'is_needed' => $needed > 0
        ]);
    }

    /**
     * Auto-generate for multiple periods (for catch-up)
     */
    public function generateMultiplePeriods()
    {
        $startPeriode = $this->request->getPost('start_periode') ?: date('Y-m');
        $months = (int)($this->request->getPost('months') ?: 1);

        $results = [];
        $totalCreated = 0;
        $totalSkipped = 0;

        for ($i = 0; $i < $months; $i++) {
            $periode = date('Y-m', strtotime($startPeriode . " +{$i} month"));
            $result = $this->generateForPeriod($periode);

            $results[] = $result;
            $totalCreated += $result['created'];
            $totalSkipped += $result['skipped'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Multi-generate selesai. Total: $totalCreated dibuat, $totalSkipped dilewati untuk $months periode.",
            'total_created' => $totalCreated,
            'total_skipped' => $totalSkipped,
            'details' => $results
        ]);
    }
}
