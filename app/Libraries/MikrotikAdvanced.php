<?php

namespace App\Libraries;

use App\Libraries\MikrotikNew;

/**
 * MikroTik Advanced Library
 * 
 * Library untuk operasi advanced MikroTik:
 * - PPPoE Secret Management
 * - VPN Configuration
 * - Routing Management
 * - Queue Management
 * 
 * Menggunakan EvilFreelancer RouterOS API untuk VPN compatibility
 */
class MikrotikAdvanced
{
    protected $client;
    protected $config;
    protected $connected = false;
    protected $lastError = null;
    protected $isVPN = false;

    public function __construct($config = null)
    {
        if ($config === null) {
            $db = \Config\Database::connect();
            $serverLocation = $db->table('lokasi_server')->where('id_lokasi', 12)->get()->getRow();

            if ($serverLocation) {
                $config = [
                    'host' => $serverLocation->ip_router,
                    'user' => $serverLocation->username ?? 'admin',
                    'pass' => $serverLocation->password_router ?? '',
                    'port' => (int)($serverLocation->port_api ?? 8728),
                ];
            }
        }

        $this->config = $config;
        $this->isVPN = $this->detectVPN($config['host']);

        // Set timeout berdasarkan connection type
        $config['timeout'] = $this->isVPN ? 60 : 30;
        $config['attempts'] = 3;

        try {
            $this->safeLog('info', 'MikrotikAdvanced: Attempting connection to ' . $config['host'] .
                ' (Type: ' . ($this->isVPN ? 'VPN' : 'Direct') . ') - Using EvilFreelancer library');

            $this->client = new MikrotikNew($config);
            $this->connected = true;

            $this->safeLog('info', 'MikrotikAdvanced: Connection successful with EvilFreelancer');
        } catch (\Exception $e) {
            $this->connected = false;
            $this->lastError = $e->getMessage();
            $this->safeLog('error', 'MikrotikAdvanced: Connection failed - ' . $e->getMessage());
        }
    }

    /**
     * Detect if connection is VPN
     */
    private function detectVPN($host)
    {
        return strpos($host, 'hostddns.us') !== false ||
            strpos($host, 'tunnel.web.id') !== false ||
            strpos($host, 'vpn') !== false;
    }

