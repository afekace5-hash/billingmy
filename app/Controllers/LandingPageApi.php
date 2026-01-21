<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

class LandingPageApi extends BaseController
{
    protected $customerModel;
    protected $request;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->request = \Config\Services::request();
    }

    /**
     * Handle customer registration from landing page
     * POST /api/landing/register
     */
    public function register()
    {
        // Enable CORS for landing page
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Handle preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK);
        }

        try {
            // Validate request method
            if ($this->request->getMethod() !== 'post') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Hanya POST request yang diizinkan'
                ])->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED);
            }

            // Get JSON input
            $input = $this->request->getJSON();
            if (!$input) {
                $input = (object)$this->request->getPost();
            }

            // Validate required fields
            $required = ['nama_pelanggan', 'nomor_layanan', 'alamat', 'telepon'];
            foreach ($required as $field) {
                if (empty($input->{$field})) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Field '$field' adalah wajib diisi"
                    ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
                }
            }

            // Check if nomor_layanan already exists
            $existing = $this->customerModel
                ->where('nomor_layanan', $input->nomor_layanan)
                ->first();

            if ($existing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nomor layanan sudah terdaftar'
                ])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
            }

            // Prepare data
            $data = [
                'nama_pelanggan' => $input->nama_pelanggan,
                'nomor_layanan' => $input->nomor_layanan,
                'alamat' => $input->alamat,
                'telepon' => $input->telepon,
                'email' => $input->email ?? null,
                'nomor_identitas' => $input->nomor_identitas ?? null,
                'tgl_pasang' => $input->tgl_pasang ?? date('d/m/Y'),
                'tgl_tempo' => $input->tgl_tempo ?? date('d/m/Y', strtotime('+30 days')),
                'biaya_pasang' => $input->biaya_pasang ?? 0,
                'paket_id' => $input->paket_id ?? 1,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'source' => 'landing_page' // Track source
            ];

            // Insert customer
            $customerId = $this->customerModel->insert($data);

            if (!$customerId) {
                $errors = $this->customerModel->errors();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mendaftarkan pelanggan',
                    'errors' => $errors
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pendaftaran berhasil! Silakan login ke aplikasi',
                'data' => [
                    'id' => $customerId,
                    'nomor_layanan' => $input->nomor_layanan,
                    'login_url' => base_url('login')
                ]
            ])->setStatusCode(ResponseInterface::HTTP_CREATED);
        } catch (\Exception $e) {
            log_message('error', 'Landing page registration error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get application info (for landing page)
     * GET /api/landing/info
     */
    public function info()
    {
        header('Access-Control-Allow-Origin: *');

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'app_name' => 'Billing Kimo',
                'app_url' => base_url(),
                'login_url' => base_url('login'),
                'register_url' => base_url('register'),
                'api_url' => base_url('api')
            ]
        ]);
    }

    /**
     * Get available packages
     * GET /api/landing/packages
     */
    public function packages()
    {
        header('Access-Control-Allow-Origin: *');

        try {
            $paketModel = new \App\Models\PaketModel();
            $packages = $paketModel->findAll();

            return $this->response->setJSON([
                'success' => true,
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get packages error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data paket'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
