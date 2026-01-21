<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;

class InvoiceDetails extends BaseController
{
    public function show($invoiceId)
    {
        try {
            $invoiceModel = new InvoiceModel();
            $invoice = $invoiceModel->find($invoiceId);

            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan',
                    'data' => null
                ]);
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Invoice details retrieved successfully',
                'data' => [
                    'id' => (int) $invoice['id'],
                    'bill' => (int) $invoice['bill'], // Ensure bill is returned as integer
                    'periode' => $invoice['periode'],
                    'status' => $invoice['status'],
                    'due_date' => $invoice['due_date'],
                    'customer_id' => (int) $invoice['customer_id']
                ],
                'debug' => [
                    'raw_bill_value' => $invoice['bill'],
                    'bill_type' => gettype($invoice['bill']),
                    'parsed_bill' => (int) $invoice['bill']
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'InvoiceDetails API Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat detail invoice: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
