<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\PaymentGatewayModel;

class CheckPaymentStatus extends BaseController
{
    /**
     * Manual check payment status from payment gateway
     * Useful for localhost/sandbox testing when webhook cannot reach
     */
    public function checkStatus($orderId = null)
    {
        if (!$orderId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Order ID tidak ditemukan'
            ]);
        }

        try {
            $invoiceModel = new InvoiceModel();
            $paymentModel = new PaymentGatewayModel();

            // Find invoice by transaction_id
            $invoice = $invoiceModel->where('transaction_id', $orderId)->first();

            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan untuk order: ' . $orderId
                ]);
            }

            // Get Midtrans config
            $midtransConfig = $paymentModel->getActiveGatewayByType('midtrans');

            if (!$midtransConfig) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Konfigurasi Midtrans tidak ditemukan'
                ]);
            }

            // Check status from Midtrans API
            $serverKey = $midtransConfig['api_key'];
            $environment = $midtransConfig['environment'] ?? 'sandbox';
            $baseUrl = $environment === 'production'
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com';

            $url = $baseUrl . '/v2/' . $orderId . '/status';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':')
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);

                $transactionStatus = $result['transaction_status'] ?? '';
                $fraudStatus = $result['fraud_status'] ?? '';

                // Map status
                $paymentStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

                // Update invoice if paid
                if ($paymentStatus === 'paid') {
                    $updateData = [
                        'status' => 'paid',
                        'paid_amount' => $invoice['bill'],
                        'payment_date' => date('Y-m-d H:i:s'),
                        'payment_reference' => $result['transaction_id'] ?? $orderId,
                        'gateway_response' => json_encode($result)
                    ];

                    $invoiceModel->update($invoice['id'], $updateData);

                    // Update payment_transactions table
                    $paymentTransactionModel = new \App\Models\PaymentTransactionModel();
                    $transaction = $paymentTransactionModel->where('transaction_code', $orderId)->first();

                    if ($transaction) {
                        $paymentTransactionModel->update($transaction['id'], [
                            'status' => 'paid',
                            'paid_amount' => $invoice['bill'],
                            'paid_at' => date('Y-m-d H:i:s'),
                            'callback_data' => json_encode($result)
                        ]);
                    }

                    log_message('info', 'Manual payment check - Invoice ID: ' . $invoice['id'] . ' marked as paid');

                    return $this->response->setJSON([
                        'success' => true,
                        'status' => 'paid',
                        'message' => 'Pembayaran berhasil! Invoice telah diupdate.',
                        'transaction_status' => $transactionStatus
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => true,
                        'status' => $paymentStatus,
                        'message' => 'Status pembayaran: ' . $transactionStatus,
                        'transaction_status' => $transactionStatus
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak dapat mengecek status pembayaran dari Midtrans (HTTP ' . $httpCode . ')',
                    'response' => $response
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Check payment status error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function mapMidtransStatus($transactionStatus, $fraudStatus = '')
    {
        switch ($transactionStatus) {
            case 'capture':
                return ($fraudStatus === 'accept') ? 'paid' : 'pending';
            case 'settlement':
                return 'paid';
            case 'pending':
                return 'pending';
            case 'deny':
            case 'cancel':
            case 'expire':
            case 'failure':
                return 'failed';
            default:
                return 'pending';
        }
    }
}
