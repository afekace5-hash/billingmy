<?php

namespace App\Controllers;

class TestSession extends BaseController
{
    public function index()
    {
        $session = session();
        $userId = $session->get('id_user');

        if ($userId) {
            echo "✅ Session EXISTS - User ID: " . $userId;
            echo "<br>Session data: ";
            print_r($session->get());
        } else {
            echo "❌ NO SESSION - Please login first";
            echo "<br>All session data: ";
            print_r($session->get());
        }
    }
}
