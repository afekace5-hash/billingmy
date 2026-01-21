<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\CustomerModel;
use App\Models\GroupProfileModel;
use App\Models\PackageProfileModel;


class Customer extends ResourceController
{
    protected $cust;
    protected $db;

    public function __construct()
    {
        $this->cust = new CustomerModel();
        $this->db = \Config\Database::connect();
    }



    public function index()
    {
        // Hanya ambil data yang benar-benar diperlukan untuk initial load
        // DataTable akan load data via AJAX, jadi tidak perlu getAll() di sini
        $countStatus = $this->cust->countByStatus();

        // Fetch filter data dengan optimasi (cache jika perlu)
        $filterData = $this->getFilterDataOptimized();

        return view('customer/index', compact('countStatus', 'filterData'));
    }

    /**
     * Get all filter data for dropdowns using optimized SQL queries
     */
    private function getFilterDataOptimized()
    {
        $data = [];

        // Use single query for multiple related data to reduce database calls
        $data['packages'] = $this->getPackageOptionsOptimized();
        $data['servers'] = $this->getServerOptionsOptimized();

        // These are expensive - only load basic data
        $data['districts'] = $this->getDistrictOptionsOptimized();
        $data['villages'] = $this->getVillageOptionsOptimized();
        $data['clusters'] = $this->getClusterOptionsOptimized();

        // Static options - no DB call needed
        $data['statuses'] = [
            ['id' => 'active', 'name' => 'Aktif', 'label' => 'Aktif'],
            ['id' => 'inactive', 'name' => 'Tidak Aktif', 'label' => 'Tidak Aktif'],
            ['id' => 'isolated', 'name' => 'Isolir', 'label' => 'Isolir'],
            ['id' => 'suspend', 'name' => 'Suspend', 'label' => 'Suspend (Overdue)']
        ];

        $data['newCustomers'] = [
            ['id' => 'yes', 'name' => 'Ya', 'label' => 'Ya'],
            ['id' => 'no', 'name' => 'Tidak', 'label' => 'Tidak']
        ];

        return $data;
    }

    /**
     * Get package options with limit for better performance
     */
    private function getPackageOptionsOptimized()
    {
        $builder = $this->db->table('package_profiles');
        $builder->select('id, name, bandwidth_profile, price');
        $builder->orderBy('name', 'ASC');
        $builder->limit(100); // Limit untuk performance
        $packages = $builder->get()->getResult();

        $options = [];
        foreach ($packages as $paket) {
            $options[] = [
                'id' => $paket->id,
                'name' => $paket->name . ' | ' . $paket->bandwidth_profile,
                'label' => $paket->name . ' | ' . $paket->bandwidth_profile . ' | Rp ' . number_format($paket->price, 0, ',', '.'),
                'price' => (int)$paket->price, // Ensure integer type
            ];
        }

        // Log for debugging
        log_message('info', 'Package options returned: ' . json_encode($options));

        return $options;
    }

    /**
     * Get server options with limit for better performance
     */
    private function getServerOptionsOptimized()
    {
        $builder = $this->db->table('lokasi_server');
        $builder->select('id_lokasi as id, name');
        $builder->orderBy('name', 'ASC');
        $builder->limit(50); // Limit untuk performance
        $servers = $builder->get()->getResult();

        $options = [];
        foreach ($servers as $server) {
            $options[] = [
                'id' => $server->id,
                'name' => $server->name,
                'label' => $server->name
            ];
        }

        return $options;
    }
    /**
     * Get district options with API region names
     */
    private function getDistrictOptionsOptimized()
    {
        $builder = $this->db->table('customers c');
        $builder->select('c.district as id');
        $builder->where('c.district IS NOT NULL');
        $builder->where('c.district !=', '');
        $builder->groupBy('c.district');
        $builder->orderBy('c.district', 'ASC');
        $builder->limit(100); // Limit untuk performance
        $districts = $builder->get()->getResult();

        $options = [];
        foreach ($districts as $district) {
            // Simple formatting without API call for now
            $districtName = ctype_digit($district->id) ? "Kecamatan " . $district->id : $district->id;

            $options[] = [
                'id' => $district->id,
                'name' => $districtName,
                'label' => $districtName
            ];
        }

        return $options;
    }
    /**
     * Get village options with API region names  
     */
    private function getVillageOptionsOptimized()
    {
        $builder = $this->db->table('customers c');
        $builder->select('c.village as id');
        $builder->where('c.village IS NOT NULL');
        $builder->where('c.village !=', '');
        $builder->groupBy('c.village');
        $builder->orderBy('c.village', 'ASC');
        $builder->limit(100); // Limit untuk performance
        $villages = $builder->get()->getResult();

        $options = [];
        foreach ($villages as $village) {
            // Simple formatting without API call for now
            $villageName = ctype_digit($village->id) ? "Desa/Kel. " . $village->id : $village->id;

            $options[] = [
                'id' => $village->id,
                'name' => $villageName,
                'label' => $villageName
            ];
        }

        return $options;
    }

    /**
     * Get cluster options with optimized query
     */
    private function getClusterOptionsOptimized()
    {
        $builder = $this->db->table('clustering');
        $builder->select('id_clustering, name');
        $builder->orderBy('name', 'ASC');
        $builder->limit(50); // Limit untuk performance
        $clusters = $builder->get()->getResult();

        $options = [];
        foreach ($clusters as $cluster) {
            $options[] = [
                'id' => $cluster->id_clustering,
                'name' => $cluster->name,
                'label' => $cluster->name
            ];
        }

        return $options;
    }

    /**
     * Get package options using manual SQL
     */
    private function getPackageOptions()
    {
        $builder = $this->db->table('package_profiles');
        $builder->select('id, name, bandwidth_profile, price');
        $builder->orderBy('name', 'ASC');
        $packages = $builder->get()->getResult();

        $options = [];
        foreach ($packages as $paket) {
            $options[] = [
                'id' => $paket->id,
                'name' => $paket->name . ' | ' . $paket->bandwidth_profile,
                'label' => $paket->name . ' | ' . $paket->bandwidth_profile . ' | Rp ' . number_format($paket->price, 0, ',', '.'),
            ];
        }

        return $options;
    }

    /**
     * Get server options using manual SQL
     */
    private function getServerOptions()
    {
        $builder = $this->db->table('lokasi_server');
        $builder->select('id_lokasi as id, name');
        $builder->orderBy('name', 'ASC');
        $servers = $builder->get()->getResult();

        $options = [];
        foreach ($servers as $server) {
            $options[] = [
                'id' => $server->id,
                'name' => $server->name,
                'label' => $server->name
            ];
        }

        return $options;
    }

    /**
     * Get district options using manual SQL
     */
    private function getDistrictOptionsData()
    {
        $builder = $this->db->table('customers c');
        $builder->select('c.district as id, c.district as name, c.city');
        $builder->where('c.district IS NOT NULL');
        $builder->where('c.district !=', '');
        $builder->groupBy('c.district');
        $builder->orderBy('c.district', 'ASC');
        $districts = $builder->get()->getResult();

        $options = [];
        $processedDistricts = [];

        foreach ($districts as $district) {
            $districtId = $district->id;

            if (isset($processedDistricts[$districtId])) {
                $displayName = $processedDistricts[$districtId];
            } else {
                // Just use the district ID or name as-is
                $displayName = $district->name ?: $districtId;
                $processedDistricts[$districtId] = $displayName;
            }

            $options[] = [
                'id' => $districtId,
                'name' => $displayName,
                'label' => $displayName
            ];
        }

        return $options;
    }

    /**
     * Get village options using manual SQL
     */
    private function getVillageOptionsData()
    {
        $builder = $this->db->table('customers c');
        $builder->select('c.village as id, c.village as name, c.district');
        $builder->where('c.village IS NOT NULL');
        $builder->where('c.village !=', '');
        $builder->groupBy('c.village');
        $builder->orderBy('c.village', 'ASC');
        $villages = $builder->get()->getResult();

        $options = [];
        $processedVillages = [];

        foreach ($villages as $village) {
            $villageId = $village->id;

            if (isset($processedVillages[$villageId])) {
                $displayName = $processedVillages[$villageId];
            } else {
                // Just use the village ID or name as-is
                $displayName = $village->name ?: $villageId;
                $processedVillages[$villageId] = $displayName;
            }

            $options[] = [
                'id' => $villageId,
                'name' => $displayName,
                'label' => $displayName
            ];
        }

        return $options;
    }

    /**
     * Get cluster options using manual SQL
     */
    private function getClusterOptions()
    {
        $builder = $this->db->table('clustering');
        $builder->select('id_clustering, name');
        $builder->orderBy('name', 'ASC');
        $clusters = $builder->get()->getResult();

        $options = [];
        foreach ($clusters as $cluster) {
            $options[] = [
                'id' => $cluster->id_clustering,
                'name' => $cluster->name,
                'label' => $cluster->name
            ];
        }

        return $options;
    }
    public function show($id = null)
    {
        if (!$id) {
            return redirect()->to(base_url('customers'))->with('error', 'ID pelanggan tidak ditemukan');
        }

        // Optimized single query with JOIN to get customer and package data
        $builder = $this->db->table('customers c');
        $builder->select('c.*, p.name as paket_nama, p.bandwidth_profile as paket_bandwidth, p.price as paket_harga');
        $builder->join('package_profiles p', 'p.id = c.id_paket', 'left');
        $builder->where('c.id_customers', $id);
        $customer = $builder->get()->getRow();

        if (!$customer) {
            return redirect()->to(base_url('customers'))->with('error', 'Data pelanggan tidak ditemukan');
        }

        // Set paket info if available
        if ($customer->paket_nama) {
            $customer->paket_label = $customer->paket_nama . ' (' . $customer->paket_bandwidth . ' Mbps)';
        } else {
            $customer->paket_label = '-';
        }

        // Get location names: only show valid names, never fallback to ID or ctype_digit
        try {
            $customer->province_name = (!empty($customer->province) && !ctype_digit($customer->province)) ? $customer->province : '-';
            $customer->city_name = (!empty($customer->city) && !ctype_digit($customer->city)) ? $customer->city : '-';
            $customer->district_name = (!empty($customer->district) && !ctype_digit($customer->district)) ? $customer->district : '-';
            $customer->village_name = (!empty($customer->village) && !ctype_digit($customer->village)) ? $customer->village : '-';
        } catch (\Exception $e) {
            log_message('error', 'Error formatting location names: ' . $e->getMessage());
            $customer->province_name = '-';
            $customer->city_name = '-';
            $customer->district_name = '-';
            $customer->village_name = '-';
        }

        // Get unpaid bill total with optimized query
        $unpaidTotal = $this->getUnpaidBillTotalOptimized($customer->id_customers);

        // Pass customer_id to view for invoice history DataTable
        return view('customer/detail', [
            'customer' => $customer,
            'unpaidTotal' => $unpaidTotal,
            'customer_id' => $customer->id_customers,
        ]);
    }

    /**
     * Optimized method to get unpaid bill total
     */
    private function getUnpaidBillTotalOptimized($customerId)
    {
        try {
            $builder = $this->db->table('customer_invoices');
            $builder->selectSum('bill', 'total_unpaid');
            $builder->where('customer_id', $customerId);
            $builder->where('status !=', 'paid');
            $result = $builder->get()->getRow();

            return $result->total_unpaid ?? 0;
        } catch (\Exception $e) {
            log_message('error', 'Error calculating unpaid total: ' . $e->getMessage());
            return 0;
        }
    }

    public function new()
    {
        $custModel = new CustomerModel();

        // Load lokasi server data for the dropdown
        $lokasiServerModel = new \App\Models\ServerLocationModel();
        $lokasiServers = $lokasiServerModel->findAll();

        // Get default regional settings from application settings
        $defaultRegional = $this->getDefaultRegionalSettings();

        return view('customer/create', [
            'lokasiServers' => $lokasiServers,
            'defaultRegional' => $defaultRegional
        ]);
    }

