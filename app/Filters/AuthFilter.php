<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Redirect unauthenticated users to login when accessing admin area.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/admin/login')->with('growl_info', 'Please log in to continue.');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
