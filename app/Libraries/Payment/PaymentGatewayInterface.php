<?php

namespace App\Libraries\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a payment transaction
     *
     * @param array $data Transaction data
     * @return array Response from gateway
     */
    public function createTransaction(array $data): array;

    /**
     * Get transaction status
     *
     * @param string $transactionId Transaction ID
     * @return array Transaction status
     */
    public function getTransactionStatus(string $transactionId): array;

    /**
     * Get available payment methods
     *
     * @return array Available payment methods
     */
    public function getPaymentMethods(): array;

    /**
     * Handle callback from payment gateway
     *
     * @param array $data Callback data
     * @return array Processed callback data
     */
    public function handleCallback(array $data): array;

    /**
     * Verify callback signature
     *
     * @param array $data Callback data
     * @param string $signature Signature to verify
     * @return bool True if signature is valid
     */
    public function verifyCallback(array $data, string $signature): bool;

    /**
     * Test connection to payment gateway
     *
     * @return array Test result
     */
    public function testConnection(): array;
}
