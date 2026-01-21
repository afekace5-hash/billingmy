<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\MikrotikAPI;

class RemoveProblematicScheduler extends BaseCommand
{
    protected $group       = 'fix';
    protected $name        = 'fix:remove-scheduler';
    protected $description = 'Remove problematic Expire-Monitor scheduler completely';

    public function run(array $params)
    {
        CLI::write('🗑️  Removing Problematic Scheduler...', 'yellow');
        CLI::write('====================================');

        $mikrotik = new MikrotikAPI();

        try {
            $client = $mikrotik->getClient();
            CLI::write('✅ Connected to router', 'green');

            // Get all schedulers
            CLI::write('🔍 Finding problematic schedulers...', 'cyan');
            $schedulers = $client->comm('/system/scheduler/print');

            $removed = 0;
            $problematicNames = ['Expire-Monitor', 'expire-monitor', 'ExpireMonitor'];

            foreach ($schedulers as $scheduler) {
                $name = $scheduler['name'] ?? '';
                $id = $scheduler['.id'] ?? '';

                // Check if this is a problematic scheduler
                if (in_array($name, $problematicNames)) {
                    CLI::write("🎯 Found problematic scheduler: $name (ID: $id)", 'yellow');

                    try {
                        // Remove the scheduler completely
                        $result = $client->comm('/system/scheduler/remove', ['=.id=' . $id]);
                        CLI::write("✅ Removed scheduler: $name", 'green');
                        $removed++;
                    } catch (\Exception $e) {
                        CLI::write("❌ Failed to remove scheduler $name: " . $e->getMessage(), 'red');
                    }
                }
            }

            if ($removed === 0) {
                CLI::write('ℹ️  No problematic schedulers found to remove', 'blue');
            } else {
                CLI::write("✅ Successfully removed $removed problematic scheduler(s)", 'green');
            }

            // Check remaining schedulers
            CLI::newLine();
            CLI::write('📋 Remaining Schedulers:', 'cyan');
            $remainingSchedulers = $client->comm('/system/scheduler/print');

            if (empty($remainingSchedulers)) {
                CLI::write('No schedulers remaining', 'white');
            } else {
                foreach ($remainingSchedulers as $scheduler) {
                    $name = $scheduler['name'] ?? 'Unknown';
                    $disabled = isset($scheduler['disabled']) && $scheduler['disabled'] === 'true' ? ' (DISABLED)' : ' (ENABLED)';
                    $interval = $scheduler['interval'] ?? 'N/A';
                    CLI::write("  - $name$disabled - Interval: $interval", 'white');
                }
            }

            // Also check and clean any problematic system scripts
            CLI::newLine();
            CLI::write('🧹 Checking for problematic scripts...', 'cyan');

            try {
                $scripts = $client->comm('/system/script/print');
                $scriptRemoved = 0;

                foreach ($scripts as $script) {
                    $name = $script['name'] ?? '';
                    $source = $script['source'] ?? '';

                    // Check for broken script syntax or expire-related problems
                    if (strpos($name, 'expire') !== false || strpos($name, 'Expire') !== false) {
                        if (
                            strpos($source, ':if ([:len [/sys scr job find script') !== false ||
                            strpos($source, 'no such item') !== false ||
                            empty(trim($source))
                        ) {

                            CLI::write("🎯 Found problematic script: $name", 'yellow');

                            try {
                                $client->comm('/system/script/remove', ['=.id=' . $script['.id']]);
                                CLI::write("✅ Removed problematic script: $name", 'green');
                                $scriptRemoved++;
                            } catch (\Exception $e) {
                                CLI::write("❌ Failed to remove script $name: " . $e->getMessage(), 'red');
                            }
                        }
                    }
                }

                if ($scriptRemoved === 0) {
                    CLI::write('ℹ️  No problematic scripts found', 'blue');
                } else {
                    CLI::write("✅ Removed $scriptRemoved problematic script(s)", 'green');
                }
            } catch (\Exception $e) {
                CLI::write('⚠️  Could not check scripts: ' . $e->getMessage(), 'yellow');
            }
        } catch (\Exception $e) {
            CLI::write('❌ Connection failed: ' . $e->getMessage(), 'red');
            return;
        }

        CLI::newLine();
        CLI::write('✅ Scheduler cleanup completed!', 'green');

        CLI::newLine();
        CLI::write('💡 RECOMMENDATIONS:', 'blue');
        CLI::write('1. Monitor router logs for reduced errors', 'white');
        CLI::write('2. Check system performance improvement', 'white');
        CLI::write('3. Test PPPoE creation now that scheduler errors are resolved', 'white');
        CLI::write('4. Create new working schedulers if needed for monitoring', 'white');
    }
}
