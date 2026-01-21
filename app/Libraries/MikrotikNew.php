<?php

namespace App\Libraries;

// Load RouterOS library
require_once __DIR__ . '/RouterOSClient/SocketTrait.php';
require_once __DIR__ . '/RouterOSClient/ShortsTrait.php';
require_once __DIR__ . '/RouterOSClient/Interfaces/ClientInterface.php';
require_once __DIR__ . '/RouterOSClient/Interfaces/ConfigInterface.php';
require_once __DIR__ . '/RouterOSClient/Interfaces/QueryInterface.php';
require_once __DIR__ . '/RouterOSClient/Interfaces/StreamInterface.php';
require_once __DIR__ . '/RouterOSClient/Exceptions/ClientException.php';
require_once __DIR__ . '/RouterOSClient/Exceptions/ConfigException.php';
require_once __DIR__ . '/RouterOSClient/Exceptions/QueryException.php';
require_once __DIR__ . '/RouterOSClient/Exceptions/ConnectException.php';
require_once __DIR__ . '/RouterOSClient/Exceptions/BadCredentialsException.php';
require_once __DIR__ . '/RouterOSClient/Exceptions/StreamException.php';
require_once __DIR__ . '/RouterOSClient/Helpers/ArrayHelper.php';
require_once __DIR__ . '/RouterOSClient/Helpers/TypeHelper.php';
require_once __DIR__ . '/RouterOSClient/Helpers/BinaryStringHelper.php';
require_once __DIR__ . '/RouterOSClient/Streams/ResourceStream.php';
require_once __DIR__ . '/RouterOSClient/APILengthCoDec.php';
require_once __DIR__ . '/RouterOSClient/APIConnector.php';
require_once __DIR__ . '/RouterOSClient/ResponseIterator.php';
require_once __DIR__ . '/RouterOSClient/Config.php';
require_once __DIR__ . '/RouterOSClient/Query.php';
require_once __DIR__ . '/RouterOSClient/Client.php';

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

/**
 * MikroTik wrapper using EvilFreelancer RouterOS API
 * Compatible with existing Mikrotik class interface
 */
class MikrotikNew
{
    private $client;
    private $connected = false;

    public function __construct($config = null)
    {
        // If config provided in constructor, auto-connect
        if ($config !== null && is_array($config)) {
            $host = $config['host'] ?? null;
            $user = $config['user'] ?? null;
            $pass = $config['pass'] ?? null;
            $port = $config['port'] ?? 8728;

            if ($host && $user && $pass) {
                $this->connect($host, $user, $pass, $port);
            }
        }
    }

    /**
     * Connect to MikroTik router
     * @param string $host Router host/IP
     * @param string $user Username
     * @param string $pass Password
     * @param int $port API port (default 8728)
     * @return bool Connection status
     */
    public function connect($host, $user, $pass, $port = 8728)
    {
        try {
            // Detect VPN tunnel connections (slow)
            $isVpnTunnel = (strpos($host, 'hostddns.us') !== false || strpos($host, 'ddns') !== false);

            // Use shorter timeout for VPN to fail faster
            $timeout = $isVpnTunnel ? 30 : 60;
            $socketTimeout = $isVpnTunnel ? 30 : 60;

            ini_set('default_socket_timeout', (string)$socketTimeout);

            $routerConfig = new Config([
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'port' => $port,
                'timeout' => $timeout,
                'socket_timeout' => $socketTimeout,
                'attempts' => 2,
                'delay' => 1
            ]);

            $this->safeLog('info', 'MikroTik connecting with EvilFreelancer library: ' . json_encode([
                'host' => $host,
                'port' => $port,
                'user' => $user,
                'timeout' => $timeout,
                'is_vpn' => $isVpnTunnel
            ]));

            $this->client = new Client($routerConfig);
            $this->connected = true;

            $this->safeLog('info', 'MikroTik connected successfully via EvilFreelancer library');
            return true;
        } catch (\Exception $e) {
            $this->safeLog('error', 'MikroTik connection failed: ' . $e->getMessage());
            $this->connected = false;
            return false;
        }
    }

