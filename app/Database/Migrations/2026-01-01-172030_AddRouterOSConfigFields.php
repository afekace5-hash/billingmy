<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRouterOSConfigFields extends Migration
{
    public function up()
    {
        // Cek kolom yang sudah ada
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('lokasi_server');

        $newFields = [];

        // Tambahkan field yang belum ada
        if (!in_array('local_ip', $fields)) {
            $newFields['local_ip'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ];
        }

        if (!in_array('legacy_login', $fields)) {
            $newFields['legacy_login'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ];
        }

        if (!in_array('remote_url', $fields)) {
            $newFields['remote_url'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if (!in_array('comment_nat', $fields)) {
            $newFields['comment_nat'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ];
        }

        if (!in_array('notes', $fields)) {
            $newFields['notes'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (!empty($newFields)) {
            $this->forge->addColumn('lokasi_server', $newFields);
        }

        // Rename kolom jika perlu
        if (in_array('password_router', $fields) && !in_array('password', $fields)) {
            $this->forge->modifyColumn('lokasi_server', [
                'password_router' => [
                    'name' => 'password',
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
        }

        // Tambahkan kolom jenis_isolir jika belum ada
        if (!in_array('jenis_isolir', $fields)) {
            $this->forge->addColumn('lokasi_server', [
                'jenis_isolir' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
            ]);
        }

        // Tambahkan kolom lokasi (untuk branch) jika belum ada
        if (!in_array('lokasi', $fields)) {
            $this->forge->addColumn('lokasi_server', [
                'lokasi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('lokasi_server', [
            'username',
            'password',
            'local_ip',
            'legacy_login',
            'remote_url',
            'comment_nat',
            'notes',
        ]);
    }
}
