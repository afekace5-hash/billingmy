<?php

namespace App\Controllers;

use App\Models\ProrateModel;
use App\Models\CustomerModel;

class Prorate extends BaseController
{
    protected $prorateModel;
    protected $customerModel;

    public function __construct()
    {
        $this->prorateModel = new ProrateModel();
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Prorate Management'
        ];
        return view('prorate/index', $data);
    }

    public function data()
    {
        $request = \Config\Services::request();
        $db = \Config\Database::connect();

        // DataTables parameters
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $draw = $request->getPost('draw') ?? 1;
        $searchValue = $request->getPost('search')['value'] ?? '';

        // Base query
        $builder = $db->table('prorate p');
        $builder->select('p.*, c.nama_pelanggan as customer_name, 
                         pk.name as package, pk.price as package_price,
                         DATE_FORMAT(p.created_at, "%d %M %Y %H:%i") as created_at_formatted,
                         DATE_FORMAT(p.updated_at, "%e bulan yang lalu") as updated_at_formatted');
        $builder->join('customers c', 'c.id_customers = p.customer_id', 'left');
        $builder->join('package_profiles pk', 'pk.id = c.id_paket', 'left');

        // Search filter
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('p.id', $searchValue)
                ->orLike('c.nama_pelanggan', $searchValue)
                ->orLike('p.invoice_month', $searchValue)
                ->orLike('p.description', $searchValue)
                ->groupEnd();
        }

        // Total records
        $totalRecords = $builder->countAllResults(false);

        // Ordering
        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 1;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';
        $columns = ['', 'id', 'invoice_month', 'customer_name', 'package', 'prorate_amount', 'description', 'created_at', 'updated_at'];
        if (isset($columns[$orderColumnIndex]) && !empty($columns[$orderColumnIndex])) {
            $builder->orderBy('p.' . $columns[$orderColumnIndex], $orderDir);
        }

        // Pagination
        $builder->limit($length, $start);

        $prorates = $builder->get()->getResultArray();

        // Format data for DataTables
        $data = [];
        foreach ($prorates as $prorate) {
            // Format invoice month
            $invoiceMonth = date('F Y', strtotime($prorate['invoice_month'] . '-01'));

            $data[] = [
                'id' => $prorate['id'],
                'invoice_month' => $invoiceMonth,
                'customer_name' => $prorate['customer_name'] ?? '-',
                'package' => ($prorate['package'] ?? 'Paket Internet') . ' ' . ($prorate['package_price'] ? number_format($prorate['package_price'], 0, ',', '.') . 'Mbps' : '10Mbps'),
                'prorate_amount' => number_format($prorate['prorate_amount'], 0, ',', '.'),
                'description' => $prorate['description'] ?? '-',
                'created_at' => $prorate['created_at_formatted'] ?? date('d M Y H:i', strtotime($prorate['created_at'])),
                'updated_at' => $this->formatUpdatedAt($prorate['updated_at'])
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    private function formatUpdatedAt($datetime)
    {
        $updated = new \DateTime($datetime);
        $now = new \DateTime();
        $diff = $now->diff($updated);

        if ($diff->y > 0) {
            return $diff->y . ' tahun yang lalu';
        } elseif ($diff->m > 0) {
            return $diff->m . ' bulan yang lalu';
        } elseif ($diff->d > 0) {
            return $diff->d . ' hari yang lalu';
        } elseif ($diff->h > 0) {
            return $diff->h . ' jam yang lalu';
        } else {
            return 'setahun yang lalu';
        }
    }

    public function getCustomers()
    {
        $customers = $this->customerModel
            ->select('id_customers as id, nama_pelanggan as name, nomor_layanan as service_no, 
                     package_profiles.name as package, package_profiles.price')
            ->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left')
            ->where('status_tagihan !=', '')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $customers
        ]);
    }

    public function save()
    {
        $id = $this->request->getPost('id');

        $data = [
            'customer_id' => $this->request->getPost('customer_id'),
            'invoice_month' => $this->request->getPost('invoice_month'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'prorate_amount' => $this->request->getPost('prorate_amount'),
            'description' => $this->request->getPost('description')
        ];

        try {
            if ($id) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->prorateModel->update($id, $data);
                $message = 'Prorate updated successfully';
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->prorateModel->insert($data);
                $message = 'Prorate created successfully';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save prorate: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $this->prorateModel->delete($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Prorate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete prorate: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Auto-generate prorate untuk pelanggan baru yang dipasang antara tanggal 1-10
     * Method ini dipanggil via cron job atau manual
     */
    public function autoGenerate()
    {
        $db = \Config\Database::connect();

        // Get current month
        $currentMonth = date('Y-m');
        $currentYear = date('Y');
        $currentMonthNum = date('m');

        // Get customers yang dipasang di bulan ini antara tanggal 1-10
        $builder = $db->table('customers c');
        $builder->select('c.id_customers, c.nama_pelanggan, c.tgl_tempo, c.id_paket, 
                         pk.name as nama_paket, pk.price as harga, c.nomor_layanan');
        $builder->join('package_profiles pk', 'pk.id = c.id_paket', 'left');
        $builder->where('YEAR(c.tgl_tempo)', $currentYear);
        $builder->where('MONTH(c.tgl_tempo)', $currentMonthNum);
        $builder->where('DAY(c.tgl_tempo) <=', 10);
        $builder->where('c.status_tagihan !=', '');

        // Check if prorate already exists for this customer this month
        $builder->where("NOT EXISTS (
            SELECT 1 FROM prorate p 
            WHERE p.customer_id = c.id_customers 
            AND p.invoice_month = '{$currentMonth}'
        )", null, false);

        $customers = $builder->get()->getResultArray();

        $generated = 0;
        foreach ($customers as $customer) {
            // Parse installation date
            $installDate = new \DateTime($customer['tgl_tempo']);
            $startDay = $installDate->format('d');

            // Calculate end date (last day of month)
            $lastDay = date('t', strtotime($currentMonth . '-01'));
            $endDate = new \DateTime($currentYear . '-' . $currentMonthNum . '-' . $lastDay);

            // Calculate prorate days
            $prorateDays = $lastDay - $startDay + 1;

            // Calculate prorate amount
            $dailyRate = $customer['harga'] / $lastDay;
            $prorateAmount = $dailyRate * $prorateDays;

            // Format description
            $monthName = $installDate->format('F');
            $yearNum = $installDate->format('Y');
            $description = "Prorate dari tgl " . str_pad($startDay, 2, '0', STR_PAD_LEFT) .
                " sampai " . $lastDay . " bulan " . $monthName . " " . $yearNum;

            // Insert prorate
            $prorateData = [
                'customer_id' => $customer['id_customers'],
                'invoice_month' => $currentMonth,
                'start_date' => $installDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'prorate_amount' => round($prorateAmount, 0),
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->prorateModel->insert($prorateData);
            $generated++;
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Successfully generated {$generated} prorate records"
            ]);
        } else {
            echo "Successfully generated {$generated} prorate records\n";
        }
    }
}
