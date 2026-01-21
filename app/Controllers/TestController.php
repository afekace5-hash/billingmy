<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class TestController extends ResourceController
{
    public function index()
    {
        log_message('debug', 'TestController index method called');
        return $this->response->setJSON(['status' => 'success', 'message' => 'TestController working']);
    }

    public function simple()
    {
        log_message('debug', 'TestController simple method called');
        return $this->response->setJSON(['status' => 'success', 'message' => 'Simple test working']);
    }
}
