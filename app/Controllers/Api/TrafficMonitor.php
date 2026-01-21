<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ServerLocationModel;
use App\Libraries\MikrotikNew;
use Config\RouterOS as RouterOSConfig;

class TrafficMonitor extends BaseController
{
    private $lokasiServerModel;
    private $routerOSConfig;

    public function __construct()
    {
        $this->lokasiServerModel = new ServerLocationModel();
        $this->routerOSConfig = new RouterOSConfig();
    }

    /**
     * Connect to RouterOS using the proven RouterOS API library
     */
    private function connectToRouterOS($host, $port, $username, $password)
    {
        try {
            log_message('debug', "Attempting RouterOS connection to {$host}:{$port} with user {$username}");

            $routerOS = new MikrotikNew();
            $connected = $routerOS->connect($host, $username, $password, intval($port));

            if ($connected) {
                log_message('debug', 'RouterOS connection successful');
                return $routerOS;
            } else {
                log_message('error', "RouterOS connection failed to {$host}:{$port}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'RouterOS connection exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get interfaces from RouterOS using the proven API
     */
    private function getRouterOSInterfacesNew($routerOS)
    {
        try {
            log_message('debug', 'Getting interfaces from RouterOS API');

            $response = $routerOS->comm('/interface/print');
            if ($response && is_array($response)) {
                $interfaces = [];
                foreach ($response as $interface) {
                    $interfaceType = isset($interface['type']) ? $interface['type'] : 'unknown';
                    $interfaceName = $interface['name'];

                    // Skip PPPoE interfaces (pppoe-in, pppoe-out)
                    if (strpos($interfaceType, 'pppoe') !== false) {
                        continue;
                    }

                    // Also skip interface names that contain 'pppoe'
                    if (strpos(strtolower($interfaceName), 'pppoe') !== false) {
                        continue;
                    }

                    $interfaces[] = [
                        'id' => $interfaceName,
                        'name' => $interfaceName,
                        'type' => $interfaceType,
                        'running' => isset($interface['running']) ? ($interface['running'] === 'true') : false
                    ];
                }

                log_message('debug', 'RouterOS interfaces found (excluding PPPoE): ' . count($interfaces));
                return $interfaces;
            } else {
                log_message('warning', 'No interfaces found from RouterOS');
                return [];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching RouterOS interfaces: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Get traffic statistics from RouterOS using the proven API
     * Returns both total bytes and real-time rates
     */
    private function getRouterOSTrafficStatsNew($routerOS, $interfaceName)
    {
        try {
            log_message('debug', "Getting traffic stats for interface: {$interfaceName}");

            // Get interface statistics (total bytes)
            $response = $routerOS->comm('/interface/print', [
                '?name' => $interfaceName
            ]);

            $rxBytes = 0;
            $txBytes = 0;

            if ($response && is_array($response) && !empty($response)) {
                $interface = $response[0];
                $rxBytes = isset($interface['rx-byte']) ? (int)$interface['rx-byte'] : 0;
                $txBytes = isset($interface['tx-byte']) ? (int)$interface['tx-byte'] : 0;
            }

            // Get real-time traffic rates (bits per second)
            $txRate = 0;
            $rxRate = 0;

            try {
                $trafficResponse = $routerOS->comm('/interface/monitor-traffic', [
                    'interface' => $interfaceName,
                    'once' => ''
                ]);

                if ($trafficResponse && is_array($trafficResponse) && !empty($trafficResponse)) {
                    $traffic = $trafficResponse[0];
                    $txRate = isset($traffic['tx-bits-per-second']) ? (int)$traffic['tx-bits-per-second'] : 0;
                    $rxRate = isset($traffic['rx-bits-per-second']) ? (int)$traffic['rx-bits-per-second'] : 0;
                }
            } catch (\Exception $e) {
                log_message('warning', 'Could not get real-time traffic rates: ' . $e->getMessage());
            }

            log_message('debug', "RouterOS traffic stats - RX: {$rxBytes} bytes, TX: {$txBytes} bytes, RX Rate: {$rxRate} bps, TX Rate: {$txRate} bps");

            return [
                // Total bytes (cumulative)
                'rx_bytes' => $rxBytes,
                'tx_bytes' => $txBytes,
                'rx_mb' => round($rxBytes / 1024 / 1024, 2),
                'tx_mb' => round($txBytes / 1024 / 1024, 2),
                'total_bytes' => $rxBytes + $txBytes,
                'total_mb' => round(($rxBytes + $txBytes) / 1024 / 1024, 2),
                'rx_gb' => round($rxBytes / 1024 / 1024 / 1024, 3),
                'tx_gb' => round($txBytes / 1024 / 1024 / 1024, 3),
                'total_gb' => round(($rxBytes + $txBytes) / 1024 / 1024 / 1024, 3),

                // Real-time rates (bits per second) - for frontend display
                'tx_rate' => $txRate,
                'rx_rate' => $rxRate,
                'tx_rate_mbps' => round($txRate / 1024 / 1024, 2),
                'rx_rate_mbps' => round($rxRate / 1024 / 1024, 2)
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error fetching RouterOS traffic stats: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current traffic data from RouterOS (real-time monitoring)
     */
    private function getRouterOSTrafficHistoryNew($routerOS, $interfaceName)
    {
        try {
            log_message('debug', "Getting traffic history for interface: {$interfaceName}");

            $labels = [];
            $txData = [];
            $rxData = [];

            // Generate time labels for last 10 data points (every 10 seconds)
            $now = time();
            for ($i = 9; $i >= 0; $i--) {
                $timestamp = $now - ($i * 10);
                $labels[] = date('H:i:s', $timestamp);
            }

            // Get current real-time traffic data from RouterOS
            $response = $routerOS->comm('/interface/monitor-traffic', [
                'interface' => $interfaceName,
                'once' => ''
            ]);

            if ($response && is_array($response) && !empty($response)) {
                $traffic = $response[0];
                $currentTx = isset($traffic['tx-bits-per-second']) ? (int)$traffic['tx-bits-per-second'] : 0;
                $currentRx = isset($traffic['rx-bits-per-second']) ? (int)$traffic['rx-bits-per-second'] : 0;

                log_message('debug', "RouterOS current traffic - TX: {$currentTx}, RX: {$currentRx}");                // Use only current real data - no variations or simulations
                for ($i = 0; $i < 10; $i++) {
                    $txData[] = $currentTx;
                    $rxData[] = $currentRx;
                }

                return [
                    'labels' => $labels,
                    'tx_data' => $txData,
                    'rx_data' => $rxData,
                    'interface' => $interfaceName,
                    'timestamp' => time(),
                    'current_stats' => [
                        'tx_bps' => $currentTx,
                        'rx_bps' => $currentRx,
                        'tx_kbps' => round($currentTx / 1024, 2),
                        'rx_kbps' => round($currentRx / 1024, 2),
                        'tx_mbps' => round($currentTx / 1024 / 1024, 2),
                        'rx_mbps' => round($currentRx / 1024 / 1024, 2),
                        'units' => 'bits_per_second'
                    ]
                ];
            } else {
                log_message('warning', "No traffic history data found for interface: {$interfaceName}");
                return null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching RouterOS traffic history: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse host and port from ip_router
     */
    private function parseHostPort($ipRouter)
    {
        if (strpos($ipRouter, ':') !== false) {
            $parts = explode(':', $ipRouter);
            return [$parts[0], (int)$parts[1]]; // host, port dari URL
        }
        return [$ipRouter, 8728]; // default port 8728 jika tidak ada
    }

    /**
     * Get list of servers
     */
    public function servers()
    {
        try {
            $servers = $this->lokasiServerModel->findAll();

            $serverList = [];
            foreach ($servers as $server) {
                $serverList[] = [
                    'id' => $server['id_lokasi'],
                    'name' => $server['name'],
                    'ip' => $server['ip_router'],
                    'status' => $server['is_connected'] ? 'connected' : 'disconnected'
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $serverList
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load servers: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Get interfaces for a specific server - REAL DATA ONLY
     */
    public function interfaces($serverId)
    {
        try {
            if (!$serverId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Server ID is required',
                    'data' => []
                ]);
            }

            // Use correct primary key
            $server = $this->lokasiServerModel->where('id_lokasi', $serverId)->first();
            if (!$server) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Server not found',
                    'data' => []
                ]);
            }

            list($host, $port) = $this->parseHostPort($server['ip_router']);
            $password = $server['password'] ?? '';
            $routerOS = $this->connectToRouterOS($host, $port, $server['username'], $password);

            if ($routerOS) {
                try {
                    $interfaces = $this->getRouterOSInterfacesNew($routerOS);
                    $routerOS->disconnect();

                    if (!empty($interfaces)) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'data' => $interfaces,
                            'source' => 'routeros'
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'No interfaces found on RouterOS',
                            'data' => []
                        ]);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error during RouterOS interface fetch: ' . $e->getMessage());
                    $routerOS->disconnect();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to fetch interfaces from RouterOS: ' . $e->getMessage(),
                        'data' => []
                    ]);
                }
            }

            // No fallback - return error if RouterOS connection fails
            log_message('error', 'RouterOS API connection failed');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'RouterOS API connection failed. Please check server configuration.',
                'data' => []
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load interfaces: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Get traffic statistics for a specific server and interface - REAL DATA ONLY
     */
    public function stats($serverId, $interface = null)
    {
        try {
            if (!$serverId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Server ID is required',
                    'data' => null
                ]);
            }
            if (!$interface) {
                $interface = $this->request->getGet('interface');
            }
            if (!$interface) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Interface is required',
                    'data' => null
                ]);
            }
            // Use correct primary key
            $server = $this->lokasiServerModel->where('id_lokasi', $serverId)->first();
            if (!$server) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Server not found',
                    'data' => null
                ]);
            }

            list($host, $port) = $this->parseHostPort($server['ip_router']);
            $password = $server['password'] ?? '';
            $routerOS = $this->connectToRouterOS($host, $port, $server['username'], $password);

            if ($routerOS) {
                try {
                    $trafficData = $this->getRouterOSTrafficStatsNew($routerOS, $interface);
                    $routerOS->disconnect();

                    if ($trafficData) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'data' => $trafficData,
                            'source' => 'routeros'
                        ]);
                    } else {
                        log_message('error', 'RouterOS connected but no traffic data found for interface: ' . $interface);
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'No traffic data found for interface: ' . $interface,
                            'data' => null
                        ]);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error during RouterOS traffic fetch: ' . $e->getMessage());
                    $routerOS->disconnect();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to fetch traffic data from RouterOS: ' . $e->getMessage(),
                        'data' => null
                    ]);
                }
            }

            // No fallback - return error if RouterOS connection fails
            log_message('error', 'RouterOS API connection failed for traffic stats');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'RouterOS API connection failed. Please check server configuration.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get traffic stats: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Get historical traffic data for charts - REAL DATA ONLY
     */
    public function traffic($serverId, $interface = null)
    {
        try {
            if (!$serverId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Server ID is required',
                    'data' => null
                ]);
            }
            if (!$interface) {
                $interface = $this->request->getGet('interface');
            }
            if (!$interface) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Interface is required',
                    'data' => null
                ]);
            }

            // Use correct primary key
            $server = $this->lokasiServerModel->where('id_lokasi', $serverId)->first();
            if (!$server) {
                log_message('error', "Server not found with id_lokasi: {$serverId}");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Server not found',
                    'data' => null
                ]);
            }

            list($host, $port) = $this->parseHostPort($server['ip_router']);

            // Fix: Use 'password' field, not 'password_router'
            $password = $server['password'] ?? '';
            $routerOS = $this->connectToRouterOS($host, $port, $server['username'], $password);

            if ($routerOS) {
                try {
                    // Get current traffic data from RouterOS (real-time only)
                    $historyData = $this->getRouterOSTrafficHistoryNew($routerOS, $interface);
                    $routerOS->disconnect();

                    if ($historyData) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'data' => $historyData,
                            'source' => 'routeros'
                        ]);
                    } else {
                        log_message('error', 'RouterOS connected but no traffic history data found for interface: ' . $interface);
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'No traffic history data found for interface: ' . $interface,
                            'data' => null
                        ]);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error during RouterOS traffic history fetch: ' . $e->getMessage());
                    $routerOS->disconnect();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to fetch traffic history from RouterOS: ' . $e->getMessage(),
                        'data' => null
                    ]);
                }
            }

            // No fallback - return error if RouterOS connection fails
            log_message('error', 'RouterOS API connection failed for traffic history');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'RouterOS API connection failed. Please check server configuration.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Traffic endpoint exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to get traffic history: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
