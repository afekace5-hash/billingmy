<?php

namespace App\Libraries\Disbursement;

class XenditDisbursement implements DisbursementInterface
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('XENDIT_SECRET_KEY');
        $this->apiUrl = env('XENDIT_API_URL', 'https://api.xendit.co');
    }

    /**
     * Send money via Xendit
     */
    public function disburse(array $data): array
    {
        try {
            $externalId = 'DISB-' . time() . '-' . uniqid();

            $payload = [
                'external_id' => $externalId,
                'amount' => (int)$data['amount'],
                'bank_code' => $this->getBankCode($data['bank_name']),
                'account_holder_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'description' => $data['notes'] ?? 'Withdrawal',
                'email_to' => $data['email'] ?? [],
                'email_cc' => [],
                'email_bcc' => []
            ];

            $response = $this->makeRequest('POST', '/disbursements', $payload);

            if (isset($response['id'])) {
                return [
                    'success' => true,
                    'transaction_id' => $response['id'],
                    'reference_id' => $response['external_id'],
                    'status' => $response['status'],
                    'receipt' => $response['receipt_notification_url'] ?? null,
                    'fee' => $response['fee'] ?? 0,
                    'message' => 'Disbursement created successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Failed to create disbursement',
                'error_code' => $response['error_code'] ?? null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Disbursement failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check disbursement status
     */
    public function checkStatus(string $referenceId): array
    {
        try {
            $response = $this->makeRequest('GET', '/disbursements?external_id=' . $referenceId);

            if (!empty($response)) {
                $disbursement = $response[0];
                return [
                    'success' => true,
                    'status' => $disbursement['status'],
                    'data' => $disbursement
                ];
            }

            return [
                'success' => false,
                'message' => 'Disbursement not found'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get balance
     */
    public function getBalance(): array
    {
        try {
            $response = $this->makeRequest('GET', '/balance');

            return [
                'success' => true,
                'balance' => $response['balance'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate bank account
     */
    public function validateBankAccount(string $bankCode, string $accountNumber): array
    {
        try {
            $payload = [
                'bank_account_number' => $accountNumber,
                'bank_code' => $bankCode
            ];

            $response = $this->makeRequest('POST', '/bank_account_data_requests', $payload);

            if (isset($response['id'])) {
                // Wait a bit for validation
                sleep(2);

                // Get validation result
                $validation = $this->makeRequest('GET', '/bank_account_data_requests/' . $response['id']);

                if ($validation['status'] === 'COMPLETED') {
                    return [
                        'success' => true,
                        'account_name' => $validation['bank_account_holder_name'] ?? '',
                        'account_number' => $accountNumber,
                        'bank_name' => $validation['bank_code'] ?? ''
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Unable to validate bank account'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get supported banks
     */
    public function getSupportedBanks(): array
    {
        try {
            $response = $this->makeRequest('GET', '/available_disbursements_banks');

            $banks = [];
            foreach ($response as $bank) {
                $banks[$bank['name']] = $bank['code'];
            }

            return $banks;
        } catch (\Exception $e) {
            // Return default banks if API fails
            return [
                'BCA' => 'BCA',
                'BNI' => 'BNI',
                'BRI' => 'BRI',
                'Mandiri' => 'MANDIRI',
                'BSI' => 'BSI',
                'CIMB Niaga' => 'CIMB',
                'Danamon' => 'DANAMON',
                'Permata' => 'PERMATA',
                'BTN' => 'BTN',
            ];
        }
    }

    /**
     * Make HTTP request to Xendit API
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $curl = curl_init();

        $url = $this->apiUrl . $endpoint;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->apiKey . ':')
            ]
        ];

        if ($method === 'POST' && !empty($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($err) {
            throw new \Exception('Curl Error: ' . $err);
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new \Exception($result['message'] ?? 'API Error');
        }

        return $result;
    }

    /**
     * Get bank code from bank name
     */
    private function getBankCode(string $bankName): string
    {
        $banks = $this->getSupportedBanks();
        return $banks[$bankName] ?? strtoupper(str_replace(' ', '_', $bankName));
    }
}
