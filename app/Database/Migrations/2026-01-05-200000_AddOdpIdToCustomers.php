<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOdpIdToCustomers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [
            'odp_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'area_id',
                'comment' => 'Foreign key to odps table'
            ]
        ]);

        // Add foreign key if needed
        // $this->forge->addForeignKey('odp_id', 'odps', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        // Drop foreign key first if exists
        // $this->forge->dropForeignKey('customers', 'customers_odp_id_foreign');

        $this->forge->dropColumn('customers', 'odp_id');
    }
}
