<?php

namespace App\Libraries;

use App\Models\ServerLocationModel;
use CodeIgniter\I18n\Time;

class PingMonitoringService
{
    protected $serverModel;
    protected $logger;

    public function __construct()
    {
        $this->serverModel = new ServerLocationModel();
        $this->logger = service('logger');
    }
    /**
     * Ping a single server/router
     */
    public function pingServer($ipAddress, $timeout = 5)
    {
        $startTime = microtime(true);

        // Strip port number if present (ping doesn't use ports)
        $hostToPing = $ipAddress;
        if (strpos($ipAddress, ':') !== false) {
            $hostToPing = explode(':', $ipAddress)[0];
        }

        // For Windows systems
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pingCmd = "C:\\Windows\\System32\\ping.exe -n 1 -w " . ($timeout * 1000) . " " . escapeshellarg($hostToPing);
        } else {
            // For Linux/Unix systems
            $pingCmd = "ping -c 1 -W " . $timeout . " " . escapeshellarg($hostToPing);
        }

        $output = [];
        $returnCode = 0;

        // Use socket connection instead of exec() for hosting compatibility
        if (function_exists('fsockopen')) {
            $fp = @fsockopen($ipAddress, 80, $errno, $errstr, $timeout);
            if ($fp) {
                $returnCode = 0;
                $output[] = "Reply from $ipAddress: Connection successful";
                fclose($fp);
            } else {
                $returnCode = 1;
                $output[] = "Request timed out.";
            }
        } else {
            // Fallback to exec only if function exists and is enabled
            if (function_exists('exec')) {
                @exec($pingCmd, $output, $returnCode);
            } else {
                $returnCode = 1;
                $output[] = "Ping function not available on this server";
            }
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $result = [
            'success' => $returnCode === 0,
            'response_time' => $responseTime,
            'output' => implode("\n", $output),
            'ip_address' => $ipAddress,
            'pinged_host' => $hostToPing
        ];

        // Extract actual ping time from output if available
        if ($result['success']) {
            foreach ($output as $line) {
                if (preg_match('/time[<=](\d+(?:\.\d+)?)ms/i', $line, $matches)) {
                    $result['response_time'] = floatval($matches[1]);
                    break;
                } elseif (preg_match('/time=(\d+(?:\.\d+)?)ms/i', $line, $matches)) {
                    $result['response_time'] = floatval($matches[1]);
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Check all servers and update their ping status
     */
    public function checkAllServers()
    {
        $servers = $this->serverModel->where('auto_ping_enabled', 1)->findAll();
        $results = [];
        foreach ($servers as $server) {
            log_message('debug', 'Processing server: ' . json_encode($server));

            if (empty($server['ip_router'])) {
                continue;
            }

            $this->logger->info("Checking ping for server: {$server['name']} ({$server['ip_router']})");

            try {
                $pingResult = $this->pingServer($server['ip_router']);
                $updateData = $this->processPingResult($server, $pingResult);

                // Update server status
                $this->serverModel->update($server['id_lokasi'], $updateData);

                $results[] = [
                    'server' => $server,
                    'ping_result' => $pingResult,
                    'update_data' => $updateData
                ];
            } catch (\Exception $e) {
                log_message('error', 'Error in ping monitoring: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
                throw $e;
            }
        }

        return $results;
    }

    /**
     * Process ping result and prepare update data
     */
    private function processPingResult($server, $pingResult)
    {
        $now = Time::now();
        $updateData = [
            'last_ping_check' => $now->toDateTimeString(),
            'last_ping_response_time' => $pingResult['response_time']
        ];

        if ($pingResult['success']) {
            // Server is online
            $updateData['ping_status'] = 'online';
            $updateData['last_online_time'] = $now->toDateTimeString();
            $updateData['ping_failures_count'] = 0;

            // Calculate uptime if this is a status change from offline
            if ($server['ping_status'] === 'offline' || $server['ping_status'] === 'unknown') {
                $this->logger->info("Server {$server['name']} is back online");
            }
        } else {
            // Server is offline
            $newFailureCount = ($server['ping_failures_count'] ?? 0) + 1;
            $updateData['ping_failures_count'] = $newFailureCount;

            // Mark as offline after first failure
            $updateData['ping_status'] = 'offline';

            if ($server['ping_status'] === 'online') {
                $this->logger->warning("Server {$server['name']} went offline (failure #{$newFailureCount})");
            }
        }

        return $updateData;
    }

    /**
     * Get servers with their current status for API response
     */
    public function getServersStatus()
    {
        $servers = $this->serverModel->findAll();
        $result = [];
        foreach ($servers as $server) {
            $result[] = [
                'id' => $server['id_lokasi'],
                'name' => $server['name'],
                'ip_address' => $server['ip_router'],
                'ping_status' => $server['ping_status'] ?? 'unknown',
                'last_ping_check' => $server['last_ping_check'],
                'last_ping_response_time' => $server['last_ping_response_time'],
                'ping_failures_count' => $server['ping_failures_count'] ?? 0,
                'last_online_time' => $server['last_online_time'],
                'auto_ping_enabled' => $server['auto_ping_enabled'] ?? 1,
                'description' => $server['name'] // Using name as description for now
            ];
        }

        return $result;
    }


    /**
     * Manual ping check for a specific server
     */
    public function manualPingCheck($serverId)
    {
        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return [
                'success' => false,
                'message' => 'Server tidak ditemukan dengan ID: ' . $serverId,
                'details' => 'Pastikan server masih ada di database'
            ];
        }

        // Validate required connection fields
        $missingFields = [];
        if (empty($server['ip_router'])) $missingFields[] = 'IP Router';
        if (empty($server['username'])) $missingFields[] = 'Username';
        if (empty($server['password_router'])) $missingFields[] = 'Password';
        if (empty($server['port_api'])) $missingFields[] = 'Port API';

        if (!empty($missingFields)) {
            return [
                'success' => false,
                'message' => 'Data koneksi tidak lengkap: ' . implode(', ', $missingFields) . ' wajib diisi',
                'details' => 'Silakan lengkapi data server melalui form edit server',
                'missing_fields' => $missingFields,
                'server' => $server
            ];
        }

        $pingResult = $this->pingServer($server['ip_router']);
        $updateData = $this->processPingResult($server, $pingResult);

        // Update server status
        $this->serverModel->update($serverId, $updateData);

        // Reload server data to get the updated information
        $updatedServer = $this->serverModel->find($serverId);

        return [
            'success' => true,
            'ping_result' => $pingResult,
            'server' => $updatedServer,
            'updated_status' => $updateData['ping_status']
        ];
    }
}
