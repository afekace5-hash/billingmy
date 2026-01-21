<?php

namespace App\Controllers;

use App\Models\BillingLinkModel;
use App\Models\CustomerModel;
use App\Models\PaymentGatewayModel;

class BillingController extends BaseController
{
    protected $billingModel;
    protected $customerModel;
    protected $paymentGatewayModel;

    public function __construct()
    {
        $this->billingModel = new BillingLinkModel();
        $this->customerModel = new CustomerModel();
        $this->paymentGatewayModel = new PaymentGatewayModel();
    }

    /**
     * Display billing links management
     */
    public function index()
    {
        $data = [
            'title' => 'Kelola Billing Links',
            'billingLinks' => $this->billingModel->select('billing_links.*, customers.nama as customer_name')
                ->join('customers', 'customers.id = billing_links.customer_id', 'left')
                ->orderBy('billing_links.created_at', 'DESC')
                ->paginate(20),
            'pager' => $this->billingModel->pager
        ];

        return view('billing/index', $data);
    }

    /**
     * Create new billing link
     */
    public function create()
    {
        $data = [
            'title' => 'Buat Billing Link Baru',
            'customers' => $this->customerModel->findAll()
        ];

        return view('billing/create', $data);
    }

    /**
     * Store new billing link
     */
    public function store()
    {
        $rules = [
            'nomor_layanan' => 'required',
            'amount' => 'required|numeric|greater_than[0]',
            'description' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_layanan' => $this->request->getPost('nomor_layanan'),
            'amount' => $this->request->getPost('amount'),
            'description' => $this->request->getPost('description'),
            'expires_at' => $this->request->getPost('expires_at') ?: date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];

        // Find customer by nomor_layanan
        $customer = $this->customerModel->where('nomor_layanan', $data['nomor_layanan'])->first();
        if ($customer) {
            $data['customer_id'] = $customer['id'];
        }

        if ($this->billingModel->createBillingLink($data)) {
            return redirect()->to('/billing')->with('success', 'Billing link berhasil dibuat');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat billing link');
        }
    }

    /**
     * Public payment page by token
     */
    public function pay($token)
    {
        $billingLink = $this->billingModel->getByToken($token);

        if (!$billingLink) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check if expired
        if (
            $billingLink['status'] === 'expired' ||
            ($billingLink['expires_at'] && strtotime($billingLink['expires_at']) < time())
        ) {
            $data = [
                'title' => 'Link Pembayaran Kadaluarsa',
                'message' => 'Link pembayaran ini sudah kadaluarsa.',
                'billingLink' => $billingLink
            ];
            return view('public/payment_expired', $data);
        }

        // Check if already paid
        if ($billingLink['status'] === 'paid') {
            $data = [
                'title' => 'Pembayaran Sudah Lunas',
                'message' => 'Tagihan ini sudah dibayar.',
                'billingLink' => $billingLink
            ];
            return view('public/payment_success', $data);
        }

        // Get active payment gateways
        $activeGateways = $this->paymentGatewayModel->where('is_active', 1)->findAll();

        $data = [
            'title' => 'Pembayaran - ' . $billingLink['nomor_layanan'],
            'billingLink' => $billingLink,
            'paymentGateways' => $activeGateways
        ];

        return view('public/payment', $data);
    }

    /**
     * Check payment by nomor layanan (like bayarwifi.com)
     */
    public function check()
    {
        $nomorLayanan = $this->request->getGet('nomor_layanan');

        if ($nomorLayanan) {
            $billingLinks = $this->billingModel->getByNomorLayanan($nomorLayanan);

            $data = [
                'title' => 'Cek Tagihan - ' . $nomorLayanan,
                'nomorLayanan' => $nomorLayanan,
                'billingLinks' => $billingLinks
            ];

            return view('public/check_billing', $data);
        }

        $data = [
            'title' => 'Cek Tagihan'
        ];

        return view('public/check_form', $data);
    }

    /**
     * Process payment
     */
    public function processPayment()
    {
        $token = $this->request->getPost('token');
        $gatewayName = $this->request->getPost('payment_gateway');

        $billingLink = $this->billingModel->getByToken($token);
        if (!$billingLink || $billingLink['status'] !== 'pending') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid billing link'
            ]);
        }

        $gateway = $this->paymentGatewayModel->where('name', $gatewayName)
            ->where('is_active', 1)
            ->first();

        if (!$gateway) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment gateway not available'
            ]);
        }

        // Here you would integrate with the actual payment gateway APIs
        // For now, we'll create a placeholder payment reference

        $paymentReference = $gatewayName . '_' . time() . '_' . $token;

        // Update billing link with payment reference
        $this->billingModel->where('token', $token)->set([
            'payment_gateway' => $gatewayName,
            'payment_reference' => $paymentReference
        ])->update();

        // Return payment URL or instructions based on gateway
        $paymentUrl = base_url("payment/gateway/{$gatewayName}/{$token}");

        return $this->response->setJSON([
            'success' => true,
            'payment_url' => $paymentUrl,
            'message' => 'Redirecting to payment gateway...'
        ]);
    }

    /**
     * Payment gateway redirect
     */
    public function gateway($gatewayName, $token)
    {
        $billingLink = $this->billingModel->getByToken($token);
        $gateway = $this->paymentGatewayModel->where('name', $gatewayName)->first();

        if (!$billingLink || !$gateway) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Pembayaran via ' . ucfirst($gatewayName),
            'billingLink' => $billingLink,
            'gateway' => $gateway
        ];

        // Load specific gateway view
        return view("public/gateways/{$gatewayName}", $data);
    }

    /**
     * Payment callback handler
     */
    public function callback($gatewayName)
    {
        // Handle payment callbacks from different gateways
        // This would be implemented based on each gateway's webhook/callback requirements

        $data = $this->request->getJSON(true);

        // Basic callback handling - would need to be customized per gateway
        if (isset($data['status']) && $data['status'] === 'paid') {
            $token = $data['reference'] ?? null;
            if ($token) {
                $this->billingModel->updatePaymentStatus($token, 'paid', [
                    'payment_reference' => $data['transaction_id'] ?? null
                ]);
            }
        }

        return $this->response->setJSON(['status' => 'ok']);
    }

    /**
     * Delete billing link
     */
    public function delete($id)
    {
        if ($this->billingModel->delete($id)) {
            return redirect()->to('/billing')->with('success', 'Billing link berhasil dihapus');
        } else {
            return redirect()->to('/billing')->with('error', 'Gagal menghapus billing link');
        }
    }
}
