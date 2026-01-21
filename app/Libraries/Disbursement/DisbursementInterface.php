<?php

namespace App\Libraries\Disbursement;

interface DisbursementInterface
{
    /**
     * Send money to bank account
     * 
     * @param array $data
     * @return array
     */
    public function disburse(array $data): array;

    /**
     * Check disbursement status
     * 
     * @param string $referenceId
     * @return array
     */
    public function checkStatus(string $referenceId): array;

    /**
     * Get available balance
     * 
     * @return array
     */
    public function getBalance(): array;

    /**
     * Validate bank account
     * 
     * @param string $bankCode
     * @param string $accountNumber
     * @return array
     */
    public function validateBankAccount(string $bankCode, string $accountNumber): array;

    /**
     * Get list of supported banks
     * 
     * @return array
     */
    public function getSupportedBanks(): array;
}
