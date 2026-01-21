<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\CustomerModel;
use CodeIgniter\Files\File;

class Ticket extends BaseController
{
    protected $ticketModel;
    protected $customerModel;
    protected $session;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->customerModel = new CustomerModel();
        $this->session = session();
    }

    public function index()
    {
        // Get tickets with customer information
        $tickets = $this->ticketModel->getTicketsWithCustomers();

        // Get ticket statistics
        $stats = $this->ticketModel->getTicketStatistics();

        $data = [
            'title' => 'Sistem Tiket Bantuan ISP',
            'tickets' => $tickets,
            'stats' => $stats
        ];
        return view('ticket/index', $data);
    }

    public function data()
    {
        // Disable ALL output for clean JSON response
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $request = \Config\Services::request();
        $db = \Config\Database::connect();

        // DataTables parameters
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $draw = $request->getPost('draw') ?? 1;
        $searchValue = $request->getPost('search')['value'] ?? '';

        log_message('debug', 'Ticket data() called - start: ' . $start . ', length: ' . $length . ', draw: ' . $draw);

        // Base query
        $builder = $db->table('tickets t');
        $builder->select('t.*, c.nama_pelanggan as customer_name, c.email as customer_email, 
                         c.telepphone as customer_phone, c.nomor_layanan, 
                         l.name as branch');
        $builder->join('customers c', 'c.id_customers = t.customer_id', 'left');
        $builder->join('lokasi_server l', 'l.id_lokasi = c.id_lokasi_server', 'left');

        // Search filter
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('t.id', $searchValue)
                ->orLike('t.subject', $searchValue)
                ->orLike('c.nama_pelanggan', $searchValue)
                ->orLike('t.category', $searchValue)
                ->groupEnd();
        }

        // Total records
        $totalRecords = $builder->countAllResults(false);

        // Ordering
        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 1;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';
        $columns = ['', 'id', 'status', 'category', 'subject', 'branch', 'package', 'created_at', '', '', '', '', ''];
        if (isset($columns[$orderColumnIndex])) {
            $builder->orderBy($columns[$orderColumnIndex], $orderDir);
        }

        // Pagination
        $builder->limit($length, $start);

        $tickets = $builder->get()->getResultArray();

        log_message('debug', 'Tickets found: ' . count($tickets));
        log_message('debug', 'First ticket: ' . json_encode($tickets[0] ?? []));

        // Format data for DataTables
        $data = [];
        foreach ($tickets as $ticket) {
            // Calculate duration
            $created = new \DateTime($ticket['created_at']);
            $now = new \DateTime();
            $diff = $now->diff($created);
            $duration = '';
            if ($diff->d > 0) {
                $duration = $diff->d . ' Hari';
            } elseif ($diff->h > 0) {
                $duration = $diff->h . ' Jam ' . $diff->i . ' Menit';
            } else {
                $duration = $diff->i . ' Menit';
            }

            $data[] = [
                'id' => $ticket['id'],
                'status' => $ticket['status'] ?? 'Open',
                'category' => $ticket['category'] ?? 'PERBAIKAN',
                'subject' => $ticket['subject'] ?? 'internet mati',
                'branch' => $ticket['branch'] ?? 'N/A',
                'package' => 'Paket Internet',
                'layanan' => 'Paket Internet',
                'speed' => '10Mbps',
                'installed_at' => date('d F Y H:i', strtotime($ticket['created_at'])),
                'installed_note' => 'setahun yang lalu',
                'technician' => 'Jo Admin',
                'technician_name' => 'Jo Admin',
                'technician_phone' => 'BINTANG NET<br>+6295048613',
                'customer' => $ticket['customer_name'] ?? 'Customer Demo',
                'customer_name' => $ticket['customer_name'] ?? 'Customer Demo',
                'customer_email' => $ticket['customer_email'] ?? '',
                'customer_phone' => $ticket['customer_phone'] ?? '',
                'odp' => 'ODP 03-V4SX-DEPAN WARUNG MADURA',
                'address' => 'lengkong',
                'duration' => $duration
            ];
        }

        log_message('debug', 'Returning data count: ' . count($data));
        log_message('debug', 'Response: ' . json_encode(['draw' => intval($draw), 'recordsTotal' => $totalRecords, 'recordsFiltered' => $totalRecords, 'dataCount' => count($data)]));

        // Set proper JSON response headers
        $this->response->setContentType('application/json');

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    public function filter()
    {
        $status = $this->request->getGet('status');
        $priority = $this->request->getGet('priority');
        $category = $this->request->getGet('category');
        $search = $this->request->getGet('search');

        $tickets = $this->ticketModel->getFilteredTickets($status, $priority, $category, $search);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $tickets
        ]);
    }
    public function create()
    {
        // Get all customers
        $customers = $this->customerModel->select('id_customers, nama_pelanggan, nomor_layanan, email, telepphone as no_wa')
            ->orderBy('nama_pelanggan', 'ASC')
            ->findAll();
        $data = [
            'title' => 'Buat Tiket Bantuan Baru',
            'validation' => \Config\Services::validation(),
            'customers' => $customers
        ];
        return view('ticket/create', $data);
    }

    public function store()
    {
        // Log incoming data
        log_message('debug', 'Ticket store POST data: ' . json_encode($this->request->getPost()));

        // Validate input
        if (!$this->validate($this->ticketModel->validationRules, $this->ticketModel->validationMessages)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Ticket validation failed: ' . json_encode($errors));
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $file = $this->request->getFile('attachment');
        $fileName = '';

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return redirect()->back()->withInput()->with('error', 'Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, PDF, dan TXT yang diperbolehkan.');
            }

            if ($file->getSize() > 2048000) { // 2MB
                return redirect()->back()->withInput()->with('error', 'Ukuran file terlalu besar. Maksimal 2MB.');
            }

            $fileName = $file->getRandomName();
            $file->move('uploads/tickets', $fileName);
        }

        // Generate ticket number
        $ticketNumber = 'TKT-' . date('Ymd') . '-' . str_pad($this->ticketModel->countAllResults() + 1, 4, '0', STR_PAD_LEFT);

        $data = [
            'ticket_number' => $ticketNumber,
            'user_id' => 1, // Default admin user
            'customer_id' => $this->request->getPost('customer_id'),
            'subject' => $this->request->getPost('subject'),
            'description' => $this->request->getPost('description'),
            'status' => 'open', // Always start as open
            'priority' => $this->request->getPost('priority'),
            'category' => $this->request->getPost('category'),
            'attachment' => $fileName,
            'assigned_to' => null
        ];

        log_message('debug', 'Ticket data to insert: ' . json_encode($data));

        try {
            $insertId = $this->ticketModel->insert($data);

            if ($insertId) {
                log_message('info', 'Ticket created successfully with ID: ' . $insertId);
                return redirect()->to('/ticket')->with('success', 'Tiket berhasil dibuat dengan nomor: ' . $ticketNumber);
            } else {
                // Get database errors
                $errors = $this->ticketModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Gagal membuat tiket. Silakan coba lagi.';
                log_message('error', 'Ticket insert failed: ' . $errorMsg);
                log_message('error', 'Model errors: ' . json_encode($errors));
                return redirect()->back()->withInput()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            log_message('error', 'Ticket creation exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $ticket = $this->ticketModel->getTicketWithCustomer($id);

        if (!$ticket) {
            return redirect()->to('/ticket')->with('error', 'Tiket tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Tiket #' . $ticket['ticket_number'],
            'ticket' => $ticket
        ];

        return view('ticket/show', $data);
    }

    public function updateStatus($id)
    {
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        $newStatus = $this->request->getPost('status');
        $allowedStatuses = ['open', 'in_progress', 'resolved', 'closed'];

        if (!in_array($newStatus, $allowedStatuses)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status tidak valid'
            ]);
        }

        $updateData = [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($newStatus === 'resolved' || $newStatus === 'closed') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }

        if ($this->ticketModel->update($id, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status tiket berhasil diperbarui',
                'new_status' => $newStatus
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memperbarui status tiket'
            ]);
        }
    }

    public function update($id)
    {
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            return redirect()->to('/ticket')->with('error', 'Tiket tidak ditemukan');
        }

        $this->ticketModel->update($id, [
            'status' => $this->request->getPost('status'),
            'priority' => $this->request->getPost('priority') ?? $ticket['priority'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/ticket')->with('success', 'Tiket berhasil diperbarui');
    }

    public function assign($id)
    {
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        $assignedTo = $this->request->getPost('assigned_to');

        if ($this->ticketModel->update($id, ['assigned_to' => $assignedTo])) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Tiket berhasil ditugaskan'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menugaskan tiket'
            ]);
        }
    }

    public function delete($id)
    {
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        if ($this->ticketModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tiket berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus tiket'
            ]);
        }
    }
}
