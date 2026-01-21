<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\WhatsappMessageLogModel;

class Master extends BaseController
{
    /**
     * View untuk menampilkan semua notifikasi WhatsApp yang telah dikirim
     */
    public function notificationWhatsapp()
    {
        $messageLogModel = new WhatsappMessageLogModel();

        // Get all WhatsApp message logs with pagination
        $perPage = 10;
        $logs = $messageLogModel
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);

        $data = [
            'logs' => $logs,
            'pager' => $messageLogModel->pager
        ];

        return view('master/notification_whatsapp', $data);
    }

    /**
     * Delete notification log
     */
    public function deleteNotification($id)
    {
        $messageLogModel = new WhatsappMessageLogModel();

        if ($messageLogModel->delete($id)) {
            return redirect()->to('master/notification-whatsapp')->with('success', 'Notifikasi berhasil dihapus');
        } else {
            return redirect()->back()->with('error', 'Gagal menghapus notifikasi');
        }
    }

    /**
     * Edit notification (just view detail in modal)
     */
    public function getNotificationDetail($id)
    {
        $messageLogModel = new WhatsappMessageLogModel();
        $log = $messageLogModel->find($id);

        if (!$log) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * Retry failed notification
     */
    public function retryNotification($id)
    {
        $messageLogModel = new WhatsappMessageLogModel();
        $log = $messageLogModel->find($id);

        if (!$log) {
            return redirect()->back()->with('error', 'Notifikasi tidak ditemukan');
        }

        // Use WhatsApp service to resend
        $whatsappService = new \App\Services\WhatsAppService();
        $result = $whatsappService->sendMessage($log['phone_number'], $log['message_content']);

        if ($result['success']) {
            // Update status to sent
            $messageLogModel->update($id, [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
                'error_message' => null
            ]);
            return redirect()->back()->with('success', 'Notifikasi berhasil dikirim ulang');
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim ulang: ' . $result['message']);
        }
    }

    /**
     * View untuk menampilkan semua area/jalur
     */
    public function area()
    {
        $areaModel = new \App\Models\AreaModel();
        $branchModel = new \App\Models\BranchModel();

        $data = [
            'areas' => $areaModel->getAreasWithBranch(),
            'branches' => $branchModel->findAll()
        ];

        return view('master/area', $data);
    }

    /**
     * Get areas data for DataTables (AJAX)
     */
    public function getAreasData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $areaModel = new \App\Models\AreaModel();

        $request = $this->request->getPost();
        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $searchValue = $request['search']['value'] ?? '';

        $orderColumnIndex = $request['order'][0]['column'] ?? 0;
        $orderDir = $request['order'][0]['dir'] ?? 'desc';

        $columns = ['id', 'branch_name', 'area_name', 'latitude', 'longitude', 'created_at'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

        $params = [
            'search' => $searchValue,
            'order' => [
                'column' => $orderColumn,
                'dir' => $orderDir
            ],
            'limit' => $length,
            'offset' => $start
        ];

        $data = $areaModel->getDatatables($params);
        $totalRecords = $areaModel->countAll();
        $totalFiltered = $areaModel->countFiltered($params);

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    /**
     * Get area detail by ID
     */
    public function getAreaDetail($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $areaModel = new \App\Models\AreaModel();
        $area = $areaModel->getAreaWithBranch($id);

        if (!$area) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Area tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $area
        ]);
    }

    /**
     * Create new area
     */
    public function createArea()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $areaModel = new \App\Models\AreaModel();

        $data = [
            'branch_id' => $this->request->getPost('branch_id'),
            'area_name' => $this->request->getPost('area_name'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'created_by' => session()->get('id_user')
        ];

        if ($areaModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Area berhasil ditambahkan'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menambahkan area',
                'errors' => $areaModel->errors()
            ]);
        }
    }

    /**
     * Update area
     */
    public function updateArea($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $areaModel = new \App\Models\AreaModel();
        $area = $areaModel->find($id);

        if (!$area) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Area tidak ditemukan'
            ]);
        }

        $data = [
            'branch_id' => $this->request->getPost('branch_id'),
            'area_name' => $this->request->getPost('area_name'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'updated_by' => session()->get('id_user')
        ];

        if ($areaModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Area berhasil diperbarui'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memperbarui area',
                'errors' => $areaModel->errors()
            ]);
        }
    }

    /**
     * Delete area
     */
    public function deleteArea($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $areaModel = new \App\Models\AreaModel();
        $area = $areaModel->find($id);

        if (!$area) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Area tidak ditemukan'
            ]);
        }

        if ($areaModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Area berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus area'
            ]);
        }
    }

    /**
     * View untuk menampilkan semua ODP
     */
    public function odp()
    {
        $odpModel = new \App\Models\OdpModel();
        $areaModel = new \App\Models\AreaModel();

        $data = [
            'odps' => $odpModel->getOdpsWithRelations(),
            'areas' => $areaModel->findAll()
        ];

        return view('master/odp', $data);
    }

    /**
     * Get ODPs data for DataTables (AJAX)
     */
    public function getOdpsData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $odpModel = new \App\Models\OdpModel();

        $request = $this->request->getPost();
        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $searchValue = $request['search']['value'] ?? '';

        $orderColumnIndex = $request['order'][0]['column'] ?? 1;
        $orderDir = $request['order'][0]['dir'] ?? 'desc';

        $columns = ['id', 'id', 'branch_name', 'area_name', 'odp_name', 'customer_active', 'core', 'created_at', 'updated_at'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';

        $params = [
            'search' => $searchValue,
            'order' => [
                'column' => $orderColumn,
                'dir' => $orderDir
            ],
            'limit' => $length,
            'offset' => $start
        ];

        $data = $odpModel->getDatatables($params);
        $totalRecords = $odpModel->countAll();
        $totalFiltered = $odpModel->countFiltered($params);

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    /**
     * Get ODP detail by ID
     */
    public function getOdpDetail($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $odpModel = new \App\Models\OdpModel();
        $odp = $odpModel->getOdpWithRelations($id);

        if (!$odp) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ODP tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $odp
        ]);
    }

    /**
     * Get areas by branch ID (AJAX)
     */
    public function getAreasByBranch($branchId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $areaModel = new \App\Models\AreaModel();
        $areas = $areaModel->getAreasByBranch($branchId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $areas
        ]);
    }

    /**
     * Create new ODP
     */
    public function createOdp()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $odpModel = new \App\Models\OdpModel();

        $data = [
            'area_id' => $this->request->getPost('area_id'),
            'odp_name' => $this->request->getPost('odp_name'),
            'district' => $this->request->getPost('district'),
            'village' => $this->request->getPost('village'),
            'parent_odp' => $this->request->getPost('parent_odp') ?: null,
            'core' => $this->request->getPost('core'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'address' => $this->request->getPost('address'),
            'created_by' => session()->get('id_user')
        ];

        if ($odpModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'ODP berhasil ditambahkan',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menambahkan ODP',
                'errors' => $odpModel->errors(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Update ODP
     */
    public function updateOdp($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $odpModel = new \App\Models\OdpModel();
        $odp = $odpModel->find($id);

        if (!$odp) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ODP tidak ditemukan',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $data = [
            'area_id' => $this->request->getPost('area_id'),
            'odp_name' => $this->request->getPost('odp_name'),
            'district' => $this->request->getPost('district'),
            'village' => $this->request->getPost('village'),
            'parent_odp' => $this->request->getPost('parent_odp') ?: null,
            'core' => $this->request->getPost('core'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'address' => $this->request->getPost('address'),
            'updated_by' => session()->get('id_user')
        ];

        if ($odpModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'ODP berhasil diperbarui',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memperbarui ODP',
                'errors' => $odpModel->errors(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Delete ODP
     */
    public function deleteOdp($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $odpModel = new \App\Models\OdpModel();
        $odp = $odpModel->find($id);

        if (!$odp) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ODP tidak ditemukan',
                'csrf_hash' => csrf_hash()
            ]);
        }

        if ($odpModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'ODP berhasil dihapus',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus ODP',
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Get ODPs by area
     */
    public function getOdpsByArea($areaId)
    {
        $odpModel = new \App\Models\OdpModel();

        $odps = $odpModel
            ->where('area_id', $areaId)
            ->select('id, odp_name, customer_active, core, latitude, longitude')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $odps
        ]);
    }

    /**
     * Get customers by ODP
     */
    public function getCustomersByOdp($odpId)
    {
        // Note: Tabel customers belum memiliki field odp_id
        // Untuk sementara return sample data untuk testing UI
        // TODO: Tambahkan kolom odp_id di tabel customers dan update saat customer dipasang

        $odpModel = new \App\Models\OdpModel();
        $odp = $odpModel->find($odpId);

        if (!$odp) {
            return $this->response->setJSON([
                'success' => false,
                'data' => []
            ]);
        }

        // Sample data untuk testing (sesuai dengan screenshot)
        $customers = [
            [
                'id' => 1,
                'name' => 'Sidik Langkob',
                'phone' => '6285691791974',
                'package' => 'Paket Internet 10Mbps',
                'status' => 'Active',
                'created_at' => '20 Februari 2025 16:10'
            ],
            [
                'id' => 2,
                'name' => 'Ujang',
                'phone' => '6282112693011',
                'package' => 'Paket Internet 10Mbps',
                'status' => 'Active',
                'created_at' => '09 September 2025 22:10'
            ],
            [
                'id' => 3,
                'name' => 'Jun',
                'phone' => '6285691791974',
                'package' => 'Paket Internet 20Mbps',
                'status' => 'Active',
                'created_at' => '10 September 2025 14:30'
            ]
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $customers
        ]);
    }
}