    /**
     * Execute command - compatible with old comm() method
     */
    public function comm($command, $params = [])
    {
        try {
            $this->safeLog('info', 'MikroTik comm() called with command: ' . $command . ', params: ' . json_encode($params));

            // Check if connected first
            if (!$this->connected || !$this->client) {
                $this->safeLog('error', 'Cannot execute command: Not connected to MikroTik');
                throw new \Exception('Not connected to MikroTik router');
            }

            $query = new Query($command);

            // Parse parameters
            // Support both formats:
            // 1. Array of strings: ['=key=value', '?key=value'] 
            // 2. Associative array: ['key' => 'value']
            foreach ($params as $key => $value) {
                // Check if it's string format (=key=value or ?key=value)
                if (is_int($key) && is_string($value)) {
                    // Format 1: Array of strings like ['=name=value']
                    if (strpos($value, '=') === 0) {
                        $parts = explode('=', substr($value, 1), 2);
                        if (count($parts) == 2) {
                            $query->equal($parts[0], $parts[1]);
                        }
                    } elseif (strpos($value, '?') === 0) {
                        $parts = explode('=', substr($value, 1), 2);
                        if (count($parts) == 2) {
                            $query->where($parts[0], $parts[1]);
                        }
                    }
                } else {
                    // Format 2: Associative array like ['name' => 'value']
                    if (strpos($key, '?') === 0) {
                        // Query parameter (where)
                        $query->where(substr($key, 1), $value);
                    } else {
                        // Equal parameter (set)
                        $query->equal($key, $value);
                    }
                }
            }

            // For VPN, read with iterator to avoid timeout on large datasets
            $queryResponse = $this->client->query($query);

            // Try to read all data, with timeout handling  
            try {
                // Increase timeout for slow VPN connections
                ini_set('default_socket_timeout', '120');

                $response = $queryResponse->read();
                $this->safeLog('info', 'Direct read() - Response type: ' . gettype($response) . ', count: ' . (is_array($response) ? count($response) : 'N/A'));

                // Debug: log first item if exists
                if (is_array($response) && count($response) > 0) {
                    $this->safeLog('info', 'Direct read() - First item keys: ' . implode(', ', array_keys($response[0])));
                }

                // Ensure we return array
                if (!is_array($response)) {
                    $this->safeLog('warning', 'Response is not array, converting...');
                    $response = [];
                }

                return $response;
            } catch (\Exception $readError) {
                // If read timeout, try alternative approach
                $this->safeLog('warning', 'Read timeout on first attempt: ' . $readError->getMessage());
                $this->safeLog('info', 'Trying alternative read method with generator...');

                try {
                    // Re-execute query
                    $result = [];
                    $queryResponse2 = $this->client->query($query);

                    // Use generator (false parameter)
                    $this->safeLog('info', 'Calling read(false) for generator...');
                    $generator = $queryResponse2->read(false);

                    $this->safeLog('info', 'Generator type: ' . gettype($generator) . ', is_iterable: ' . (is_iterable($generator) ? 'yes' : 'no'));

                    if (is_iterable($generator)) {
                        $itemCount = 0;
                        $rawItems = []; // Collect raw items for debugging
                        try {
                            foreach ($generator as $index => $item) {
                                // Log first few items with their content
                                if ($index < 5) {
                                    $this->safeLog('info', "Generator item #{$index} - Type: " . gettype($item) . ", Content: " . substr(var_export($item, true), 0, 200));
                                }

                                // Collect all raw items
                                $rawItems[] = $item;

                                // Validate item is array before adding
                                if (is_array($item) && !empty($item)) {
                                    if ($itemCount == 0) {
                                        // Log first item structure
                                        $this->safeLog('info', 'First item keys: ' . implode(', ', array_keys($item)));
                                    }
                                    $result[] = $item;
                                    $itemCount++;
                                }

                                // Limit to prevent hanging on slow connections
                                if ($index >= 500) {
                                    $this->safeLog('warning', 'Stopping at 500 raw items for performance');
                                    break;
                                }
                            }

                            // Now process raw items - they might need parsing
                            $this->safeLog('info', 'Collected ' . count($rawItems) . ' raw items from generator');

                            // Check if raw items are strings that need parsing or already proper arrays
                            if (count($rawItems) > 0) {
                                $firstItem = $rawItems[0];
                                if (is_string($firstItem)) {
                                    $this->safeLog('info', 'Generator returns API protocol strings - parsing into arrays');

                                    // Parse RouterOS API protocol format
                                    $currentItem = [];
                                    foreach ($rawItems as $rawItem) {
                                        if (!is_string($rawItem)) continue;

                                        // !re marks start of new item
                                        if ($rawItem === '!re') {
                                            if (!empty($currentItem)) {
                                                $result[] = $currentItem;
                                                $itemCount++;
                                            }
                                            $currentItem = [];
                                        }
                                        // Parse attribute=value format
                                        else if (strpos($rawItem, '=') === 0) {
                                            // Remove leading '=' and split key=value
                                            $attr = substr($rawItem, 1);
                                            $parts = explode('=', $attr, 2);
                                            if (count($parts) === 2) {
                                                $currentItem[$parts[0]] = $parts[1];
                                            }
                                        }
                                    }

                                    // Add last item
                                    if (!empty($currentItem)) {
                                        $result[] = $currentItem;
                                        $itemCount++;
                                    }

                                    $this->safeLog('info', 'Parsed ' . $itemCount . ' items from API protocol strings');
                                } else if (is_array($firstItem)) {
                                    $result = $rawItems;
                                    $itemCount = count($result);
                                }
                            }
                        } catch (\Exception $foreachEx) {
                            $this->safeLog('error', 'Exception during generator iteration: ' . $foreachEx->getMessage());
                        }
                    } else {
                        $this->safeLog('warning', 'Generator is not iterable, type: ' . gettype($generator));
                    }

                    $this->safeLog('info', 'Alternative method retrieved ' . count($result) . ' items');
                    return $result;
                } catch (\Exception $iterError) {
                    $this->safeLog('error', 'Alternative method also failed: ' . $iterError->getMessage());
                    $this->safeLog('error', 'Stack trace: ' . $iterError->getTraceAsString());
                    // Return empty array instead of throwing to allow graceful degradation
                    return [];
                }
            }
        } catch (\Exception $e) {
            $this->safeLog('error', 'Command execution failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test connection with simple command
     * @return bool
     */
    public function testConnection()
    {
        try {
            $result = $this->comm('/system/identity/print');
            return !empty($result);
        } catch (\Exception $e) {
            $this->safeLog('error', 'Connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Safe logging method
     */
    private function safeLog($level, $message)
    {
        if (function_exists('log_message')) {
            log_message($level, $message);
        }
    }

    public function disconnect()
    {
        // EvilFreelancer client auto-disconnects
    }
}
