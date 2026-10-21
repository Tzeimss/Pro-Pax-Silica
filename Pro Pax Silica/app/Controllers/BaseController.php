<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected function currentUser(): ?array
    {
        $sessionUser = session()->get('user');
        $id = is_array($sessionUser) ? ($sessionUser['id'] ?? null) : null;
        if (! $id) {
            return null;
        }

        $user = (new UserModel())->find($id);
        if (! $user || ! (int) $user['is_active']) {
            session()->destroy();
            return null;
        }

        return $user;
    }

    protected function authorize(array $roles = [], bool $allowPasswordChange = false): ?RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->to(site_url('account?mode=signin'))->with('status', 'Please sign in to continue.');
        }
        if ((int) ($user['must_change_password'] ?? 0) && ! $allowPasswordChange) {
            return redirect()->to(site_url('portal'))->with('status', 'Change your temporary password before using the workspace.');
        }
        if ($roles && ! in_array($user['role'], $roles, true)) {
            return redirect()->to(site_url('portal'))->with('status', 'You do not have permission to view that page.');
        }
        return null;
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }
}
