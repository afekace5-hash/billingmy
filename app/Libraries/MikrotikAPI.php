<?php

namespace App\Libraries;

use App\Libraries\MikrotikNew;

class MikrotikAPI
{
    protected $client;
    protected $config;
    protected $connected = false;
    protected $lastError = null;
    public function __construct($config = null)
    {
        // Default config jika tidak diberikan - menggunakan data dari lokasi_server database
        if ($config === null) {
            // Gunakan database untuk mendapatkan konfigurasi default
            $db = \Config\Database::connect();
            $serverLocation = $db->table('lokasi_server')->where('id_lokasi', 12)->get()->getRow();

            if ($serverLocation) {
                // GUNAKAN DATA DARI DATABASE TANPA MENGUBAH APAPUN
                $config = [
                    'host' => $serverLocation->ip_router ?? 'localhost', // id-14.hostddns.us:8211
                    'user' => $serverLocation->username ?? 'admin', // userbaru
                    'pass' => $serverLocation->password_router ?? '', // lailia
                    'port' => (int)($serverLocation->port_api ?? 8728), // 8728
                ];
            }
        }

        $this->config = $config;
        try {
            // Log konfigurasi yang akan digunakan
            $this->safeLog('info', 'Attempting MikroTik connection with config: ' . json_encode([
                'host' => $config['host'],
                'user' => $config['user'],
                'port' => $config['port'] ?? 8728,
                'timeout' => $config['timeout'] ?? 30
            ]));

            // Detect VPN and adjust timeout if not set
            $isVPN = strpos($config['host'], 'hostddns.us') !== false ||
                strpos($config['host'], 'tunnel.web.id') !== false;

            if ($isVPN && (!isset($config['timeout']) || $config['timeout'] < 60)) {
                $config['timeout'] = 120; // Force minimum 2 minutes for VPN
                $this->safeLog('info', 'VPN connection detected, timeout increased to 120 seconds');
            }

            // Buat koneksi dengan handling untuk tunnel
            $this->client = new MikrotikNew([
                'host' => $config['host'],
                'user' => $config['user'],
                'pass' => $config['pass'],
                'port' => $config['port'] ?? 8728,
                'timeout' => $config['timeout'] ?? 120, // Default 2 minutes for better compatibility
                'attempts' => 5 // Retry connection attempts
            ]);

            // Mark as connected without test command (test command will be done in testConnection)
            $this->connected = true;
            $this->safeLog('info', 'MikroTik connection initialized for: ' . $config['host'] . ':' . ($config['port'] ?? 8728));
        } catch (\Exception $e) {
            $this->connected = false;
            $errorMsg = 'Could not connect to MikroTik router';

            // Determine the connection endpoint
            $endpoint = $config['host'] . ':' . ($config['port'] ?? 8728);
            $errorMsg .= ' (Endpoint: ' . $endpoint;
            $errorMsg .= ', User: ' . ($config['user'] ?? 'not set') . ')';

            // Log the specific error from the RouterOS API
            $specificError = $e->getMessage();

            // Better error categorization
            if (strpos($specificError, 'Undefined array key') !== false) {
                $this->safeLog('error', 'RouterOS API communication error - possible authentication or protocol issue');
                $errorMsg .= ' - Authentication or protocol error';
            } elseif (strpos($specificError, 'Cannot execute command') !== false) {
                $this->safeLog('error', 'Cannot execute command: Not connected to MikroTik');
                $errorMsg .= ' - Connection established but command execution failed';
            } elseif (strpos($specificError, 'timeout') !== false || strpos($specificError, 'timed out') !== false) {
                $this->safeLog('error', 'Connection timeout to MikroTik');
                $errorMsg .= ' - Connection timeout';
            } else {
                $this->safeLog('error', 'MikroTik connection failed: ' . $specificError);
                $errorMsg .= ' - ' . $specificError;
            }

            // Simpan error untuk testConnection()
            $this->lastError = $errorMsg;
        }
    }

