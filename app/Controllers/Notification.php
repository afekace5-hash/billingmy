<?php

namespace App\Controllers;

class Notification extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = model('NotificationModel');
    }

    public function index()
    {
        $data = [
            'title' => 'Notifications'
        ];

        return view('notification/index', $data);
    }

    public function getData()
    {
        try {
            $request = service('request');
            $userId = session()->get('id_user');

            // Support both GET and POST for DataTables
            $draw = intval($request->getVar('draw'));
            $start = intval($request->getVar('start'));
            $length = intval($request->getVar('length'));
            $search = $request->getVar('search');
            $searchValue = is_array($search) ? ($search['value'] ?? '') : '';

            $builder = $this->notificationModel->builder();
            $builder->select('notifications.*');
            $builder->where('user_id', $userId);
            $builder->orWhere('user_id', null); // Global notifications

            if ($searchValue) {
                $builder->groupStart();
                $builder->like('title', $searchValue);
                $builder->orLike('message', $searchValue);
                $builder->groupEnd();
            }

            $totalRecords = $builder->countAllResults(false);

            $data = $builder->orderBy('created_at', 'DESC')
                ->limit($length, $start)
                ->get()
                ->getResultArray();

            $result = [];
            foreach ($data as $row) {
                $isReadBadge = $row['is_read'] ? '<span class="badge bg-secondary">Read</span>' : '<span class="badge bg-primary">Unread</span>';

                $action = '<div class="btn-group">';
                if (!$row['is_read']) {
                    $action .= '<button type="button" class="btn btn-sm btn-success markAsRead" data-id="' . $row['id'] . '" title="Mark as Read"><i class="bx bx-check"></i></button>';
                }
                $action .= '<button type="button" class="btn btn-sm btn-danger deleteNotification" data-id="' . $row['id'] . '" title="Delete"><i class="bx bx-trash"></i></button>';
                $action .= '</div>';

                $result[] = [
                    'action' => $action,
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'status' => $isReadBadge,
                    'created_at' => date('d M Y H:i', strtotime($row['created_at']))
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Notification getData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function markAsRead($id)
    {
        try {
            $userId = session()->get('id_user');

            $notification = $this->notificationModel->find($id);
            if (!$notification) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ]);
            }

            // Check ownership
            if ($notification['user_id'] && $notification['user_id'] != $userId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ]);
            }

            $this->notificationModel->update($id, ['is_read' => 1]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $userId = session()->get('id_user');

            $notification = $this->notificationModel->find($id);
            if (!$notification) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ]);
            }

            // Check ownership
            if ($notification['user_id'] && $notification['user_id'] != $userId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ]);
            }

            $this->notificationModel->delete($id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notification deleted'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
