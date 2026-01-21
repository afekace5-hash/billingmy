<?php

namespace App\Models;

use CodeIgniter\Model;

class BroadcastModel extends Model
{
    protected $table = 'broadcasts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'type',
        'branch',
        'area',
        'title',
        'message',
        'image',
        'scheduled_at',
        'target_users',
        'total_users',
        'status',
        'sent_count',
        'created_by',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'type' => 'required',
        'title' => 'required|min_length[3]|max_length[255]',
        'message' => 'required|min_length[10]',
        'scheduled_at' => 'required',
        'target_users' => 'required'
    ];

    protected $validationMessages = [
        'type' => [
            'required' => 'Type is required'
        ],
        'title' => [
            'required' => 'Title is required',
            'min_length' => 'Title must be at least 3 characters',
            'max_length' => 'Title cannot exceed 255 characters'
        ],
        'message' => [
            'required' => 'Message is required',
            'min_length' => 'Message must be at least 10 characters'
        ],
        'scheduled_at' => [
            'required' => 'Schedule time is required'
        ],
        'target_users' => [
            'required' => 'Target users is required'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get all broadcasts with pagination
     */
    public function getBroadcasts($limit = 10, $offset = 0)
    {
        return $this->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Get scheduled broadcasts
     */
    public function getScheduledBroadcasts()
    {
        return $this->where('status', 'scheduled')
            ->where('scheduled_at <=', date('Y-m-d H:i:s'))
            ->findAll();
    }

    /**
     * Update broadcast status
     */
    public function updateStatus($id, $status, $sentCount = null)
    {
        $data = ['status' => $status];

        if ($sentCount !== null) {
            $data['sent_count'] = $sentCount;
        }

        return $this->update($id, $data);
    }
}
