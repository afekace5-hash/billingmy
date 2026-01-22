<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BiayaTambahanModel;

class BiayaTambahan extends BaseController
{
    protected $biayaTambahanModel;

    public function __construct()
    {
        $this->biayaTambahanModel = new BiayaTambahanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Biaya Tambahan'
        ];

        return view('biaya_tambahan/index', $data);
    }

    public function list()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        try {
            $data = $this->biayaTambahanModel->where('status', 1)->findAll();

            $result = [];
            foreach ($data as $row) {
                $result[] = [
                    'id' => $row['id'],
                    'text' => $row['nama_biaya'] . ' (' . $row['kategori'] . ') - Rp ' . number_format($row['jumlah'], 0, ',', '.'),
                    'kategori' => $row['kategori'],
                    'jumlah' => $row['jumlah']
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengambil data biaya tambahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    public function test()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Test berhasil',
            'data' => $this->biayaTambahanModel->findAll()
        ]);
    }

    public function data()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        try {
            $request = $this->request;
            $length = (int)($request->getVar('length') ?? 10);
            $start = (int)($request->getVar('start') ?? 0);

            // Handle search parameter safely
            $searchParam = $request->getVar('search');
            $search = '';
            if (is_array($searchParam) && isset($searchParam['value'])) {
                $search = $searchParam['value'];
            }

            // Handle order parameter safely
            $orderParam = $request->getVar('order');
            $order = ['column' => 0, 'dir' => 'desc'];
            if (is_array($orderParam) && isset($orderParam[0])) {
                $order = $orderParam[0];
            }

            $builder = $this->biayaTambahanModel;

            // Total records
            $totalRecords = $this->biayaTambahanModel->countAll();

            // Search
            if (!empty($search)) {
                $builder = $this->biayaTambahanModel
                    ->like('nama_biaya', $search)
                    ->orLike('deskripsi', $search)
                    ->orLike('kategori', $search);
            }

            // Get filtered count
            $filteredRecords = !empty($search) ? $builder->countAllResults(false) : $totalRecords;

            // Order
            $columns = ['id', 'nama_biaya', 'kategori', 'jumlah', 'tanggal', 'status'];
            $orderColumn = (int)($order['column'] ?? 0);
            if (isset($columns[$orderColumn])) {
                $builder = $builder->orderBy($columns[$orderColumn], $order['dir']);
            }

            // Limit
            $data = $builder->findAll($length, $start);

            $result = [];
            $no = $start + 1;
            foreach ($data as $row) {
                $result[] = [
                    'DT_RowIndex' => $no++,
                    'id' => $row['id'],
                    'nama_biaya' => $row['nama_biaya'],
                    'kategori' => $row['kategori'] ?? '-',
                    'jumlah' => 'Rp ' . number_format($row['jumlah'], 0, ',', '.'),
                    'tanggal' => date('d/m/Y', strtotime($row['tanggal'])),
                    'status' => $row['status'] == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>',
                    'deskripsi' => $row['deskripsi'],
                    'action' => '
                        <button class="btn btn-sm btn-warning editData" data-id="' . $row['id'] . '">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger deleteData" data-id="' . $row['id'] . '">
                            <i class="bx bx-trash"></i>
                        </button>
                    '
                ];
            }
            return $this->response->setJSON([
                'draw' => intval($request->getVar('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function create()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/biaya_tambahan');
        }

        $rules = [
            'kategori' => 'required|min_length[3]|max_length[100]',
            'nama_biaya' => 'required|min_length[3]|max_length[255]',
            'jumlah' => 'required|numeric',
            'tanggal' => 'required|valid_date',
            'deskripsi' => 'permit_empty|max_length[500]',
            'status' => 'required|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'kategori' => $this->request->getPost('kategori'),
                'nama_biaya' => $this->request->getPost('nama_biaya'),
                'jumlah' => str_replace(['.', ','], '', $this->request->getPost('jumlah')),
                'tanggal' => $this->request->getPost('tanggal'),
                'deskripsi' => $this->request->getPost('deskripsi'),
                'status' => $this->request->getPost('status'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $biayaTambahanId = $this->biayaTambahanModel->insert($data);

            // Handle customer assignment
            $customerIds = $this->request->getPost('customer_ids');
            if (!empty($customerIds) && is_array($customerIds)) {
                $this->assignCustomersToNewBiayaTambahan($biayaTambahanId, $customerIds);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Biaya tambahan berhasil ditambahkan' . (empty($customerIds) ? '' : ' dan pelanggan berhasil di-assign')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal menambahkan biaya tambahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    private function assignCustomersToNewBiayaTambahan($biayaTambahanId, $customerIds)
    {
        $customerBiayaTambahanModel = model('CustomerBiayaTambahanModel');

        foreach ($customerIds as $customerId) {
            if (!empty($customerId)) {
                $customerBiayaTambahanModel->insert([
                    'customer_id' => $customerId,
                    'biaya_tambahan_id' => $biayaTambahanId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function edit($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/biaya_tambahan');
        }

        $data = $this->biayaTambahanModel->find($id);
        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/biaya_tambahan');
        }

        $rules = [
            'kategori' => 'required|min_length[3]|max_length[100]',
            'nama_biaya' => 'required|min_length[3]|max_length[255]',
            'jumlah' => 'required|numeric',
            'tanggal' => 'required|valid_date',
            'deskripsi' => 'permit_empty|max_length[500]',
            'status' => 'required|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        try {
            $data = [
                'kategori' => $this->request->getPost('kategori'),
                'nama_biaya' => $this->request->getPost('nama_biaya'),
                'jumlah' => str_replace(['.', ','], '', $this->request->getPost('jumlah')),
                'tanggal' => $this->request->getPost('tanggal'),
                'deskripsi' => $this->request->getPost('deskripsi'),
                'status' => $this->request->getPost('status'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->biayaTambahanModel->update($id, $data);

            // Handle customer assignment update
            $customerIds = $this->request->getPost('customer_ids');
            $this->updateCustomerAssignments($id, $customerIds);

            return $this->response->setJSON([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Biaya tambahan berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal mengupdate biaya tambahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/biaya_tambahan');
        }

        try {
            $data = $this->biayaTambahanModel->find($id);
            if (!$data) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title' => 'Gagal',
                    'message' => 'Data tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Delete related customer assignments first
            $db = \Config\Database::connect();
            $db->table('customer_biaya_tambahan')
                ->where('biaya_tambahan_id', $id)
                ->delete();

            // Then delete the main biaya tambahan record
            $this->biayaTambahanModel->delete($id);

            return $this->response->setJSON([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Biaya tambahan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal menghapus biaya tambahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    private function updateCustomerAssignments($biayaTambahanId, $customerIds)
    {
        $db = \Config\Database::connect();

        // Delete existing assignments with proper WHERE condition
        if (!empty($biayaTambahanId)) {
            $db->table('customer_biaya_tambahan')
                ->where('biaya_tambahan_id', $biayaTambahanId)
                ->delete();
        }

        // Insert new assignments
        if (!empty($customerIds) && is_array($customerIds)) {
            foreach ($customerIds as $customerId) {
                if (!empty($customerId)) {
                    $db->table('customer_biaya_tambahan')->insert([
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    /**
     * Search customers by query
     */
    public function searchCustomers()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        $query = $this->request->getVar('q');

        if (strlen($query) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Query terlalu pendek'
            ]);
        }

        try {
            $customerModel = model('CustomerModel');
            $customers = $customerModel->select('id_customers, nama_pelanggan, nomor_layanan')
                ->where('status_layanan', 'Active')
                ->groupStart()
                ->like('nama_pelanggan', $query)
                ->orLike('nomor_layanan', $query)
                ->groupEnd()
                ->orderBy('nama_pelanggan', 'ASC')
                ->limit(10) // Limit hasil untuk performance
                ->findAll();

            $options = [];
            foreach ($customers as $customer) {
                $options[] = [
                    'id' => $customer['id_customers'],
                    'nama_customer' => $customer['nama_pelanggan'],
                    'username' => $customer['nomor_layanan']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $options
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mencari customer: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get customer options for assignment
     */
    public function getCustomerOptions()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        try {
            $customerModel = model('CustomerModel');
            $customers = $customerModel->select('id_customers, nama_pelanggan, nomor_layanan')
                ->where('status_layanan', 'Active')
                ->orderBy('nama_pelanggan', 'ASC')
                ->findAll();

            $options = [];
            foreach ($customers as $customer) {
                $options[] = [
                    'id' => $customer['id_customers'],
                    'nama_customer' => $customer['nama_pelanggan'],
                    'username' => $customer['nomor_layanan']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $options
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data customer: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Assign customers to biaya tambahan
     */
    public function assignCustomers()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        try {
            $biayaTambahanId = $this->request->getPost('biaya_tambahan_id');
            $customerIds = $this->request->getPost('customer_ids');

            if (!$biayaTambahanId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID Biaya tambahan tidak valid'
                ])->setStatusCode(400);
            }

            // Validate biaya tambahan exists
            $biayaTambahan = $this->biayaTambahanModel->find($biayaTambahanId);
            if (!$biayaTambahan) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Biaya tambahan tidak ditemukan'
                ])->setStatusCode(404);
            }

            $customerBiayaTambahanModel = model('CustomerBiayaTambahanModel');

            // Remove existing assignments for this biaya tambahan
            $customerBiayaTambahanModel->where('biaya_tambahan_id', $biayaTambahanId)->delete();

            $assignedCount = 0;
            if (!empty($customerIds) && is_array($customerIds)) {
                $data = [];
                foreach ($customerIds as $customerId) {
                    $data[] = [
                        'customer_id' => $customerId,
                        'biaya_tambahan_id' => $biayaTambahanId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $assignedCount++;
                }

                if (!empty($data)) {
                    $customerBiayaTambahanModel->insertBatch($data);
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Berhasil assign {$assignedCount} customer ke biaya tambahan: {$biayaTambahan['nama_biaya']}"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal assign customer: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get assigned customers for a biaya tambahan
     */
    public function getAssignedCustomers()
    {
        $biayaTambahanId = $this->request->getVar('biaya_tambahan_id');

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request'])->setStatusCode(400);
        }

        try {
            $customerBiayaTambahanModel = model('CustomerBiayaTambahanModel');
            $assignedCustomers = $customerBiayaTambahanModel->getCustomersByBiayaTambahan($biayaTambahanId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $assignedCustomers
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data customer: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
