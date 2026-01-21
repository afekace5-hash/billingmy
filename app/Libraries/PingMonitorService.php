<?php

namespace App\Libraries;

use App\Models\ServerLocationModel;
use CodeIgniter\I18n\Time;

class PingMonitorService
{
    protected $serverModel;
    protected $logger;

    public function __construct()
    {
        $this->serverModel = new ServerLocationModel();
        $this->logger = service('logger');
    }

    /**
     * Ping a single router and update its status
     */
    public function pingRouter($serverId)
    {
        try {
            $server = $this->serverModel->find($serverId);
            if (!$server || !$server['auto_ping_enabled']) {
                return false;
            }

            $ip = $server['ip_router'];
            $pingResult = $this->performPing($ip);

            $updateData = [
                'last_ping_check' => date('Y-m-d H:i:s'),
                'last_ping_response_time' => $pingResult['response_time']
            ];

            if ($pingResult['success']) {
                $updateData['ping_status'] = 'online';
                $updateData['ping_failures_count'] = 0;
                $updateData['last_online_time'] = date('Y-m-d H:i:s');

                // Calculate uptime if this was previously offline
                if ($server['ping_status'] === 'offline') {
                    $this->updateUptimeCalculation($serverId, $server);
                }
            } else {
                $newFailureCount = (int)$server['ping_failures_count'] + 1;
                $updateData['ping_failures_count'] = $newFailureCount;

                // Mark as offline after 3 consecutive failures
                if ($newFailureCount >= 3) {
                    $updateData['ping_status'] = 'offline';
                }
            }

            $this->serverModel->update($serverId, $updateData);

            $this->logger->info("Ping check completed for router {$server['name']} ({$ip}): " .
                ($pingResult['success'] ? 'SUCCESS' : 'FAILED'));

            return $pingResult;
        } catch (\Exception $e) {
            $this->logger->error("Ping check failed for router ID {$serverId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ping all active routers
     */
    public function pingAllRouters()
    {
        $servers = $this->serverModel->where('auto_ping_enabled', 1)->findAll();
        $results = [];

        foreach ($servers as $server) {
            $results[$server['id_lokasi']] = $this->pingRouter($server['id_lokasi']);
        }

        $this->logger->info("Bulk ping check completed for " . count($servers) . " routers");
        return $results;
    }

    /**
     * Perform actual ping operation
     */
    private function performPing($ip, $timeout = 5)
    {
        $startTime = microtime(true);

        try {
            // Use system ping command
            $os = strtoupper(substr(PHP_OS, 0, 3));

            if ($os === 'WIN') {
                // Windows ping command
                $pingCmd = "ping -n 1 -w " . ($timeout * 1000) . " " . escapeshellarg($ip);
            } else {
                // Linux/Unix ping command
                $pingCmd = "ping -c 1 -W {$timeout} " . escapeshellarg($ip);
            }

            $output = [];
            $returnVar = 0;

            // Use socket connection instead of exec() for hosting compatibility
            if (function_exists('fsockopen')) {
                $fp = @fsockopen($ip, 80, $errno, $errstr, $timeout);
                if ($fp) {
                    $returnVar = 0;
                    $output[] = "Reply from $ip: Connection successful";
                    fclose($fp);
                } else {
                    $returnVar = 1;
                    $output[] = "Request timed out.";
                }
            } else {
                // Fallback to exec only if available
                if (function_exists('exec')) {
                    @exec($pingCmd, $output, $returnVar);
                } else {
                    $returnVar = 1;
                    $output[] = "Ping not available on this server";
                }
            }

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

            $success = $returnVar === 0;

            // Try to extract actual ping time from output if available
            if ($success && !empty($output)) {
                $outputStr = implode(' ', $output);
                if (preg_match('/time[<=](\d+\.?\d*)ms?/i', $outputStr, $matches)) {
                    $responseTime = (float)$matches[1];
                } elseif (preg_match('/time=(\d+\.?\d*)ms?/i', $outputStr, $matches)) {
                    $responseTime = (float)$matches[1];
                }
            }

            return [
                'success' => $success,
                'response_time' => $success ? $responseTime : null,
                'output' => $output,
                'ip' => $ip
            ];
        } catch (\Exception $e) {
            $this->logger->error("Ping execution failed for {$ip}: " . $e->getMessage());
            return [
                'success' => false,
                'response_time' => null,
                'output' => ['Error: ' . $e->getMessage()],
                'ip' => $ip
            ];
        }
    }

    /**
     * Update uptime calculation when router comes back online
     */
    private function updateUptimeCalculation($serverId, $serverData)
    {
        if (!empty($serverData['last_online_time'])) {
            $lastOnline = new Time($serverData['last_online_time']);
            $now = new Time();
            $downtimeHours = $lastOnline->difference($now)->getHours();

            // Add downtime period to total tracked time (for uptime percentage calculation)
            $currentUptime = (float)$serverData['total_uptime_hours'];
            // Note: We don't add downtime to uptime, but we could track total monitoring time
        }
    }

    /**
     * Get ping statistics for a router
     */
    public function getPingStats($serverId, $days = 7)
    {
        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return null;
        }

        // For now, return current status. In the future, you could implement
        // a ping_logs table to track historical data
        return [
            'current_status' => $server['ping_status'],
            'last_check' => $server['last_ping_check'],
            'response_time' => $server['last_ping_response_time'],
            'failures_count' => $server['ping_failures_count'],
            'last_online' => $server['last_online_time'],
            'uptime_hours' => $server['total_uptime_hours']
        ];
    }

    /**
     * Enable/disable ping monitoring for a router
     */
    public function togglePingMonitoring($serverId, $enabled = true)
    {
        return $this->serverModel->update($serverId, [
            'auto_ping_enabled' => $enabled ? 1 : 0
        ]);
    }

    /**
     * Get summary of all routers ping status
     */
    public function getPingSummary()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                ping_status,
                COUNT(*) as count,
                AVG(last_ping_response_time) as avg_response_time
            FROM lokasi_server 
            WHERE auto_ping_enabled = 1 
            GROUP BY ping_status
        ");

        $results = $query->getResultArray();

        $summary = [
            'online' => 0,
            'offline' => 0,
            'unknown' => 0,
            'total' => 0,
            'avg_response_time' => 0
        ];

        foreach ($results as $row) {
            $summary[$row['ping_status']] = (int)$row['count'];
            $summary['total'] += (int)$row['count'];

            if ($row['ping_status'] === 'online' && $row['avg_response_time']) {
                $summary['avg_response_time'] = round($row['avg_response_time'], 2);
            }
        }

        $summary['uptime_percentage'] = $summary['total'] > 0 ?
            round(($summary['online'] / $summary['total']) * 100, 2) : 0;

        return $summary;
    }
}
