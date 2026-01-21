<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Cluster extends ResourceController
{
	/**
	 * @var \App\Models\ClusteringModel
	 */
	protected $clustering;

	function __construct()
	{
		$this->clustering = model('ClusteringModel');
	}

	public function index()
	{
		$request = $this->request;
		// DataTables always sends 'draw' parameter
		if ($request->getGet('draw') !== null) {
			$start = $request->getGet('start') ?? 0;
			$length = $request->getGet('length') ?? 10;
			$search = $request->getGet('search')['value'] ?? '';

			$builder = $this->clustering;
			if ($search) {
				$builder = $builder->like('name', $search)
					->orLike('type_option', $search)
					->orLike('lokasi_server_id', $search)
					->orLike('number_of_ports', $search)
					->orLike('coordinate', $search)
					->orLike('address', $search);
			}
			$total = $this->clustering->countAllResults(false);
			$filtered = $builder->countAllResults(false);

			// Join with lokasi_server table to get server names
			$db = \Config\Database::connect();
			$sqlBuilder = $db->table('clustering c');
			$sqlBuilder->select('c.*, ls.name as server_name');
			$sqlBuilder->join('lokasi_server ls', 'ls.id_lokasi = c.lokasi_server_id', 'left');

			if ($search) {
				$sqlBuilder->groupStart()
					->like('c.name', $search)
					->orLike('c.type_option', $search)
					->orLike('ls.name', $search)
					->orLike('c.number_of_ports', $search)
					->orLike('c.coordinate', $search)
					->orLike('c.address', $search)
					->groupEnd();
			}

			$sqlBuilder->orderBy('c.id_clustering', 'DESC');
			$sqlBuilder->limit($length, $start);
			$data = $sqlBuilder->get()->getResultArray();

			$result = [];
			$no = $start + 1;
			foreach ($data as $row) {
				// Hitung jumlah pelanggan yang memakai port pada cluster ini
				$customerModel = model('CustomerModel');
				$used_ports = $customerModel->where('customer_clustering_id', $row['id_clustering'])->countAllResults();
				$remaining_ports = isset($row['number_of_ports']) ? ($row['number_of_ports'] - $used_ports) : '-';
				$result[] = [
					'DT_RowIndex' => $no++,
					'name' => $row['name'],
					'type_option' => $row['type_option'],
					'lokasi_server_id' => $row['server_name'] ?? 'Server tidak ditemukan',
					'number_of_ports' => $row['number_of_ports'],
					'remaining_ports' => $remaining_ports,
					'coordinate' => $row['coordinate'],
					'address' => $row['address'],
					'action' => '<a href="' . site_url('clustering/' . ($row['id_clustering'] ?? '') . '/edit') . '" class="btn btn-sm btn-primary"><i class="bx bx-edit"></i></a> '
						. '<button class="btn btn-sm btn-danger deleteCluster" data-id="' . ($row['id_clustering'] ?? '') . '"><i class="bx bx-trash"></i></button>',
				];
			}

			return $this->response->setJSON([
				'draw' => intval($request->getGet('draw')),
				'recordsTotal' => $total,
				'recordsFiltered' => $filtered,
				'data' => $result
			]);
		}

		// Load server locations for dropdown
		$db = \Config\Database::connect();
		$servers = $db->table('lokasi_server')
			->select('id_lokasi, name')
			->orderBy('name', 'ASC')
			->get()
			->getResult();

		return view('clustering/index', ['servers' => $servers]);
	}

	/**
	 * Store cluster data (handles both AJAX and regular form submission)
	 */
	public function store()
	{
		// Debug: log request data
		log_message('debug', 'Store method called. Request data: ' . json_encode($this->request->getPost()));
		log_message('debug', 'Is AJAX: ' . ($this->request->isAJAX() ? 'true' : 'false'));

		// Allow both AJAX and regular form submissions
		$isAjax = $this->request->isAJAX();

		$validation = \Config\Services::validation();
		$data = [
			'name' => $this->request->getPost('name'),
			'number_of_ports' => $this->request->getPost('number_of_ports'),
			'type_option' => $this->request->getPost('type_option'),
			'lokasi_server_id' => $this->request->getPost('lokasi_server_id'),
			'latitude' => $this->request->getPost('latitude'),
			'longitude' => $this->request->getPost('longitude'),
			'address' => $this->request->getPost('address'),
		];

		// Buat coordinate dari latitude dan longitude untuk backward compatibility
		if ($data['latitude'] && $data['longitude']) {
			$data['coordinate'] = $data['latitude'] . ',' . $data['longitude'];
		}

		$validationRules = [
			'name' => 'required|min_length[3]',
			'number_of_ports' => 'permit_empty|numeric',
			'type_option' => 'required',
			'lokasi_server_id' => 'required|numeric',
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
			'address' => 'permit_empty|min_length[3]',
		];
		if (!$validation->setRules($validationRules)->run($data)) {
			if ($isAjax) {
				return $this->response->setJSON([
					'success' => false,
					'errors' => $validation->getErrors(),
				]);
			} else {
				// For regular form submission, redirect back with errors
				return redirect()->back()->withInput()->with('errors', $validation->getErrors());
			}
		}

		try {
			$this->clustering->insert($data);
			if ($isAjax) {
				return $this->response->setJSON([
					'success' => true,
					'message' => 'Cluster berhasil disimpan!'
				]);
			} else {
				// For regular form submission, redirect with success message
				return redirect()->to(site_url('clustering'))->with('success', 'Cluster berhasil disimpan!');
			}
		} catch (\Exception $e) {
			if ($isAjax) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'Gagal menyimpan cluster: ' . $e->getMessage()
				]);
			} else {
				// For regular form submission, redirect back with error
				return redirect()->back()->withInput()->with('error', 'Gagal menyimpan cluster: ' . $e->getMessage());
			}
		}
	}

	/**
	 * Get cluster by ID (for edit modal)
	 */
	public function show($id = null)
	{
		if (!$this->request->isAJAX()) {
			return $this->fail('Invalid request', 400);
		}
		$row = $this->clustering->find($id);
		if ($row) {
			return $this->response->setJSON($row);
		} else {
			return $this->failNotFound('Cluster not found');
		}
	}

	/**
	 * Update cluster by ID (handles both AJAX and regular form submission)
	 */
	public function update($id = null)
	{
		// Allow both AJAX and regular form submissions
		$isAjax = $this->request->isAJAX();
		$validation = \Config\Services::validation();
		$data = [
			'name' => $this->request->getPost('name'),
			'number_of_ports' => $this->request->getPost('number_of_ports'),
			'type_option' => $this->request->getPost('type_option'),
			'lokasi_server_id' => $this->request->getPost('lokasi_server_id'),
			'latitude' => $this->request->getPost('latitude'),
			'longitude' => $this->request->getPost('longitude'),
			'address' => $this->request->getPost('address'),
		];

		// Buat coordinate dari latitude dan longitude untuk backward compatibility
		if ($data['latitude'] && $data['longitude']) {
			$data['coordinate'] = $data['latitude'] . ',' . $data['longitude'];
		}

		$validationRules = [
			'name' => 'required|min_length[3]',
			'number_of_ports' => 'permit_empty|numeric',
			'type_option' => 'required',
			'lokasi_server_id' => 'required|numeric',
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
			'address' => 'permit_empty|min_length[3]',
		];
		if (!$validation->setRules($validationRules)->run($data)) {
			if ($isAjax) {
				return $this->response->setJSON([
					'success' => false,
					'errors' => $validation->getErrors(),
				]);
			} else {
				// For regular form submission, redirect back with errors
				return redirect()->back()->withInput()->with('errors', $validation->getErrors());
			}
		}
		try {
			$this->clustering->update($id, $data);
			if ($isAjax) {
				return $this->response->setJSON([
					'success' => true,
					'message' => 'Cluster berhasil diupdate!'
				]);
			} else {
				// For regular form submission, redirect with success message
				return redirect()->to(site_url('clustering'))->with('success', 'Cluster berhasil diupdate!');
			}
		} catch (\Exception $e) {
			if ($isAjax) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'Gagal update cluster: ' . $e->getMessage()
				]);
			} else {
				// For regular form submission, redirect back with error
				return redirect()->back()->withInput()->with('error', 'Gagal update cluster: ' . $e->getMessage());
			}
		}
	}

	/**
	 * Delete cluster by ID (AJAX)
	 */
	public function delete($id = null)
	{
		if (!$this->request->isAJAX()) {
			return $this->fail('Invalid request', 400);
		}

		try {
			$cluster = $this->clustering->find($id);
			if (!$cluster) {
				return $this->response->setJSON([
					'success' => false,
					'message' => 'Cluster tidak ditemukan'
				]);
			}

			$this->clustering->delete($id);
			return $this->response->setJSON([
				'success' => true,
				'message' => 'Cluster berhasil dihapus!'
			]);
		} catch (\Exception $e) {
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Gagal menghapus cluster: ' . $e->getMessage()
			]);
		}
	}

	public function create()
	{
		try {
			// Load server locations for dropdown
			$servers = model('ServerLocationModel')->findAll();

			$data = [
				'servers' => $servers
			];

			return view('clustering/create', $data);
		} catch (\Exception $e) {
			log_message('error', 'Error in Cluster::create - ' . $e->getMessage());
			return redirect()->to(site_url('clustering'))->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
		}
	}

	public function edit($id = null)
	{
		if (!$id) {
			return redirect()->to(site_url('clustering'))->with('error', 'ID cluster tidak valid.');
		}

		$cluster = $this->clustering->find($id);
		if (!$cluster) {
			return redirect()->to(site_url('clustering'))->with('error', 'Cluster tidak ditemukan.');
		}

		// Load server locations for dropdown
		$servers = model('ServerLocationModel')->findAll();

		$data = [
			'cluster' => $cluster,
			'servers' => $servers
		];

		return view('clustering/edit', $data);
	}

	// --- ADDED: API endpoint to get all clusters for dropdown ---
	public function all()
	{
		// Jangan batasi hanya AJAX agar bisa diakses dari browser/fetch
		$clusters = $this->clustering->orderBy('name', 'ASC')->findAll();
		$result = [];
		foreach ($clusters as $c) {
			$result[] = [
				'id_clustering' => $c->id_clustering,
				'name' => $c->name
			];
		}
		return $this->response->setJSON($result);
	}

	// Test methods untuk debugging
	public function testGet()
	{
		log_message('debug', 'testGet method called - GET request berhasil');
		return $this->response->setJSON(['status' => 'success', 'message' => 'GET test berhasil']);
	}

	public function testStore()
	{
		log_message('debug', 'testStore method called - POST request berhasil');
		log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
		log_message('debug', 'Is AJAX: ' . ($this->request->isAJAX() ? 'Yes' : 'No'));

		// Format response yang diharapkan JavaScript
		return $this->response->setJSON([
			'success' => true,
			'message' => 'Data clustering berhasil disimpan (test mode)',
			'data' => $this->request->getPost()
		]);
	}
}
