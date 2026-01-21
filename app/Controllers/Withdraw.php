<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\WithdrawModel;
use App\Models\TransactionModel;
use App\Libraries\Disbursement\DisbursementFactory;

class Withdraw extends BaseController
{
    protected $withdrawModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->withdrawModel = new WithdrawModel();
        $this->transactionModel = new TransactionModel();
    }

    /**
     * Display withdraw history page
     */
    public function index()
    {
        // Debug session
        log_message('info', 'Withdraw index accessed. Session id_user: ' . (session('id_user') ?? 'NULL'));

        $data = [
            'title' => 'Withdraw History',
        ];

        return view('withdraw/index', $data);
    }

    /**
     * Get withdraw data for DataTables
     */
    public function getData()
    {
        // Allow this method to work without CSRF for DataTables AJAX
        try {
            $withdraws = $this->withdrawModel->getDatatables();

            // Support both GET and POST
            $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 0;

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $this->withdrawModel->countAll(),
                'recordsFiltered' => $this->withdrawModel->countFiltered(),
                'data' => $withdraws
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Withdraw getData error: ' . $e->getMessage());

            $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 0;
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get available balance for withdrawal
     */
    public function getAvailableBalance()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Calculate available balance from transactions
        $totalIncome = $this->transactionModel
            ->selectSum('amount')
            ->where('type', 'income')
            ->where('status', 'completed')
            ->first()['amount'] ?? 0;

        $totalWithdrawn = $this->withdrawModel
            ->selectSum('amount')
            ->whereIn('status', ['completed', 'pending', 'processing'])
            ->first()['amount'] ?? 0;

        $availableBalance = $totalIncome - $totalWithdrawn;

        return $this->response->setJSON([
            'success' => true,
            'available_balance' => $availableBalance,
            'total_income' => $totalIncome,
            'total_withdrawn' => $totalWithdrawn,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Create new withdraw request
     */
    public function create()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $rules = [
            'amount' => 'required|numeric|greater_than[0]',
            'bank_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required',
            'notes' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Generate withdraw code
        $code = 'WD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $data = [
            'code' => $code,
            'amount' => $this->request->getPost('amount'),
            'bank_name' => $this->request->getPost('bank_name'),
            'account_number' => $this->request->getPost('account_number'),
            'account_name' => $this->request->getPost('account_name'),
            'notes' => $this->request->getPost('notes'),
            'status' => 'pending',
            'requested_by' => session()->get('user_id') ?? 1,
            'requested_at' => date('Y-m-d H:i:s')
        ];

        if ($this->withdrawModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Withdraw request created successfully',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create withdraw request',
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Get withdraw detail
     */
    public function getDetail($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $withdraw = $this->withdrawModel->find($id);

        if (!$withdraw) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Withdraw not found',
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $withdraw,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Update withdraw status
     */
    public function updateStatus($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $withdraw = $this->withdrawModel->find($id);

        if (!$withdraw) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Withdraw not found',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $status = $this->request->getPost('status');
        $adminNotes = $this->request->getPost('admin_notes');

        $data = [
            'status' => $status,
            'admin_notes' => $adminNotes,
            'processed_by' => session()->get('user_id') ?? 1,
            'processed_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        if ($this->withdrawModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Withdraw status updated successfully',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update withdraw status',
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Delete withdraw request
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $withdraw = $this->withdrawModel->find($id);

        if (!$withdraw) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Withdraw not found',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Only allow delete if status is pending or rejected
        if (!in_array($withdraw['status'], ['pending', 'rejected'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete withdraw with status: ' . $withdraw['status'],
                'csrf_hash' => csrf_hash()
            ]);
        }

        if ($this->withdrawModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Withdraw request deleted successfully',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete withdraw request',
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Process auto disbursement
     */
    public function processAutoDisbursement($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $withdraw = $this->withdrawModel->find($id);

        if (!$withdraw) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Withdraw not found',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Check if already processed
        if (!empty($withdraw['disbursement_reference'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This withdraw has already been processed',
                'csrf_hash' => csrf_hash()
            ]);
        }

        try {
            $provider = $this->request->getPost('provider') ?? env('DISBURSEMENT_PROVIDER', 'flip');
            $disbursement = DisbursementFactory::create($provider);

            // Prepare data
            $disbursementData = [
                'amount' => $withdraw['amount'],
                'bank_name' => $withdraw['bank_name'],
                'account_number' => $withdraw['account_number'],
                'account_name' => $withdraw['account_name'],
                'notes' => $withdraw['notes'] ?? 'Withdrawal - ' . $withdraw['code'],
                'email' => session()->get('email') ?? ''
            ];

            // Process disbursement
            $result = $disbursement->disburse($disbursementData);

            if ($result['success']) {
                // Update withdraw record
                $updateData = [
                    'disbursement_provider' => $provider,
                    'disbursement_reference' => $result['reference_id'],
                    'disbursement_status' => $result['status'],
                    'disbursement_fee' => $result['fee'] ?? 0,
                    'disbursement_response' => json_encode($result),
                    'auto_disburse' => 1,
                    'status' => 'processing',
                    'processed_by' => session()->get('user_id') ?? 1,
                    'processed_at' => date('Y-m-d H:i:s')
                ];

                $this->withdrawModel->update($id, $updateData);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Disbursement processed successfully',
                    'data' => $result,
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'csrf_hash' => csrf_hash()
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Check disbursement status
     */
    public function checkDisbursementStatus($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $withdraw = $this->withdrawModel->find($id);

        if (!$withdraw || empty($withdraw['disbursement_reference'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No disbursement found for this withdraw',
                'csrf_hash' => csrf_hash()
            ]);
        }

        try {
            $disbursement = DisbursementFactory::create($withdraw['disbursement_provider']);
            $result = $disbursement->checkStatus($withdraw['disbursement_reference']);

            if ($result['success']) {
                // Update status if changed
                $newStatus = $result['status'];
                if ($newStatus !== $withdraw['disbursement_status']) {
                    $updateData = [
                        'disbursement_status' => $newStatus
                    ];

                    // Update main status if disbursement completed
                    if (in_array(strtolower($newStatus), ['completed', 'success'])) {
                        $updateData['status'] = 'completed';
                        $updateData['completed_at'] = date('Y-m-d H:i:s');
                    } elseif (in_array(strtolower($newStatus), ['failed', 'rejected'])) {
                        $updateData['status'] = 'rejected';
                    }

                    $this->withdrawModel->update($id, $updateData);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'status' => $newStatus,
                    'data' => $result['data'],
                    'csrf_hash' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => $result['message'],
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Validate bank account
     */
    public function validateBankAccount()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $bankName = $this->request->getPost('bank_name');
        $accountNumber = $this->request->getPost('account_number');
        $provider = $this->request->getPost('provider') ?? env('DISBURSEMENT_PROVIDER', 'flip');

        if (!$bankName || !$accountNumber) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bank name and account number are required',
                'csrf_hash' => csrf_hash()
            ]);
        }

        try {
            $disbursement = DisbursementFactory::create($provider);
            $banks = $disbursement->getSupportedBanks();
            $bankCode = $banks[$bankName] ?? strtolower($bankName);

            $result = $disbursement->validateBankAccount($bankCode, $accountNumber);

            return $this->response->setJSON(array_merge($result, [
                'csrf_hash' => csrf_hash()
            ]));
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Get disbursement balance
     */
    public function getDisbursementBalance()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $provider = $this->request->getPost('provider') ?? env('DISBURSEMENT_PROVIDER', 'flip');

        try {
            $disbursement = DisbursementFactory::create($provider);
            $result = $disbursement->getBalance();

            return $this->response->setJSON(array_merge($result, [
                'provider' => $provider,
                'csrf_hash' => csrf_hash()
            ]));
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Webhook handler for disbursement callback
     */
    public function webhookHandler($provider = null)
    {
        // Get raw POST data
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        // Log webhook
        log_message('info', 'Disbursement Webhook from ' . $provider . ': ' . $rawInput);

        try {
            // Find withdraw by reference
            $reference = $data['reference_no'] ?? $data['external_id'] ?? $data['id'] ?? null;

            if (!$reference) {
                log_message('error', 'No reference found in webhook data');
                return $this->response->setJSON(['status' => 'error', 'message' => 'No reference']);
            }

            $withdraw = $this->withdrawModel->where('disbursement_reference', $reference)->first();

            if (!$withdraw) {
                log_message('error', 'Withdraw not found for reference: ' . $reference);
                return $this->response->setJSON(['status' => 'error', 'message' => 'Withdraw not found']);
            }

            // Update status based on provider
            $status = $data['status'] ?? '';
            $updateData = [
                'disbursement_status' => $status,
                'disbursement_response' => $rawInput
            ];

            if (in_array(strtolower($status), ['completed', 'success'])) {
                $updateData['status'] = 'completed';
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            } elseif (in_array(strtolower($status), ['failed', 'rejected'])) {
                $updateData['status'] = 'rejected';
            }

            $this->withdrawModel->update($withdraw['id'], $updateData);

            return $this->response->setJSON(['status' => 'success']);
        } catch (\Exception $e) {
            log_message('error', 'Webhook error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
