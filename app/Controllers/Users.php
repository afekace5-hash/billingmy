<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Users extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = model('UserModel');
    }

    public function index()
    {
        $data = [
            'title' => 'Manage Users'
        ];

        return view('users/index', $data);
    }

    public function data()
    {
        try {
            $request = \Config\Services::request();
            $db = \Config\Database::connect();

            $draw = $request->getPost('draw') ?? 1;
            $start = $request->getPost('start') ?? 0;
            $length = $request->getPost('length') ?? 10;
            $searchValue = $request->getPost('search')['value'] ?? '';
            $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 1;
            $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

            $columns = ['', 'id_user', 'id_user', 'info_user', 'name_user', 'email_user', 'id_user', 'id_user', 'id_user', 'id_user'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id_user';

            // If orderColumn is empty (action column), default to id_user
            if (empty($orderColumn)) {
                $orderColumn = 'id_user';
            }

            // Base query
            $builder = $db->table('users');
            $builder->select('id_user as id, id_user as code, info_user as role, name_user as name, email_user as email, "" as phone, "" as branches, NOW() as created_at, NOW() as updated_at');

            // Search
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('name_user', $searchValue)
                    ->orLike('email_user', $searchValue)
                    ->orLike('info_user', $searchValue)
                    ->groupEnd();
            }

            // Count total before pagination
            $totalRecords = $builder->countAllResults(false);

            // Apply ordering and pagination
            $builder->orderBy($orderColumn, $orderDir);
            $builder->limit($length, $start);

            $users = $builder->get()->getResultArray();

            // Format data
            foreach ($users as &$user) {
                $user['join_at'] = $user['created_at'] ? date('d M Y H:i', strtotime($user['created_at'])) : '-';
                $user['last_update'] = $user['updated_at'] ? $this->formatTimeAgo($user['updated_at']) : '-';
                $user['branches'] = $user['branches'] ?? '-';
            }

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Users data error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    private function formatTimeAgo($datetime)
    {
        $now = new \DateTime();
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->y > 0) {
            return $diff->y . ' tahun yang lalu';
        } elseif ($diff->m > 0) {
            return $diff->m . ' bulan yang lalu';
        } elseif ($diff->d > 0) {
            return $diff->d . ' hari yang lalu';
        } elseif ($diff->h > 0) {
            return $diff->h . ' jam yang lalu';
        } elseif ($diff->i > 0) {
            return $diff->i . ' menit yang lalu';
        } else {
            return 'baru saja';
        }
    }

    public function create()
    {
        $data = [
            'title' => 'New User'
        ];

        return view('users/create', $data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'name' => 'required',
            'email' => 'required|valid_email|is_unique[users.email]',
            'code' => 'required|is_unique[users.code]',
            'role' => 'required',
            'phone' => 'required',
            'password' => 'required|min_length[6]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'code' => $this->request->getPost('code'),
            'role' => $this->request->getPost('role'),
            'phone' => $this->request->getPost('phone'),
            'branches' => $this->request->getPost('branches'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create user'
        ]);
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update($id)
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'name' => 'required',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'code' => "required|is_unique[users.code,id,{$id}]",
            'role' => 'required',
            'phone' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'code' => $this->request->getPost('code'),
            'role' => $this->request->getPost('role'),
            'phone' => $this->request->getPost('phone'),
            'branches' => $this->request->getPost('branches'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update user'
        ]);
    }

    public function delete($id)
    {
        if ($this->userModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete user'
        ]);
    }
}
