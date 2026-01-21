<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\PingMonitoringService;

class PingMonitor extends BaseCommand
{
    protected $group       = 'monitoring';
    protected $name        = 'monitor:ping';
    protected $description = 'Monitor router ping status (runs every 5 minutes via cron)';

    public function run(array $params)
    {
        CLI::write('Starting ping monitoring check...', 'yellow');
        CLI::newLine();

        $pingService = new PingMonitoringService();

        try {
            $results = $pingService->checkAllServers();

            $onlineCount = 0;
            $offlineCount = 0;
            $unknownCount = 0;

            foreach ($results as $result) {
                $server = $result['server'];
                $pingResult = $result['ping_result'];
                $status = $result['update_data']['ping_status'];

                $statusColor = match ($status) {
                    'online' => 'green',
                    'offline' => 'red',
                    default => 'yellow'
                };
                CLI::write(
                    sprintf(
                        "%-20s %-15s [%s] %s (%.2f ms)",
                        $server['name'],
                        $server['ip_router'],
                        strtoupper($status),
                        $pingResult['success'] ? 'OK' : 'FAILED',
                        $pingResult['response_time']
                    ),
                    $statusColor
                );

                match ($status) {
                    'online' => $onlineCount++,
                    'offline' => $offlineCount++,
                    default => $unknownCount++
                };
            }

            CLI::newLine();
            CLI::write("Ping monitoring summary:", 'cyan');
            CLI::write("  Online servers: {$onlineCount}", 'green');
            CLI::write("  Offline servers: {$offlineCount}", 'red');
            CLI::write("  Unknown status: {$unknownCount}", 'yellow');
            CLI::write("  Total checked: " . count($results), 'blue');

            // Log summary
            log_message('info', "Ping monitoring completed. Online: {$onlineCount}, Offline: {$offlineCount}, Unknown: {$unknownCount}");
        } catch (\Exception $e) {
            CLI::write("Error during ping monitoring: " . $e->getMessage(), 'red');
            log_message('error', "Ping monitoring failed: " . $e->getMessage());
        }

        CLI::newLine();
        CLI::write('Ping monitoring check completed.', 'green');
    }
}
