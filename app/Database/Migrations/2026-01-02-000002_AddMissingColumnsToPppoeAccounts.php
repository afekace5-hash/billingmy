<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToPppoeAccounts extends Migration
{
    public function up()
    {
        $fields = [
            'server_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'customer_id'
            ],
            'pppoe_id' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'id'
            ],
            'disabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'status'
            ],
            'remote_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'after' => 'ip_address'
            ],
            'mac_address' => [
                'type' => 'VARCHAR',
                'constraint' => 17,
                'null' => true,
                'after' => 'remote_address'
            ],
            'local_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'after' => 'mac_address'
            ],
            'last_sync' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'radius_reply_attributes'
            ],
        ];

        $this->forge->addColumn('pppoe_accounts', $fields);

        // Add foreign key for server_id
        $this->forge->addForeignKey('server_id', 'lokasi_server', 'id_lokasi', 'CASCADE', 'SET NULL', '', 'pppoe_accounts');
    }

    public function down()
    {
        $this->forge->dropForeignKey('pppoe_accounts', 'pppoe_accounts_server_id_foreign');
        $this->forge->dropColumn('pppoe_accounts', ['server_id', 'pppoe_id', 'disabled', 'remote_address', 'mac_address', 'local_address', 'last_sync']);
    }
}
