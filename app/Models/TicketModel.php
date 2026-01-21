<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ticket_number',
        'user_id',
        'customer_id',
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'attachment',
        'assigned_to',
        'resolved_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'subject' => 'required|min_length[3]|max_length[255]',
        'description' => 'required|min_length[10]',
        'priority' => 'required|in_list[low,medium,high,urgent]',
        'category' => 'required|in_list[teknis,billing,layanan,instalasi,pemasangan,gangguan,lainnya]',
        'customer_id' => 'required|numeric'
    ];

    protected $validationMessages = [
        'subject' => [
            'required' => 'Judul tiket harus diisi',
            'min_length' => 'Judul tiket minimal 3 karakter',
            'max_length' => 'Judul tiket maksimal 255 karakter'
        ],
        'description' => [
            'required' => 'Deskripsi tiket harus diisi',
            'min_length' => 'Deskripsi tiket minimal 10 karakter'
        ],
        'priority' => [
            'required' => 'Prioritas tiket harus diisi',
            'in_list' => 'Prioritas tiket tidak valid'
        ],
        'category' => [
            'required' => 'Kategori tiket harus diisi',
            'in_list' => 'Kategori tiket tidak valid'
        ],
        'customer_id' => [
            'required' => 'Pelanggan harus dipilih',
            'numeric' => 'ID pelanggan tidak valid'
        ]
    ];

    public function getTicketsWithCustomers()
    {
        return $this->db->table($this->table)
            ->select('tickets.*, customers.nama_pelanggan, customers.nomor_layanan, customers.email, customers.telepphone as no_wa')
            ->join('customers', 'customers.id_customers = tickets.customer_id', 'left')
            ->orderBy('tickets.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getTicketWithCustomer($id)
    {
        return $this->db->table($this->table)
            ->select('tickets.*, customers.nama_pelanggan, customers.nomor_layanan, customers.email, customers.telepphone as no_wa, customers.address as alamat')
            ->join('customers', 'customers.id_customers = tickets.customer_id', 'left')
            ->where('tickets.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getFilteredTickets($status = null, $priority = null, $category = null, $search = null)
    {
        $builder = $this->db->table($this->table)
            ->select('tickets.*, customers.nama_pelanggan, customers.nomor_layanan')
            ->join('customers', 'customers.id_customers = tickets.customer_id', 'left');

        if ($status) {
            $builder->where('tickets.status', $status);
        }

        if ($priority) {
            $builder->where('tickets.priority', $priority);
        }

        if ($category) {
            $builder->where('tickets.category', $category);
        }

        if ($search) {
            $builder->groupStart()
                ->like('tickets.subject', $search)
                ->orLike('tickets.description', $search)
                ->orLike('customers.nama_pelanggan', $search)
                ->orLike('tickets.ticket_number', $search)
                ->groupEnd();
        }

        return $builder->orderBy('tickets.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getTicketStatistics()
    {
        // Since we're using hard delete, we don't need to check deleted_at
        $total = $this->countAllResults();

        // Reset the model for each query to avoid conflicts
        $open = $this->where('status', 'open')->countAllResults();
        $this->resetQuery();

        $inProgress = $this->where('status', 'in_progress')->countAllResults();
        $this->resetQuery();

        $resolved = $this->where('status', 'resolved')->countAllResults();
        $this->resetQuery();

        $closed = $this->where('status', 'closed')->countAllResults();
        $this->resetQuery();

        $highPriority = $this->groupStart()
            ->where('priority', 'urgent')
            ->orWhere('priority', 'high')
            ->groupEnd()
            ->countAllResults();

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'closed' => $closed,
            'high_priority' => $highPriority
        ];
    }

    public function getTicketsByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getOpenTickets()
    {
        return $this->whereIn('status', ['open', 'in_progress'])
            ->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
