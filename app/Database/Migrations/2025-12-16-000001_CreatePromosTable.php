<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromosTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (!$this->db->tableExists('promos')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'description' => [
                    'type'       => 'TEXT',
                    'null'       => true,
                ],
                'badge_text' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '50',
                    'comment'    => 'Text yang ditampilkan di badge (ex: Rp 100K, 24/7, 100%)',
                ],
                'button_text' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                    'default'    => 'Lihat Detail',
                ],
                'button_action' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'comment'    => 'URL atau javascript function',
                ],
                'gradient_start' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '7',
                    'default'    => '#A8C0FF',
                    'comment'    => 'Warna gradient awal (hex)',
                ],
                'gradient_end' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '7',
                    'default'    => '#C1A5F0',
                    'comment'    => 'Warna gradient akhir (hex)',
                ],
                'display_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'comment'    => 'Urutan tampil',
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'comment'    => '1=aktif, 0=non-aktif',
                ],
                'start_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Tanggal mulai promo',
                ],
                'end_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Tanggal berakhir promo',
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
            $this->forge->createTable('promos');
        }
    }

    public function down()
    {
        $this->forge->dropTable('promos');
    }
}
