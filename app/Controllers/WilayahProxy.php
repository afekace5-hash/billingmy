<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class WilayahProxy extends BaseController
{

    // Base URL for ibnux data-indonesia API
    private $baseUrl = 'https://ibnux.github.io/data-indonesia';

    // Cache directory
    private $cacheDir;

    // Cache TTL (24 hours)
    private $cacheTTL = 86400;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->cacheDir = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'wilayah';

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Clean region names by removing prefixes
     */
    private function cleanRegionName($name, $type = null)
    {
        if (empty($name)) {
            return $name;
        }

        // Remove prefixes based on type
        $prefixes = [];

        switch ($type) {
            case 'district':
                $prefixes = ['Kecamatan ', 'Kec. ', 'Kec ', 'KECAMATAN '];
                break;
            case 'village':
                $prefixes = ['Desa ', 'Kelurahan ', 'Kel. ', 'Kel ', 'DESA ', 'KELURAHAN '];
                break;
            default:
                // General prefixes that might appear
                $prefixes = [
                    'Kabupaten ',
                    'Kab. ',
                    'Kab ',
                    'KABUPATEN ',
                    'Kota ',
                    'KOTA ',
                    'Provinsi ',
                    'Prov. ',
                    'Prov ',
                    'PROVINSI ',
                    'Kecamatan ',
                    'Kec. ',
                    'Kec ',
                    'KECAMATAN ',
                    'Desa ',
                    'Kelurahan ',
                    'Kel. ',
                    'Kel ',
                    'DESA ',
                    'KELURAHAN '
                ];
                break;
        }

        $cleanName = $name;
        foreach ($prefixes as $prefix) {
            if (stripos($cleanName, $prefix) === 0) {
                $cleanName = substr($cleanName, strlen($prefix));
                break;
            }
        }

        return trim($cleanName);
    }

    /**
     * Get cached data
     */
    private function getFromCache($cacheKey)
    {
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.json';

        if (!file_exists($cacheFile)) {
            return null;
        }

        $cacheTime = filemtime($cacheFile);
        if (time() - $cacheTime > $this->cacheTTL) {
            // Cache expired
            unlink($cacheFile);
            return null;
        }

        $cachedData = file_get_contents($cacheFile);
        return json_decode($cachedData, true);
    }

    /**
     * Save data to cache
     */
    private function saveToCache($cacheKey, $data)
    {
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.json';
        file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Fetch data from external API with error handling
     */
    private function fetchExternalData($url)
    {
        $client = \Config\Services::curlrequest();

        try {
            $response = $client->get($url, [
                'timeout' => 30,
                'verify' => false, // Disable SSL verification for development
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'BillingKimo/1.0'
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP ' . $response->getStatusCode() . ': ' . $response->getReasonPhrase());
            }

            $data = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch from ' . $url . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get provinces
     */
    public function provinces()
    {
        try {
            $cacheKey = 'provinces';

            // Try to get from cache first
            $data = $this->getFromCache($cacheKey);

            if ($data === null) {
                // Fetch from external API
                $url = $this->baseUrl . '/provinsi.json';
                $data = $this->fetchExternalData($url);

                // Validate data format
                if (!is_array($data)) {
                    throw new \Exception('Invalid data format: expected array');
                }

                // Save to cache
                $this->saveToCache($cacheKey, $data);

                log_message('info', 'Provinces data fetched from external API and cached');
            } else {
                log_message('info', 'Provinces data loaded from cache');
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'message' => 'Provinces loaded successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching provinces: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'Gagal memuat data provinsi: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get regencies by province ID
     */
    public function regencies($provinceId = null)
    {
        if (empty($provinceId)) {
            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'Province ID is required'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $cacheKey = 'regencies_' . $provinceId;

            // Try to get from cache first
            $data = $this->getFromCache($cacheKey);

            if ($data === null) {
                // Fetch from external API
                $url = $this->baseUrl . '/kabupaten/' . $provinceId . '.json';
                $data = $this->fetchExternalData($url);

                // Validate data format
                if (!is_array($data)) {
                    throw new \Exception('Invalid data format: expected array');
                }

                // Save to cache
                $this->saveToCache($cacheKey, $data);

                log_message('info', "Regencies data for province {$provinceId} fetched from external API and cached");
            } else {
                log_message('info', "Regencies data for province {$provinceId} loaded from cache");
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'message' => 'Regencies loaded successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', "Error fetching regencies for province {$provinceId}: " . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'Gagal memuat data kabupaten/kota: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get districts by regency ID
     */
    public function districts($regencyId = null)
    {
        if (empty($regencyId)) {
            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'Regency ID is required'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $cacheKey = 'districts_' . $regencyId;

            // Try to get from cache first
            $data = $this->getFromCache($cacheKey);

            if ($data === null) {
                // Fetch from external API
                $url = $this->baseUrl . '/kecamatan/' . $regencyId . '.json';
                $data = $this->fetchExternalData($url);

                // Validate data format
                if (!is_array($data)) {
                    throw new \Exception('Invalid data format: expected array');
                }

                // Clean district names
                foreach ($data as &$district) {
                    if (isset($district['nama'])) {
                        $district['nama'] = $this->cleanRegionName($district['nama'], 'district');
                    }
                }

                // Save to cache
                $this->saveToCache($cacheKey, $data);

                log_message('info', "Districts data for regency {$regencyId} fetched from external API and cached");
            } else {
                log_message('info', "Districts data for regency {$regencyId} loaded from cache");
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'message' => 'Districts loaded successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', "Error fetching districts for regency {$regencyId}: " . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'Gagal memuat data kecamatan: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get villages by district ID
     */
    public function villages($districtId = null)
    {
        if (empty($districtId)) {
            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'District ID is required'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $cacheKey = 'villages_' . $districtId;

            // Try to get from cache first
            $data = $this->getFromCache($cacheKey);

            if ($data === null) {
                // Fetch from external API
                $url = $this->baseUrl . '/kelurahan/' . $districtId . '.json';
                $data = $this->fetchExternalData($url);

                // Validate data format
                if (!is_array($data)) {
                    throw new \Exception('Invalid data format: expected array');
                }

                // Clean village names
                foreach ($data as &$village) {
                    if (isset($village['nama'])) {
                        $village['nama'] = $this->cleanRegionName($village['nama'], 'village');
                    }
                }

                // Save to cache
                $this->saveToCache($cacheKey, $data);

                log_message('info', "Villages data for district {$districtId} fetched from external API and cached");
            } else {
                log_message('info', "Villages data for district {$districtId} loaded from cache");
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'message' => 'Villages loaded successfully'
            ]);
        } catch (\Exception $e) {
            log_message('error', "Error fetching villages for district {$districtId}: " . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'data' => [],
                'message' => 'Gagal memuat data desa/kelurahan: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Clear all cached data
     */
    public function clearCache()
    {
        try {
            $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*.json');
            $deletedCount = 0;

            foreach ($files as $file) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Cache cleared successfully. {$deletedCount} files deleted."
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
