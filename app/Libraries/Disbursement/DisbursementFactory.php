<?php

namespace App\Libraries\Disbursement;

class DisbursementFactory
{
    /**
     * Create disbursement instance based on provider
     * 
     * @param string $provider (flip|xendit|midtrans)
     * @return DisbursementInterface
     * @throws \Exception
     */
    public static function create(string $provider = null): DisbursementInterface
    {
        // Get default provider from env if not specified
        if ($provider === null) {
            $provider = env('DISBURSEMENT_PROVIDER', 'flip');
        }

        switch (strtolower($provider)) {
            case 'flip':
                return new FlipDisbursement();

            case 'xendit':
                return new XenditDisbursement();

            case 'midtrans':
            case 'iris':
                return new MidtransDisbursement();

            default:
                throw new \Exception('Unsupported disbursement provider: ' . $provider);
        }
    }

    /**
     * Get list of available providers
     * 
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        return [
            'flip' => 'Flip',
            'xendit' => 'Xendit',
            'midtrans' => 'Midtrans Iris'
        ];
    }
}
