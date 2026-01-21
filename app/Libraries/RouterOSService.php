<?php

namespace App\Libraries;

use Config\RouterOS as RouterOSConfig;
use App\Libraries\MikrotikAPI;

class RouterOSService
{
    protected $config;
    protected $connection;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Connect to MikroTik router
     */
    public function connect()
    {
        try {
            // Get database connection
            $db = \Config\Database::connect();

            // First, try to get connected server (is_connected = 1)
            $activeRouter = $db->table('lokasi_server')
                ->where('is_connected', 1)
                ->orderBy('id_lokasi', 'ASC')
                ->get()
                ->getRowArray();

            // If no connected server, try any online server (ping_status = 'online')
            if (!$activeRouter) {
                $activeRouter = $db->table('lokasi_server')
                    ->where('ping_status', 'online')
                    ->orderBy('id_lokasi', 'ASC')
                    ->get()
                    ->getRowArray();
            }

            // If still no server, get first available server
            if (!$activeRouter) {
                $activeRouter = $db->table('lokasi_server')
                    ->orderBy('id_lokasi', 'ASC')
                    ->get()
                    ->getRowArray();
            }

            if ($activeRouter) {
                // Clean host - remove port if accidentally included
                $host = $activeRouter['ip_router'];
                // Remove port from host if present (e.g., "host:port" -> "host")
                if (strpos($host, ':') !== false) {
                    $parts = explode(':', $host);
                    $host = $parts[0];
                }

                $config = [
                    'host' => $host,
                    'user' => $activeRouter['username'],
                    'pass' => $activeRouter['password_router'],
                    'port' => (int)($activeRouter['port_api'] ?: 8728) // Ensure port is integer
                ];

                log_message('info', 'Using server from database: ' . ($activeRouter['name'] ?? 'Unknown') . ' (' . $config['host'] . ':' . $config['port'] . ')');
            } else {
                // Fallback to config values
                $config = [
                    'host' => $this->config->host,
                    'user' => $this->config->username,
                    'pass' => $this->config->password,
                    'port' => $this->config->port
                ];

                log_message('warning', 'No server found in database, using config fallback: ' . $config['host']);
            }

            // Use the project's custom MikroTik library
            $mikrotik = new MikrotikAPI($config);
            if (!$mikrotik->isConnected()) {
                throw new \Exception('Could not connect to MikroTik: ' . $mikrotik->getLastError());
            }

            // Test connection with a simple command
            try {
                $client = $mikrotik->getClient();
                $testResult = $client->comm('/system/identity/print');
                log_message('info', 'Connection test successful, identity: ' . json_encode($testResult));
            } catch (\Exception $e) {
                log_message('warning', 'Connection test failed but connection marked as active: ' . $e->getMessage());
            }

            $this->connection = $mikrotik;
            log_message('info', 'Successfully connected to MikroTik router');
            return $this->connection;
        } catch (\Exception $e) {
            log_message('error', 'RouterOS Connection Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Check if connected
     */
    public function isConnected()
    {
        if ($this->connection) {
            return $this->connection->isConnected();
        }
        return false;
    }

    /**
     * Get all profiles from router
     */
    public function getProfiles()
    {
        try {
            if (!$this->connection) {
                log_message('error', 'getProfiles: No MikroTik connection available');
                return null;
            }

            if (!$this->connection->isConnected()) {
                log_message('error', 'getProfiles: MikroTik connection not established');
                return null;
            }

            $client = $this->connection->getClient();
            if (!$client) {
                log_message('error', 'getProfiles: Could not get MikroTik client');
                return null;
            }

            log_message('info', 'Fetching PPP profiles from MikroTik...');

            // Fetch PPP profiles with error handling
            try {
                $pppProfiles = $client->comm('/ppp/profile/print');
                log_message('info', 'PPP profiles fetched: ' . count($pppProfiles ?: []));
            } catch (\Exception $e) {
                log_message('error', 'Failed to fetch PPP profiles: ' . $e->getMessage());
                $pppProfiles = [];
            }

            log_message('info', 'Fetching Queue profiles from MikroTik...');

            // Fetch Queue profiles with error handling
            try {
                $queueProfiles = $client->comm('/queue/simple/print');
                log_message('info', 'Queue profiles fetched: ' . count($queueProfiles ?: []));
            } catch (\Exception $e) {
                log_message('error', 'Failed to fetch Queue profiles: ' . $e->getMessage());
                $queueProfiles = [];
            }

            return [
                'ppp_profiles' => $pppProfiles ?: [],
                'queue_profiles' => $queueProfiles ?: []
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting MikroTik profiles: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new PPP profile
     */
    public function addPppProfile($name, $localAddress = '172.16.1.1', $rateLimitUp = '', $rateLimitDown = '', $comment = '')
    {
        try {
            if (!$this->connection) {
                throw new \Exception('No MikroTik connection available');
            }

            $params = [
                'name' => $name,
                'local-address' => $localAddress
            ];

            if ($rateLimitUp || $rateLimitDown) {
                $params['rate-limit'] = trim($rateLimitUp . '/' . $rateLimitDown);
            }

            if ($comment) {
                $params['comment'] = $comment;
            }

            $client = $this->connection->getClient();
            if (!$client) {
                throw new \Exception('Could not get MikroTik client');
            }

            return $client->comm('/ppp/profile/add', $params);
        } catch (\Exception $e) {
            log_message('error', 'Error adding PPP profile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new Queue profile
     */
    public function addQueueProfile($name, $target, $maxLimitUp = '', $maxLimitDown = '', $priority = '8', $comment = '')
    {
        try {
            if (!$this->connection) {
                throw new \Exception('No MikroTik connection available');
            }

            $params = [
                'name' => $name,
                'target' => $target,
                'priority' => $priority
            ];

            if ($maxLimitUp || $maxLimitDown) {
                $params['max-limit'] = trim($maxLimitUp . '/' . $maxLimitDown);
            }

            if ($comment) {
                $params['comment'] = $comment;
            }

            $client = $this->connection->getClient();
            if (!$client) {
                throw new \Exception('Could not get MikroTik client');
            }

            return $client->comm('/queue/simple/add', $params);
        } catch (\Exception $e) {
            log_message('error', 'Error adding Queue profile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a PPP profile
     */
    public function updatePppProfile($name, $localAddress = null, $rateLimitUp = null, $rateLimitDown = null, $comment = null)
    {
        try {
            if (!$this->connection) {
                throw new \Exception('No MikroTik connection available');
            }

            $client = $this->connection->getClient();
            if (!$client) {
                throw new \Exception('Could not get MikroTik client');
            }

            // Find profile
            $profiles = $client->comm('/ppp/profile/print', ['?name' => $name]);
            if (empty($profiles) || !isset($profiles[0]['.id'])) {
                throw new \Exception("Profile '{$name}' not found");
            }

            $params = ['.id' => $profiles[0]['.id']];

            if ($localAddress !== null) {
                $params['local-address'] = $localAddress;
            }

            if ($rateLimitUp !== null || $rateLimitDown !== null) {
                $params['rate-limit'] = trim(($rateLimitUp ?? '') . '/' . ($rateLimitDown ?? ''));
            }

            if ($comment !== null) {
                $params['comment'] = $comment;
            }

            return $client->comm('/ppp/profile/set', $params);
        } catch (\Exception $e) {
            log_message('error', 'Error updating PPP profile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a Queue profile
     */
    public function updateQueueProfile($name, $target = null, $maxLimitUp = null, $maxLimitDown = null, $priority = null, $comment = null)
    {
        try {
            if (!$this->connection) {
                throw new \Exception('No MikroTik connection available');
            }

            $client = $this->connection->getClient();
            if (!$client) {
                throw new \Exception('Could not get MikroTik client');
            }

            // Find profile
            $profiles = $client->comm('/queue/simple/print', ['?name' => $name]);
            if (empty($profiles) || !isset($profiles[0]['.id'])) {
                throw new \Exception("Profile '{$name}' not found");
            }

            $params = ['.id' => $profiles[0]['.id']];

            if ($target !== null) {
                $params['target'] = $target;
            }

            if ($maxLimitUp !== null || $maxLimitDown !== null) {
                $params['max-limit'] = trim(($maxLimitUp ?? '') . '/' . ($maxLimitDown ?? ''));
            }

            if ($priority !== null) {
                $params['priority'] = $priority;
            }

            if ($comment !== null) {
                $params['comment'] = $comment;
            }

            return $client->comm('/queue/simple/set', $params);
        } catch (\Exception $e) {
            log_message('error', 'Error updating Queue profile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a profile (PPP or Queue)
     */
    public function deleteProfile($name, $type = 'ppp')
    {
        try {
            if (!$this->connection) {
                throw new \Exception('No MikroTik connection available');
            }

            // Determine command based on type
            $command = $type === 'ppp' ? '/ppp/profile/print' : '/queue/simple/print';

            $client = $this->connection->getClient();
            if (!$client) {
                throw new \Exception('Could not get MikroTik client');
            }

            // Find profile
            $profiles = $client->comm($command, ['?name' => $name]);
            if (empty($profiles) || !isset($profiles[0]['.id'])) {
                throw new \Exception("Profile '{$name}' not found");
            }

            // Determine remove command based on type
            $removeCommand = $type === 'ppp' ? '/ppp/profile/remove' : '/queue/simple/remove';

            return $client->comm($removeCommand, ['.id' => $profiles[0]['.id']]);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting ' . strtoupper($type) . ' profile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Close connection
     */
    public function disconnect()
    {
        if ($this->connection) {
            $client = $this->connection->getClient();
            if ($client) {
                $client->disconnect();
            }
            $this->connection = null;
        }
    }
}
