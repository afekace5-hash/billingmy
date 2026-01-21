<?php

namespace App\Controllers;

class RouterDiagnostic extends BaseController
{
    public function index()
    {
        return view('diagnostic/router_test');
    }

    public function testConnectivity()
    {
        $host = $this->request->getPost('host') ?? 'id-14.hostddns.us';
        $port = (int)($this->request->getPost('port') ?? 8211);

        $results = [];

        // Test 1: Basic connectivity via curl
        $results['curl_test'] = $this->testCurlConnectivity($host, $port);

        // Test 2: Socket connection test
        $results['socket_test'] = $this->testSocketConnection($host, $port);

        // Test 3: DNS resolution
        $results['dns_test'] = $this->testDNSResolution($host);

        // Test 4: MikroTik API attempt
        $results['mikrotik_test'] = $this->testMikrotikConnection();

        return $this->response->setJSON([
            'status' => 'success',
            'host' => $host,
            'port' => $port,
            'timestamp' => date('Y-m-d H:i:s'),
            'results' => $results
        ]);
    }

    private function testCurlConnectivity($host, $port)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://{$host}:{$port}");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $start = microtime(true);
        $result = curl_exec($ch);
        $time = round((microtime(true) - $start) * 1000, 2);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => $result !== false,
            'http_code' => $httpCode,
            'response_time_ms' => $time,
            'error' => $error ?: null
        ];
    }

    private function testSocketConnection($host, $port)
    {
        $start = microtime(true);
        $socket = @fsockopen($host, $port, $errno, $errstr, 10);
        $time = round((microtime(true) - $start) * 1000, 2);

        if ($socket) {
            fclose($socket);
            return [
                'success' => true,
                'response_time_ms' => $time,
                'message' => 'Socket connection successful'
            ];
        } else {
            return [
                'success' => false,
                'response_time_ms' => $time,
                'error' => "Error {$errno}: {$errstr}"
            ];
        }
    }

    private function testDNSResolution($host)
    {
        $start = microtime(true);
        $ip = gethostbyname($host);
        $time = round((microtime(true) - $start) * 1000, 2);

        return [
            'success' => $ip !== $host,
            'resolved_ip' => $ip,
            'response_time_ms' => $time,
            'hostname' => $host
        ];
    }

    private function testMikrotikConnection()
    {
        try {
            $model = new \App\Models\LokasiServerModel();
            $router = $model->first(); // Get first router

            if (!$router) {
                return [
                    'success' => false,
                    'error' => 'No router found in database'
                ];
            }

            $mt = new \App\Libraries\MikrotikNew([
                'host' => $router['ip_router'],
                'user' => $router['username'],
                'pass' => $router['password_router'],
                'port' => (int)$router['port_api'],
                'timeout' => 60,
            ]);

            $start = microtime(true);
            $result = $mt->comm('/system/identity/print');
            $time = round((microtime(true) - $start) * 1000, 2);

            return [
                'success' => !empty($result),
                'response_time_ms' => $time,
                'router_config' => [
                    'host' => $router['ip_router'],
                    'port' => $router['port_api'],
                    'username' => $router['username']
                ],
                'identity' => $result[0]['name'] ?? 'Unknown'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
