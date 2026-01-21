<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WhatsApp extends BaseConfig
{
    /**
     * WhatsApp API Configuration
     */
    // Primary API endpoint - get from environment
    public string $apiUrl;

    // API Service Types and their configurations
    public array $apiServices = [];

    public function __construct()
    {
        parent::__construct();

        // Get base URL from environment or use default
        $baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'https://wazero.difihome.my.id';

        // Set API URL
        $this->apiUrl = $baseUrl . '/send-message';

        // Configure API services
        $this->apiServices = [
            'wamoo' => [
                'url' => $baseUrl . '/send-message',
                'method' => 'GET',
                'headers' => [],
                'data_format' => 'query',
                'fields' => [
                    'api_key' => 'api_key',
                    'sender' => 'sender',
                    'number' => 'number',
                    'message' => 'message'
                ]
            ],
        ];
    }

    // Timeout settings
    public int $timeout = 15; // Increased timeout
    public int $connectTimeout = 8; // Connection timeout in seconds

    // Retry settings
    public bool $enableRetry = true;
    public int $maxRetries = 3;
    public int $retryDelay = 2; // Seconds between retries    // Fallback settings
    public bool $enableFallback = false; // Disabled backup services per user request
    public bool $tryAllServices = false; // Disabled per user request// Debug settings
    public bool $enableLogging = true;
    public bool $logRequests = true;
    public bool $logResponses = true;

    // Demo mode (for testing without actual API calls)
    public bool $demoMode = false; // Live mode enabled - Real API calls active

    // Response format configuration
    public array $responseMapping = [
        'success_field' => 'status',
        'message_field' => 'msg',
        'success_value' => true
    ];    // Demo mode configuration
    public array $demoSettings = [
        'success_rate' => 95, // Increased success rate to 95%
        'delay_min' => 1, // Minimum delay in seconds
        'delay_max' => 2, // Reduced max delay
        'simulate_errors' => true // Sometimes simulate realistic errors
    ];
}
