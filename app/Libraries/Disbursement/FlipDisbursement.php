<?php

namespace App\Libraries\Disbursement;

class FlipDisbursement implements DisbursementInterface
{
    private $apiKey;
    private $apiUrl;
    private $validationUrl;

    public function __construct()
    {
        $this->apiKey = env('FLIP_SECRET_KEY');
        $this->apiUrl = env('FLIP_API_URL', 'https://bigflip.id/api/v2');
        $this->validationUrl = env('FLIP_VALIDATION_URL', 'https://bigflip.id/api/v2');
    }

    /**
     * Send money via Flip
     */
    public function disburse(array $data): array
    {
        try {
            $payload = [
                'account_number' => $data['account_number'],
                'bank_code' => $this->getBankCode($data['bank_name']),
                'amount' => (int)$data['amount'],
                'remark' => $data['notes'] ?? 'Withdrawal',
                'recipient_city' => $data['recipient_city'] ?? 100, // 100 = Jakarta
                'beneficiary_email' => $data['email'] ?? [],
            ];

            $response = $this->makeRequest('POST', '/disbursement', $payload);

            if (isset($response['id'])) {
                return [
                    'success' => true,
                    'transaction_id' => $response['id'],
                    'reference_id' => $response['id'],
                    'status' => $response['status'],
                    'receipt' => $response['receipt'] ?? null,
                    'fee' => $response['fee'] ?? 0,
                    'message' => 'Disbursement created successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Failed to create disbursement',
                'errors' => $response['errors'] ?? []
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
            $response = $this->makeRequest('GET', '/disbursement/' . $referenceId);

            return [
                'success' => true,
                'status' => $response['status'],
                'data' => $response
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
            $response = $this->makeRequest('GET', '/general/balance');

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
                'account_number' => $accountNumber,
                'bank_code' => $bankCode
            ];

            $response = $this->makeRequest('POST', '/disbursement/bank-account-inquiry', $payload);

            if (isset($response['account_number'])) {
                return [
                    'success' => true,
                    'account_name' => $response['account_holder'] ?? '',
                    'account_number' => $response['account_number'],
                    'bank_name' => $response['bank_name'] ?? ''
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid bank account'
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
        return [
            'BCA' => 'bca',
            'BNI' => 'bni',
            'BRI' => 'bri',
            'Mandiri' => 'mandiri',
            'BSI' => 'bsi',
            'CIMB Niaga' => 'cimb',
            'Danamon' => 'danamon',
            'Permata' => 'permata',
            'BTN' => 'btn',
            'BNC' => 'bnc',
            'Muamalat' => 'muamalat',
        ];
    }

    /**
     * Make HTTP request to Flip API
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
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_USERPWD => $this->apiKey . ':'
        ];

        if ($method === 'POST' && !empty($data)) {
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
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
        return $banks[$bankName] ?? strtolower($bankName);
    }
}
