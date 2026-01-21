<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MasterBank extends BaseController
{
    public function index()
    {
        $bankModel = new \App\Models\BankModel();
        $banks = $bankModel->orderBy('id', 'DESC')->findAll();
        return view('settings/master_bank', ['banks' => $banks]);
    }

    public function create()
    {
        $bankModel = new \App\Models\BankModel();
        $data = [
            'bank_name' => $this->request->getPost('bank_name'),
            'account_number' => $this->request->getPost('account_number'),
            'account_holder' => $this->request->getPost('account_holder'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];
        $bankModel->insert($data);
        return redirect()->to(site_url('settings/master-bank'))->with('success', 'Bank berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $bankModel = new \App\Models\BankModel();
        $bankModel->delete($id);
        return redirect()->to(site_url('settings/master-bank'))->with('success', 'Bank berhasil dihapus.');
    }

    public function edit($id)
    {
        $bankModel = new \App\Models\BankModel();
        $bank = $bankModel->find($id);
        if (!$bank) {
            return redirect()->to(site_url('settings/master-bank'))->with('error', 'Data bank tidak ditemukan.');
        }
        // Kirim data bank dan daftar bank ke view
        $banks = $bankModel->orderBy('id', 'DESC')->findAll();
        return view('settings/master_bank_edit', [
            'bank' => $bank,
            'banks' => $banks
        ]);
    }

    public function update($id)
    {
        $bankModel = new \App\Models\BankModel();
        $data = [
            'bank_name' => $this->request->getPost('bank_name'),
            'account_number' => $this->request->getPost('account_number'),
            'account_holder' => $this->request->getPost('account_holder'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];
        $bankModel->update($id, $data);
        return redirect()->to(site_url('settings/master-bank'))->with('success', 'Data bank berhasil diupdate.');
    }


    public function listJson()
    {
        $bankModel = new \App\Models\BankModel();
        $banks = $bankModel->findAll();
        return $this->response->setJSON($banks);
    }
}
