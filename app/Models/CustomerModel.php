<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id_customers';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nomor_layanan',
        'nama_pelanggan',
        'tipe_service',
        'cluster_address',
        'telepphone',
        'id_lokasi_server',
        'email',
        'status_tagihan',
        'id_paket',
        'no_ktp',
        'tgl_pasang',
        'tgl_tempo',
        'biaya_pasang',
        'province', // standardized field name for province
        'city',     // standardized field name for city
        'district', // standardized field name for district
        'village',  // standardized field name for village
        'customer_clustering_id', // ensure cluster can be updated
        'sales_id', // ensure sales can be updated
        'coordinat', // ensure coordinate can be updated
        'latitude', // parsed latitude from coordinat
        'longitude', // parsed longitude from coordinat
        'subscription_method',
        'additional_fee_id',
        'discount_id',
        'login',
        'is_new_customer',
        'address',
        'area_id', // Area/coverage area
        'odp_id', // ODP reference
        'pppoe_username',
        'pppoe_password',
        'pppoe_service',
        'pppoe_caller_id',
        'pppoe_comment',
        'pppoe_local_ip',
        'pppoe_remote_address',
        'ppp_secret',
        'pppoe_type_ip',
        'group_profile_id',
        'isolir_status',
        'isolir_date',
        'isolir_reason',
        'original_remote_address',
        'original_profile',
        'session_timeout',
        'idle_timeout',
        'status_installation',
        'status_layanan',
        'tgl_aktivasi',
        'payment_method',
        'pemegang_ikr',
        'branch_id',
        // 'pppoe_odp', // Commented out as this column might not exist yet
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getAll()
    {
        $builder = $this->db->table('customers');
        $builder->select('customers.*, paket.nama as paket_label, paket.bandwidth as paket_bandwidth'); // Added paket_bandwidth for customer list
        $builder->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left');
        $builder->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left');
        $query = $builder->get();
        return $query->getResult();
    }

    // Hitung jumlah pelanggan aktif dan tidak aktif
    public function countByStatus()
    {
        $builder = $this->db->table($this->table);
        $active = $builder->where('status_tagihan', 'Lunas')->countAllResults();

        $builder = $this->db->table($this->table);
        $inactive = $builder->where('status_tagihan', 'Belum Lunas')->countAllResults();

        // Count suspended customers (overdue and unpaid)
        $builder = $this->db->table($this->table);
        $suspended = $builder->where('status_tagihan !=', 'Lunas')
            ->where('tgl_tempo <', date('Y-m-d'))
            ->countAllResults();

        // Count new customers this month
        $newThisMonth = $this->countNewCustomersThisMonth();

        return [
            'active' => $active,
            'inactive' => $inactive,
            'suspended' => $suspended,
            'new_this_month' => $newThisMonth
        ];
    }

    /**
     * Count new customers for the current month
     * @return int Number of new customers this month
     */
    public function countNewCustomersThisMonth()
    {
        $builder = $this->db->table($this->table);
        $startOfMonth = date('Y-m-01 00:00:00');
        $endOfMonth = date('Y-m-t 23:59:59');

        return $builder->where('created_at >=', $startOfMonth)
            ->where('created_at <=', $endOfMonth)
            ->countAllResults();
    }

    /**
     * Count new customers by month for a specific year
     * @param int $year The year to get statistics for (default: current year)
     * @return array Monthly statistics with month name and count
     */
    public function getNewCustomersByMonth($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $monthlyStats = [];
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        for ($month = 1; $month <= 12; $month++) {
            $builder = $this->db->table($this->table);
            $startDate = sprintf('%d-%02d-01 00:00:00', $year, $month);
            $endDate = sprintf('%d-%02d-%02d 23:59:59', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));

            $count = $builder->where('created_at >=', $startDate)
                ->where('created_at <=', $endDate)
                ->countAllResults();

            $monthlyStats[] = [
                'month' => $month,
                'month_name' => $monthNames[$month],
                'year' => $year,
                'count' => $count,
                'period' => $monthNames[$month] . ' ' . $year
            ];
        }

        return $monthlyStats;
    }

    /**
     * Get new customers count for the last N months
     * @param int $months Number of months to look back (default: 12)
     * @return array Monthly statistics
     */
    public function getNewCustomersLastMonths($months = 12)
    {
        $stats = [];
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-{$i} months");

            $year = (int) $date->format('Y');
            $month = (int) $date->format('m');

            $builder = $this->db->table($this->table);
            $startDate = $date->format('Y-m-01 00:00:00');
            $endDate = $date->format('Y-m-t 23:59:59');

            $count = $builder->where('created_at >=', $startDate)
                ->where('created_at <=', $endDate)
                ->countAllResults();

            $stats[] = [
                'month' => $month,
                'month_name' => $monthNames[$month],
                'year' => $year,
                'count' => $count,
                'period' => $monthNames[$month] . ' ' . $year,
                'date' => $date->format('Y-m')
            ];
        }

        return $stats;
    }

    /**
     * Placeholder: Get total unpaid bills for a customer (replace with real logic)
     * @param int $customerId
     * @return int Total unpaid in rupiah
     */
    public function getUnpaidBillTotal($customerId)
    {
        // TODO: Replace this with real query to your invoice/tagihan table
        // Example: return (int) $this->db->table('invoices')->where(['customer_id'=>$customerId,'status'=>'unpaid'])->selectSum('amount')->get()->getRow('amount') ?? 0;
        return 0; // Always 0 for now (no invoice table found)
    }

    /**
     * Handle duplicate entry errors for paket assignments
     * Allow multiple customers to use the same paket
     */    public function insertWithDuplicateHandling($data)
    {
        // Now that we've removed the unique constraint on id_paket, 
        // we can just do a normal insert
        return $this->insert($data);
    }

    /**
     * Get available packages that can be assigned to customers
     * All packages should be available for multiple assignments
     */
    public function getAvailablePakets()
    {
        $db = \Config\Database::connect();
        return $db->table('package_profiles')
            ->select('id, name, bandwidth_profile, price')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Update customer with duplicate entry error handling
     * Allow multiple customers to use the same paket during updates
     */
    public function updateWithDuplicateHandling($id, $data)
    {
        try {
            // Debug: Log the incoming data
            log_message('debug', 'CustomerModel updateWithDuplicateHandling called with id: ' . $id . ', data: ' . json_encode($data));

            // Debug: Check if id_paket is in the data
            if (isset($data['id_paket'])) {
                log_message('debug', 'id_paket is present in data: ' . $data['id_paket']);
            } else {
                log_message('debug', 'id_paket is NOT present in data');
            }

            $result = $this->update($id, $data);

            // Debug: Log the result
            log_message('debug', 'CustomerModel update result: ' . ($result ? 'success' : 'failed'));

            return $result;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            log_message('error', 'CustomerModel update exception: ' . $errorMessage);

            // Check if it's a duplicate entry error for paket
            if (
                strpos($errorMessage, "Duplicate entry") !== false &&
                strpos($errorMessage, "paket") !== false
            ) {

                // This is a database constraint issue that needs to be fixed
                // Multiple customers should be able to use the same package
                log_message('error', 'CRITICAL: Unique constraint on paket field prevents multiple customers from using same package: ' . $errorMessage);

                // Try to provide more information about the constraint
                $db = \Config\Database::connect();
                try {
                    $query = $db->query("SHOW INDEXES FROM customers WHERE Key_name = 'paket'");
                    $result = $query->getResult();
                    if (!empty($result)) {
                        log_message('error', 'Unique constraint "paket" exists and needs to be removed. Run: php spark migrate');
                    }
                } catch (\Exception $indexCheckError) {
                    log_message('error', 'Could not check index status: ' . $indexCheckError->getMessage());
                }

                // Don't attempt to continue - this needs to be fixed at database level
                throw new \RuntimeException(
                    'Database constraint error: Multiple customers should be able to use the same package. ' .
                        'Please run "php spark migrate" to fix the database schema.'
                );
            }

            // Re-throw other errors
            throw $e;
        }
    }
}
