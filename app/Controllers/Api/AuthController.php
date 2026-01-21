<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Helpers\JWTHelper;

class AuthController extends BaseController
{
    protected $customerModel;
    protected $validation;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->validation = \Config\Services::validation();
        helper('jwt');
    }

    /**
     * Customer login
     * POST /api/auth/login
     * Body: { "login": "nomor_layanan or email", "password": "password" }
     */
    public function login()
    {
        $rules = [
            'login' => 'required',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $login = $this->request->getPost('login');
        $password = $this->request->getPost('password');

        // Find customer by nomor_layanan or email
        $customer = $this->customerModel
            ->where('nomor_layanan', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nomor layanan atau email tidak ditemukan'
            ])->setStatusCode(401);
        }

        // Check if account is activated
        if (!$customer['is_activated']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akun belum diaktivasi. Silakan aktivasi akun Anda terlebih dahulu.',
                'requires_activation' => true,
                'activation_token' => $customer['activation_token']
            ])->setStatusCode(403);
        }

        // Verify password
        if (!password_verify($password, $customer['password_hash'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password salah'
            ])->setStatusCode(401);
        }

        // Generate JWT token
        $token = JWTHelper::generateToken($customer);

        // Update last login and api token
        $this->customerModel->update($customer['id_customers'], [
            'api_token' => $token,
            'last_login' => date('Y-m-d H:i:s'),
            'device_info' => json_encode([
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'ip_address' => $this->request->getIPAddress(),
                'login_time' => date('Y-m-d H:i:s')
            ])
        ]);

        // Get customer with package info
        $customerData = $this->getCustomerWithPackage($customer['id_customers']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 2592000, // 30 days
                'customer' => $customerData
            ]
        ]);
    }

    /**
     * Set password for first time activation
     * POST /api/auth/set-password
     * Body: { "activation_token": "token", "password": "newpassword", "password_confirm": "newpassword" }
     */
    public function setPassword()
    {
        $rules = [
            'activation_token' => 'required',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $activationToken = $this->request->getPost('activation_token');
        $password = $this->request->getPost('password');

        // Find customer by activation token
        $customer = $this->customerModel->where('activation_token', $activationToken)->first();

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token aktivasi tidak valid'
            ])->setStatusCode(400);
        }

        // Update password and activate account
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $this->customerModel->update($customer['id_customers'], [
            'password_hash' => $passwordHash,
            'is_activated' => true,
            'activation_token' => null
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password berhasil dibuat. Silakan login dengan nomor layanan dan password Anda.'
        ]);
    }

    /**
     * Change password (requires authentication)
     * POST /api/auth/change-password
     * Body: { "old_password": "current", "new_password": "new", "new_password_confirm": "new" }
     */
    public function changePassword()
    {
        $customerId = $this->request->customerId;

        $rules = [
            'old_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'new_password_confirm' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $customer = $this->customerModel->find($customerId);

        // Verify old password
        if (!password_verify($this->request->getPost('old_password'), $customer['password_hash'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password lama tidak sesuai'
            ])->setStatusCode(400);
        }

        // Update password
        $newPasswordHash = password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT);

        $this->customerModel->update($customerId, [
            'password_hash' => $newPasswordHash
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    /**
     * Request password reset (send OTP)
     * POST /api/auth/forgot-password
     * Body: { "nomor_layanan": "DFH123" }
     */
    public function forgotPassword()
    {
        $rules = ['nomor_layanan' => 'required'];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nomor layanan wajib diisi'
            ])->setStatusCode(400);
        }

        $nomorLayanan = $this->request->getPost('nomor_layanan');
        $customer = $this->customerModel->where('nomor_layanan', $nomorLayanan)->first();

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nomor layanan tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Generate OTP and reset token
        $otp = JWTHelper::generateOTP();
        $resetToken = JWTHelper::generateRandomToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Save reset token
        $this->customerModel->update($customer['id_customers'], [
            'password_reset_token' => $resetToken,
            'password_reset_expires' => $expiresAt
        ]);

        // TODO: Send OTP via WhatsApp
        // For now, return OTP in response (remove this in production!)

        log_message('info', "Password reset OTP for {$nomorLayanan}: {$otp}");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke WhatsApp Anda',
            'data' => [
                'reset_token' => $resetToken,
                'expires_at' => $expiresAt,
                // Remove this in production:
                'otp_debug' => $otp,
                'phone' => $customer['telepphone']
            ]
        ]);
    }

    /**
     * Verify OTP and reset password
     * POST /api/auth/reset-password
     * Body: { "reset_token": "token", "otp": "123456", "new_password": "newpass", "new_password_confirm": "newpass" }
     */
    public function resetPassword()
    {
        $rules = [
            'reset_token' => 'required',
            'new_password' => 'required|min_length[6]',
            'new_password_confirm' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $resetToken = $this->request->getPost('reset_token');
        $newPassword = $this->request->getPost('new_password');

        // Find customer by reset token
        $customer = $this->customerModel
            ->where('password_reset_token', $resetToken)
            ->where('password_reset_expires >', date('Y-m-d H:i:s'))
            ->first();

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token reset tidak valid atau sudah kadaluarsa'
            ])->setStatusCode(400);
        }

        // Update password
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->customerModel->update($customer['id_customers'], [
            'password_hash' => $passwordHash,
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login dengan password baru Anda.'
        ]);
    }

    /**
     * Logout
     * POST /api/auth/logout
     */
    public function logout()
    {
        $customerId = $this->request->customerId;

        // Clear api token and fcm token
        $this->customerModel->update($customerId, [
            'api_token' => null,
            'fcm_token' => null
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Register FCM token for push notifications
     * POST /api/auth/register-fcm
     * Body: { "fcm_token": "firebase_token" }
     */
    public function registerFCM()
    {
        $customerId = $this->request->customerId;
        $fcmToken = $this->request->getPost('fcm_token');

        if (empty($fcmToken)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'FCM token wajib diisi'
            ])->setStatusCode(400);
        }

        $this->customerModel->update($customerId, [
            'fcm_token' => $fcmToken
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'FCM token berhasil didaftarkan'
        ]);
    }

    /**
     * Helper: Get customer with package info
     */
    private function getCustomerWithPackage($customerId)
    {
        $db = \Config\Database::connect();

        $customer = $db->table('customers c')
            ->select('c.id_customers, c.nomor_layanan, c.nama_pelanggan, c.email, c.telepphone, 
                      c.address, c.status_tagihan, c.tgl_tempo, c.tgl_pasang,
                      p.name as package_name, p.bandwidth_profile, p.price as package_price,
                      ls.name as server_name')
            ->join('package_profiles p', 'p.id = c.id_paket', 'left')
            ->join('lokasi_server ls', 'ls.id_lokasi = c.id_lokasi_server', 'left')
            ->where('c.id_customers', $customerId)
            ->get()
            ->getRowArray();

        // Remove sensitive data
        unset($customer['password_hash'], $customer['api_token']);

        return $customer;
    }
}