    public function isConnected()
    {
        return $this->connected;
    }
    public function getConfig()
    {
        return $this->config;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getSystemResource()
    {
        try {
            if (!$this->connected) {
                throw new \Exception('Not connected to MikroTik router');
            }

            $response = $this->client->comm('/system/resource/print');
            $this->safeLog('info', 'System resource retrieved: ' . json_encode($response));

            // Ensure we have valid data
            if (empty($response) || !is_array($response)) {
                throw new \Exception('Empty or invalid response from system/resource/print');
            }

            // Biasanya hanya satu data, jadi ambil index ke-0
            return $response[0] ?? [];
        } catch (\Exception $e) {
            $errorMsg = 'Failed to get system resource: ' . $e->getMessage();
            $this->safeLog('warning', $errorMsg);

            // Check if this is the array key error
            if (strpos($e->getMessage(), 'Undefined array key') !== false) {
                $this->safeLog('warning', 'RouterOS API array access error - possibly due to connection timeout or protocol mismatch');
            }

            throw new \Exception($errorMsg);
        }
    }

    public function getInterfaces()
    {
        try {
            return $this->client->comm('/interface/print');
        } catch (\Exception $e) {
            $this->safeLog('debug', 'Failed to get interfaces: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPPPSecrets()
    {
        try {
            $secrets = $this->client->comm('/ppp/secret/print');
            $this->safeLog('info', 'PPP secrets retrieved: ' . count($secrets) . ' secrets found');
            return $secrets;
        } catch (\Exception $e) {
            $this->safeLog('debug', 'Failed to get PPP secrets: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add PPPoE secret to MikroTik
     */ public function addPPPSecret($secretData)
    {
        try { // Debug log the incoming data
            $this->safeLog('info', 'addPPPSecret called with data: ' . json_encode($secretData)); // Validate profile exists if specified
            if (isset($secretData['profile']) && !empty($secretData['profile'])) {
                $this->safeLog('info', 'Checking if profile exists: ' . $secretData['profile']);
                try {
                    // Check for exact match first
                    $profiles = $this->client->comm('/ppp/profile/print', ['?name=' . $secretData['profile']]);
                    if (empty($profiles)) {
                        // Try to find profile with partial match (case-insensitive)
                        $allProfiles = $this->client->comm('/ppp/profile/print');
                        $matchedProfile = null;

                        foreach ($allProfiles as $profile) {
                            if (isset($profile['name'])) {
                                if (strcasecmp($profile['name'], $secretData['profile']) === 0) {
                                    $matchedProfile = $profile['name'];
                                    break;
                                }
                            }
                        }

                        if ($matchedProfile) {
                            $this->safeLog('info', 'Found profile with case-insensitive match: ' . $matchedProfile);
                            $secretData['profile'] = $matchedProfile;
                        } else {
                            $this->safeLog('warning', 'Specified profile does not exist, trying default-encryption profile: ' . $secretData['profile']);

                            // Try common profile names
                            $fallbackProfiles = ['default-encryption', 'default', 'pppoe-default'];
                            $foundFallback = false;

                            foreach ($fallbackProfiles as $fallback) {
                                $fallbackCheck = $this->client->comm('/ppp/profile/print', ['?name=' . $fallback]);
                                if (!empty($fallbackCheck)) {
                                    $this->safeLog('info', 'Using fallback profile: ' . $fallback);
                                    $secretData['profile'] = $fallback;
                                    $foundFallback = true;
                                    break;
                                }
                            }

                            if (!$foundFallback) {
                                $this->safeLog('warning', 'No fallback profile found, removing profile parameter');
                                unset($secretData['profile']); // Let MikroTik use its default
                            }
                        }
                    } else {
                        $this->safeLog('info', 'Profile exists and will be used: ' . $secretData['profile']);
                    }
                } catch (\Exception $e) {
                    $this->safeLog('warning', 'Could not verify profile existence, using default-encryption: ' . $e->getMessage());
                    $secretData['profile'] = 'default-encryption'; // Most common default profile
                }
            }

            // Prepare parameters for /ppp/secret/add command
            $params = [];

            // Required parameters
            if (isset($secretData['name'])) {
                $params[] = '=name=' . $secretData['name'];
            } else {
                $this->safeLog('error', 'Missing required field: name');
                throw new \Exception('Missing required field: name');
            }

            if (isset($secretData['password'])) {
                $params[] = '=password=' . $secretData['password'];
            } else {
                $this->safeLog('error', 'Missing required field: password');
                throw new \Exception('Missing required field: password');
            }

            // Optional parameters - tambahkan dalam urutan yang benar
            if (isset($secretData['service']) && !empty($secretData['service'])) {
                $params[] = '=service=' . $secretData['service'];
            }

            if (isset($secretData['profile']) && !empty($secretData['profile'])) {
                $params[] = '=profile=' . $secretData['profile'];
            }

            if (isset($secretData['local-address']) && !empty($secretData['local-address'])) {
                $params[] = '=local-address=' . $secretData['local-address'];
            }

            if (isset($secretData['remote-address']) && !empty($secretData['remote-address'])) {
                $params[] = '=remote-address=' . $secretData['remote-address'];
            }

            if (isset($secretData['comment']) && !empty($secretData['comment'])) {
                // Escape karakter khusus dalam comment
                $comment = str_replace(['"', "'", "\n", "\r"], ['', '', ' ', ''], $secretData['comment']);
                $params[] = '=comment=' . $comment;
            }

            if (isset($secretData['disabled'])) {
                $params[] = '=disabled=' . ($secretData['disabled'] ? 'yes' : 'no');
            }

            $this->safeLog('info', 'Creating PPP secret with params: ' . json_encode($params));

            // Execute the command with timeout handling
            $result = null;
            $secretCreated = false;

            try {
                $this->safeLog('info', 'Attempting PPP secret creation - Username: ' . $secretData['name']);

                // Untuk VPN connection, gunakan approach yang lebih robust
                $isVPN = strpos($this->config['host'], 'hostddns.us') !== false ||
                    strpos($this->config['host'], 'tunnel.web.id') !== false;

                if ($isVPN) {
                    $this->safeLog('info', 'VPN connection detected, using extended timeout approach');
                }

                // Kirim command
                $result = $this->client->comm('/ppp/secret/add', $params);
                $this->safeLog('info', 'PPP secret add command sent, result: ' . json_encode($result));
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                $this->safeLog('error', 'Exception during PPP secret creation: ' . $errorMsg);

                // Check if it's a timeout error
                if (strpos($errorMsg, 'timeout') !== false || strpos($errorMsg, 'timed out') !== false) {
                    $this->safeLog('warning', 'PPP secret creation timed out - verifying if secret was created');

                    // Wait longer for VPN connections
                    sleep(5);

                    try {
                        $this->safeLog('info', 'Verifying if PPP secret was created despite timeout');
                        $existingSecrets = $this->client->comm('/ppp/secret/print', ['?name=' . $secretData['name']]);

                        if (!empty($existingSecrets)) {
                            $this->safeLog('info', 'PPP secret was created successfully despite timeout: ' . $secretData['name']);
                            // Consider it successful since the secret exists
                            $result = true; // Indicate success
                        } else {
                            $this->safeLog('error', 'PPP secret was not created after timeout');
                            throw $e; // Re-throw the original timeout error
                        }
                    } catch (\Exception $verifyException) {
                        $this->safeLog('error', 'Could not verify PPP secret creation after timeout: ' . $verifyException->getMessage());
                        throw $e; // Re-throw the original timeout error
                    }
                } else {
                    // Non-timeout error, don't retry
                    throw $e;
                }
            }

            $this->safeLog('info', 'PPP secret creation result: ' . json_encode($result));

            // Check if the command was successful
            // MikroTik returns berbagai jenis response:
            // - Empty array [] untuk success
            // - Array dengan ['!done'] untuk success
            // - Array dengan [0 => ['!done']] untuk success
            // - Array dengan [0 => ['.id' => '*xx']] untuk success dengan ID
            // - Array dengan error/trap untuk failure

            if (is_array($result)) {
                // Check for error responses first
                $hasError = false;
                $errorMsg = '';

                foreach ($result as $item) {
                    if (is_array($item)) {
                        // Check for !trap in array
                        if (isset($item['!trap'])) {
                            $hasError = true;
                            $errorMsg = isset($item['message']) ? $item['message'] : 'MikroTik API trap error';
                            break;
                        }
                        // Check for message field indicating error
                        if (isset($item['message']) && (isset($item['!re']) || isset($item['!trap']))) {
                            $hasError = true;
                            $errorMsg = $item['message'];
                            break;
                        }
                    } elseif (is_string($item)) {
                        // Check for !trap string
                        if (strpos($item, '!trap') === 0 || strpos($item, '!fatal') === 0) {
                            $hasError = true;
                            $errorMsg = 'MikroTik API error: ' . $item;
                            break;
                        }
                    }
                }

                if ($hasError) {
                    $this->lastError = $errorMsg;
                    $this->safeLog('error', 'PPP secret creation failed: ' . $errorMsg);
                    return false;
                }

                // No error found, check for success indicators
                // Success can be: empty array, [!done], [[!done]], or [['.id' => 'xxx']]
                $hasSuccess = empty($result) ||
                    (isset($result[0]) && $result[0] === '!done') ||
                    (isset($result[0]) && is_array($result[0]) && isset($result[0]['!done'])) ||
                    (count($result) === 1 && isset($result[0]['.id']));

                if ($hasSuccess) {
                    $this->safeLog('info', 'PPP secret created successfully - verifying...');

                    // Double-check dengan print untuk memastikan
                    try {
                        sleep(2); // Beri waktu untuk sinkronisasi
                        $verification = $this->client->comm('/ppp/secret/print', ['?name=' . $secretData['name']]);

                        if (!empty($verification)) {
                            $this->safeLog('info', 'PPP secret verified in MikroTik: ' . $secretData['name']);
                            $this->lastError = null;
                            return true;
                        } else {
                            $this->safeLog('warning', 'PPP secret command succeeded but verification failed');
                            // Masih return true karena command berhasil
                            $this->lastError = null;
                            return true;
                        }
                    } catch (\Exception $verifyEx) {
                        $this->safeLog('warning', 'Could not verify PPP secret: ' . $verifyEx->getMessage());
                        // Command succeeded, verification gagal - still success
                        $this->lastError = null;
                        return true;
                    }
                } else {
                    // Unknown response format
                    $this->lastError = 'Unexpected response from MikroTik API: ' . json_encode($result);
                    $this->safeLog('warning', $this->lastError);

                    // Try to verify anyway
                    try {
                        sleep(2);
                        $verification = $this->client->comm('/ppp/secret/print', ['?name=' . $secretData['name']]);
                        if (!empty($verification)) {
                            $this->safeLog('info', 'PPP secret verified despite unknown response');
                            $this->lastError = null;
                            return true;
                        }
                    } catch (\Exception $e) {
                        // Ignore verification error
                    }

                    return false;
                }
            } else {
                $this->lastError = 'Invalid response type from MikroTik API: ' . gettype($result);
                $this->safeLog('error', $this->lastError);
                return false;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->lastError = $errorMessage; // Store the error for getLastError()
            $this->safeLog('error', 'Failed to add PPP secret: ' . $errorMessage);
            $this->safeLog('error', 'PPP secret data that failed: ' . json_encode($secretData));

            // Check if it's a timeout error and provide more specific information
            if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'timed out') !== false) {
                $this->safeLog('warning', 'PPP secret creation timed out - trying verification approach');

                // Try to verify if secret was created despite timeout
                try {
                    sleep(2); // Wait briefly
                    $existingSecrets = $this->client->comm('/ppp/secret/print', ['?name' => $secretData['name']]);

                    if (!empty($existingSecrets)) {
                        $this->safeLog('info', 'PPP secret was actually created successfully: ' . $secretData['name']);
                        return true; // Return success since secret exists
                    }
                } catch (\Exception $verifyEx) {
                    $this->safeLog('debug', 'Could not verify secret after timeout: ' . $verifyEx->getMessage());
                }

                $this->lastError = 'Connection timeout while creating PPP secret - router may be slow to respond';
            } elseif (strpos($errorMessage, 'could not connect') !== false || strpos($errorMessage, 'connection') !== false) {
                $this->lastError = 'Could not connect to MikroTik router - check network connectivity';
            } elseif (strpos($errorMessage, 'login') !== false || strpos($errorMessage, 'auth') !== false) {
                $this->lastError = 'Authentication failed - check MikroTik username/password';
            } else {
                $this->lastError = 'MikroTik API error: ' . $errorMessage;
            }

            return false;
        }
    }
    /**
     * Remove PPPoE secret from MikroTik
     */
    public function removePPPSecret($username)
    {
        try {
            // First, find the secret by username
            $secrets = $this->client->comm('/ppp/secret/print', ['?name' => $username]);

            if (empty($secrets)) {
                $this->safeLog('info', 'PPP secret not found in MikroTik, considering as already removed: ' . $username);
                // Return true because the goal (secret not existing) is achieved
                return true;
            }

            $secretId = $secrets[0]['.id'];

            // Remove the secret with shorter timeout for deletion
            $result = $this->client->comm('/ppp/secret/remove', ['.id' => $secretId]);

            $this->safeLog('info', 'PPP secret removed successfully: ' . $username);
            return true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Check if it's a timeout error - this is acceptable for deletion
            if (
                strpos($errorMessage, 'Connection timed out') !== false ||
                strpos($errorMessage, 'timeout') !== false
            ) {
                $this->safeLog('warning', 'PPP secret removal timed out, but this may be acceptable: ' . $username);
                // For deletion operations, timeout might mean the secret was removed but confirmation failed
                // Return true since deletion timeout is not necessarily a failure
                return true;
            }

            $this->safeLog('error', 'Failed to remove PPP secret: ' . $errorMessage);
            return false;
        }
    }

    /**
     * Update an existing PPP secret in MikroTik
     * 
     * @param string $username The username of the PPP secret to update
     * @param array $updateData Associative array of fields to update (profile, password, remote-address, etc.)
     * @return bool True if update was successful, false otherwise
     */
    public function updatePPPSecret($username, $updateData)
    {
        try {
            // First, find the secret by username
            $secrets = $this->client->comm('/ppp/secret/print', ['?name=' . $username]);

            if (empty($secrets)) {
                $this->safeLog('error', 'PPP secret not found for update: ' . $username);
                return false;
            }

            $secretId = $secrets[0]['.id'];

            // Build parameters for update command
            $params = [
                '=.id=' . $secretId
            ];

            // Map common field names to RouterOS parameter names
            $fieldMapping = [
                'password' => 'password',
                'profile' => 'profile',
                'service' => 'service',
                'local-address' => 'local-address',
                'remote-address' => 'remote-address',
                'comment' => 'comment',
                'caller-id' => 'caller-id',
                'disabled' => 'disabled'
            ];

            // Add parameters from updateData
            foreach ($updateData as $key => $value) {
                $fieldName = $fieldMapping[$key] ?? $key;

                // Handle empty values (set to empty string)
                if ($value === '' || $value === null) {
                    $params[] = '=' . $fieldName . '=';
                } else {
                    $params[] = '=' . $fieldName . '=' . $value;
                }
            }

            // Execute the update command
            $this->client->comm('/ppp/secret/set', $params);

            $this->safeLog('info', 'PPP secret updated successfully: ' . $username . ' with data: ' . json_encode($updateData));
            return true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Check if it's a timeout error
            if (
                strpos($errorMessage, 'Connection timed out') !== false ||
                strpos($errorMessage, 'timeout') !== false
            ) {
                $this->safeLog('warning', 'PPP secret update timed out: ' . $username);
                // For update operations, timeout might mean the update was applied but confirmation failed
                return true;
            }

            $this->safeLog('error', 'Failed to update PPP secret: ' . $errorMessage);
            return false;
        }
    }

    public function testConnection()
    {
        // Jika tidak connected dari awal, return error dengan detail
        if (!$this->connected) {
            $errorDetail = $this->lastError ?? 'Connection failed during initialization';
            // Add connection details for debugging
            $connectionInfo = sprintf(
                'Host: %s, Port: %s, User: %s',
                $this->config['host'] ?? 'unknown',
                $this->config['port'] ?? 'unknown',
                $this->config['user'] ?? 'unknown'
            );
            return [
                'success' => false,
                'message' => $errorDetail . ' (' . $connectionInfo . ')'
            ];
        }

        try {
            // Test dengan command sederhana untuk memastikan koneksi aktif
            $identity = $this->client->comm('/system/identity/print');
            if (!empty($identity)) {
                $this->safeLog('info', 'MikroTik connection test successful');
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'identity' => $identity[0]['name'] ?? 'Unknown',
                    'connection_type' => strpos($this->config['host'], 'hostddns.us') !== false ? 'tunnel' : 'direct'
                ];
            }

            // Fallback ke system resource jika identity gagal
            $resource = $this->getSystemResource();
            return [
                'success' => true,
                'message' => 'Connection successful',
                'board_name' => $resource['board-name'] ?? 'Unknown',
                'version' => $resource['version'] ?? 'Unknown',
                'connection_type' => strpos($this->config['host'], 'hostddns.us') !== false ? 'tunnel' : 'direct'
            ];
        } catch (\Exception $e) {
            $this->safeLog('debug', 'MikroTik connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Ambil jumlah PPPoE aktif di MikroTik
    public function getActivePPPoECount()
    {
        try {
            $active = $this->client->comm('/ppp/active/print');
            return is_array($active) ? count($active) : 0;
        } catch (\Exception $e) {
            $this->safeLog('debug', 'Failed to get active PPPoE count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get PPP profiles from MikroTik
     */
    public function getPPPProfiles()
    {
        try {
            $profiles = $this->client->comm('/ppp/profile/print');
            $this->safeLog('info', 'PPP profiles retrieved: ' . count($profiles) . ' profiles found');
            return $profiles;
        } catch (\Exception $e) {
            $this->safeLog('debug', 'Failed to get PPP profiles: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test if we can perform write operations on MikroTik
     */
    public function testWriteOperation()
    {
        try {
            // Try a simple write operation - add a comment to system identity
            $currentTime = date('Y-m-d H:i:s');
            $result = $this->client->comm('/system/identity/set', ['=comment=Test write operation at ' . $currentTime]);

            $this->safeLog('info', 'Write operation test result: ' . json_encode($result));

            return [
                'success' => true,
                'message' => 'Write operation successful',
                'timestamp' => $currentTime
            ];
        } catch (\Exception $e) {
            $this->safeLog('error', 'Write operation test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Write operation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Safe logging method that works with or without CodeIgniter
     */
    private function safeLog($level, $message)
    {
        if (function_exists('log_message')) {
            log_message($level, $message);
        } else {
            // Fallback for standalone usage
            error_log("[$level] $message");
        }
    }
    /**
     * Get health info (temperature, voltage, etc) from MikroTik, if available
     * Returns associative array or empty array if not supported
     */
    public function getSystemHealth()
    {
        try {
            if (!$this->connected) {
                throw new \Exception('Not connected to MikroTik router');
            }

            $response = $this->client->comm('/system/health/print');
            $this->safeLog('info', 'System health retrieved: ' . json_encode($response));

            if (empty($response) || !is_array($response)) {
                throw new \Exception('Empty or invalid response from system/health/print');
            }

            // Some devices return multiple sensors, but usually index 0 is main board
            return $response[0] ?? [];
        } catch (\Exception $e) {
            $errorMsg = 'Failed to get system health: ' . $e->getMessage();
            $this->safeLog('warning', $errorMsg);
            return [];
        }
    }
}
