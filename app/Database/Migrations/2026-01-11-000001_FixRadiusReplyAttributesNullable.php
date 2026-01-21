<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixRadiusReplyAttributesNullable extends Migration
{
    public function up()
    {
        // Modify the radius_reply_attributes column to explicitly allow NULL
        // and set a default value
        $this->db->query("
            ALTER TABLE `pppoe_accounts` 
            MODIFY COLUMN `radius_reply_attributes` JSON NULL DEFAULT NULL
        ");

        log_message('info', 'Migration: radius_reply_attributes column modified to allow NULL values');
    }

    public function down()
    {
        // Revert changes if needed
        $this->db->query("
            ALTER TABLE `pppoe_accounts` 
            MODIFY COLUMN `radius_reply_attributes` JSON NULL
        ");

        log_message('info', 'Migration: radius_reply_attributes column reverted');
    }
}
