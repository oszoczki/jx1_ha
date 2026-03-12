<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LoginController extends BaseController
{
    protected UserModel $userModel;
    protected $helpers = ['form', 'url'];

    public function initController(RequestInterface $request, ResponseInterface $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->userModel = model(UserModel::class);
    }

    /**
     * Show login form.
     */
    public function index(): string|RedirectResponse
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('/admin/dashboard')->with('growl_success', 'You are already logged in.');
        }
        return view('admin/login', [
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    /**
     * Process login form submission.
     */
    public function processLogin(): RedirectResponse|string
    {
        $rules = [
            'nickname' => 'required|min_length[1]|max_length[64]',
            'password' => 'required|min_length[1]',
        ];
        $messages = [
            'nickname' => [
                'required'   => 'The nickname field is required.',
                'min_length' => 'The nickname must be at least 1 character.',
            ],
            'password' => [
                'required'   => 'The password field is required.',
                'min_length' => 'The password must be at least 1 character.',
            ],
        ];
        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $nickname = $this->request->getPost('nickname');
        $password = $this->request->getPost('password');
        $user = $this->userModel->authenticate($nickname, $password);
        if ($user === null) {
            return redirect()->back()->withInput()->with('errors', [
                'credentials' => 'Invalid nickname or password.',
            ]);
        }
        session()->set([
            'user_id'   => $user['id'],
            'nickname'  => $user['nickname'],
            'logged_in' => true,
        ]);
        return redirect()->to('/admin/dashboard')->with('growl_success', 'Login successful. Welcome!');
    }

    /**
     * Logout and redirect to login.
     */
    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/admin/login')->with('growl_info', 'You have been logged out.');
    }

    protected function isLoggedIn(): bool
    {
        return (bool) session('logged_in');
    }
}