    /**
     * Parse coordinate string to latitude and longitude
     * @param array $data Reference to data array to modify
     */
    private function parseCoordinates(&$data)
    {
        if (!empty($data['coordinat'])) {
            $coordString = trim($data['coordinat']);
            log_message('info', 'Parsing coordinate: ' . $coordString);

            if (strpos($coordString, ',') !== false) {
                $coords = explode(',', $coordString);
                if (count($coords) == 2) {
                    $lat = floatval(trim($coords[0]));
                    $lng = floatval(trim($coords[1]));

                    // Validate coordinates (basic check for valid lat/lng ranges)
                    if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                        $data['latitude'] = $lat;
                        $data['longitude'] = $lng;
                        log_message('info', 'Coordinate parsed successfully: lat=' . $lat . ', lng=' . $lng);
                        return true;
                    } else {
                        log_message('warning', 'Invalid coordinate ranges: lat=' . $lat . ', lng=' . $lng);
                        $data['latitude'] = null;
                        $data['longitude'] = null;
                    }
                } else {
                    log_message('warning', 'Invalid coordinate format: expected 2 parts, got ' . count($coords));
                }
            } else {
                log_message('warning', 'Invalid coordinate format: no comma found in ' . $coordString);
            }
        } else {
            // If coordinat is empty, clear latitude and longitude
            if (isset($data['coordinat']) && empty($data['coordinat'])) {
                $data['latitude'] = null;
                $data['longitude'] = null;
                log_message('info', 'Coordinate cleared (empty coordinat field)');
            }
        }
        return false;
    }

    public function create()
    {

        $data = $this->request->getPost();

        // Debug: Log all received data to check tgl_pasang
        log_message('debug', 'Customer create POST data: ' . json_encode($data));

        // Specific debug for tgl_pasang
        log_message('debug', 'tgl_pasang raw value: ' . ($data['tgl_pasang'] ?? 'NOT_SET'));

        // Debug: Log received data to check PPPoE fields
        log_message('debug', 'Customer create POST data: ' . json_encode([
            'pppoe_username' => $data['pppoe_username'] ?? 'not_set',
            'pppoe_password' => !empty($data['pppoe_password']) ? 'set' : 'not_set',
            'pppoe_local_ip' => $data['pppoe_local_ip'] ?? 'not_set',
            'pppoe_remote_address' => $data['pppoe_remote_address'] ?? 'not_set',
            'id_lokasi_server' => $data['id_lokasi_server'] ?? 'not_set',
            'ppp_secret' => $data['ppp_secret'] ?? 'not_set'
        ]));

        // Map status_tagihan from 'enable'/'disable' to ENUM values
        if (isset($data['status_tagihan'])) {
            $data['status_tagihan'] = $data['status_tagihan'] === 'enable' ? 'Lunas' : 'Belum Lunas';
        }
        // Generate nomor_layanan automatically if not provided (local helper, not model method)
        if (empty($data['nomor_layanan'])) {
            $data['nomor_layanan'] = $this->generateNomorLayanan();
        }
        // Optionally, remove any user-supplied service_number
        unset($data['service_number']);

        // --- TGL_PASANG HANDLING ---
        // Format tgl_pasang to Y-m-d if needed (same as update method)
        if (!empty($data['tgl_pasang'])) {
            log_message('debug', 'Processing tgl_pasang: ' . $data['tgl_pasang']);

            $dateObj = \DateTime::createFromFormat('d/m/Y', $data['tgl_pasang'])
                ?: \DateTime::createFromFormat('Y-m-d', $data['tgl_pasang'])
                ?: \DateTime::createFromFormat('d-m-Y', $data['tgl_pasang'])
                ?: \DateTime::createFromFormat('Y/m/d', $data['tgl_pasang']);
            if ($dateObj) {
                $data['tgl_pasang'] = $dateObj->format('Y-m-d');
                log_message('debug', 'tgl_pasang converted to: ' . $data['tgl_pasang']);
            } else {
                log_message('error', 'Failed to parse tgl_pasang: ' . $data['tgl_pasang']);
            }
        } else {
            log_message('debug', 'tgl_pasang is empty or not set');
        }

        // --- TGL_TEMPO HANDLING ---
        if (!empty($data['tgl_tempo'])) {
            $tempo = $data['tgl_tempo'];
            // If numeric, treat as day of month
            if (is_numeric($tempo)) {
                // Use tgl_pasang as base if available, else current month/year
                $baseDate = !empty($data['tgl_pasang']) ? $data['tgl_pasang'] : date('d/m/Y');
                // Try to parse tgl_pasang in various formats
                $dateObj = \DateTime::createFromFormat('d/m/Y', $baseDate)
                    ?: \DateTime::createFromFormat('Y-m-d', $baseDate)
                    ?: \DateTime::createFromFormat('d-m-Y', $baseDate)
                    ?: \DateTime::createFromFormat('Y/m/d', $baseDate);
                if ($dateObj) {
                    $year = $dateObj->format('Y');
                    $month = $dateObj->format('m');
                    $day = str_pad($tempo, 2, '0', STR_PAD_LEFT);
                    // If the day exceeds the number of days in the month, clamp to last day
                    $lastDay = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    if ((int)$day > $lastDay) $day = $lastDay;
                    $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
                } else {
                    // Fallback: just use current month/year
                    $year = date('Y');
                    $month = date('m');
                    $day = str_pad($tempo, 2, '0', STR_PAD_LEFT);
                    $lastDay = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    if ((int)$day > $lastDay) $day = $lastDay;
                    $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
                }
            } else {
                // Try to parse as date string in various formats
                $dateObj = \DateTime::createFromFormat('Y-m-d', $tempo)
                    ?: \DateTime::createFromFormat('d/m/Y', $tempo)
                    ?: \DateTime::createFromFormat('d-m-Y', $tempo)
                    ?: \DateTime::createFromFormat('Y/m/d', $tempo);
                if ($dateObj) {
                    $data['tgl_tempo'] = $dateObj->format('Y-m-d');
                } else {
                    // If invalid, fallback to last day of current month
                    $year = date('Y');
                    $month = date('m');
                    $day = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
                }
            }
        } else {
            // If empty, fallback to last day of the month from tgl_pasang if available, else current month
            if (!empty($data['tgl_pasang'])) {
                $dateObj = \DateTime::createFromFormat('d/m/Y', $data['tgl_pasang'])
                    ?: \DateTime::createFromFormat('Y-m-d', $data['tgl_pasang'])
                    ?: \DateTime::createFromFormat('d-m-Y', $data['tgl_pasang'])
                    ?: \DateTime::createFromFormat('Y/m/d', $data['tgl_pasang']);
                if ($dateObj) {
                    $year = $dateObj->format('Y');
                    $month = $dateObj->format('m');
                } else {
                    $year = date('Y');
                    $month = date('m');
                }
            } else {
                $year = date('Y');
                $month = date('m');
            }
            $day = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
            $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
        }

        // --- COORDINATE HANDLING ---
        $this->parseCoordinates($data);

        try {
            log_message('debug', 'Starting customer creation with data: ' . json_encode([
                'nama_pelanggan' => $data['nama_pelanggan'] ?? 'not_set',
                'pppoe_username' => $data['pppoe_username'] ?? 'not_set',
                'has_pppoe_password' => !empty($data['pppoe_password']),
                'has_coordinates' => isset($data['latitude']) && isset($data['longitude'])
            ]));

            if ($this->cust->insertWithDuplicateHandling($data)) {
                $customerId = $this->cust->getInsertID();
                log_message('info', 'Customer created successfully with ID: ' . $customerId);

                // Save biaya tambahan if provided
                if (!empty($data['additional_fee_id'])) {
                    $customerBiayaTambahanModel = model('CustomerBiayaTambahanModel');
                    $biayaIds = is_array($data['additional_fee_id']) ? $data['additional_fee_id'] : [$data['additional_fee_id']];
                    $customerBiayaTambahanModel->addBiayaTambahanToCustomer($customerId, $biayaIds);
                    log_message('info', 'Biaya tambahan saved for customer: ' . $customerId);
                }

                // Jika customer baru dibuat dengan PPPoE credentials, akan ditangani oleh AUTO PPPOE SYNC
                if (!empty($data['pppoe_username']) && !empty($data['pppoe_password'])) {
                    log_message('info', 'PPPoE credentials provided for customer: ' . $customerId . ', will be handled by AUTO PPPOE SYNC');
                }

                $successMessage = 'Pelanggan berhasil ditambahkan';

                // AUTO GENERATE PRORATA INVOICE - If customer is marked as new customer
                if (isset($data['is_new_customer']) && $data['is_new_customer'] == 1) {
                    $prorataResult = $this->generateProrataInvoiceForCustomer($customerId, $data);
                    if ($prorataResult['success']) {
                        $successMessage .= ' dan tagihan prorata berhasil dibuat';
                        log_message('info', 'Prorata invoice generated for new customer: ' . $customerId);
                    } else {
                        log_message('warning', 'Failed to generate prorata invoice for customer ' . $customerId . ': ' . $prorataResult['message']);
                        $successMessage .= ', namun tagihan prorata gagal dibuat: ' . $prorataResult['message'];
                    }
                }

                // AUTO PPPOE SYNC - Made optional to avoid blocking slow VPN connections
                // Customer will be saved first, secret creation can be done later if needed
                if (($data['ppp_secret'] ?? '') === 'buat_secret_baru' && !empty($data['pppoe_username'])) {
                    try {
                        // Set shorter timeout to fail fast if VPN is slow
                        ini_set('max_execution_time', '60'); // Max 60 seconds for PPPoE creation

                        $pppoeResult = $this->handlePppoeSync($customerId, $data, 'create');
                        if ($pppoeResult['success']) {
                            $successMessage .= ' dan berhasil disinkronkan ke MikroTik server';
                        } else {
                            log_message('warning', 'Auto PPPoE sync failed: ' . $pppoeResult['message']);
                            $successMessage .= ' (PPPoE secret bisa dibuat manual nanti)';
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'PPPoE creation timeout/error: ' . $e->getMessage());
                        $successMessage .= ' (PPPoE secret bisa dibuat manual nanti via menu PPPoE)';
                    }
                } else {
                    log_message('info', 'PPPoE secret creation skipped - will be created manually');
                }

                // Remove duplicate PPPoE creation - already handled above
                // This MikroTikAutoService was causing double processing

                // SEND WHATSAPP NOTIFICATION
                // Get complete customer data including ID for WhatsApp message
                $dataWithPackage = $data;
                $dataWithPackage['id_customers'] = $customerId; // Add customer ID

                // Get package information for WhatsApp message
                if (!empty($data['id_paket'])) {
                    $package = $this->db->table('package_profiles')->where('id', $data['id_paket'])->get()->getRow();
                    if ($package) {
                        $dataWithPackage['package_name'] = $package->name ?? $package->name_package ?? 'Paket Internet';
                        $dataWithPackage['tarif'] = $package->price ?? 0;
                    }
                }

                // Log the data being sent to WhatsApp for debugging
                log_message('info', 'Preparing WhatsApp notification for customer: ' . $customerId);
                log_message('info', 'WhatsApp notification data: ' . json_encode([
                    'customer_id' => $customerId,
                    'nama_pelanggan' => $dataWithPackage['nama_pelanggan'] ?? 'N/A',
                    'phone_fields' => [
                        'telepphone' => $dataWithPackage['telepphone'] ?? null,
                        'no_tlp' => $dataWithPackage['no_tlp'] ?? null,
                        'no_hp' => $dataWithPackage['no_hp'] ?? null,
                    ],
                    'package_name' => $dataWithPackage['package_name'] ?? 'N/A',
                    'nomor_layanan' => $dataWithPackage['nomor_layanan'] ?? 'N/A'
                ]));

                $whatsappResult = $this->sendNewCustomerNotification($dataWithPackage);

                log_message('info', 'WhatsApp notification result: ' . json_encode([
                    'success' => $whatsappResult['success'],
                    'message' => $whatsappResult['message'],
                    'customer_id' => $customerId
                ]));

                if ($whatsappResult['success']) {
                    log_message('info', 'WhatsApp notification sent successfully to customer: ' . $customerId);
                    $successMessage .= ' dan notifikasi WhatsApp terkirim';
                } else {
                    log_message('warning', 'WhatsApp notification failed for customer: ' . $customerId . ' - ' . $whatsappResult['message']);
                    // Don't add to success message since it's not critical
                }

                return redirect()->to('customers')->with('success', $successMessage);
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pelanggan');
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Handle duplicate entry specifically for paket
            if (
                strpos($errorMessage, "Duplicate entry") !== false &&
                strpos($errorMessage, "paket") !== false
            ) {

                log_message('info', 'Paket duplicate entry handled gracefully: ' . $errorMessage);
                return redirect()->back()->withInput()->with('info', 'Pelanggan berhasil ditambahkan. Paket dapat digunakan bersama dengan pelanggan lain.');
            }

            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pelanggan: ' . $errorMessage);
        }
    }

    /**
     * Generate a random nomor_layanan (service number) for a customer.
     * This mimics the helper in the view.
     * @return string
     */
    protected function generateNomorLayanan($seed = null)
    {
        $prefix = '141437';
        if ($seed) {
            mt_srand(crc32($seed));
        }
        $random = '';
        for ($i = 0; $i < 6; $i++) {
            $random .= mt_rand(0, 9);
        }
        if ($seed) mt_srand(); // reset
        return $prefix . $random;
    }

    public function edit($id = null)
    {
        // Pastikan id_customer yang digunakan
        $customer = $this->cust->where('id_customers', $id)->first();
        if (empty($customer)) {
            return redirect()->to('customers')->with('error', 'Data pelanggan tidak ditemukan');
        }

        // Ambil semua cluster untuk select
        $clusters = $this->db->table('clustering')->orderBy('name', 'ASC')->get()->getResult();

        // Ambil data paket untuk server-side fallback
        $paketModel = model('PackageProfileModel');
        $paketList = $paketModel->findAll();
        $paketOptions = [];
        foreach ($paketList as $paket) {
            $paketOptions[] = [
                'id' => $paket['id'],
                'nama' => $paket['name'],
                'bandwidth' => $paket['bandwidth_profile'],
                'harga' => $paket['price'],
                'label' => $paket['name'] . ' | ' . $paket['bandwidth_profile'] . ' | Rp ' . number_format($paket['price'], 0, ',', '.'),
            ];
        }

        $data = [
            'customer' => $customer,
            'id_lokasi_server' => $this->db->table('lokasi_server')->get()->getResult(),
            'clusters' => $clusters,
            'paketOptions' => $paketOptions, // Server-side paket data
        ];
        return view('customer/edit', $data);
    }

    public function update($id = null)
    {
        $data = $this->request->getPost();

        // Get existing customer data to compare PPPoE changes
        $existingCustomer = $this->cust->where('id_customers', $id)->first();

        // Debug: Log existing customer package
        log_message('debug', 'Existing customer id_paket: ' . ($existingCustomer['id_paket'] ?? 'not_set'));

        // Ensure package field is properly handled
        if (isset($data['id_paket']) && !empty($data['id_paket'])) {
            // Convert to integer to ensure proper type
            $data['id_paket'] = (int)$data['id_paket'];
            log_message('debug', 'Package field converted to integer: ' . $data['id_paket']);
        } else {
            log_message('debug', 'Package field is empty or not set');
        }

        // Map status_tagihan from 'enable'/'disable' to ENUM values
        if (isset($data['status_tagihan'])) {
            $data['status_tagihan'] = $data['status_tagihan'] === 'enable' ? 'Lunas' : 'Belum Lunas';
        }
        // Format tgl_pasang to Y-m-d if needed
        if (!empty($data['tgl_pasang'])) {
            $dateObj = \DateTime::createFromFormat('d/m/Y', $data['tgl_pasang'])
                ?: \DateTime::createFromFormat('Y-m-d', $data['tgl_pasang'])
                ?: \DateTime::createFromFormat('d-m-Y', $data['tgl_pasang'])
                ?: \DateTime::createFromFormat('Y/m/d', $data['tgl_pasang']);
            if ($dateObj) {
                $data['tgl_pasang'] = $dateObj->format('Y-m-d');
            }
        }

        // --- TGL_TEMPO HANDLING ---
        if (!empty($data['tgl_tempo'])) {
            $tempo = $data['tgl_tempo'];
            // If numeric, treat as day of month
            if (is_numeric($tempo)) {
                // Use tgl_pasang as base if available, else current month/year
                $baseDate = !empty($data['tgl_pasang']) ? $data['tgl_pasang'] : date('d/m/Y');
                // Try to parse tgl_pasang in various formats
                $dateObj = \DateTime::createFromFormat('d/m/Y', $baseDate)
                    ?: \DateTime::createFromFormat('Y-m-d', $baseDate)
                    ?: \DateTime::createFromFormat('d-m-Y', $baseDate)
                    ?: \DateTime::createFromFormat('Y/m/d', $baseDate);
                if ($dateObj) {
                    $year = $dateObj->format('Y');
                    $month = $dateObj->format('m');
                    $day = str_pad($tempo, 2, '0', STR_PAD_LEFT);
                    // If the day exceeds the number of days in the month, clamp to last day
                    $lastDay = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    if ((int)$day > $lastDay) $day = $lastDay;
                    $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
                } else {
                    // Fallback: just use current month/year
                    $year = date('Y');
                    $month = date('m');
                    $day = str_pad($tempo, 2, '0', STR_PAD_LEFT);
                    $lastDay = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    if ((int)$day > $lastDay) $day = $lastDay;
                    $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
                }
            } else {
                // Try to parse as date string in various formats
                $dateObj = \DateTime::createFromFormat('Y-m-d', $tempo)
                    ?: \DateTime::createFromFormat('d/m/Y', $tempo)
                    ?: \DateTime::createFromFormat('d-m-Y', $tempo)
                    ?: \DateTime::createFromFormat('Y/m/d', $tempo);
                if ($dateObj) {
                    $data['tgl_tempo'] = $dateObj->format('Y-m-d');
                } else {
                    // If invalid, fallback to last day of current month
                    $year = date('Y');
                    $month = date('m');
                    $day = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                    $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
                }
            }
        } else {
            // If empty, fallback to last day of the month from tgl_pasang if available, else current month
            if (!empty($data['tgl_pasang'])) {
                $dateObj = \DateTime::createFromFormat('d/m/Y', $data['tgl_pasang'])
                    ?: \DateTime::createFromFormat('Y-m-d', $data['tgl_pasang'])
                    ?: \DateTime::createFromFormat('d-m-Y', $data['tgl_pasang'])
                    ?: \DateTime::createFromFormat('Y/m/d', $data['tgl_pasang']);
                if ($dateObj) {
                    $year = $dateObj->format('Y');
                    $month = $dateObj->format('m');
                } else {
                    $year = date('Y');
                    $month = date('m');
                }
            } else {
                $year = date('Y');
                $month = date('m');
            }
            $day = \cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
            $data['tgl_tempo'] = $year . '-' . $month . '-' . $day;
        }

        // --- COORDINATE HANDLING ---
        $this->parseCoordinates($data);

        try {
            $save = $this->cust->updateWithDuplicateHandling($id, $data);

            if (!$save) {
                $errors = $this->cust->errors();
                log_message('error', 'Customer update validation failed: ' . json_encode($errors));
                return redirect()->back()->withInput()->with('errors', $errors);
            } else {

                // AUTO PPPOE SYNC - Temporarily disabled to prevent timeout
                // TODO: Re-enable after optimizing MikroTik API calls
                /*
                $pppoeResult = $this->handlePppoeSync($id, $data, 'update');
                if (!$pppoeResult['success']) {
                    log_message('warning', 'Auto PPPoE sync failed during customer update: ' . $pppoeResult['message']);
                    // Don't prevent customer update, just log the warning
                }
                */

                return redirect()->to(base_url('customers'))->with('success', 'Pelanggan berhasil diupdate');
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            log_message('error', 'Customer update exception: ' . $errorMessage);

            // Handle duplicate entry specifically for paket
            if (
                strpos($errorMessage, "Duplicate entry") !== false &&
                strpos($errorMessage, "paket") !== false
            ) {

                log_message('info', 'Paket duplicate entry detected: ' . $errorMessage);

                // This indicates a database constraint issue that needs to be fixed
                // Multiple customers should be able to use the same package
                return redirect()->back()->withInput()->with(
                    'error',
                    'Terjadi masalah dengan constraint database. Paket seharusnya bisa digunakan oleh beberapa pelanggan. ' .
                        'Silakan hubungi administrator untuk menjalankan migrasi database: php spark migrate'
                );
            }

            // Handle other duplicate constraints
            if (strpos($errorMessage, "Duplicate entry") !== false) {
                // Extract field name from error message
                preg_match("/for key '([^']+)'/", $errorMessage, $matches);
                $fieldName = isset($matches[1]) ? $matches[1] : 'unknown';

                return redirect()->back()->withInput()->with(
                    'error',
                    "Data duplikat ditemukan pada field: {$fieldName}. Silakan periksa data yang dimasukkan."
                );
            }

            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate pelanggan: ' . $errorMessage);
        }
    }
    public function delete($id = null)
    {
        try {
            // Get customer data before deletion
            $customer = $this->cust->where('id_customers', $id)->first();

            if (!$customer) {
                // Check if this is AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Customer tidak ditemukan',
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash()
                    ]);
                }
                return redirect()->to(site_url('customers'))->with('error', 'Customer tidak ditemukan');
            }

            $messages = [];
            $customerName = $customer['nama_pelanggan'] ?? "ID: {$id}";

            // Skip MikroTik deletion - just delete from database
            // User requested: "kalo hapus gausah kirim kemikrotik"

            // Delete customer from database
            if ($this->cust->where('id_customers', $id)->delete()) {
                $successMessage = 'Pelanggan berhasil dihapus';
                if (!empty($messages)) {
                    $successMessage .= '. ' . implode(', ', $messages);
                }

                log_message('info', 'Customer deleted successfully: ' . $customerName . ' (ID: ' . $id . ')');

                // Check if this is AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $successMessage,
                        'title' => 'Berhasil',
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash()
                    ]);
                }

                return redirect()->to(site_url('customers'))->with('success', $successMessage);
            } else {
                $errorMessage = 'Gagal menghapus pelanggan dari database';
                log_message('error', 'Failed to delete customer from database: ' . $customerName . ' (ID: ' . $id . ')');

                // Check if this is AJAX request
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $errorMessage,
                        'title' => 'Error',
                        'csrfName' => csrf_token(),
                        'csrfHash' => csrf_hash()
                    ]);
                }

                return redirect()->to(site_url('customers'))->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage();
            log_message('error', 'Error in customer delete: ' . $e->getMessage());

            // Check if this is AJAX request
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMessage,
                    'title' => 'Error',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash()
                ]);
            }

            return redirect()->to(site_url('customers'))->with('error', $errorMessage);
        }
    }

    /**
     * Delete selected customers (bulk delete)
     * POST: /customers/delete with array of id
     */
    public function deleteSelected()
    {
        try {
            $ids = $this->request->getPost('id');

            // Validate input
            if (empty($ids) || !is_array($ids)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak ada data yang dipilih untuk dihapus',
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash()
                ]);
            }

            $deletedCount = 0;
            $errorMessages = [];
            $successMessages = [];

            foreach ($ids as $id) {
                try {
                    // Get customer data before deletion
                    $customer = $this->cust->where('id_customers', $id)->first();

                    if (!$customer) {
                        $errorMessages[] = "Customer dengan ID {$id} tidak ditemukan";
                        continue;
                    }

                    $customerName = $customer['nama_pelanggan'] ?? "ID: {$id}";

                    // Try to delete PPPoE secret from MikroTik if customer has PPPoE account
                    if (!empty($customer['pppoe_username'])) {
                        try {
                            $deleteResult = $this->handlePppoeSync($id, $customer, 'delete');
                            if ($deleteResult['success']) {
                                log_message('info', 'PPPoE secret deleted successfully for customer: ' . $id);
                            } else {
                                log_message('warning', 'PPPoE secret deletion failed but continuing with customer deletion: ' . $deleteResult['message']);
                            }
                        } catch (\Exception $e) {
                            log_message('error', 'Exception during PPPoE secret deletion for customer ' . $id . ': ' . $e->getMessage());
                        }
                    }

                    // Delete customer from database
                    if ($this->cust->delete($id)) {
                        $deletedCount++;
                        $successMessages[] = $customerName;
                        log_message('info', 'Customer deleted successfully: ' . $customerName . ' (ID: ' . $id . ')');
                    } else {
                        $errorMessages[] = "Gagal menghapus {$customerName} dari database";
                        log_message('error', 'Failed to delete customer from database: ' . $customerName . ' (ID: ' . $id . ')');
                    }
                } catch (\Exception $e) {
                    $errorMessages[] = "Error menghapus customer ID {$id}: " . $e->getMessage();
                    log_message('error', 'Error deleting customer ID ' . $id . ': ' . $e->getMessage());
                }
            }

            // Prepare response message
            $message = '';
            if ($deletedCount > 0) {
                $message = "{$deletedCount} pelanggan berhasil dihapus";
                if (count($successMessages) <= 5) {
                    $message .= ": " . implode(', ', $successMessages);
                }
            }

            if (!empty($errorMessages)) {
                if ($deletedCount > 0) {
                    $message .= ". Namun ada " . count($errorMessages) . " error";
                } else {
                    $message = "Gagal menghapus pelanggan";
                }

                // Log all errors
                foreach ($errorMessages as $error) {
                    log_message('error', 'Bulk delete error: ' . $error);
                }
            }

            return $this->response->setJSON([
                'status' => $deletedCount > 0 ? 'success' : 'error',
                'message' => $message,
                'deleted_count' => $deletedCount,
                'error_count' => count($errorMessages),
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in bulk delete: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
                'csrfName' => csrf_token(),
                'csrfHash' => csrf_hash()
            ]);
        }
    }

    // Endpoint: /customer/paket-options
    public function paketOptions()
    {
        $paketModel = model('PackageProfileModel');
        $paketList = $paketModel->findAll();

        $options = [];
        foreach ($paketList as $paket) {
            $options[] = [
                'id' => $paket['id'],
                'name' => $paket['name'],
                'label' => $paket['name'] . ' | ' . $paket['bandwidth_profile'] . ' | Rp ' . number_format($paket['price'], 0, ',', '.'),
                'price' => $paket['price']
            ];
        }
        return $this->response->setJSON($options);
    }

    // Endpoint: /customer/branchOptions
    public function branchOptions()
    {
        $builder = $this->db->table('branches');
        $branchList = $builder->get()->getResultArray();

        $options = [];
        foreach ($branchList as $branch) {
            $options[] = [
                'id_lokasi' => $branch['id'],
                'nama' => $branch['branch_name']
            ];
        }
        return $this->response->setJSON($options);
    }

    // Endpoint: /customer/areaOptions
    public function areaOptions()
    {
        $areaModel = model('AreaModel');
        $areaList = $areaModel->orderBy('area_name', 'ASC')->findAll();

        $options = [];
        foreach ($areaList as $area) {
            $options[] = [
                'id' => $area['id'],
                'name' => $area['area_name']
            ];
        }
        return $this->response->setJSON($options);
    }

    public function getPaketOptions()
    {

        $options = [];
        foreach ($paketList as $paket) {
            $options[] = [
                'id' => $paket['id'],
                'label' => $paket['name'] . ' | ' . $paket['bandwidth_profile'] . ' | Rp ' . number_format($paket['price'], 0, ',', '.'),
            ];
        }
        return $this->response->setJSON($options);
    }

    /**
     * Get Group Profiles for AJAX requests
     * Returns group profiles with IP range information for PPPoE configuration
     */
    public function getGroupProfiles()
    {
        // Bersihkan output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            // Allow both AJAX and regular requests, but respond with JSON
            $this->response->setContentType('application/json');

            $groupProfileModel = model('GroupProfileModel');
            $groupProfiles = $groupProfileModel->where('status', 'active')
                ->orderBy('name', 'ASC')
                ->findAll();

            $data = [];
            foreach ($groupProfiles as $profile) {
                $data[] = [
                    'id' => $profile['id'],
                    'name' => $profile['name'],
                    'description' => $profile['description'] ?? '',
                    'local_address' => $profile['local_address'] ?? '',
                    'ip_range_start' => $profile['ip_range_start'] ?? '',
                    'ip_range_end' => $profile['ip_range_end'] ?? '',
                    'max_users' => $profile['max_users'] ?? 0,
                    'session_timeout' => $profile['session_timeout'] ?? 0,
                    'idle_timeout' => $profile['idle_timeout'] ?? 0
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting group profiles: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data group profiles',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getUsedIPs()
    {
        try {

            // Build query to get used IPs
            $builder = $this->db->table('customers')
                ->select('
                    pppoe_remote_address as remote_address,
                    pppoe_local_ip as local_address,
                    nama_pelanggan as customer_name,
                    nomor_layanan as service_number,
                    tgl_pasang as created_date
                ')
                ->where('pppoe_remote_address IS NOT NULL')
                ->where('pppoe_remote_address !=', '');

            // If group profile is specified, filter by it
            if ($groupProfileId) {
                // Assuming there's a relationship between customers and group profiles
                // You may need to adjust this based on your actual database schema
                $builder->where('group_profile_id', $groupProfileId);
            }

            $usedIPs = $builder->orderBy('tgl_pasang', 'DESC')->get()->getResultArray();

            // Format the data for display
            $data = [];
            foreach ($usedIPs as $ip) {
                $data[] = [
                    'remote_address' => $ip['remote_address'],
                    'local_address' => $ip['local_address'] ?? '-',
                    'customer_name' => $ip['customer_name'],
                    'service_number' => $ip['service_number'],
                    'formatted_date' => $ip['created_date'] ? date('d/m/Y H:i', strtotime($ip['created_date'])) : '-'
                ];
            }

            $message = $groupProfileId
                ? "Menampilkan IP yang digunakan untuk Group Profile ID: {$groupProfileId}"
                : "Menampilkan semua IP yang digunakan";

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'message' => $message,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting used IPs: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data IP yang digunakan',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get customer options for dropdown selections
     * Used by ticket system and other forms that need customer data
     */
    public function getCustomerOptions()
    {
        // Bersihkan output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            // Set JSON response header
            $this->response->setContentType('application/json');            // Get customers with essential data for dropdown
            $customers = $this->cust->select([
                'id_customers as id',
                'nama_pelanggan',
                'nomor_layanan',
                'email',
                'telepphone as no_wa',
                'address as alamat',
                'status_tagihan as status'
            ])
                ->where('status_tagihan !=', 'nonaktif') // Only active customers
                ->orderBy('nama_pelanggan', 'ASC')
                ->findAll();

            // Format data for frontend consumption
            $formattedCustomers = [];
            foreach ($customers as $customer) {
                $formattedCustomers[] = [
                    'id' => $customer['id'],
                    'nama_pelanggan' => $customer['nama_pelanggan'],
                    'nomor_layanan' => $customer['nomor_layanan'] ?? '',
                    'email' => $customer['email'] ?? '',
                    'no_wa' => $customer['no_wa'] ?? '',
                    'alamat' => $customer['alamat'] ?? '',
                    'status' => $customer['status'] ?? 'aktif'
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Customer data retrieved successfully',
                'data' => $formattedCustomers,
                'count' => count($formattedCustomers)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in getCustomerOptions: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to retrieve customer data',
                'error' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function data()
    {
        $request = service('request');
        $customerModel = model('CustomerModel');

        // DataTables parameters
        $draw = intval($request->getPost('draw'));
        $start = intval($request->getPost('start'));
        $length = intval($request->getPost('length'));

        // Filtering
        $searchValue = $request->getPost('searchTableList');
        $filterPackage = $request->getPost('filterPackage');
        $filterServer = $request->getPost('filterServer');
        $filterDistrict = $request->getPost('filterDistrict');
        $filterVillage = $request->getPost('filterVillage');
        $filterCluster = $request->getPost('filterCluster');
        $filterStatus = $request->getPost('filterStatus');
        $filterNewCustomer = $request->getPost('filterNewCustomer');
        $filterSubscriptionMethod = $request->getPost('filterSubscriptionMethod');
        $loadOldData = $request->getPost('loadOldData'); // Flag untuk memuat data lama

        $builder = $customerModel->builder();
        // Always select the address field as 'address' for DataTables
        $builder->select('customers.*, customers.address as address, package_profiles.name as paket_label, package_profiles.bandwidth_profile as paket_bandwidth, package_profiles.price as paket_harga, lokasi_server.name as server_name, clustering.name as cluster_name');
        $builder->join('package_profiles', 'package_profiles.id = customers.id_paket', 'left');
        $builder->join('lokasi_server', 'lokasi_server.id_lokasi = customers.id_lokasi_server', 'left');
        $builder->join('clustering', 'clustering.id_clustering = customers.customer_clustering_id', 'left');

        if ($searchValue) {
            $builder->like('customers.nama_pelanggan', $searchValue);
        }
        if ($filterPackage && $filterPackage != '0') {
            $builder->where('customers.id_paket', $filterPackage);
        }
        if ($filterServer && $filterServer != '0') {
            $builder->where('customers.id_lokasi_server', $filterServer);
        }
        if ($filterDistrict && $filterDistrict != '0') {
            $builder->where('customers.district', $filterDistrict);
        }
        if ($filterVillage && $filterVillage != '0') {
            $builder->where('customers.village', $filterVillage);
        }
        if ($filterCluster && $filterCluster != '0') {
            $builder->where('customers.customer_clustering_id', $filterCluster);
        }
        if ($filterStatus && $filterStatus != '0' && $filterStatus != '') {
            // Map status filter to DB values
            if ($filterStatus === 'active' || $filterStatus === 'Aktif') {
                $builder->where('customers.status_tagihan', 'Lunas');
            } elseif ($filterStatus === 'inactive' || $filterStatus === 'Tidak Aktif') {
                $builder->where('customers.status_tagihan', 'Belum Lunas');
            } elseif ($filterStatus === 'suspend') {
                // Filter for overdue customers (unpaid and past due date)
                $builder->where('customers.status_tagihan !=', 'Lunas');
                $builder->where('customers.tgl_tempo <', date('Y-m-d'));
            }
        }
        if ($filterNewCustomer && $filterNewCustomer != '0' && $filterNewCustomer != '') {
            if ($filterNewCustomer === 'yes' || $filterNewCustomer === 'new_customer') {
                $builder->where('customers.is_new_customer', 1);
            } elseif ($filterNewCustomer === 'no') {
                $builder->groupStart();
                $builder->where('customers.is_new_customer', 0);
                $builder->orWhere('customers.is_new_customer IS NULL', null, false);
                $builder->groupEnd();
            }
        }
        if ($filterSubscriptionMethod && $filterSubscriptionMethod != '0' && $filterSubscriptionMethod != '') {
            $builder->where('customers.subscription_method', $filterSubscriptionMethod);
        }

        // Khusus untuk memuat data pelanggan lama - urutkan berdasarkan tanggal terlama
        if ($loadOldData === 'true' || $loadOldData === true) {
            $builder->orderBy('customers.created_at', 'ASC'); // Data terlama dulu
            $builder->orderBy('customers.tgl_pasang', 'ASC'); // Atau berdasarkan tanggal pasang
        } else {
            $builder->orderBy('customers.created_at', 'DESC'); // Data terbaru dulu (default)
        }

        $totalRecords = $builder->countAllResults(false);
        $builder->limit($length, $start);
        $query = $builder->get();
        $customers = $query->getResult();

        $data = [];
        $i = $start + 1;
        foreach ($customers as $row) {
            // Get formatted region names with safe fallback
            $districtName = '-';
            $villageName = '-';

            try {
                if ($row->district) {
                    if (ctype_digit($row->district)) {
                        $districtName = "Kecamatan " . $row->district;
                    } else {
                        $districtName = $row->district;
                    }
                }

                if ($row->village) {
                    if (ctype_digit($row->village)) {
                        $villageName = "Desa/Kel. " . $row->village;
                    } else {
                        $villageName = $row->village;
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error formatting region names: ' . $e->getMessage());
                $districtName = $row->district ?: '-';
                $villageName = $row->village ?: '-';
            }

            // Check if customer is overdue (melewati jatuh tempo dan belum bayar)
            $isOverdue = false;
            if ($row->status_tagihan !== 'Lunas' && !empty($row->tgl_tempo)) {
                try {
                    // Parse tanggal tempo (format: YYYY-MM-DD atau YYYY-MM-DD HH:MM:SS)
                    $tempoDate = new \DateTime($row->tgl_tempo);
                    $currentDate = new \DateTime();

                    // Set ke awal hari untuk perbandingan tanggal yang akurat
                    $tempoDate->setTime(0, 0, 0);
                    $currentDate->setTime(0, 0, 0);

                    $isOverdue = $currentDate > $tempoDate;
                } catch (\Exception $e) {
                    // Jika parsing tanggal gagal, coba metode alternatif
                    $isOverdue = (strtotime($row->tgl_tempo) < strtotime('today'));
                }
            }

            // ===== CEK STATUS OVERDUE BERDASARKAN INVOICE YANG BELUM DIBAYAR =====
            // Cek apakah ada invoice yang belum dibayar dan sudah lewat due date
            $unpaidInvoice = $this->db->table('customer_invoices')
                ->where('customer_id', $row->id_customers)
                ->where('status', 'unpaid')
                ->where('due_date <', date('Y-m-d'))
                ->countAllResults();

            // Customer dianggap overdue jika punya invoice unpaid yang due date-nya sudah lewat
            $isOverdue = $unpaidInvoice > 0;

            // Tentukan status aktif berdasarkan invoice, bukan dari status_tagihan
            $actualStatus = $isOverdue ? 'Tidak Aktif' : ($row->status_tagihan == 'Lunas' ? 'Aktif' : 'Tidak Aktif');

            $data[] = [
                "checkbox" => "<input type='checkbox' class='deleteCheckbox customer-checkbox' value='{$row->id_customers}' data-isolated='{$row->isolir_status}' data-name='{$row->nama_pelanggan}'>",
                "DT_RowIndex" => $i++, // row number
                "id_customers" => $row->id_customers,
                "name" => $row->nama_pelanggan,
                "service_no" => $row->nomor_layanan,
                "pppoe_username" => $row->pppoe_username ?? '-',
                "server_name" => $row->server_name,
                "tgl_tempo" => $row->tgl_tempo ? $row->tgl_tempo . ' 11:46:03' : null, // Format with timestamp
                "tgl_renue" => null, // Will be calculated in frontend based on payment date
                "phone_number" => $row->telepphone,
                "package" => $row->paket_label ? $row->paket_label . ' | ' . $row->paket_bandwidth : '-',
                "price" => $row->paket_harga ? 'Rp ' . number_format($row->paket_harga, 0, ',', '.') : '-',
                "subscription_method" => $row->subscription_method ?? '-',
                "tgl_pasang" => $row->tgl_pasang ?? $row->created_at ?? null,
                "district" => $districtName,
                "village" => $villageName,
                "is_active" => $actualStatus, // Status berdasarkan invoice, bukan status_tagihan
                "is_overdue" => $isOverdue, // Add overdue status for frontend
                "action" =>
                "<a href='" . site_url("customers/{$row->id_customers}") . "' class='btn btn-info btn-sm'><i class='bx bx-show'></i></a> " .
                    "<a href='" . site_url("customers/{$row->id_customers}/edit") . "' class='btn btn-warning btn-sm'><i class='bx bx-edit'></i></a> " .
                    "<a href='#' class='btn btn-danger btn-sm deleteData' data-id='{$row->id_customers}'><i class='bx bx-trash'></i></a>"
            ];
        }

        // Tambahkan CSRF token baru ke response agar DataTables bisa update meta tag
        return $this->response->setJSON([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data,
            // Tambahan untuk CSRF refresh
            "csrfName" => csrf_token(),
            "csrfHash" => csrf_hash(),
        ]);
    }

    public function searchPPPSecrets()
    {
        // Bersihkan output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->response->setContentType('application/json');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $input = $this->request->getJSON(true);
            $serverLocationId = $input['server_location_id'] ?? null;

            if (!$serverLocationId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Server location ID is required']);
            }

            // Get server location details from database
            $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverLocationId)->get()->getRow();

            if (!$serverLocation) {
                return $this->response->setJSON(['success' => false, 'message' => 'Server location not found']);
            }

            // Log the server details for debugging
            log_message('info', 'Attempting to connect to MikroTik server: ' . json_encode([
                'id' => $serverLocationId,
                'host' => $serverLocation->ip_router ?? 'N/A',
                'user' => $serverLocation->username ?? 'N/A',
                'port' => $serverLocation->port_api ?? 'N/A'
            ]));

            // Connect to MikroTik using server location details - GUNAKAN DATA ASLI
            $config = [
                'host' => $serverLocation->ip_router,           // GUNAKAN PERSIS DARI DATABASE
                'user' => $serverLocation->username ?? 'admin',
                'pass' => $serverLocation->password_router ?? '',
                'port' => (int)($serverLocation->port_api ?? 8728), // GUNAKAN PORT API DARI DATABASE
                'timeout' => 15,
                'attempts' => 3
            ];

            // Validate config - VALIDASI DATA ASLI
            if (empty($config['host'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Router IP address tidak ditemukan dalam konfigurasi server'
                ]);
            }

            // Log konfigurasi asli dari database (termasuk password untuk debug)
            log_message('info', 'MikroTik config (data asli dari database): ' . json_encode([
                'host' => $config['host'],
                'port' => $config['port'],
                'user' => $config['user'],
                'pass_length' => strlen($config['pass'] ?: ''),
                'pass_set' => !empty($config['pass']),
                'server_id' => $serverLocationId
            ]));

            // Log detailed connection attempt
            log_message('info', 'MikroTik connection attempt with config: ' . json_encode([
                'host' => $config['host'],
                'port' => $config['port'],
                'user' => $config['user'],
                'timeout' => $config['timeout'],
                'server_id' => $serverLocationId
            ]));

            try {
                $mikrotikAPI = new \App\Libraries\MikrotikAPI($config);

                // Test connection first
                $connectionTest = $mikrotikAPI->testConnection();
                if (!$connectionTest['success']) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to connect to router: ' . $connectionTest['message'],
                        'debug_info' => [
                            'host' => $config['host'],
                            'port' => $config['port'],
                            'user' => $config['user'],
                            'password_set' => !empty($config['pass']),
                            'password_length' => strlen($config['pass'] ?: ''),
                            'server_id' => $serverLocationId,
                            'connection_type' => strpos($config['host'], 'hostddns.us') !== false ? 'tunnel' : 'direct',
                            'original_config' => [
                                'ip_router' => $serverLocation->ip_router,
                                'port_api' => $serverLocation->port_api,
                                'username' => $serverLocation->username,
                                'password_exists' => !empty($serverLocation->password_router)
                            ]
                        ],
                        'debug_url' => site_url('customer/debug-mikrotik?server_id=' . $serverLocationId)
                    ]);
                }

                // Get PPP secrets from MikroTik
                $secrets = $mikrotikAPI->getPPPSecrets();

                if (empty($secrets)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No PPP secrets found on router',
                        'debug_info' => [
                            'connection_successful' => true,
                            'secrets_count' => 0
                        ]
                    ]);
                }

                // Get list of already used PPPoE usernames from database
                $usedUsernames = $this->getUsedPPPoEUsernames();

                // Format secrets for response and filter out already used ones
                $formattedSecrets = [];
                foreach ($secrets as $secret) {
                    $username = $secret['name'] ?? '';

                    // Skip if username is already used by existing customers
                    if (!empty($username) && in_array($username, $usedUsernames)) {
                        continue;
                    }

                    $formattedSecrets[] = [
                        'name' => $username,
                        'password' => $secret['password'] ?? '',
                        'service' => $secret['service'] ?? 'any',
                        'local_address' => $secret['local-address'] ?? '',
                        'remote_address' => $secret['remote-address'] ?? '',
                        'profile' => $secret['profile'] ?? 'default',
                        'comment' => $secret['comment'] ?? '',
                        'disabled' => isset($secret['disabled']) ? ($secret['disabled'] === 'true') : false
                    ];
                }

                log_message('info', 'PPP secrets retrieved successfully: ' . count($formattedSecrets) . ' available secrets (filtered from ' . count($secrets) . ' total)');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => count($formattedSecrets) > 0 ?
                        'PPP secrets retrieved successfully' :
                        'All PPP secrets are already in use',
                    'data' => $formattedSecrets,
                    'total' => count($formattedSecrets),
                    'total_on_router' => count($secrets),
                    'filtered_count' => count($secrets) - count($formattedSecrets)
                ]);
            } catch (\Exception $e) {
                log_message('debug', 'Mikrotik connection failed: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to connect to router: ' . $e->getMessage(),
                    'debug_info' => [
                        'server_id' => $serverLocationId,
                        'error_details' => $e->getMessage()
                    ]
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'searchPPPSecrets general error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unexpected error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get PPPoE availability status for a specific server
     */
    public function getPppoeAvailabilityStatus()
    {
        // Bersihkan output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->response->setContentType('application/json');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $input = $this->request->getJSON(true);
            $serverLocationId = $input['server_location_id'] ?? null;

            if (!$serverLocationId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Server location ID is required']);
            }

            // Get MikroTik connection
            $mikrotikConnection = $this->getMikrotikConnection($serverLocationId);
            if (!$mikrotikConnection['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to connect to MikroTik: ' . $mikrotikConnection['message']
                ]);
            }

            $mikrotikAPI = $mikrotikConnection['api'];

            // Get all PPP secrets from MikroTik
            $allSecrets = $mikrotikAPI->getPPPSecrets();
            $totalOnRouter = count($allSecrets);

            // Get used usernames from database
            $usedUsernames = $this->getUsedPPPoEUsernames();
            $totalUsedInDB = count($usedUsernames);

            // Count available secrets (not used by customers)
            $availableSecrets = [];
            foreach ($allSecrets as $secret) {
                $username = $secret['name'] ?? '';
                if (!empty($username) && !in_array($username, $usedUsernames)) {
                    $availableSecrets[] = $username;
                }
            }

            $totalAvailable = count($availableSecrets);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'server_location_id' => $serverLocationId,
                    'total_secrets_on_router' => $totalOnRouter,
                    'total_used_by_customers' => $totalUsedInDB,
                    'total_available' => $totalAvailable,
                    'availability_percentage' => $totalOnRouter > 0 ? round(($totalAvailable / $totalOnRouter) * 100, 2) : 0,
                    'used_usernames' => $usedUsernames,
                    'available_usernames' => array_slice($availableSecrets, 0, 10), // Show first 10 available
                    'status' => $totalAvailable > 0 ? 'available' : 'all_used'
                ],
                'message' => $totalAvailable > 0 ?
                    "Ada {$totalAvailable} PPPoE secret yang tersedia dari {$totalOnRouter} total" :
                    "Semua PPPoE secret sudah digunakan ({$totalOnRouter} total)"
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in getPppoeAvailabilityStatus: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }



    /**
     * Save PPPoE secret directly to MikroTik (public endpoint)
     * Called from frontend when creating PPPoE account in MikroTik
     */
    public function savePppoeToMikrotik()
    {
        // Bersihkan output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->response->setContentType('application/json');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Increase PHP execution time for slow VPN connections
        set_time_limit(300); // 5 minutes

        try {
            // Get POST data
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $serverLocationId = $this->request->getPost('server_location_id');
            $paketId = $this->request->getPost('paket_id');
            $profile = $this->request->getPost('profile') ?: 'default';
            $service = $this->request->getPost('service') ?: 'pppoe';
            $localIP = $this->request->getPost('local_ip');
            $remoteIP = $this->request->getPost('remote_ip');
            $comment = $this->request->getPost('comment') ?: '';
            $customerName = $this->request->getPost('customer_name') ?: '';

            // If paket_id is provided, get the profile name from the package
            if ($paketId) {
                $packageModel = new PackageProfileModel();
                $package = $packageModel->find($paketId);
                if ($package) {
                    // Prioritize default_profile_mikrotik (most reliable), then others
                    if (!empty($package['default_profile_mikrotik'])) {
                        $profile = $package['default_profile_mikrotik'];
                        log_message('info', "Using default_profile_mikrotik from package: {$profile}");
                    } elseif (!empty($package['group_profile'])) {
                        $profile = $package['group_profile'];
                        log_message('info', "Using group profile from package: {$profile}");
                    } elseif (!empty($package['bandwidth_profile'])) {
                        $profile = $package['bandwidth_profile'];
                        log_message('info', "Using bandwidth profile from package: {$profile}");
                    } elseif (!empty($package['name'])) {
                        $profile = $package['name'];
                        log_message('info', "Using package name as profile: {$profile}");
                    }

                    log_message('info', "Package data: " . json_encode($package));
                }
            }

            // Validate required fields
            if (!$username || !$password || !$serverLocationId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Missing required fields: username, password, server_location_id'
                ]);
            }

            log_message('info', 'Saving PPPoE secret to MikroTik (MikrotikAdvanced): ' . json_encode([
                'username' => $username,
                'server_id' => $serverLocationId,
                'profile' => $profile,
                'local_ip' => $localIP ?: 'not_set',
                'remote_ip' => $remoteIP ?: 'not_set'
            ]));

            // Get server location config
            $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverLocationId)->get()->getRow();
            if (!$serverLocation) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Server location not found'
                ]);
            }

            // Prepare connection config for MikrotikAdvanced
            $config = [
                'host' => $serverLocation->ip_router,
                'port' => (int)$serverLocation->port_api,
                'user' => $serverLocation->username,
                'pass' => $serverLocation->password_router,
                'timeout' => 120
            ];

            $mikrotikAdvanced = new \App\Libraries\MikrotikAdvanced($config);

            // Prepare PPPoE secret data
            $secretData = [
                'name' => $username,
                'password' => $password,
                'service' => $service,
                'profile' => $profile,
                'comment' => $comment,
                'disabled' => false
            ];

            // Add local IP if provided
            if (!empty($localIP)) {
                $secretData['local-address'] = $localIP;
            }

            // Add remote IP if provided
            if (!empty($remoteIP)) {
                $secretData['remote-address'] = $remoteIP;
            }

            // If customer name is provided, enhance the comment
            if (!empty($customerName)) {
                $secretData['comment'] = 'Customer: ' . $customerName . ' | ' . $comment;
            }

            log_message('info', 'Creating PPPoE secret with MikrotikAdvanced: ' . json_encode([
                'username' => $secretData['name'],
                'profile' => $secretData['profile'],
                'local_address' => $secretData['local-address'] ?? null,
                'remote_address' => $secretData['remote-address'] ?? null,
                'comment' => $secretData['comment']
            ]));

            try {
                // Get bandwidth from package for queue creation
                $bandwidth = null;
                if ($paketId && isset($package) && !empty($package['bandwidth_profile'])) {
                    $bandwidth = $package['bandwidth_profile'];
                }

                // Create PPPoE secret with MikrotikAdvanced (includes retry logic)
                $result = $mikrotikAdvanced->addPPPoESecretComplete(
                    $secretData,
                    $bandwidth,
                    true  // Enable auto routing
                );

                if ($result['success'] && $result['pppoe']) {
                    $message = 'PPPoE secret berhasil dibuat di MikroTik';
                    if ($result['queue']) $message .= ' dengan bandwidth queue';
                    if ($result['route']) $message .= ' dan routing';

                    log_message('info', 'PPPoE secret created successfully: ' . $username);
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'username' => $secretData['name'],
                            'profile' => $secretData['profile'],
                            'local_address' => $secretData['local-address'] ?? null,
                            'remote_address' => $secretData['remote-address'] ?? null,
                            'service' => $secretData['service'],
                            'queue_created' => $result['queue'],
                            'route_created' => $result['route']
                        ]
                    ]);
                } else {
                    log_message('error', 'Failed to create PPPoE secret: ' . $username . ' - ' . ($result['message'] ?? 'Unknown error'));

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat PPPoE secret: ' . ($result['message'] ?? 'Unknown error')
                    ]);
                }
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                log_message('error', 'MikroTik API error in savePppoeToMikrotik: ' . $errorMessage);

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error MikroTik API: ' . $errorMessage
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'General error in savePppoeToMikrotik: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unexpected error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate prorata invoice for a new customer
     */
    private function generateProrataInvoiceForCustomer($customerId, $data)
    {
        try {
            log_message('info', 'Prorata invoice generation called for customer: ' . $customerId);

            // Ambil data customer yang baru dibuat
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($customerId);

            if (!$customer) {
                return ['success' => false, 'message' => 'Customer tidak ditemukan'];
            }

            // Ambil tanggal pemasangan
            $tglPasang = $data['tgl_pasang'] ?? null;
            if (!$tglPasang) {
                return ['success' => false, 'message' => 'Tanggal pemasangan tidak ditemukan'];
            }

            // Konversi tanggal pemasangan ke format yang benar
            $installDate = date('Y-m-d', strtotime($tglPasang));
            $installMonth = date('Y-m', strtotime($installDate));
            $currentMonth = date('Y-m');

            // Hanya buat prorata jika pemasangan di bulan ini
            if ($installMonth !== $currentMonth) {
                return ['success' => false, 'message' => 'Prorata hanya untuk pemasangan bulan ini'];
            }

            // Ambil data paket
            $paketId = $customer['id_paket'] ?? null;
            if (!$paketId) {
                return ['success' => false, 'message' => 'Paket customer tidak ditemukan'];
            }

            $db = \Config\Database::connect();
            $paket = $db->table('package_profiles')
                ->where('id', $paketId)
                ->get()
                ->getRow();

            if (!$paket) {
                return ['success' => false, 'message' => 'Data paket tidak ditemukan'];
            }

            // Hitung prorata
            $monthlyPrice = $paket->price ?? 0;
            $prorataCalculation = $this->calculateProrataAmount($installDate, $monthlyPrice);

            if ($prorataCalculation['amount'] <= 0) {
                return ['success' => false, 'message' => 'Jumlah prorata tidak valid'];
            }

            // Cek apakah sudah ada invoice prorata untuk periode ini
            $invoiceModel = new \App\Models\InvoiceModel();
            $existingInvoice = $invoiceModel->where([
                'customer_id' => $customerId,
                'periode' => $installMonth,
                'is_prorata' => 1
            ])->first();

            if ($existingInvoice) {
                return ['success' => false, 'message' => 'Invoice prorata sudah ada untuk periode ini'];
            }

            // Generate nomor invoice unik
            $invoiceNo = $this->generateProrataInvoiceNumber($customerId);

            // Data invoice prorata
            $invoiceData = [
                'customer_id' => $customerId,
                'invoice_no' => $invoiceNo,
                'periode' => $installMonth,
                'bill' => $prorataCalculation['amount'],
                'arrears' => 0,
                'status' => 'unpaid',
                'package' => ($paket->name ?? '') . ' | ' . ($paket->bandwidth_profile ?? '') . ' (Prorata)',
                'additional_fee' => 0,
                'discount' => 0,
                'server' => $customer['id_lokasi_server'] ?? null,
                'due_date' => $customer['tgl_tempo'] ?? 15,
                'district' => $customer['district'] ?? null,
                'village' => $customer['village'] ?? null,
                'is_prorata' => 1,
                'prorata_start_date' => $installDate,
                'prorata_days' => $prorataCalculation['days'],
                'prorata_full_amount' => $monthlyPrice,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert invoice prorata
            if ($invoiceModel->insert($invoiceData)) {
                log_message('info', "Prorata invoice created successfully for customer {$customerId}: {$invoiceNo}");
                return [
                    'success' => true,
                    'message' => "Tagihan prorata berhasil dibuat: {$invoiceNo}",
                    'invoice_no' => $invoiceNo,
                    'amount' => $prorataCalculation['amount'],
                    'days' => $prorataCalculation['days'],
                    'periode' => $installMonth
                ];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan invoice prorata'];
            }
        } catch (\Exception $e) {
            log_message('error', 'Prorata invoice generation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Calculate prorata amount based on installation date
     */
    private function calculateProrataAmount($installDate, $monthlyPrice)
    {
        $installDateTime = new \DateTime($installDate);
        $endOfMonth = new \DateTime($installDate);
        $endOfMonth->modify('last day of this month');

        // Hitung jumlah hari dari tanggal pasang sampai akhir bulan
        $remainingDays = $installDateTime->diff($endOfMonth)->days + 1; // +1 untuk include hari pemasangan

        // Total hari dalam bulan
        $totalDaysInMonth = $endOfMonth->format('j');

        // Hitung prorata (pembulatan ke atas)
        $prorataAmount = ceil(($remainingDays / $totalDaysInMonth) * $monthlyPrice);

        return [
            'amount' => $prorataAmount,
            'days' => $remainingDays,
            'total_days' => $totalDaysInMonth,
            'percentage' => ($remainingDays / $totalDaysInMonth) * 100
        ];
    }

    /**
     * Generate unique prorata invoice number
     */
    private function generateProrataInvoiceNumber($customerId)
    {
        $prefix = 'PRO';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

        return "{$prefix}-{$date}-{$customerId}-{$random}";
    }

    /**
     * Handle automatic MikroTik PPPoE secret management
     */
    private function handlePppoeSync($customerId, $data, $operation)
    {
        try {
            log_message('info', 'PPPoE sync called for customer: ' . $customerId . ' operation: ' . $operation);

            // Check PPP secret option
            $pppSecretOption = $data['ppp_secret'] ?? 'not_set';
            log_message('info', 'PPP Secret option: ' . $pppSecretOption);

            // Jika secret diambil dari router, jangan buat secret baru
            if ($pppSecretOption === 'ambil_dari_router') {
                log_message('info', 'PPP Secret diambil dari router, tidak perlu membuat secret baru di MikroTik');
                return ['success' => true, 'message' => 'PPP Secret diambil dari router yang sudah ada, tidak perlu membuat baru'];
            }

            // Jika tanpa secret, skip PPPoE sync
            if ($pppSecretOption === 'tanpa_secret') {
                log_message('info', 'Customer tidak menggunakan PPPoE secret, skipping sync');
                return ['success' => true, 'message' => 'Customer tidak menggunakan PPPoE secret'];
            }

            // Check if PPPoE credentials are provided
            if (empty($data['pppoe_username']) || empty($data['pppoe_password'])) {
                log_message('info', 'No PPPoE credentials provided, skipping PPPoE sync');
                return ['success' => true, 'message' => 'No PPPoE credentials provided, sync skipped'];
            }

            $mikrotikResult = ['success' => true, 'message' => 'MikroTik sync skipped'];

            switch ($operation) {
                case 'create':
                    // Hanya buat PPPoE secret di MikroTik jika opsi adalah 'buat_secret_baru'
                    if ($pppSecretOption === 'buat_secret_baru') {
                        $mikrotikResult = $this->createPppoeSecretInMikrotik($customerId, $data);
                    } else {
                        $mikrotikResult = ['success' => true, 'message' => 'PPPoE secret tidak dibuat karena opsi: ' . $pppSecretOption];
                    }
                    break;

                case 'update':
                    // For update, we need the existing customer data
                    $existingCustomer = $this->cust->where('id_customers', $customerId)->first();
                    if (!$existingCustomer) {
                        return ['success' => false, 'message' => 'Customer not found for update'];
                    }

                    // Hanya update PPPoE secret di MikroTik jika opsi adalah 'buat_secret_baru'
                    if ($pppSecretOption === 'buat_secret_baru') {
                        $mikrotikResult = $this->updatePppoeSecretInMikrotik($customerId, $existingCustomer, $data);
                    } else {
                        $mikrotikResult = ['success' => true, 'message' => 'PPPoE secret tidak diupdate karena opsi: ' . $pppSecretOption];
                    }
                    break;

                case 'delete':
                    $customer = $this->cust->where('id_customers', $customerId)->first();
                    if (!$customer) {
                        return ['success' => false, 'message' => 'Customer not found for deletion'];
                    }

                    // Delete PPPoE secret from MikroTik only
                    $mikrotikResult = $this->deletePppoeSecretFromMikrotik($customer);
                    break;

                default:
                    return ['success' => false, 'message' => 'Unknown operation: ' . $operation];
            }

            return [
                'success' => $mikrotikResult['success'],
                'message' => 'MikroTik: ' . $mikrotikResult['message'],
                'mikrotik_result' => $mikrotikResult
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in PPPoE sync auto: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create PPPoE secret in MikroTik
     */
    private function createPppoeSecretInMikrotik($customerId, $data)
    {
        try {
            log_message('info', 'Creating PPPoE secret in MikroTik for customer: ' . $customerId);

            // Check if required PPPoE data is available
            if (empty($data['pppoe_username']) || empty($data['pppoe_password'])) {
                return ['success' => false, 'message' => 'PPPoE username and password are required'];
            }

            // Get customer data to determine server location
            $customer = $this->cust->where('id_customers', $customerId)->first();
            if (!$customer) {
                // Use provided data if customer not found in DB yet
                $customer = $data;
                $customer['id_customers'] = $customerId;
            }

            // Get server location ID
            $serverLocationId = $customer['id_lokasi_server'] ?? $data['id_lokasi_server'] ?? null;
            if (!$serverLocationId) {
                return ['success' => false, 'message' => 'Server location ID is required for PPPoE secret creation'];
            }

            // Get MikroTik connection config
            $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverLocationId)->get()->getRow();
            if (!$serverLocation) {
                return [
                    'success' => false,
                    'message' => 'Server location not found'
                ];
            }

            // Prepare connection config for MikrotikAdvanced
            $config = [
                'host' => $serverLocation->ip_router,
                'port' => (int)$serverLocation->port_api,
                'user' => $serverLocation->username,
                'pass' => $serverLocation->password_router,
                'timeout' => 120
            ];

            // Use MikrotikAdvanced library with retry logic and auto queue/routing
            $mikrotikAdvanced = new \App\Libraries\MikrotikAdvanced($config);

            if (!$mikrotikAdvanced->isConnected()) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to MikroTik: ' . $mikrotikAdvanced->getLastError()
                ];
            }

            // Check if secret already exists and delete it first (untuk testing/development)
            try {
                log_message('info', 'Checking if PPPoE secret already exists: ' . $data['pppoe_username']);

                // Get MikroTik client
                $mtClient = $mikrotikAdvanced->getClient();

                // Search for existing secret - use query format that works
                $existingSecrets = $mtClient->comm('/ppp/secret/print', [
                    '?name' => $data['pppoe_username']
                ]);

                log_message('info', 'Existing secrets check result: ' . json_encode($existingSecrets));

                if (!empty($existingSecrets) && is_array($existingSecrets)) {
                    log_message('warning', 'Found ' . count($existingSecrets) . ' existing secret(s) with name: ' . $data['pppoe_username']);

                    foreach ($existingSecrets as $secret) {
                        $secretId = $secret['.id'] ?? null;
                        $secretName = $secret['name'] ?? 'unknown';

                        if ($secretId) {
                            log_message('warning', "Deleting existing PPPoE secret: {$secretName} (ID: {$secretId})");

                            // Use string format for remove command
                            $deleteResult = $mtClient->comm('/ppp/secret/remove', [
                                '=numbers=' . $secretId
                            ]);

                            log_message('info', 'Delete result: ' . json_encode($deleteResult));
                            log_message('info', 'Old PPPoE secret deleted successfully');
                        }
                    }

                    // Wait for MikroTik to process deletion
                    log_message('info', 'Waiting 1 second for MikroTik to process deletion...');
                    sleep(1);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error checking/deleting existing secret: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                // Continue anyway - will fail on create if still exists
            }

            // Get package info for profile and bandwidth
            $package = null;
            $packageId = $customer['id_paket'] ?? $data['id_paket'] ?? null;
            if (!empty($packageId)) {
                $package = $this->db->table('package_profiles')->where('id', $packageId)->get()->getRow();
            }

            // Determine profile to use based on payment status
            $profileToUse = 'default';

            // Check payment status - if not paid, use isolir profile
            $statusTagihan = $customer['status_tagihan'] ?? $data['status_tagihan'] ?? 'Belum Lunas';
            $useIsolirProfile = ($statusTagihan !== 'Lunas');

            if ($useIsolirProfile) {
                // Customer belum bayar - gunakan profile isolir
                $serverLocationId = $customer['id_lokasi_server'] ?? $data['id_lokasi_server'] ?? null;
                if ($serverLocationId) {
                    $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverLocationId)->get()->getRow();
                    if ($serverLocation && !empty($serverLocation->profile_isolir)) {
                        $profileToUse = $serverLocation->profile_isolir;
                        log_message('info', "Customer belum bayar - menggunakan profile isolir: {$profileToUse}");
                    } else {
                        // Fallback ke profile default isolir
                        $profileToUse = 'ISOLIR';
                        log_message('info', "Customer belum bayar - menggunakan profile isolir default: {$profileToUse}");
                    }
                }
            } else if ($package) {
                // Customer sudah bayar - gunakan profile normal dari package
                $profileToUse = $package->default_profile_mikrotik ?? $package->bandwidth_profile ?? 'default';
                log_message('info', "Customer sudah bayar - menggunakan profile normal: {$profileToUse}");
            }

            // Prepare PPPoE secret data
            $secretData = [
                'name' => $data['pppoe_username'],
                'password' => $data['pppoe_password'],
                'service' => 'pppoe',
                'profile' => $profileToUse,
                'comment' => 'Customer: ' . ($customer['nama_pelanggan'] ?? $data['nama_pelanggan'] ?? '') .
                    ' | Service: ' . ($customer['nomor_layanan'] ?? $data['nomor_layanan'] ?? '') .
                    ' | Status: ' . $statusTagihan,
                'disabled' => false
            ];

            // Add local IP if provided
            if (!empty($data['pppoe_local_ip'])) {
                $secretData['local-address'] = $data['pppoe_local_ip'];
            }

            // Add remote IP if provided
            if (!empty($data['pppoe_remote_address'])) {
                $secretData['remote-address'] = $data['pppoe_remote_address'];
            }

            log_message('info', 'Creating PPPoE secret with MikrotikAdvanced: ' . json_encode([
                'username' => $secretData['name'],
                'profile' => $secretData['profile'],
                'local_address' => $secretData['local-address'] ?? null,
                'remote_address' => $secretData['remote-address'] ?? null,
                'bandwidth' => $package->bandwidth_profile ?? 'default'
            ]));

            // Create PPPoE secret with auto queue and routing using advanced library
            $result = $mikrotikAdvanced->addPPPoESecretComplete(
                $secretData,
                $package->bandwidth_profile ?? null,  // Will auto-create queue if bandwidth provided
                true  // Enable auto routing
            );

            if ($result['success'] && $result['pppoe']) {
                $message = 'PPPoE secret created successfully';
                if ($result['queue']) {
                    $message .= ' with bandwidth queue';
                }
                if ($result['route']) {
                    $message .= ' and routing';
                }

                log_message('info', $message . ' for customer: ' . $customerId);
                return [
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'username' => $secretData['name'],
                        'profile' => $secretData['profile'],
                        'local_address' => $secretData['local-address'] ?? null,
                        'remote_address' => $secretData['remote-address'] ?? null,
                        'queue_created' => $result['queue'],
                        'route_created' => $result['route']
                    ]
                ];
            } else {
                log_message('error', 'Failed to create PPPoE secret: ' . ($result['message'] ?? 'Unknown error'));
                return [
                    'success' => false,
                    'message' => 'Failed to create PPPoE secret: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating PPPoE secret in MikroTik: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating PPPoE secret: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update PPPoE secret in MikroTik
     */
    private function updatePppoeSecretInMikrotik($customerId, $existingCustomer, $data)
    {
        try {
            log_message('info', 'Updating PPPoE secret in MikroTik for customer: ' . $customerId);

            // Check if PPPoE credentials have changed
            $oldUsername = $existingCustomer['pppoe_username'] ?? null;
            $newUsername = $data['pppoe_username'] ?? null;
            $newPassword = $data['pppoe_password'] ?? null;

            if (empty($oldUsername) && empty($newUsername)) {
                return ['success' => true, 'message' => 'No PPPoE credentials to update'];
            }

            // Get server location ID
            $serverLocationId = $existingCustomer['id_lokasi_server'] ?? $data['id_lokasi_server'] ?? null;
            if (!$serverLocationId) {
                return ['success' => false, 'message' => 'Server location ID is required for PPPoE secret update'];
            }

            // Get MikroTik connection config
            $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverLocationId)->get()->getRow();
            if (!$serverLocation) {
                return [
                    'success' => false,
                    'message' => 'Server location not found'
                ];
            }

            // Prepare connection config for MikrotikAdvanced
            $config = [
                'host' => $serverLocation->ip_router,
                'port' => (int)$serverLocation->port_api,
                'user' => $serverLocation->username,
                'pass' => $serverLocation->password_router,
                'timeout' => 120
            ];

            $mikrotikAdvanced = new \App\Libraries\MikrotikAdvanced($config);

            // If username changed, we need to remove old secret and create new one
            if (!empty($oldUsername) && $oldUsername !== $newUsername) {
                log_message('info', 'PPPoE username changed, removing old secret: ' . $oldUsername);
                $mikrotikAdvanced->removePPPSecret($oldUsername);
            }

            // If we have new credentials, create/update the secret
            if (!empty($newUsername) && !empty($newPassword)) {
                // Get updated customer data
                $customer = $this->cust->where('id_customers', $customerId)->first();
                if (!$customer) {
                    return ['success' => false, 'message' => 'Customer not found for PPPoE update'];
                }

                // Get package info for profile
                $package = null;
                if (!empty($customer['id_paket'])) {
                    $package = $this->db->table('package_profiles')->where('id', $customer['id_paket'])->get()->getRow();
                }

                // Determine profile to use - prioritize default_profile_mikrotik
                $profileToUse = 'default';
                if ($package) {
                    $profileToUse = $package->default_profile_mikrotik ?? $package->bandwidth_profile ?? 'default';
                }

                // If username is the same, try to remove old secret first to avoid conflicts
                if (!empty($oldUsername) && $oldUsername === $newUsername) {
                    log_message('info', 'Removing existing secret before recreating: ' . $oldUsername);
                    $mikrotikAdvanced->removePPPSecret($oldUsername);
                }

                // Prepare PPPoE secret data
                $secretData = [
                    'name' => $newUsername,
                    'password' => $newPassword,
                    'service' => 'pppoe',
                    'profile' => $profileToUse,
                    'comment' => 'Customer: ' . ($customer['nama_pelanggan'] ?? '') .
                        ' | Service: ' . ($customer['nomor_layanan'] ?? ''),
                    'disabled' => false
                ];

                // Add local IP address if provided
                if (!empty($data['pppoe_local_ip'])) {
                    $secretData['local-address'] = $data['pppoe_local_ip'];
                }

                // Add remote IP address if provided
                if (!empty($data['pppoe_remote_address'])) {
                    $secretData['remote-address'] = $data['pppoe_remote_address'];
                }

                log_message('info', 'Updating PPPoE secret with MikrotikAdvanced: ' . json_encode([
                    'username' => $secretData['name'],
                    'profile' => $profileToUse,
                    'bandwidth' => $package->bandwidth_profile ?? null
                ]));

                // Create/update PPPoE secret with auto queue and routing
                $result = $mikrotikAdvanced->addPPPoESecretComplete(
                    $secretData,
                    $package->bandwidth_profile ?? null,
                    true
                );

                if ($result['success'] && $result['pppoe']) {
                    $message = 'PPPoE secret updated successfully';
                    if ($result['queue']) $message .= ' with bandwidth queue';
                    if ($result['route']) $message .= ' and routing';

                    log_message('info', $message . ' for customer: ' . $customerId);
                    return [
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'username' => $secretData['name'],
                            'profile' => $secretData['profile'],
                            'local_address' => $secretData['local-address'] ?? null,
                            'remote_address' => $secretData['remote-address'] ?? null,
                            'queue_created' => $result['queue'],
                            'route_created' => $result['route']
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to update PPPoE secret: ' . ($result['message'] ?? 'Unknown error')
                    ];
                }
            } else {
                // Only removing old secret
                return ['success' => true, 'message' => 'PPPoE secret removed from MikroTik'];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating PPPoE secret in MikroTik: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating PPPoE secret: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete PPPoE secret from MikroTik
     */
    private function deletePppoeSecretFromMikrotik($customer)
    {
        try {
            $customerId = $customer['id_customers'] ?? 'unknown';
            log_message('info', 'Deleting PPPoE secret from MikroTik for customer: ' . $customerId);

            // Check if customer has PPPoE credentials to delete
            if (empty($customer['pppoe_username'])) {
                return ['success' => true, 'message' => 'No PPPoE username to delete from MikroTik'];
            }

            // Get server location ID
            $serverLocationId = $customer['id_lokasi_server'] ?? null;
            if (!$serverLocationId) {
                return ['success' => false, 'message' => 'Server location ID is required for PPPoE secret deletion'];
            }

            // Get MikroTik connection
            $mikrotikConnection = $this->getMikrotikConnection($serverLocationId);
            if (!$mikrotikConnection['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to MikroTik: ' . $mikrotikConnection['message']
                ];
            }

            $mikrotikAPI = $mikrotikConnection['api'];

            log_message('info', 'Removing PPPoE secret from MikroTik: ' . $customer['pppoe_username']);

            // Remove the PPPoE secret from MikroTik
            $result = $mikrotikAPI->removePPPSecret($customer['pppoe_username']);

            if ($result) {
                log_message('info', 'PPPoE secret deleted successfully from MikroTik for customer: ' . $customerId);
                return [
                    'success' => true,
                    'message' => 'PPPoE secret deleted successfully from MikroTik',
                    'username' => $customer['pppoe_username']
                ];
            } else {
                log_message('warning', 'Failed to delete PPPoE secret from MikroTik for customer: ' . $customerId);
                return [
                    'success' => false,
                    'message' => 'Failed to delete PPPoE secret from MikroTik'
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting PPPoE secret from MikroTik: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error deleting PPPoE secret: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp notification for new customer
     */
    private function sendNewCustomerNotification($data)
    {
        try {
            // Check if WhatsApp notification for new customer is enabled
            $whatsappSettings = $this->getWhatsAppSettings();

            if (!$whatsappSettings || !isset($whatsappSettings['on_customer_created']) || !$whatsappSettings['on_customer_created']) {
                log_message('info', 'WhatsApp notification for new customer is disabled');
                return [
                    'success' => true,
                    'message' => 'WhatsApp notification disabled for new customers'
                ];
            }

            // Load WhatsApp service
            $whatsAppService = new \App\Services\WhatsAppService();

            // Send new customer notification
            $result = $whatsAppService->sendNewCustomerNotification($data);

            if ($result['success']) {
                log_message('info', 'WhatsApp notification sent successfully for customer: ' . ($data['nama_pelanggan'] ?? 'unknown'));
            } else {
                log_message('warning', 'WhatsApp notification failed for customer: ' . ($data['nama_pelanggan'] ?? 'unknown') . ' - ' . $result['message']);
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error sending WhatsApp notification: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending WhatsApp notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Display map of customer locations
     */
    public function showMaps()
    {
        try {
            log_message('info', 'ShowMaps method called');

            // Get customers with coordinates
            $customers = $this->db->query("
                SELECT 
                    c.id_customers,
                    c.nama_pelanggan,
                    c.nomor_layanan,
                    c.address,
                    c.latitude,
                    c.longitude,
                    c.telepphone as phone,
                    c.status_tagihan,
                    c.tgl_tempo,
                    c.customer_clustering_id,
                    p.name as package_name,
                    p.bandwidth_profile,
                    p.price as package_price,
                    ls.name as server_name,
                    cl.name as cluster_name,
                    cl.latitude as cluster_lat,
                    cl.longitude as cluster_lng
                FROM customers c
                LEFT JOIN package_profiles p ON p.id = c.id_paket
                LEFT JOIN lokasi_server ls ON ls.id_lokasi = c.id_lokasi_server
                LEFT JOIN clustering cl ON cl.id_clustering = c.customer_clustering_id
                WHERE c.latitude IS NOT NULL 
                AND c.longitude IS NOT NULL 
                AND c.latitude != 0 
                AND c.longitude != 0
                ORDER BY c.nama_pelanggan
            ")->getResultArray();

            // Get ODP/Clustering data with coordinates
            $odps = $this->db->query("
                SELECT 
                    cl.id_clustering,
                    cl.name as cluster_name,
                    cl.type_option,
                    cl.latitude,
                    cl.longitude,
                    cl.number_of_ports,
                    cl.address,
                    ls.name as server_name,
                    COUNT(c.id_customers) as connected_customers
                FROM clustering cl
                LEFT JOIN lokasi_server ls ON ls.id_lokasi = cl.lokasi_server_id
                LEFT JOIN customers c ON c.customer_clustering_id = cl.id_clustering
                WHERE cl.latitude IS NOT NULL 
                AND cl.longitude IS NOT NULL 
                AND cl.latitude != 0 
                AND cl.longitude != 0
                GROUP BY cl.id_clustering, cl.name, cl.type_option, cl.latitude, cl.longitude, 
                         cl.number_of_ports, cl.address, ls.name
                ORDER BY cl.name
            ")->getResultArray();

            log_message('info', 'Found ' . count($customers) . ' customers and ' . count($odps) . ' ODPs with coordinates');

            // Process coordinates for the map
            $mapData = [];
            $odpData = [];
            $totalLat = 0;
            $totalLng = 0;
            $totalPoints = 0;

            // Process customer data
            foreach ($customers as $customer) {
                $lat = floatval($customer['latitude']);
                $lng = floatval($customer['longitude']);

                if ($lat != 0 && $lng != 0) {
                    $mapData[] = [
                        'id' => $customer['id_customers'],
                        'name' => $customer['nama_pelanggan'],
                        'service_number' => $customer['nomor_layanan'],
                        'address' => $customer['address'],
                        'phone' => $customer['phone'],
                        'package' => $customer['package_name'],
                        'server' => $customer['server_name'],
                        'status' => $customer['status_tagihan'],
                        'due_date' => $customer['tgl_tempo'],
                        'monthly_fee' => $customer['package_price'],
                        'created_at' => date('Y-m-d'),
                        'lat' => $lat,
                        'lng' => $lng,
                        'type' => 'customer',
                        // Clustering/ODP connection info
                        'cluster_id' => $customer['customer_clustering_id'],
                        'cluster_name' => $customer['cluster_name'],
                        'cluster_lat' => $customer['cluster_lat'] ? floatval($customer['cluster_lat']) : null,
                        'cluster_lng' => $customer['cluster_lng'] ? floatval($customer['cluster_lng']) : null
                    ];

                    $totalLat += $lat;
                    $totalLng += $lng;
                    $totalPoints++;
                }
            }

            // Process ODP/Clustering data
            foreach ($odps as $odp) {
                $lat = floatval($odp['latitude']);
                $lng = floatval($odp['longitude']);

                if ($lat != 0 && $lng != 0) {
                    $odpData[] = [
                        'id' => $odp['id_clustering'],
                        'name' => $odp['cluster_name'],
                        'type_option' => $odp['type_option'],
                        'address' => $odp['address'],
                        'server' => $odp['server_name'],
                        'total_ports' => $odp['number_of_ports'],
                        'connected_customers' => $odp['connected_customers'],
                        'available_ports' => ($odp['number_of_ports'] - $odp['connected_customers']),
                        'lat' => $lat,
                        'lng' => $lng,
                        'type' => 'odp'
                    ];

                    $totalLat += $lat;
                    $totalLng += $lng;
                    $totalPoints++;
                }
            }

            // Calculate center point from all points (customers + ODPs)
            $centerLat = $totalPoints > 0 ? $totalLat / $totalPoints : -6.200000;
            $centerLng = $totalPoints > 0 ? $totalLng / $totalPoints : 106.816666;

            // Calculate statistics
            $stats = [
                'total_customers' => count($mapData),
                'active_customers' => count(array_filter($mapData, function ($c) {
                    return $c['status'] == 'Aktif' || $c['status'] == 'Active';
                })),
                'inactive_customers' => count(array_filter($mapData, function ($c) {
                    return $c['status'] == 'Tidak Aktif' || $c['status'] == 'Inactive';
                })),
                'overdue_customers' => count(array_filter($mapData, function ($c) {
                    return $c['status'] == 'Terlambat' || $c['status'] == 'Overdue';
                }))
            ];

            $data = [
                'customers' => $mapData,
                'odps' => $odpData,
                'center_lat' => $centerLat,
                'center_lng' => $centerLng,
                'total_customers' => count($mapData),
                'total_odps' => count($odpData),
                'stats' => $stats
            ];

            return view('customer/map', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in showMaps: ' . $e->getMessage());

            // Return error view
            $stats = [
                'total_customers' => 0,
                'active_customers' => 0,
                'inactive_customers' => 0,
                'overdue_customers' => 0
            ];

            $data = [
                'customers' => [],
                'center_lat' => -6.200000,
                'center_lng' => 106.816666,
                'total_customers' => 0,
                'stats' => $stats,
                'error' => 'Terjadi kesalahan saat memuat data peta: ' . $e->getMessage()
            ];

            return view('customer/map', $data);
        }
    }
    /**
     * Display map of customer locations
     */
    public function mapCustomers()
    {
        try {
            log_message('info', 'MapCustomers method called - Start execution');

            // Query dengan support untuk format koordinat gabungan (coordinat) dan terpisah (latitude, longitude)
            $customers = $this->db->query("
                SELECT 
                    c.id_customers,
                    c.nama_pelanggan,
                    c.nomor_layanan,
                    c.address,
                    c.coordinat,
                    c.latitude,
                    c.longitude,
                    c.telepphone as phone,
                    c.status_tagihan,
                    c.tgl_tempo,
                    p.name as package_name,
                    p.bandwidth_profile,
                    p.price as package_price,
                    ls.name as server_name,
                    cl.latitude as cluster_lat,
                    cl.longitude as cluster_lng,
                    cl.name as cluster_name
                FROM customers c
                LEFT JOIN package_profiles p ON p.id = c.id_paket
                LEFT JOIN lokasi_server ls ON ls.id_lokasi = c.id_lokasi_server
                LEFT JOIN clustering cl ON cl.id_clustering = c.customer_clustering_id
                WHERE (
                    (c.coordinat IS NOT NULL AND c.coordinat != '' AND c.coordinat != '0,0')
                    OR 
                    (c.latitude IS NOT NULL AND c.longitude IS NOT NULL AND c.latitude != 0 AND c.longitude != 0)
                )
            ")->getResultArray();

            log_message('info', 'MapCustomers - Found ' . count($customers) . ' customers with coordinates');

            // Debug: Log sample data
            if (count($customers) > 0) {
                log_message('debug', 'MapCustomers - First customer sample: ' . json_encode($customers[0]));
            }

            // Debug: Check if we have any customers
            if (empty($customers)) {
                log_message('warning', 'MapCustomers - No customers found with valid coordinates');
                // Show a message to the user
                return redirect()->to('customers')->with('info', 'Tidak ada pelanggan dengan data koordinat lokasi. Silakan tambahkan koordinat pada data pelanggan.');
            }



            // Format customer data for map markers
            $mapData = [];
            foreach ($customers as $customer) {
                // Debug: Log raw customer data
                if (empty($mapData)) {
                    log_message('debug', 'First customer raw data: ' . json_encode($customer));
                }

                // Parse koordinat - support format gabungan "lat,lng" atau terpisah
                $latitude = 0;
                $longitude = 0;

                if (!empty($customer['coordinat']) && strpos($customer['coordinat'], ',') !== false) {
                    // Format gabungan: "-6.957800,110.035064"
                    $coords = explode(',', $customer['coordinat']);
                    $latitude = (float)trim($coords[0]);
                    $longitude = (float)trim($coords[1]);
                    log_message('debug', "Customer {$customer['nama_pelanggan']}: Parsed from coordinat field: {$latitude}, {$longitude}");
                } elseif (!empty($customer['latitude']) && !empty($customer['longitude'])) {
                    // Format terpisah
                    $latitude = (float)$customer['latitude'];
                    $longitude = (float)$customer['longitude'];
                    log_message('debug', "Customer {$customer['nama_pelanggan']}: Using separate lat/lng: {$latitude}, {$longitude}");
                } else {
                    log_message('warning', "Customer {$customer['nama_pelanggan']}: No valid coordinates found. coordinat='{$customer['coordinat']}', lat='{$customer['latitude']}', lng='{$customer['longitude']}'");
                }

                // Skip jika koordinat tidak valid
                if ($latitude == 0 && $longitude == 0) {
                    continue;
                }

                // Parse koordinat cluster/ODP jika ada
                $clusterLat = !empty($customer['cluster_lat']) ? (float)$customer['cluster_lat'] : null;
                $clusterLng = !empty($customer['cluster_lng']) ? (float)$customer['cluster_lng'] : null;

                // Determine marker color based on status
                $markerColor = 'green'; // default for active customers
                $statusLabel = 'Aktif';

                if ($customer['status_tagihan'] !== 'Lunas') {
                    // Check if overdue
                    if (!empty($customer['tgl_tempo'])) {
                        $tempoDate = new \DateTime($customer['tgl_tempo']);
                        $currentDate = new \DateTime();
                        if ($currentDate > $tempoDate) {
                            $markerColor = 'red'; // overdue
                            $statusLabel = 'Overdue';
                        } else {
                            $markerColor = 'orange'; // unpaid but not overdue yet
                            $statusLabel = 'Belum Bayar';
                        }
                    } else {
                        $markerColor = 'orange';
                        $statusLabel = 'Tidak Aktif';
                    }
                }

                $mapData[] = [
                    'id' => $customer['id_customers'],
                    'name' => $customer['nama_pelanggan'],
                    'service_number' => $customer['nomor_layanan'],
                    'address' => $customer['address'],
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'cluster_lat' => $clusterLat,
                    'cluster_lng' => $clusterLng,
                    'cluster_name' => $customer['cluster_name'] ?? null,
                    'phone' => $customer['phone'],
                    'status' => $statusLabel,
                    'status_color' => $markerColor,
                    'package_name' => $customer['package_name'],
                    'bandwidth' => $customer['bandwidth_profile'],
                    'price' => $customer['package_price'],
                    'server_name' => $customer['server_name'],
                    'due_date' => $customer['tgl_tempo']
                ];
            }

            // Debug: Log hasil parsing
            log_message('info', 'MapCustomers - Parsed ' . count($mapData) . ' valid customers from ' . count($customers) . ' total');
            if (count($mapData) > 0) {
                log_message('debug', 'MapCustomers - First parsed customer: ' . json_encode($mapData[0]));
            }

            // Get summary statistics
            $stats = [
                'total_customers' => count($mapData),
                'active_customers' => count(array_filter($mapData, fn($c) => $c['status_color'] === 'green')),
                'inactive_customers' => count(array_filter($mapData, fn($c) => $c['status_color'] === 'orange')),
                'overdue_customers' => count(array_filter($mapData, fn($c) => $c['status_color'] === 'red'))
            ];

            // Calculate center point for map
            $centerLat = 0;
            $centerLng = 0;
            if (!empty($mapData)) {
                $totalLat = array_sum(array_column($mapData, 'latitude'));
                $totalLng = array_sum(array_column($mapData, 'longitude'));
                $centerLat = $totalLat / count($mapData);
                $centerLng = $totalLng / count($mapData);
            } else {
                // Default to Indonesia center coordinates if no customer data
                $centerLat = -2.5489;
                $centerLng = 118.0149;
            }

            log_message('info', 'MapCustomers data prepared: ' . json_encode([
                'total_customers' => count($mapData),
                'stats' => $stats,
                'center' => [$centerLat, $centerLng]
            ]));

            log_message('info', 'MapCustomers - Returning view with ' . count($mapData) . ' customers');

            return view('customer/map', [
                'customers' => $mapData,
                'stats' => $stats,
                'centerLat' => $centerLat,
                'centerLng' => $centerLng
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in mapCustomers: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Return with detailed error information
            return redirect()->to('customers')->with('error', 'Terjadi kesalahan saat memuat peta: ' . $e->getMessage() . ' pada line ' . $e->getLine());
        }
    }

    /**
     * Manual PPPoE secret synchronization
     */
    public function syncPppoeSecret($customerId = null)
    {
        // Validate request (skip AJAX validation in CLI mode)
        if (!is_cli() && $this->request && !$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        if (!$customerId) {
            $errorData = [
                'success' => false,
                'message' => 'Customer ID is required'
            ];

            if (is_cli()) {
                return $errorData;
            }

            return $this->response->setJSON($errorData);
        }

        try {
            // Get customer data
            $customer = $this->cust->where('id_customers', $customerId)->first();

            if (!$customer) {
                $errorData = [
                    'success' => false,
                    'message' => 'Customer not found'
                ];

                if (is_cli()) {
                    return $errorData;
                }

                return $this->response->setJSON($errorData);
            }

            // Check if customer has PPPoE credentials
            if (empty($customer['pppoe_username']) || empty($customer['pppoe_password'])) {
                $errorData = [
                    'success' => false,
                    'message' => 'Customer does not have PPPoE credentials configured'
                ];

                if (is_cli()) {
                    return $errorData;
                }

                return $this->response->setJSON($errorData);
            }

            log_message('info', 'Manual PPPoE sync requested for customer: ' . $customerId);

            // Perform PPPoE synchronization to MikroTik
            $result = $this->handlePppoeSync($customerId, $customer, 'update');

            if ($result['success']) {
                $successData = [
                    'success' => true,
                    'message' => 'PPPoE secret synchronized successfully'
                ];

                if (is_cli()) {
                    return $successData;
                }

                return $this->response->setJSON($successData);
            } else {
                $errorData = [
                    'success' => false,
                    'message' => 'Failed to synchronize PPPoE secret: ' . $result['message']
                ];

                if (is_cli()) {
                    return $errorData;
                }

                return $this->response->setJSON($errorData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in manual PPPoE sync: ' . $e->getMessage());

            $errorData = [
                'success' => false,
                'message' => 'An error occurred during synchronization: ' . $e->getMessage()
            ];

            if (is_cli()) {
                return $errorData;
            }

            return $this->response->setJSON($errorData);
        }
    }

    /**
     * Debug method to test MikroTik PPPoE secret creation
     */
    public function debugMikrotikPppoe()
    {
        try {
            $serverLocationId = $this->request ? $this->request->getGet('server_id') : 12; // Default server ID or fallback

            log_message('info', 'Debug MikroTik PPPoE creation for server ID: ' . $serverLocationId);

            // Get MikroTik connection
            $mikrotikConnection = $this->getMikrotikConnection($serverLocationId);
            if (!$mikrotikConnection['success']) {
                $errorData = [
                    'success' => false,
                    'message' => 'Failed to connect to MikroTik: ' . $mikrotikConnection['message']
                ];

                if (is_cli()) {
                    return $errorData;
                }

                return $this->response->setJSON($errorData);
            }

            $mikrotikAPI = $mikrotikConnection['api'];

            // Test creating a sample PPPoE secret
            $testSecretData = [
                'name' => 'test_user_' . time(),
                'password' => 'test_password_123',
                'service' => 'pppoe',
                'profile' => 'default',
                'local-address' => '192.168.1.1',
                'remote-address' => '192.168.1.100',
                'comment' => 'Test PPPoE secret created by debug - ' . date('Y-m-d H:i:s'),
                'disabled' => false
            ];

            log_message('info', 'Creating test PPPoE secret: ' . json_encode($testSecretData));

            $result = $mikrotikAPI->addPPPSecret($testSecretData);

            if ($result) {
                $successData = [
                    'success' => true,
                    'message' => 'Test PPPoE secret created successfully in MikroTik',
                    'data' => $testSecretData
                ];

                if (is_cli()) {
                    return $successData;
                }

                return $this->response->setJSON($successData);
            } else {
                $errorData = [
                    'success' => false,
                    'message' => 'Failed to create test PPPoE secret in MikroTik'
                ];

                if (is_cli()) {
                    return $errorData;
                }

                return $this->response->setJSON($errorData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in debugMikrotikPppoe: ' . $e->getMessage());

            $errorData = [
                'success' => false,
                'message' => 'Debug error: ' . $e->getMessage()
            ];

            if (is_cli()) {
                return $errorData;
            }

            return $this->response->setJSON($errorData);
        }
    }
    /**
     * Debug MikroTik connection for troubleshooting
     */
    public function debugMikrotikConnection()
    {
        $serverId = $this->request->getGet('server_id') ?? 12; // Default to server ID 12

        // Get server details from database
        $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverId)->get()->getRow();

        if (!$serverLocation) {
            return $this->response->setStatusCode(404)->setBody('Server location not found');
        }

        $debugInfo = [
            'server_id' => $serverId,
            'server_name' => $serverLocation->name,
            'host' => $serverLocation->ip_router,
            'username' => $serverLocation->username,
            'port_api' => $serverLocation->port_api,
            'password_set' => !empty($serverLocation->password_router),
            'password_length' => strlen($serverLocation->password_router ?? ''),
            'connection_type' => strpos($serverLocation->ip_router, 'hostddns.us') !== false ? 'tunnel' : 'direct',
            'address' => $serverLocation->address ?? 'N/A'
        ];

        // Test connection
        $connectionResult = null;
        $errorDetails = '';
        try {
            $config = [
                'host' => $serverLocation->ip_router,
                'user' => $serverLocation->username ?? 'admin',
                'pass' => $serverLocation->password_router ?? '',
                'port' => (int)($serverLocation->port_api ?? 8728),
                'timeout' => 10,
                'attempts' => 2
            ];

            $mikrotikAPI = new \App\Libraries\MikrotikAPI($config);
            $connectionResult = $mikrotikAPI->testConnection();

            if ($connectionResult['success']) {
                // Try to get additional info
                try {
                    $systemResource = $mikrotikAPI->getSystemResource();
                    $connectionResult['system_info'] = $systemResource;
                } catch (\Exception $e) {
                    $connectionResult['system_info_error'] = $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $errorDetails = $e->getMessage();
            $connectionResult = [
                'success' => false,
                'message' => $errorDetails
            ];
        }

        // Generate HTML debug page
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug MikroTik Connection - ' . esc($serverLocation->name) . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .debug-section { margin-bottom: 2rem; }
        .status-success { color: #198754; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .code-block { background: #f8f9fa; padding: 1rem; border-radius: 0.375rem; border-left: 4px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="bx bx-test-tube me-2"></i>Debug MikroTik Connection</h4>
                        <small>' . date('Y-m-d H:i:s') . '</small>
                    </div>
                    <div class="card-body">
                        
                        <!-- Server Information -->
                        <div class="debug-section">
                            <h5><i class="bx bx-server me-2"></i>Server Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr><td><strong>ID:</strong></td><td>' . $debugInfo['server_id'] . '</td></tr>
                                        <tr><td><strong>Name:</strong></td><td>' . esc($debugInfo['server_name']) . '</td></tr>
                                        <tr><td><strong>Host:</strong></td><td><code>' . esc($debugInfo['host']) . '</code></td></tr>
                                        <tr><td><strong>Username:</strong></td><td><code>' . esc($debugInfo['username']) . '</code></td></tr>
                                        <tr><td><strong>API Port:</strong></td><td><code>' . $debugInfo['port_api'] . '</code></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr><td><strong>Address:</strong></td><td>' . esc($debugInfo['address']) . '</td></tr>
                                        <tr><td><strong>Password:</strong></td><td>' . ($debugInfo['password_set'] ? '<span class="text-success">Set (' . $debugInfo['password_length'] . ' chars)</span>' : '<span class="text-danger">Not Set</span>') . '</td></tr>
                                        <tr><td><strong>Connection Type:</strong></td><td><span class="badge bg-info">' . ucfirst($debugInfo['connection_type']) . '</span></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Connection Test -->
                        <div class="debug-section">
                            <h5><i class="bx bx-wifi me-2"></i>Connection Test</h5>';

        if ($connectionResult['success']) {
            $html .= '<div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i><strong>Connection Successful!</strong>
                        <p class="mb-0 mt-2">' . esc($connectionResult['message']) . '</p>';

            if (isset($connectionResult['identity'])) {
                $html .= '<p class="mb-0"><strong>Router Identity:</strong> ' . esc($connectionResult['identity']) . '</p>';
            }

            if (isset($connectionResult['system_info'])) {
                $info = $connectionResult['system_info'];
                $html .= '<div class="mt-2">
                            <strong>System Information:</strong><br>
                            <small>Board: ' . esc($info['board-name'] ?? 'N/A') . ' | Version: ' . esc($info['version'] ?? 'N/A') . ' | Architecture: ' . esc($info['architecture-name'] ?? 'N/A') . '</small>
                         </div>';
            }

            $html .= '</div>';
        } else {
            $html .= '<div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i><strong>Connection Failed!</strong>
                        <p class="mb-0 mt-2">' . esc($connectionResult['message']) . '</p>
                      </div>';
        }

        // Add troubleshooting section
        $html .= '</div>

                        <!-- Troubleshooting Guide -->
                        <div class="debug-section">
                            <h5><i class="bx bx-wrench me-2"></i>Troubleshooting Guide</h5>';

        if ($debugInfo['connection_type'] === 'tunnel') {
            $html .= '<div class="alert alert-info">
                        <h6><i class="bx bx-info-circle me-1"></i>Tunnel Connection Troubleshooting</h6>
                        <ol>
                            <li>Check if the tunnel service (hostddns.us) is running</li>
                            <li>Verify router internet connection to tunnel server</li>
                            <li>Check port forwarding configuration (8211 → 8728)</li>
                            <li>Ensure MikroTik API service is enabled</li>
                            <li>Try restarting the router if needed</li>
                            <li>Check if router firewall allows API connections</li>
                        </ol>
                      </div>';
        } else {
            $html .= '<div class="alert alert-info">
                        <h6><i class="bx bx-info-circle me-1"></i>Direct Connection Troubleshooting</h6>
                        <ol>
                            <li>Ensure router is reachable from this network</li>
                            <li>Check firewall that might block API port</li>
                            <li>Verify username and password</li>
                            <li>Ensure API service is enabled in router</li>
                            <li>Try different timeout settings</li>
                        </ol>
                      </div>';
        }

        $html .= '</div>

                        <!-- Quick Tests -->
                        <div class="debug-section">
                            <h5><i class="bx bx-terminal me-2"></i>Quick Network Tests</h5>
                            <div class="code-block">
                                <strong>Commands to test from router:</strong><br>
                                <code>/ip service print</code> - Check if API service is enabled<br>
                                <code>/user print</code> - Verify user exists<br>
                                <code>/system identity print</code> - Check router identity<br>
                                <code>/interface print</code> - List interfaces<br>';

        if ($debugInfo['connection_type'] === 'tunnel') {
            $html .= '<br><strong>Tunnel specific:</strong><br>
                      <code>/ip cloud print</code> - Check cloud DDNS status<br>
                      <code>/ip firewall nat print</code> - Check NAT rules for port forwarding';
        }

        $html .= '</div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center">
                            <button class="btn btn-primary me-2" onclick="location.reload()">
                                <i class="bx bx-refresh me-1"></i>Retry Test
                            </button>
                            <button class="btn btn-secondary" onclick="window.close()">
                                <i class="bx bx-x me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

        return $this->response->setBody($html);
    }

    /**
     * Get list of PPPoE usernames that are already used by customers
     */
    private function getUsedPPPoEUsernames()
    {
        try {
            $result = $this->cust->select('pppoe_username')
                ->where('pppoe_username IS NOT NULL')
                ->where('pppoe_username !=', '')
                ->findAll();

            $usernames = [];
            foreach ($result as $customer) {
                if (!empty($customer['pppoe_username'])) {
                    $usernames[] = $customer['pppoe_username'];
                }
            }

            return $usernames;
        } catch (\Exception $e) {
            log_message('error', 'Error getting used PPPoE usernames: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get MikroTik API connection for a specific server location
     */
    private function getMikrotikConnection($serverLocationId)
    {
        try {
            // Get server location details
            $serverLocation = $this->db->table('lokasi_server')->where('id_lokasi', $serverLocationId)->get()->getRow();

            if (!$serverLocation) {
                return [
                    'success' => false,
                    'message' => 'Server location not found with ID: ' . $serverLocationId
                ];
            }

            // Prepare MikroTik API configuration
            // Detect VPN connection and use longer timeout
            $isVPN = strpos($serverLocation->ip_router, 'hostddns.us') !== false ||
                strpos($serverLocation->ip_router, 'tunnel.web.id') !== false;

            $config = [
                'host' => $serverLocation->ip_router,
                'user' => $serverLocation->username ?? 'admin',
                'pass' => $serverLocation->password_router ?? '',
                'port' => (int)($serverLocation->port_api ?? 8728),
                'timeout' => $isVPN ? 120 : 30, // 2 minutes for VPN, 30 seconds for direct
                'attempts' => 3
            ];

            // Create MikroTik API connection
            $mikrotikAPI = new \App\Libraries\MikrotikAPI($config);

            // Test connection
            $connectionTest = $mikrotikAPI->testConnection();
            if (!$connectionTest['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to MikroTik: ' . $connectionTest['message'],
                    'api' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'MikroTik connection established successfully',
                'api' => $mikrotikAPI
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error establishing MikroTik connection: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error connecting to MikroTik: ' . $e->getMessage(),
                'api' => null
            ];
        }
    }

    /**
     * Get WhatsApp notification settings
     */
    private function getWhatsAppSettings()
    {
        try {
            // Get WhatsApp notification settings from database
            $notifModel = new \App\Models\WhatsappNotifSettingModel();
            $settings = $notifModel->orderBy('id', 'DESC')->first();

            if (!$settings) {
                log_message('info', 'No WhatsApp notification settings found in database');
                return [
                    'on_customer_created' => false,
                    'on_invoice_created' => false,
                    'on_payment_received' => false,
                    'on_isolated' => false,
                    'notif_invoice' => false,
                    'notif_payment' => false,
                    'notif_reminder' => false,
                    'notif_other' => false
                ];
            }

            return [
                'on_customer_created' => (bool)($settings['notif_other'] ?? false),
                'on_invoice_created' => (bool)($settings['notif_invoice'] ?? false),
                'on_payment_received' => (bool)($settings['notif_payment'] ?? false),
                'on_isolated' => (bool)($settings['notif_other'] ?? false),
                'notif_invoice' => (bool)($settings['notif_invoice'] ?? false),
                'notif_payment' => (bool)($settings['notif_payment'] ?? false),
                'notif_reminder' => (bool)($settings['notif_reminder'] ?? false),
                'notif_other' => (bool)($settings['notif_other'] ?? false)
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting WhatsApp settings: ' . $e->getMessage());
            return [
                'on_customer_created' => false,
                'on_invoice_created' => false,
                'on_payment_received' => false,
                'on_isolated' => false,
                'notif_invoice' => false,
                'notif_payment' => false,
                'notif_reminder' => false,
                'notif_other' => false
            ];
        }
    }

    /**
     * Get cities by province ID
     */
    public function getCities($provinceId)
    {
        try {
            // Mock data for Indonesian cities by province
            // In a real application, you would fetch this from a database
            $cities = $this->getMockCities($provinceId);

            return $this->response->setJSON([
                'success' => true,
                'cities' => $cities
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading cities: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get districts by city ID
     */
    public function getDistricts($cityId)
    {
        try {
            // Mock data for Indonesian districts by city
            $districts = $this->getMockDistricts($cityId);

            return $this->response->setJSON([
                'success' => true,
                'districts' => $districts
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading districts: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get villages by district ID
     */
    public function getVillages($districtId)
    {
        try {
            // Mock data for Indonesian villages by district
            $villages = $this->getMockVillages($districtId);

            return $this->response->setJSON([
                'success' => true,
                'villages' => $villages
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading villages: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Get default regional settings from application settings
     */
    private function getDefaultRegionalSettings()
    {
        try {
            $settingModel = new \App\Models\ApplicationSettingModel();
            $settings = $settingModel->getSettings();

            return [
                'province_id' => $settings['province_id'] ?? null,
                'city_id' => $settings['city_id'] ?? null,
                'district_id' => $settings['district_id'] ?? null,
                'village_id' => $settings['village_id'] ?? null,
                'default_coordinat' => $settings['default_coordinat'] ?? '-7.216493,107.901878',
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting default regional settings: ' . $e->getMessage());
            return [
                'province_id' => null,
                'city_id' => null,
                'district_id' => null,
                'village_id' => null,
                'default_coordinat' => '-7.216493,107.901878',
            ];
        }
    }

    /**
     * Get active banks for payment dropdown
     */
    public function getBankOptions()
    {
        $bankModel = new \App\Models\BankModel();
        $banks = $bankModel->where('is_active', 1)->orderBy('bank_name', 'ASC')->findAll();
        $options = [];
        foreach ($banks as $bank) {
            $options[] = [
                'id' => $bank['id'],
                'name' => $bank['bank_name'],
                'account_number' => $bank['account_number'],
                'account_holder' => $bank['account_holder'],
            ];
        }
        return $this->response->setJSON($options);
    }
}
