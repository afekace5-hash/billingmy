<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixRadiusAttributes extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:fix-radius-attributes';
    protected $description = 'Fix radius_reply_attributes column to allow NULL values';

    public function run(array $params)
    {
        CLI::write('Fixing radius_reply_attributes column...', 'yellow');
        CLI::newLine();

        $db = \Config\Database::connect();

        try {
            // Check current column definition
            $query = $db->query("SHOW COLUMNS FROM pppoe_accounts LIKE 'radius_reply_attributes'");
            $currentColumn = $query->getRow();

            if ($currentColumn) {
                CLI::write('Current column definition:', 'cyan');
                CLI::write('  Field: ' . $currentColumn->Field);
                CLI::write('  Type: ' . $currentColumn->Type);
                CLI::write('  Null: ' . $currentColumn->Null);
                CLI::write('  Default: ' . ($currentColumn->Default ?? 'NULL'));
                CLI::newLine();
            }

            // Fix the column to allow NULL
            $sql = "ALTER TABLE `pppoe_accounts` 
                    MODIFY COLUMN `radius_reply_attributes` JSON NULL DEFAULT NULL";

            $db->query($sql);

            CLI::write('✓ Column modified successfully!', 'green');
            CLI::newLine();

            // Verify the change
            $query = $db->query("SHOW COLUMNS FROM pppoe_accounts LIKE 'radius_reply_attributes'");
            $newColumn = $query->getRow();

            if ($newColumn) {
                CLI::write('New column definition:', 'cyan');
                CLI::write('  Field: ' . $newColumn->Field);
                CLI::write('  Type: ' . $newColumn->Type);
                CLI::write('  Null: ' . $newColumn->Null);
                CLI::write('  Default: ' . ($newColumn->Default ?? 'NULL'));
                CLI::newLine();
            }

            CLI::write('✓ Fix completed successfully!', 'green');
            CLI::write('You can now delete PPPoE accounts without errors.', 'green');
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
