<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupAutoIsolirTable extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'setup:auto-isolir-table';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Setup auto isolir config table in database';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'setup:auto-isolir-table';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Setting up auto_isolir_config table...', 'green');

        $db = \Config\Database::connect();

        // Create table SQL
        $sql = "
        CREATE TABLE IF NOT EXISTS `auto_isolir_config` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `router_id` INT(11) UNSIGNED NOT NULL,
            `isolir_ip` VARCHAR(45) NOT NULL,
            `isolir_page_url` VARCHAR(255) DEFAULT NULL,
            `grace_period_days` INT(3) DEFAULT 0,
            `is_enabled` TINYINT(1) DEFAULT 1,
            `last_run` DATETIME DEFAULT NULL,
            `pool_name` VARCHAR(100) DEFAULT NULL,
            `profile_name` VARCHAR(100) DEFAULT NULL,
            `address_list_name` VARCHAR(100) DEFAULT NULL,
            `setup_completed` TINYINT(1) DEFAULT 0,
            `last_setup_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_router_id` (`router_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        try {
            CLI::write('Creating table auto_isolir_config...', 'yellow');

            $db->query($sql);

            CLI::write('✅ Table auto_isolir_config created successfully!', 'green');

            // Check if table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'auto_isolir_config'")->getResultArray();
            if (count($tableExists) > 0) {
                CLI::write('✅ Table verification: auto_isolir_config exists', 'green');

                // Check table structure
                $columns = $db->query("DESCRIBE auto_isolir_config")->getResultArray();
                CLI::write('Table structure:', 'cyan');
                foreach ($columns as $column) {
                    CLI::write("  - {$column['Field']} ({$column['Type']})", 'white');
                }
            } else {
                CLI::write('❌ Table verification: auto_isolir_config NOT found', 'red');
            }
        } catch (\Exception $e) {
            CLI::write('❌ Error creating table: ' . $e->getMessage(), 'red');
        }

        CLI::write('Setup completed!', 'green');
    }
}
