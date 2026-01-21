<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class RouterOS extends BaseConfig
{
    /**
     * RouterOS API Configuration
     */

    // Default RouterOS connection settings
    public string $host = '192.168.1.1';
    public int $port = 8728;
    public int $timeout = 5;
    public string $username = 'admin';
    public string $password = '';

    // Enable/disable RouterOS integration
    public bool $enabled = true;

    // Multiple servers configuration
    public array $servers = [
        [
            'id' => 'server1',
            'name' => 'Server 1',
            'host' => '192.168.1.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => '',
            'timeout' => 5,
            'enabled' => true,
        ],
        [
            'id' => 'server2',
            'name' => 'Server 2',
            'host' => '192.168.1.2',
            'port' => 8728,
            'username' => 'admin',
            'password' => '',
            'timeout' => 5,
            'enabled' => false,
        ]
    ];    // Default interfaces to monitor
    public array $defaultInterfaces = [
        'ether1' => 'ether1',
        'ether2' => 'ether2',
        'ether3' => 'ether3',
        'ether4' => 'ether4',
        'ether5' => 'ether5',
        'wlan1' => 'wlan1',
        'wlan2' => 'wlan2',
        'pppoe-out1' => 'pppoe-out1',
        'pppoe-out2' => 'pppoe-out2',
        'bridge' => 'bridge'
    ];

    // Traffic monitoring settings
    public array $monitoring = [
        'refreshInterval' => 5, // seconds
        'historyPoints' => 60,  // number of data points to keep
        'maxDataAge' => 3600,   // seconds (1 hour)
    ];

    /**
     * Load configuration from environment variables
     */
    public function __construct()
    {
        parent::__construct();

        // Override with environment variables if available
        $this->host = env('ROUTEROS_HOST', $this->host);
        $this->port = env('ROUTEROS_PORT', $this->port);
        $this->timeout = env('ROUTEROS_TIMEOUT', $this->timeout);
        $this->username = env('ROUTEROS_USERNAME', $this->username);
        $this->password = env('ROUTEROS_PASSWORD', $this->password);
        $this->enabled = env('ROUTEROS_ENABLED', $this->enabled);

        // Override server configurations from environment if available
        if (env('ROUTEROS_HOST')) {
            $this->servers[0]['host'] = env('ROUTEROS_HOST');
            $this->servers[0]['username'] = env('ROUTEROS_USERNAME', 'admin');
            $this->servers[0]['password'] = env('ROUTEROS_PASSWORD', '');
            $this->servers[0]['port'] = env('ROUTEROS_PORT', 8728);
            $this->servers[0]['enabled'] = env('ROUTEROS_ENABLED', true);
        }
    }
}
