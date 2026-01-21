<?php

namespace App\Libraries\Disbursement;

class MidtransDisbursement implements DisbursementInterface
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('MIDTRANS_IRIS_API_KEY');
        $this->apiUrl = env('MIDTRANS_IRIS_API_URL', 'https://app.midtrans.com/iris/api/v1');
    }

    /**
     * Send money via Midtrans Iris
     */
    public function disburse(array $data): array
    {
        try {
            $referenceNo = 'DISB-' . date('YmdHis') . '-' . rand(1000, 9999);

            $payload = [
                'payouts' => [
                    [
                        'beneficiary_name' => $data['account_name'],
                        'beneficiary_account' => $data['account_number'],
                        'beneficiary_bank' => $this->getBankCode($data['bank_name']),
                        'beneficiary_email' => $data['email'] ?? '',
                        'amount' => (int)$data['amount'],
                        'notes' => $data['notes'] ?? 'Withdrawal',
                        'reference_no' => $referenceNo
                    ]
                ]
            ];

            $response = $this->makeRequest('POST', '/payouts', $payload);

            if (isset($response['payouts']) && !empty($response['payouts'])) {
                $payout = $response['payouts'][0];

                return [
                    'success' => true,
                    'transaction_id' => $payout['reference_no'],
                    'reference_id' => $payout['reference_no'],
                    'status' => $payout['status'],
                    'receipt' => $payout['receipt'] ?? null,
                    'fee' => $payout['fee'] ?? 0,
                    'message' => 'Disbursement created successfully'
                ];
            }

            return [
                'success' => false,
                'message' => $response['error_message'] ?? 'Failed to create disbursement',
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
            $response = $this->makeRequest('GET', '/payouts/' . $referenceId);

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
                'bank' => $bankCode,
                'account' => $accountNumber
            ];

            $response = $this->makeRequest('GET', '/bank_accounts?' . http_build_query($payload));

            if (isset($response['account_name'])) {
                return [
                    'success' => true,
                    'account_name' => $response['account_name'],
                    'account_number' => $accountNumber,
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
        try {
            $response = $this->makeRequest('GET', '/beneficiary_banks');

            $banks = [];
            foreach ($response['beneficiary_banks'] as $bank) {
                $banks[$bank['name']] = $bank['code'];
            }

            return $banks;
        } catch (\Exception $e) {
            // Return default banks if API fails
            return [
                'BCA' => 'bca',
                'BNI' => 'bni',
                'BRI' => 'bri',
                'Mandiri' => 'mandiri',
                'BSI' => 'bsm',
                'CIMB Niaga' => 'cimb',
                'Danamon' => 'danamon',
                'Permata' => 'permata',
                'BTN' => 'btn',
            ];
        }
    }

    /**
     * Make HTTP request to Midtrans Iris API
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
                'Accept: application/json',
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
            throw new \Exception($result['error_message'] ?? 'API Error');
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
