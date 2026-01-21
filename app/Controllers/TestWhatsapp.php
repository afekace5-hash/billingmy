<?php

// Test WhatsApp via CodeIgniter framework
// URL: http://localhost:8080/test-whatsapp

namespace App\Controllers;

class TestWhatsapp extends BaseController
{
    public function index()
    {
        echo "<h1>Test WhatsApp Integration</h1>";

        // Test 1: Basic message sending
        echo "<h2>Test 1: Basic Message</h2>";
        $result1 = $this->testBasicMessage();
        echo $result1 ? "✅ PASSED<br>" : "❌ FAILED<br>";

        // Test 2: Payment notification
        echo "<h2>Test 2: Payment Notification</h2>";
        $result2 = $this->testPaymentNotification();
        echo $result2 ? "✅ PASSED<br>" : "❌ FAILED<br>";

        // Test 3: New customer notification
        echo "<h2>Test 3: New Customer Notification</h2>";
        $result3 = $this->testNewCustomerNotification();
        echo $result3 ? "✅ PASSED<br>" : "❌ FAILED<br>";

        // Summary
        $total = 3;
        $passed = ($result1 ? 1 : 0) + ($result2 ? 1 : 0) + ($result3 ? 1 : 0);

        echo "<hr>";
        echo "<h2>Summary: {$passed}/{$total} tests passed</h2>";

        if ($passed === $total) {
            echo "<p style='color: green;'><strong>🎉 All WhatsApp functionality working correctly!</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>⚠️ Some tests failed. Please check the logs.</strong></p>";
        }

        return;
    }

    private function testBasicMessage()
    {
        try {
            $whatsappService = new \App\Services\WhatsAppService();

            $testNumber = '6285183112127';
            $testMessage = 'Test basic message from CodeIgniter - ' . date('Y-m-d H:i:s');

            echo "Sending message to: {$testNumber}<br>";
            echo "Message: {$testMessage}<br>";

            $result = $whatsappService->sendMessage($testNumber, $testMessage);

            echo "Result: " . json_encode($result) . "<br><br>";

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "<br><br>";
            return false;
        }
    }

    private function testPaymentNotification()
    {
        try {
            // Simulate payment confirmation message
            $company = 'PT. KIMONET DIGITAL SYNERGY';
            $customerName = 'Test Customer';
            $noInvoice = 'INV-TEST-' . date('Ymd-His');
            $tanggal = date('d-m-Y H:i');
            $total = number_format(150000, 0, ',', '.');
            $periode = date('F Y');
            $tunggakan = '0';

            $template = "```{$company}```\n\n_Halo {$customerName},_\n\nTerima kasih sudah melakukan pembayaran\n\n*No Invoice*: {$noInvoice}\n*Tanggal*: {$tanggal}\n*Jumlah pembayaran*: {$total}\n*Tunggakan*: {$tunggakan}\n*Periode*: {$periode}\n\n*Terima kasih*";

            // Use Invoices controller method
            $invoicesController = new \App\Controllers\Invoices();

            // Get a test device
            $deviceModel = new \App\Models\WhatsappDeviceModel();
            $device = $deviceModel->first();

            if (!$device) {
                echo "ERROR: No WhatsApp device found<br>";
                return false;
            }

            $testNumber = '6285183112127';

            echo "Sending payment notification to: {$testNumber}<br>";
            echo "Device: {$device['number']}<br>";

            // Call the protected method using reflection
            $reflection = new \ReflectionClass($invoicesController);
            $method = $reflection->getMethod('sendWhatsAppPaymentConfirmation');
            $method->setAccessible(true);

            $result = $method->invoke($invoicesController, $testNumber, $template);

            echo "Result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br><br>";

            return $result;
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "<br><br>";
            return false;
        }
    }

    private function testNewCustomerNotification()
    {
        try {
            $whatsappService = new \App\Services\WhatsAppService();

            // Simulate new customer data
            $customerData = [
                'id_customers' => 999,
                'nama_pelanggan' => 'Test Customer Baru',
                'telepphone' => '6285183112127',
                'nomor_layanan' => 'KIM-' . date('Ymd') . '-001',
                'package_name' => 'Paket Internet 20 Mbps',
                'tarif' => 150000,
                'tgl_pasang' => date('Y-m-d'),
                'village' => 'Test Village',
                'district' => 'Test District',
                'city' => 'Test City',
                'address' => 'Test Address'
            ];

            echo "Sending new customer notification<br>";
            echo "Customer: {$customerData['nama_pelanggan']}<br>";
            echo "Phone: {$customerData['telepphone']}<br>";

            $result = $whatsappService->sendNewCustomerNotification($customerData);

            echo "Result: " . json_encode($result) . "<br><br>";

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "<br><br>";
            return false;
        }
    }
}
