<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class Installation extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    /**
     * Waitinglist Installation Page
     */
    public function waitingList()
    {
        // Get customers waiting for installation (customers without installation date)
        $data['waitinglist'] = $this->customerModel
            ->select('customers.*, lokasi_server.name as branch_name, package_profiles.name as package_name')
            ->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left')
            ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
            ->where('customers.tgl_pasang IS NULL')
            ->orderBy('customers.created_at', 'DESC')
            ->findAll();

        $data['title'] = 'Waitinglist Installation';

        return view('installation/waiting_list', $data);
    }

    /**
     * On Progress Installation Page
     */
    public function onProgress()
    {
        // Get customers with installation date but not completed yet
        // Only show if: status_tagihan = 'enable' OR (has tgl_pasang AND status not Lunas AND has valid id)
        $data['onprogress'] = $this->customerModel
            ->select('customers.*, lokasi_server.name as branch_name, package_profiles.name as package_name')
            ->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left')
            ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
            ->where('customers.tgl_pasang IS NOT NULL')
            ->where('customers.status_tagihan !=', 'Lunas')
            ->where('customers.id_customers >', 0) // Exclude invalid IDs
            ->orderBy('customers.tgl_pasang', 'DESC')
            ->findAll();

        $data['title'] = 'On Progress Installation';

        return view('installation/on_progress', $data);
    }

    /**
     * Process installation (change status from waitinglist to active)
     */
    public function processInstallation($customerId)
    {
        try {
            $customer = $this->customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Update status to active
            $this->customerModel->update($customerId, [
                'status_tagihan' => 'enable',
                'tgl_pasang' => date('Y-m-d')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Customer berhasil diproses untuk instalasi'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel waitinglist
     */
    public function cancelWaitinglist($customerId)
    {
        try {
            $customer = $this->customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Update status to cancelled
            $this->customerModel->update($customerId, [
                'status_tagihan' => 'cancelled'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Waitinglist customer dibatalkan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel installation progress (move back to waitinglist)
     */
    public function cancelProgress($customerId)
    {
        try {
            $customer = $this->customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Reset installation date to move back to waitinglist
            $this->customerModel->update($customerId, [
                'tgl_pasang' => null,
                'status_tagihan' => 'Belum Lunas'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Instalasi dibatalkan, customer kembali ke waitinglist'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Activate customer (complete installation)
     */
    public function activateInstallation($customerId)
    {
        try {
            $customer = $this->customerModel->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            // Get JSON data from request
            $json = $this->request->getJSON(true);

            // Prepare update data
            $updateData = [
                'status_tagihan' => 'Lunas',
                'status_installation' => 'Installed',
                'status_layanan' => 'Active',
                'tgl_aktivasi' => date('Y-m-d')
            ];

            // Add optional fields if provided
            if (isset($json['odp']) && !empty($json['odp'])) {
                $updateData['odp_id'] = $json['odp'];
            }

            if (isset($json['area']) && !empty($json['area'])) {
                $updateData['area_id'] = $json['area'];
            }

            if (isset($json['router']) && !empty($json['router'])) {
                $updateData['id_lokasi_server'] = $json['router'];
            }

            if (isset($json['date_activated']) && !empty($json['date_activated'])) {
                $updateData['tgl_aktivasi'] = $json['date_activated'];
            }

            if (isset($json['payment_method']) && !empty($json['payment_method'])) {
                $updateData['payment_method'] = $json['payment_method'];
            }

            if (isset($json['pemegang_ikr']) && !empty($json['pemegang_ikr'])) {
                $updateData['pemegang_ikr'] = $json['pemegang_ikr'];
            }

            // Note: teknisi_id column doesn't exist in customers table
            // Tim teknisi info is stored separately or not stored

            // Update customer data
            $this->customerModel->update($customerId, $updateData);

            // Get updated customer data with all details for WhatsApp message
            $customerData = $this->customerModel
                ->select('customers.*, package_profiles.name as package_name, package_profiles.price as package_price')
                ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
                ->find($customerId);

            // Send WhatsApp notification
            $this->sendInstallationWhatsApp($customerData);

            // TODO: If create_ppoe is checked, create PPOE account here
            // if (isset($json['create_ppoe']) && $json['create_ppoe'] == 1) {
            //     // Create PPOE logic here
            // }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Customer berhasil diaktifkan dan notifikasi WhatsApp telah dikirim'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error activating installation: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send Installation WhatsApp Message
     */
    private function sendInstallationWhatsApp($customer)
    {
        try {
            // Check if customer has phone number
            if (empty($customer['telepphone'])) {
                log_message('warning', 'Customer ' . $customer['id_customers'] . ' has no phone number');
                return false;
            }

            // Calculate installation costs
            $biayaLayanan = floatval($customer['package_price'] ?? 0);
            $biayaInstallasi = 150000; // Default installation fee
            $biayaAdmin = 5000; // Admin fee
            $totalTagihan = $biayaLayanan + $biayaInstallasi + $biayaAdmin;

            // Get app settings
            $appName = env('app.name', 'KreatiVAbill');
            $appUrl = base_url();
            $appPhone = '0821-1269-2011'; // You can make this configurable

            // Generate default password (you can customize this)
            $defaultPassword = '123456';

            // Format message
            $message = "Yth. {$customer['nama']}, Tagihan Instalasi {$customer['package_name']}\n";
            $message .= "Total tagihan instalasi Anda adalah " . number_format($totalTagihan, 0, ',', '.') . "\n\n";
            $message .= "Detail Tagihan:\n";
            $message .= "Nama Paket : {$customer['package_name']}\n";
            $message .= "Biaya Layanan : Rp." . number_format($biayaLayanan, 0, ',', '.') . "\n";
            $message .= "Biaya Installasi : Rp." . number_format($biayaInstallasi, 0, ',', '.') . "\n";
            $message .= "Biaya Admin : Rp." . number_format($biayaAdmin, 0, ',', '.') . "\n";
            $message .= "Total Tagihan : " . number_format($totalTagihan, 0, ',', '.') . "\n\n";
            $message .= "Pembayaran {$appName} hanya dapat dilakukan melalui Aplikasi yang dapat di akses di\n";
            $message .= $appUrl . "\n\n";
            $message .= "Username : {$customer['email']}\n";
            $message .= "Password : {$defaultPassword}\n";
            $message .= "Catatan : Password default bisa digunakan jika sebelumnya tidak pernah merubah password.\n\n";
            $message .= "Salam,\n";
            $message .= "{$appName}\n";
            $message .= "{$appPhone}\n";
            $message .= $appUrl;

            // Send via WhatsApp Service
            $whatsappService = new \App\Services\WhatsAppService();
            $result = $whatsappService->sendMessage($customer['telepphone'], $message);

            if ($result['success']) {
                log_message('info', 'Installation WhatsApp sent to customer ' . $customer['id_customers']);
                return true;
            } else {
                log_message('error', 'Failed to send installation WhatsApp: ' . ($result['message'] ?? 'Unknown error'));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error sending installation WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get customer detail
     */
    public function getCustomerDetail($customerId)
    {
        try {
            $customer = $this->customerModel
                ->select('customers.*, lokasi_server.name as branch_name, package_profiles.name as package_name, package_profiles.price as harga, areas.area_name')
                ->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left')
                ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
                ->join('areas', 'areas.id = customers.area_id', 'left')
                ->find($customerId);

            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer tidak ditemukan'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * History Installation List Page
     */
    public function historyList()
    {
        // Get customers with completed installation (status_installation = 'Installed')
        $data['history'] = $this->customerModel
            ->select('customers.*, 
                      customers.nama_pelanggan as nama,
                      COALESCE(branches.branch_name, lokasi_server.name) as branch_name,
                      package_profiles.name as package_name')
            ->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left')
            ->join('branches', 'branches.id = customers.branch_id', 'left')
            ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
            ->where('customers.status_installation', 'Installed')
            ->orderBy('customers.tgl_aktivasi', 'DESC')
            ->findAll();

        $data['title'] = 'History Installation';

        return view('installation/history_list', $data);
    }

    /**
     * Installation History Detail
     */
    public function historyDetail($customerId)
    {
        $customer = $this->customerModel
            ->select('customers.*, 
                      customers.nama_pelanggan as nama,
                      customers.address as alamat,
                      COALESCE(areas.area_name, clustering.name) as area_name,
                      COALESCE(branches.branch_name, lokasi_server.name) as branch_name,
                      package_profiles.name as package_name, 
                      package_profiles.price as harga,
                      odps.odp_name,
                      users_sales.name_user as sales_name,
                      pppoe_accounts.pppoe_id,
                      pppoe_accounts.remote_address,
                      pppoe_accounts.local_address')
            ->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left')
            ->join('branches', 'branches.id = customers.branch_id', 'left')
            ->join('areas', 'areas.id = customers.area_id', 'left')
            ->join('clustering', 'clustering.id_clustering = customers.customer_clustering_id', 'left')
            ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
            ->join('odps', 'odps.id = customers.odp_id', 'left')
            ->join('users as users_sales', 'users_sales.id_user = customers.sales_id', 'left')
            ->join('pppoe_accounts', 'pppoe_accounts.customer_id = customers.id_customers', 'left')
            ->find($customerId);

        if (!$customer) {
            return redirect()->to('installation/history')->with('error', 'Customer tidak ditemukan');
        }

        $data['customer'] = $customer;
        $data['title'] = 'Installation Detail';

        return view('installation/history_detail', $data);
    }
}
