<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'notif_due_date' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi tagihan pada tanggal jatuh tempo',
            ],
            'notif_1_day_before' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi tagihan 1 hari sebelum jatuh tempo',
            ],
            'notif_3_days_before' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi tagihan 3 hari sebelum jatuh tempo',
            ],
            'notif_7_days_before' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi tagihan 7 hari sebelum jatuh tempo',
            ],
            'notif_isolir' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi saat pelanggan di isolir',
            ],
            'notif_payment' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi saat pembayaran pelanggan dibuat',
            ],
            'notif_new_customer' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Kirim notifikasi saat pelanggan baru di buat',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('notification_settings');

        // Insert default settings
        $data = [
            'id' => 1,
            'notif_due_date' => 0,
            'notif_1_day_before' => 0,
            'notif_3_days_before' => 0,
            'notif_7_days_before' => 0,
            'notif_isolir' => 0,
            'notif_payment' => 0,
            'notif_new_customer' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('notification_settings')->insert($data);
    }

    public function down()
    {
        $this->forge->dropTable('notification_settings');
    }
}
