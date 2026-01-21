<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class MikroTikTest extends ResourceController
{
    use ResponseTrait;

    public function testConnection()
    {
        try {
            // Test 1: Database connection dan query manual
            $db = \Config\Database::connect();

            // Query manual untuk check
            $manualQuery = $db->query("SELECT * FROM lokasi_server WHERE ping_status = 'online' AND is_connected = '1'");
            $manualResults = $manualQuery->getResult();

            // Query untuk check semua data
            $allQuery = $db->query("SELECT id_lokasi, ping_status, is_connected FROM lokasi_server");
            $allResults = $allQuery->getResult();

            // Test dengan model
            $lokasiServerModel = new \App\Models\ServerLocationModel();
            $modelResults = $lokasiServerModel->where('ping_status', 'online')
                ->where('is_connected', '1')
                ->findAll();

            $result = [
                'database_connection' => 'OK',
                'manual_query_count' => count($manualResults),
                'model_query_count' => count($modelResults),
                'all_servers' => $allResults,
                'active_servers_manual' => $manualResults,
                'active_servers_model' => $modelResults
            ];

            // Test 2: RouterOS Service dengan debug
            helper(['routeros']);
            $routerOSService = \Config\Services::routeros();
            $result['routeros_service'] = $routerOSService ? 'Loaded' : 'Failed to load';

            // Test 3: Connection attempt dengan debug
            if ($routerOSService) {
                $connection = $routerOSService->connect();
                $result['mikrotik_connection'] = $connection ? 'Connected' : 'Failed';

                if ($connection) {
                    $result['connection_type'] = get_class($connection);
                    $result['is_connected'] = $connection->isConnected();
                    $result['connection_config'] = $connection->getConfig();
                    $result['last_error'] = $connection->getLastError();
                } else {
                    $result['connection_error'] = 'No connection object returned';
                }
            }

            return $this->respond([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
