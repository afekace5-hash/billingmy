<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBroadcastsTable extends Migration
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
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'promotion, information, reminder, announcement',
            ],
            'branch' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'area' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
            ],
            'target_users' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'all, active, overdue, specific_package',
            ],
            'total_users' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'scheduled',
                'comment' => 'scheduled, sending, sent, failed',
            ],
            'sent_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->addKey('status');
        $this->forge->addKey('scheduled_at');
        $this->forge->createTable('broadcasts');
    }

    public function down()
    {
        $this->forge->dropTable('broadcasts');
    }
}
