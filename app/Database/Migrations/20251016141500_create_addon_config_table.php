<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_addon_config_table extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (!$this->db->tableExists('addon_config')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'addon_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'is_active' => [
                    'type' => 'BOOLEAN',
                    'default' => false,
                ],
                'config' => [
                    'type' => 'TEXT',
                    'null' => true,
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
            $this->forge->createTable('addon_config');
        }
    }

    public function down()
    {
        $this->forge->dropTable('addon_config');
    }
}
