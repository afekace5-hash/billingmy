<?php

namespace App\Controllers;

use App\Models\CashFlowModel;
use CodeIgniter\RESTful\ResourceController;

class CashFlowController extends ResourceController
{
    protected $cashFlowModel;

    public function __construct()
    {
        $this->cashFlowModel = new CashFlowModel();
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'No ID provided'
                ])->setStatusCode(400);
            }

            $cashFlow = $this->cashFlowModel->find($id);
            if (!$cashFlow) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Data kas tidak ditemukan'
                ])->setStatusCode(404);
            }

            if ($this->cashFlowModel->delete($id)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'title' => 'Berhasil',
                    'message' => 'Data kas berhasil dihapus'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'Gagal menghapus data kas'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
