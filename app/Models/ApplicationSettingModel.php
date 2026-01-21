<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationSettingModel extends Model
{
    protected $table = 'application_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'due_date',
        'table_entry',
        'timezone',
        'default_coordinat',
        'province_id',
        'city_id',
        'district_id',
        'village_id',
        'default_subscription_method',
        'chart_daily',
        'chart_yearly',
        'unpaid_bill',
        'show_used_period',
        'default_due_date',
        'invoice_template',
        'invoice_note',
        'updated_at'
    ];
    protected $useTimestamps = true;

    /**
     * Get application settings
     */
    public function getSettings()
    {
        $settings = $this->first();

        if (!$settings) {
            // Return default settings if none exist
            return [
                'due_date' => 10,
                'table_entry' => 10,
                'timezone' => 'Asia/Jakarta',
                'default_coordinat' => '-7.216493,107.901878',
                'province_id' => null,
                'city_id' => null,
                'district_id' => null,
                'village_id' => null,
                'default_subscription_method' => 'prepaid',
                'chart_daily' => 1,
                'chart_yearly' => 1,
                'unpaid_bill' => 1,
                'show_used_period' => 1,
                'default_due_date' => 'server',
                'invoice_template' => 'template2',
                'invoice_note' => '<p>Pembayaran yang sudah masuk tidak dapat dikembalikan</p>'
            ];
        }

        return $settings;
    }

    /**
     * Save or update application settings
     */
    public function saveSettings($data)
    {
        $existing = $this->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['id'] = 1;
            return $this->insert($data);
        }
    }

    /**
     * Get setting by key
     */
    public function getSetting($key, $default = null)
    {
        $settings = $this->getSettings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
}
// File dihapus sesuai permintaan
