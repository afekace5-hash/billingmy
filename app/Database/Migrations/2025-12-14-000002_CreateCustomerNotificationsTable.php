<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerNotificationsTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (!$this->db->tableExists('customer_notifications')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'customer_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'comment' => 'ID customer from customers table'
                ],
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'comment' => 'Notification title'
                ],
                'message' => [
                    'type' => 'TEXT',
                    'comment' => 'Notification message content'
                ],
                'type' => [
                    'type' => 'ENUM',
                    'constraint' => ['invoice', 'payment', 'isolir', 'promo', 'system', 'general'],
                    'default' => 'general',
                    'comment' => 'Type of notification'
                ],
                'is_read' => [
                    'type' => 'BOOLEAN',
                    'default' => false,
                    'comment' => 'Read status'
                ],
                'read_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Timestamp when notification was read'
                ],
                'data' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Additional JSON data (invoice_id, payment_id, etc)'
                ],
                'sent_via' => [
                    'type' => 'ENUM',
                    'constraint' => ['push', 'app', 'both'],
                    'default' => 'app',
                    'comment' => 'How notification was sent'
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

            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('customer_id');
            $this->forge->addKey(['customer_id', 'is_read']);
            $this->forge->addKey('created_at');

            $this->forge->createTable('customer_notifications');
            echo "Created customer_notifications table\n";
        }
    }

    public function down()
    {
        $this->forge->dropTable('customer_notifications');
    }
}
