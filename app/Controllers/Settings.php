<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Settings extends BaseController
{
    public function company()
    {
        $companyModel = new \App\Models\CompanyModel();
        // Ambil data perusahaan pertama (jika ada)
        $company = $companyModel->first();
        return view('settings/company', [
            'company' => $company
        ]);
    }
    public function saveCompany()
    {
        $request = $this->request;

        // Debug: Log request data
        log_message('info', 'Company save request received');
        log_message('info', 'POST data: ' . json_encode($request->getPost()));
        log_message('info', 'Files: ' . json_encode($_FILES));
        log_message('info', 'Headers: ' . json_encode($request->headers()));

        // Check if request is AJAX
        if (!$request->isAJAX()) {
            log_message('warning', 'Non-AJAX request to saveCompany');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request harus menggunakan AJAX'
            ]);
        }

        // Validation rules - diperbaiki
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|max_length[255]',
            'address' => 'required|min_length[5]|max_length[500]',
            'phone' => 'required|min_length[8]|max_length[20]',
            'website' => 'permit_empty|max_length[255]'
        ];

        // Custom validation messages
        $messages = [
            'name' => [
                'required' => 'Nama perusahaan harus diisi',
                'min_length' => 'Nama perusahaan minimal 3 karakter',
                'max_length' => 'Nama perusahaan maksimal 255 karakter'
            ],
            'email' => [
                'required' => 'Email perusahaan harus diisi',
                'valid_email' => 'Format email tidak valid',
                'max_length' => 'Email maksimal 255 karakter'
            ],
            'address' => [
                'required' => 'Alamat perusahaan harus diisi',
                'min_length' => 'Alamat minimal 5 karakter',
                'max_length' => 'Alamat maksimal 500 karakter'
            ],
            'phone' => [
                'required' => 'Nomor telepon harus diisi',
                'min_length' => 'Nomor telepon minimal 8 karakter',
                'max_length' => 'Nomor telepon maksimal 20 karakter'
            ],
            'website' => [
                'max_length' => 'Website maksimal 255 karakter'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', array_values($errors));

            log_message('warning', 'Validation failed: ' . json_encode($errors));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . $errorMessage,
                'errors' => $errors
            ]);
        }

        $data = [
            'name'    => trim($request->getPost('name')),
            'email'   => trim($request->getPost('email')),
            'address' => trim($request->getPost('address')),
            'phone'   => trim($request->getPost('phone')),
            'website' => trim($request->getPost('website')) ?: null,
        ];

        // Jika ada ID, tambahkan untuk update
        $id = $request->getPost('id');
        if (!empty($id)) {
            $data['id'] = $id;
        }

        // Handle logo upload
        if ($logo = $this->request->getFile('logo')) {
            if ($logo->isValid() && !$logo->hasMoved()) {
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($logo->getMimeType(), $allowedTypes)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF'
                    ]);
                }

                // Validate file size (max 2MB)
                if ($logo->getSize() > 2 * 1024 * 1024) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Ukuran file terlalu besar. Maksimal 2MB'
                    ]);
                }

                // Hapus logo lama jika update
                if (!empty($id)) {
                    $companyModel = new \App\Models\CompanyModel();
                    $oldCompany = $companyModel->find($id);
                    if ($oldCompany && !empty($oldCompany['logo'])) {
                        $oldLogoPath = WRITEPATH . '../public/uploads/' . $oldCompany['logo'];
                        if (file_exists($oldLogoPath)) {
                            unlink($oldLogoPath);
                        }
                    }
                }

                $newName = $logo->getRandomName();
                if (!$logo->move('uploads', $newName)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal mengupload logo'
                    ]);
                }
                $data['logo'] = $newName;
            }
        }

        $companyModel = new \App\Models\CompanyModel();

        try {
            // Debug: Log data yang akan disimpan
            log_message('info', 'Company data to save: ' . json_encode($data));

            if (!empty($data['id'])) {
                // Update existing company
                $result = $companyModel->update($data['id'], $data);
                $message = 'Data perusahaan berhasil diperbarui';
            } else {
                // Insert new company
                unset($data['id']); // Remove id for insert
                $result = $companyModel->insert($data);
                $message = 'Data perusahaan berhasil disimpan';
            }

            if (!$result) {
                $errors = $companyModel->errors();
                log_message('error', 'Company save failed: ' . json_encode($errors));

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data perusahaan',
                    'errors' => $errors,
                    'debug_data' => $data
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Company save exception: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'debug_trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Debug method untuk testing
    public function debugSaveCompany()
    {
        $request = $this->request;

        // Log semua data request untuk debug
        log_message('info', 'DEBUG: Company save request');
        log_message('info', 'Method: ' . $request->getMethod());
        log_message('info', 'POST data: ' . json_encode($request->getPost()));
        log_message('info', 'Content-Type: ' . $request->getHeaderLine('Content-Type'));
        log_message('info', 'Is AJAX: ' . ($request->isAJAX() ? 'Yes' : 'No'));

        // Simple validation untuk debug
        $name = trim($request->getPost('name'));
        $email = trim($request->getPost('email'));

        if (empty($name)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nama perusahaan harus diisi',
                'debug' => 'Name field is empty'
            ]);
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email tidak valid',
                'debug' => 'Email validation failed'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Debug: Validasi berhasil',
            'received_data' => [
                'name' => $name,
                'email' => $email,
                'address' => $request->getPost('address'),
                'phone' => $request->getPost('phone'),
                'website' => $request->getPost('website')
            ]
        ]);
    }

    public function debugRoute()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Settings controller is accessible',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function whatsapp()
    {
        // Ambil nomor WhatsApp aktif (dari device terakhir atau default)
        $deviceModel = new \App\Models\WhatsappDeviceModel();
        $notifModel = new \App\Models\WhatsappNotifSettingModel();
        $number = null;
        $device = $deviceModel->orderBy('id', 'desc')->first();
        if ($device) {
            $number = $device['number'];
        }
        // Ambil pengaturan notifikasi dari DB
        $notif = null;
        if ($number) {
            $notif = $notifModel->where('number', $number)->first();
        }
        return view('whatsapp', [
            'notif_settings' => $notif,
            'wa_number' => $number,
            'device' => $device
        ]);
    }

    public function paymentGateway()
    {
        $paymentModel = new \App\Models\PaymentGatewayModel();
        $gateways = $paymentModel->getGatewaySettings();

        return view('settings/payment_gateway', [
            'gateways' => $gateways
        ]);
    }

    public function savePaymentGateway()
    {
        $request = $this->request;
        $paymentModel = new \App\Models\PaymentGatewayModel();

        $gatewayType = $request->getPost('gateway_type');

        // Get existing gateway data
        $existing = $paymentModel->where('gateway_type', $gatewayType)->first();

        $data = [
            'gateway_name' => $request->getPost('gateway_name'),
            'is_active' => $request->getPost('is_active') ? 1 : 0,
            'environment' => $request->getPost('environment'),
            'payment_expiry_hours' => (int) ($request->getPost('payment_expiry_hours') ?? 24), // Tambahkan payment expiry
            'settings' => json_encode([
                'webhook_url' => $request->getPost('webhook_url'),
                'timeout' => $request->getPost('timeout'),
                'auto_settle' => $request->getPost('auto_settle')
            ])
        ];

        // Log untuk debugging
        log_message('info', 'Saving payment gateway with expiry: ' . $data['payment_expiry_hours'] . ' hours');

        // If activating this gateway, deactivate all other gateways
        if ($data['is_active'] == 1) {
            $paymentModel->where('gateway_type !=', $gatewayType)->set(['is_active' => 0])->update();
        }

        // Only update credentials if they are provided (not empty)
        $apiKey = $request->getPost('api_key');
        $apiSecret = $request->getPost('api_secret');
        $merchantCode = $request->getPost('merchant_code');
        $privateKey = $request->getPost('private_key');
        $callbackKey = $request->getPost('callback_key');

        // If updating existing gateway, only update fields that are not empty
        if ($existing) {
            if (!empty($apiKey)) {
                $data['api_key'] = $apiKey;
            }
            if (!empty($apiSecret)) {
                $data['api_secret'] = $apiSecret;
            }
            if (!empty($merchantCode)) {
                $data['merchant_code'] = $merchantCode;
            }
            if (!empty($privateKey)) {
                $data['private_key'] = $privateKey;
            }
            if (!empty($callbackKey)) {
                $data['callback_key'] = $callbackKey;
            }
        } else {
            // New gateway, require all fields
            $data['api_key'] = $apiKey;
            $data['api_secret'] = $apiSecret;
            $data['merchant_code'] = $merchantCode;
            $data['private_key'] = $privateKey;
            $data['callback_key'] = $callbackKey;
        }

        // Set default admin fees untuk Flip
        if ($gatewayType === 'flip') {
            $data['admin_fees'] = json_encode([
                'flip_va' => 4000,
                'flip_qris' => 0,
                'flip_ewallet' => 0,
                'flip_retail' => 2500
            ]);
        }

        $result = $paymentModel->saveGatewayConfig($gatewayType, $data);

        if ($result) {
            $message = $data['is_active'] == 1
                ? 'Payment gateway berhasil diaktifkan. Gateway lain otomatis dinonaktifkan.'
                : 'Pengaturan payment gateway berhasil disimpan.';

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan pengaturan payment gateway.'
            ]);
        }
    }

    // ==================== BRANCH MANAGEMENT ====================

    /**
     * Display branch management page
     */
    public function branch()
    {
        return view('settings/branch');
    }

    /**
     * Get branch list for DataTables
     */
    public function branchList()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid request'
                ]);
            }

            $branchModel = new \App\Models\BranchModel();

            $request = $this->request->getPost();

            // DataTables parameters
            $start = $request['start'] ?? 0;
            $length = $request['length'] ?? 10;
            $searchValue = $request['search']['value'] ?? '';

            // Order parameters
            $orderColumnIndex = $request['order'][0]['column'] ?? 1;
            $orderDir = $request['order'][0]['dir'] ?? 'desc';

            $columns = ['', 'id', 'branch_name', 'city', 'payment_type', 'due_date', 'day_before_due_date', 'created_by', 'created_at', 'updated_at'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';

            $params = [
                'start' => $start,
                'length' => $length,
                'search' => $searchValue,
                'order' => [
                    'column' => $orderColumn,
                    'dir' => $orderDir
                ]
            ];

            $data = $branchModel->getDatatables($params);
            $totalRecords = $branchModel->countAll();
            $filteredRecords = $branchModel->countFiltered($params);

            // Format data for DataTables
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'action' => '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary editBranch" data-id="' . $row['id'] . '">
                                    <i class="bx bx-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger deleteBranch" data-id="' . $row['id'] . '">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>',
                    'id' => $row['id'],
                    'branch_name' => $row['branch_name'],
                    'city' => $row['city'],
                    'payment_type' => $row['payment_type'],
                    'due_date' => $row['due_date'],
                    'day_before_due_date' => $row['day_before_due_date'],
                    'created_by' => $row['created_by_name'] ?? 'System',
                    'created_at' => date('d M Y H:i', strtotime($row['created_at'])),
                    'updated_at' => date('d M Y H:i', strtotime($row['updated_at']))
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($request['draw'] ?? 1),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $formattedData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Branch list error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store new branch
     */
    public function branchStore()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        }

        $branchModel = new \App\Models\BranchModel();

        $data = [
            'branch_name' => trim($this->request->getPost('branch_name')),
            'city' => trim($this->request->getPost('city')),
            'payment_type' => $this->request->getPost('payment_type'),
            'due_date' => $this->request->getPost('due_date'),
            'day_before_due_date' => $this->request->getPost('day_before_due_date'),
            'address' => trim($this->request->getPost('address')),
            'description' => trim($this->request->getPost('description')),
            'created_by' => session()->get('id_user')
        ];

        if (!$branchModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to save branch',
                'errors' => $branchModel->errors()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Branch created successfully'
        ]);
    }

    /**
     * Get branch data for edit
     */
    public function branchEdit($id)
    {
        $branchModel = new \App\Models\BranchModel();
        $branch = $branchModel->find($id);

        if (!$branch) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Branch not found'
            ]);
        }

        return $this->response->setJSON($branch);
    }

    /**
     * Update branch
     */
    public function branchUpdate()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        }

        $branchModel = new \App\Models\BranchModel();
        $id = $this->request->getPost('branch_id');

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Branch ID is required'
            ]);
        }

        $data = [
            'branch_name' => trim($this->request->getPost('branch_name')),
            'city' => trim($this->request->getPost('city')),
            'payment_type' => $this->request->getPost('payment_type'),
            'due_date' => $this->request->getPost('due_date'),
            'day_before_due_date' => $this->request->getPost('day_before_due_date'),
            'address' => trim($this->request->getPost('address')),
            'description' => trim($this->request->getPost('description')),
            'updated_by' => session()->get('id_user')
        ];

        if (!$branchModel->update($id, $data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update branch',
                'errors' => $branchModel->errors()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Branch updated successfully'
        ]);
    }

    /**
     * Delete branch
     */
    public function branchDelete($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);
        }

        $branchModel = new \App\Models\BranchModel();

        if (!$branchModel->find($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Branch not found'
            ]);
        }

        if (!$branchModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete branch'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Branch deleted successfully'
        ]);
    }
}
