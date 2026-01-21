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
            'jumlah' => 'required|numeric|greater_than[0]',
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

            $this->biayaTambahanModel->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Biaya tambahan berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal menambahkan biaya tambahan: ' . $e->getMessage()
            ])->setStatusCode(500);
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
            'jumlah' => 'required|numeric|greater_than[0]',
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
}
