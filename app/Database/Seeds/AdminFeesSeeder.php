<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminFeesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Update Midtrans admin fees
        $midtransAdminFees = [
            'credit_card' => 0,
            'bca_va' => 4000,
            'bni_va' => 4000,
            'bri_va' => 4000,
            'mandiri_va' => 4000,
            'echannel' => 4000,
            'gopay' => 0,
            'qris' => 0
        ];

        $db->table('payment_gateways')
            ->where('gateway_type', 'midtrans')
            ->update(['admin_fees' => json_encode($midtransAdminFees)]);

        // Update Xendit admin fees
        $xenditAdminFees = [
            'bca_va' => 4000,
            'bni_va' => 4000,
            'bri_va' => 4000,
            'mandiri_va' => 4000,
            'ovo' => 0,
            'dana' => 0,
            'linkaja' => 0,
            'shopeepay' => 1500,
            'qris' => 0
        ];

        $db->table('payment_gateways')
            ->where('gateway_type', 'xendit')
            ->update(['admin_fees' => json_encode($xenditAdminFees)]);

        // Update Duitku admin fees
        $duitkuAdminFees = [
            'bca_va' => 4000,
            'bni_va' => 4000,
            'bri_va' => 4000,
            'mandiri_va' => 4000,
            'permata_va' => 4000,
            'ovo' => 0,
            'dana' => 0,
            'linkaja' => 0,
            'shopeepay' => 1500,
            'qris' => 0
        ];

        $db->table('payment_gateways')
            ->where('gateway_type', 'duitku')
            ->update(['admin_fees' => json_encode($duitkuAdminFees)]);

        echo "Admin fees data has been seeded successfully.\n";
    }
}
