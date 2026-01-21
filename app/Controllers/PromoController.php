<?php

namespace App\Controllers;

use App\Models\PromoModel;

class PromoController extends BaseController
{
    protected $promoModel;

    public function __construct()
    {
        $this->promoModel = new PromoModel();
    }

    /**
     * Display list of promos
     */
    public function index()
    {
        $data = [
            'title' => 'Kelola Promo & Penawaran',
            'promos' => $this->promoModel->getAllPromos(),
        ];

        return view('admin/promos/index', $data);
    }

    /**
     * Show form to create new promo
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Promo Baru',
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/promos/create', $data);
    }

    /**
     * Store new promo
     */
    public function store()
    {

        $rules = [
            'title' => 'required|max_length[255]',
            'badge_text' => 'required|max_length[50]',
            'button_text' => 'required|max_length[100]',
            'button_action' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'badge_text' => $this->request->getPost('badge_text'),
            'button_text' => $this->request->getPost('button_text'),
            'button_action' => $this->request->getPost('button_action'),
            'gradient_start' => $this->request->getPost('gradient_start') ?: '#A8C0FF',
            'gradient_end' => $this->request->getPost('gradient_end') ?: '#C1A5F0',
            'display_order' => $this->request->getPost('display_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
        ];

        if ($this->promoModel->insert($data)) {
            return redirect()->to('/admin/promos')->with('success', 'Promo berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan promo');
    }

    /**
     * Show form to edit promo
     */
    public function edit($id)
    {
        $promo = $this->promoModel->find($id);

        if (!$promo) {
            return redirect()->to('/admin/promos')->with('error', 'Promo tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Promo',
            'promo' => $promo,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/promos/edit', $data);
    }

    /**
     * Update promo
     */
    public function update($id)
    {

        $rules = [
            'title' => 'required|max_length[255]',
            'badge_text' => 'required|max_length[50]',
            'button_text' => 'required|max_length[100]',
            'button_action' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'badge_text' => $this->request->getPost('badge_text'),
            'button_text' => $this->request->getPost('button_text'),
            'button_action' => $this->request->getPost('button_action'),
            'gradient_start' => $this->request->getPost('gradient_start') ?: '#A8C0FF',
            'gradient_end' => $this->request->getPost('gradient_end') ?: '#C1A5F0',
            'display_order' => $this->request->getPost('display_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
        ];

        if ($this->promoModel->update($id, $data)) {
            return redirect()->to('/admin/promos')->with('success', 'Promo berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui promo');
    }

    /**
     * Delete promo
     */
    public function delete($id)
    {
        if ($this->promoModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Promo berhasil dihapus'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menghapus promo'
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        if ($this->promoModel->toggleActive($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status promo berhasil diubah'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal mengubah status promo'
        ]);
    }
}
