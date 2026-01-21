<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'customer_invoices';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id',
        'invoice_no',
        'periode',
        'bill',
        'arrears',
        'status',
        'package',
        'additional_fee',
        'discount',
        'paid_amount',
        'server',
        'due_date',
        'district',
        'village',
        'transaction_id',
        'payment_gateway',
        'payment_method',
        'payment_url',
        'payment_date',
        'payment_reference',
        'gateway_response',
        'is_prorata',
        'prorata_days',
        'prorata_start_date',
        'prorata_full_amount',
        'full_amount',
        'payment_button_used',
        'payment_button_used_at',
        'next_payment_available_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps = true;
    protected $returnType = 'array';
}