    /**
     * Check if connected
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Get last error
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get MikroTik client for direct API calls
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Add PPPoE Secret with auto VPN and Routing setup
     * 
     * @param array $data Secret data
     * @return array Result with success status
     */
    public function addPPPoESecretComplete($data)
    {
        try {
            $this->safeLog('info', 'addPPPoESecretComplete: Starting for user ' . ($data['username'] ?? 'unknown'));

            if (!$this->connected) {
                throw new \Exception('Not connected to MikroTik');
            }

            $result = [
                'success' => false,
                'pppoe' => false,
                'queue' => false,
                'route' => false,
                'message' => ''
            ];

            // 1. Add PPPoE Secret
            $pppoeResult = $this->addPPPoESecret($data);
            $result['pppoe'] = $pppoeResult['success'];

            if (!$pppoeResult['success']) {
                $result['message'] = 'Failed to create PPPoE secret: ' . ($pppoeResult['message'] ?? 'Unknown error');
                return $result;
            }

            $this->safeLog('info', 'PPPoE secret created successfully');

            // 2. Add Queue (bandwidth management) if specified
            if (!empty($data['bandwidth_profile'])) {
                $queueResult = $this->addSimpleQueue($data);
                $result['queue'] = $queueResult['success'];

                if ($queueResult['success']) {
                    $this->safeLog('info', 'Queue created successfully');
                }
            }

            // 3. Add Static Route if needed (for VPN clients)
            if (!empty($data['static_route']) && $data['static_route'] === true) {
                $routeResult = $this->addStaticRoute($data);
                $result['route'] = $routeResult['success'];

                if ($routeResult['success']) {
                    $this->safeLog('info', 'Static route created successfully');
                }
            }

            $result['success'] = true;
            $result['message'] = 'PPPoE secret created successfully';

            return $result;
        } catch (\Exception $e) {
            $this->safeLog('error', 'addPPPoESecretComplete failed: ' . $e->getMessage());
            return [
                'success' => false,
                'pppoe' => false,
                'queue' => false,
                'route' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add PPPoE Secret
     */
    public function addPPPoESecret($data)
    {
        try {
            // Validate required fields
            $username = $data['username'] ?? $data['name'] ?? null;
            $password = $data['password'] ?? null;

            if (empty($username)) {
                throw new \Exception('PPPoE username/name is required but not provided');
            }

            if (empty($password)) {
                throw new \Exception('PPPoE password is required but not provided');
            }

            $this->safeLog('info', 'Adding PPPoE secret: ' . $username);

            // Prepare secret data
            $secretData = [
                'name' => $username,
                'password' => $password,
                'service' => $data['service'] ?? 'pppoe',
                'profile' => $data['profile'] ?? 'default',
            ];

            // Optional fields
            if (!empty($data['local_address'])) {
                $secretData['local-address'] = $data['local_address'];
            }
            if (!empty($data['remote_address'])) {
                $secretData['remote-address'] = $data['remote_address'];
            }
            // Comment removed - not needed

            // Build command parameters
            // comm() expects associative array, NOT array of strings like ['=name=value']
            // Just use $secretData directly as it's already in the right format
            $this->safeLog('info', 'Sending command: /ppp/secret/add with ' . count($secretData) . ' parameters');
            $this->safeLog('info', 'Parameters: ' . json_encode($secretData));

            // Execute with optimized approach for VPN
            $maxAttempts = $this->isVPN ? 1 : 1;
            $attempt = 0;
            $success = false;
            $lastException = null;

            while ($attempt < $maxAttempts && !$success) {
                $attempt++;

                try {
                    $this->safeLog('info', 'Attempt #' . $attempt . ' to add PPPoE secret');

                    // Use normal comm() - it expects associative array, not array of strings
                    $response = $this->client->comm('/ppp/secret/add', $secretData);
                    $this->safeLog('info', 'Response received: ' . json_encode($response));

                    if ($this->isSuccessResponse($response)) {
                        $success = true;
                        $this->safeLog('info', 'PPPoE secret added successfully');
                    } else {
                        $errorMsg = $this->extractErrorFromResponse($response);
                        throw new \Exception($errorMsg ?: 'Unknown error from MikroTik');
                    }
                } catch (\Exception $e) {
                    $lastException = $e;
                    $this->safeLog('warning', 'Attempt #' . $attempt . ' failed: ' . $e->getMessage());

                    // If "already exists" error, try to delete and retry
                    if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'same name') !== false) {
                        $this->safeLog('warning', 'Secret already exists, attempting to delete and retry...');

                        try {
                            // Find and delete existing secret
                            $existing = $this->client->comm('/ppp/secret/print', [
                                '?name' => $secretData['name']
                            ]);

                            if (!empty($existing) && is_array($existing)) {
                                foreach ($existing as $secret) {
                                    $secretId = $secret['.id'] ?? null;
                                    if ($secretId) {
                                        $this->safeLog('info', 'Deleting existing secret with ID: ' . $secretId);

                                        // Use string format for remove command
                                        $deleteResult = $this->client->comm('/ppp/secret/remove', [
                                            '=numbers=' . $secretId
                                        ]);

                                        $this->safeLog('info', 'Delete result: ' . json_encode($deleteResult));
                                    }
                                }

                                // Wait a bit and retry
                                sleep(1);
                                $this->safeLog('info', 'Retrying after deletion...');

                                // Retry create
                                $response = $this->client->comm('/ppp/secret/add', $secretData);
                                if ($this->isSuccessResponse($response)) {
                                    $success = true;
                                    $this->safeLog('info', 'PPPoE secret added successfully after deletion');
                                }
                            }
                        } catch (\Exception $deleteException) {
                            $this->safeLog('error', 'Failed to delete and retry: ' . $deleteException->getMessage());
                        }
                    }

                    // For timeout, try quick verify
                    if (strpos($e->getMessage(), 'timed out') !== false) {
                        $this->safeLog('info', 'Timeout detected, quick check...');
                        $verifyResult = $this->verifySecretExists($secretData['name']);
                        if ($verifyResult) {
                            $this->safeLog('info', 'Secret exists despite timeout!');
                            return [
                                'success' => true,
                                'message' => 'Secret created (verified)',
                                'verified' => true
                            ];
                        }
                    }
                }
            }

            if (!$success) {
                // Final verify attempt
                $verifyResult = $this->verifySecretExists($secretData['name']);
                if ($verifyResult) {
                    $this->safeLog('info', 'Secret exists despite error - considering as success');
                    return [
                        'success' => true,
                        'message' => 'Secret created (verified)',
                        'verified' => true
                    ];
                }

                throw $lastException ?? new \Exception('Failed to add PPPoE secret after ' . $maxAttempts . ' attempts');
            }

            return [
                'success' => true,
                'message' => 'PPPoE secret created successfully'
            ];
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add Simple Queue for bandwidth management
     */
    public function addSimpleQueue($data)
    {
        try {
            $username = $data['username'] ?? $data['name'];
            $this->safeLog('info', 'Adding simple queue for: ' . $username);

            // Parse bandwidth (e.g., "10M/10M" or "10000000/10000000")
            $bandwidth = $this->parseBandwidth($data['bandwidth_profile'] ?? $data['bandwidth'] ?? '');

            if (empty($bandwidth)) {
                return [
                    'success' => false,
                    'message' => 'Invalid bandwidth specification'
                ];
            }

            $params = [
                '=name=' . $username,
                '=target=' . ($data['remote_address'] ?? ''),
                '=max-limit=' . $bandwidth,
                '=comment=Auto queue for ' . $username
            ];

            $response = $this->client->comm('/queue/simple/add', $params);

            if ($this->isSuccessResponse($response)) {
                $this->safeLog('info', 'Queue created successfully');
                return [
                    'success' => true,
                    'message' => 'Queue created'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create queue'
            ];
        } catch (\Exception $e) {
            $this->safeLog('error', 'Failed to add queue: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add Static Route
     */
    public function addStaticRoute($data)
    {
        try {
            $this->safeLog('info', 'Adding static route');

            if (empty($data['dst_address'])) {
                return [
                    'success' => false,
                    'message' => 'Destination address required for routing'
                ];
            }

            $params = [
                '=dst-address=' . $data['dst_address'],
                '=gateway=' . ($data['gateway'] ?? 'pppoe-' . $data['username']),
                '=comment=Auto route for ' . ($data['username'] ?? 'customer')
            ];

            $response = $this->client->comm('/ip/route/add', $params);

            if ($this->isSuccessResponse($response)) {
                $this->safeLog('info', 'Static route created successfully');
                return [
                    'success' => true,
                    'message' => 'Route created'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create route'
            ];
        } catch (\Exception $e) {
            $this->safeLog('error', 'Failed to add route: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify if secret exists - optimized for VPN
     */
    private function verifySecretExists($username)
    {
        try {
            $this->safeLog('info', 'Quick verify: ' . $username);

            // Use normal comm() for all connections
            $response = $this->client->comm('/ppp/secret/print', ['?name=' . $username]);

            if (!empty($response) && is_array($response)) {
                foreach ($response as $item) {
                    if (is_array($item) && isset($item['name']) && $item['name'] === $username) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            $this->safeLog('debug', 'Verify timeout (acceptable): ' . $e->getMessage());
            return false; // Assume not yet created if verify times out
        }
    }
    /**
     * Check if response indicates success
     */
    private function isSuccessResponse($response)
    {
        if (!is_array($response)) {
            return false;
        }

        // Empty array usually means success
        if (empty($response)) {
            return true;
        }

        // Check for !done
        foreach ($response as $item) {
            if ($item === '!done') {
                return true;
            }
            if (is_array($item) && isset($item['!done'])) {
                return true;
            }
            // Check for error indicators
            if (is_string($item) && (strpos($item, '!trap') === 0 || strpos($item, '!fatal') === 0)) {
                return false;
            }
            if (is_array($item) && isset($item['!trap'])) {
                return false;
            }
        }

        // If no error found, consider as success
        return true;
    }

    /**
     * Extract error message from response
     */
    private function extractErrorFromResponse($response)
    {
        if (!is_array($response)) {
            return 'Invalid response format';
        }

        foreach ($response as $item) {
            if (is_array($item) && isset($item['message'])) {
                return $item['message'];
            }
            if (is_string($item) && strpos($item, '!trap') === 0) {
                return $item;
            }
        }

        return 'Unknown error';
    }

    /**
     * Parse bandwidth specification
     */
    private function parseBandwidth($bandwidth)
    {
        if (empty($bandwidth)) {
            return '';
        }

        // If already in format "10M/10M", return as is
        if (preg_match('/^\d+[KMG]\/\d+[KMG]$/i', $bandwidth)) {
            return $bandwidth;
        }

        // If just "10M" or "10 Mbps", convert to "10M/10M"
        if (preg_match('/^(\d+)\s*(M|Mbps|MB)?/i', $bandwidth, $matches)) {
            $speed = $matches[1] . 'M';
            return $speed . '/' . $speed;
        }

        return $bandwidth;
    }

    /**
     * Sanitize comment text
     */
    private function sanitizeComment($comment)
    {
        // Remove problematic characters
        $comment = str_replace(['"', "'", "\n", "\r", "\\"], ['', '', ' ', '', ''], $comment);
        return substr($comment, 0, 255); // Limit length
    }

    /**
     * Safe logging
     */
    private function safeLog($level, $message)
    {
        if (function_exists('log_message')) {
            log_message($level, $message);
        } else {
            error_log("[$level] $message");
        }
    }

    /**
     * Remove PPPoE Secret
     */
    public function removePPPoESecret($username)
    {
        try {
            $this->safeLog('info', 'Removing PPPoE secret: ' . $username);

            // Find secret
            $response = $this->client->comm('/ppp/secret/print', ['?name=' . $username]);

            if (empty($response)) {
                $this->safeLog('info', 'Secret not found, considering as already removed');
                return [
                    'success' => true,
                    'message' => 'Secret not found (already removed)'
                ];
            }

            // Get ID
            $secretId = null;
            foreach ($response as $item) {
                if (is_array($item) && isset($item['.id'])) {
                    $secretId = $item['.id'];
                    break;
                }
            }

            if (!$secretId) {
                throw new \Exception('Could not find secret ID');
            }

            // Remove
            $removeResponse = $this->client->comm('/ppp/secret/remove', ['.id=' . $secretId]);

            $this->safeLog('info', 'PPPoE secret removed successfully');

            return [
                'success' => true,
                'message' => 'Secret removed'
            ];
        } catch (\Exception $e) {
            $this->safeLog('error', 'Failed to remove secret: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Alias for removePPPoESecret - for compatibility with MikrotikAPI
     */
    public function removePPPSecret($username)
    {
        return $this->removePPPoESecret($username);
    }

    /**
     * Get all PPPoE secrets
     */
    public function getPPPoESecrets()
    {
        try {
            $response = $this->client->comm('/ppp/secret/print');
            return $response;
        } catch (\Exception $e) {
            $this->safeLog('error', 'Failed to get secrets: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get PPP profiles
     */
    public function getPPPProfiles()
    {
        try {
            $response = $this->client->comm('/ppp/profile/print');
            return $response;
        } catch (\Exception $e) {
            $this->safeLog('error', 'Failed to get profiles: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        if (!$this->connected) {
            return [
                'success' => false,
                'message' => $this->lastError ?? 'Not connected'
            ];
        }

        try {
            $identity = $this->client->comm('/system/identity/print');

            return [
                'success' => true,
                'message' => 'Connection successful',
                'identity' => $identity[0]['name'] ?? 'Unknown',
                'connection_type' => $this->isVPN ? 'VPN' : 'Direct'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
