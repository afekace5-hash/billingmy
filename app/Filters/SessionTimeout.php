<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;

class SessionTimeout implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Mengecek apakah ada sesi pengguna
        if (!$session->has('isLoggedIn')) {
            return redirect()->to('/login'); // Ubah URL ke halaman login
        }

        // Mengecek waktu terakhir aktivitas
        $lastActivity = $session->get('lastActivity');
        if (time() - $lastActivity > 300) { // 300 detik = 5 menit
            // Logout otomatis jika lebih dari 5 menit tidak ada aktivitas
            $session->destroy();
            return redirect()->to('/login'); // Ubah URL ke halaman login
        }

        // Perbarui waktu terakhir aktivitas
        $session->set('lastActivity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu dilakukan setelah request diproses
    }
}
