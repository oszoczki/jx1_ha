<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Dashboard after successful login.
     */
    public function index(): string
    {
        return view('admin/dashboard', [
            'nickname'      => session('nickname'),
            'growl_success' => session()->getFlashdata('growl_success'),
        ]);
    }
}
