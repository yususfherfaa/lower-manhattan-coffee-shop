<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SettingModel;

class Auth extends BaseController
{
    private const LOGIN_FAILURE_LIMIT = 5;
    private const LOGIN_FAILURE_WINDOW = 900;

    public function index()
    {
        $settingModel = new SettingModel();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {
            $settings[$s['nama']] = $s['value'];
        }

        $data = [
            'msg' => session()->getFlashdata('msg'),

            'nama_warkop' => 'Lower Manhattan Coffee Shop',

            'alamat' => $settings['alamat'] ?? 'Alamat belum diatur',

            'kontak' => $settings['kontak'] ?? '-',

            'logo' => $settings['logo'] ?? 'uploads/manhattan-coffee-shop-logo.png',
        ];

        return view('auth/login', $data);
    }

    public function processLogin()
    {
        $cache = \Config\Services::cache();
        $failureKey = 'login_failures_' . hash('sha256', $this->request->getIPAddress());
        $failureCount = (int) ($cache->get($failureKey) ?? 0);

        if ($failureCount >= self::LOGIN_FAILURE_LIMIT) {
            return redirect()
                ->back()
                ->with('msg', 'Terlalu banyak percobaan login. Coba lagi setelah 15 menit.');
        }

        $userModel = new UserModel();

        $username = (string) $this->request->getPost('username');

        $password = (string) $this->request->getPost('password');

        $user = $userModel
            ->where('username', $username)
            ->first();

        if ($user && password_verify($password, $user['password'])) {
            if (
                $failureCount > 0
                && !$cache->delete($failureKey)
                && $cache->get($failureKey) !== null
            ) {
                log_message('error', 'Unable to clear login failure counter for client {client}.', [
                    'client' => $failureKey,
                ]);

                return $this->response
                    ->setStatusCode(503)
                    ->setBody('Login sementara tidak tersedia. Silakan coba lagi.');
            }

            session()->regenerate(true);

            session()->set([
                'id_user'     => $user['id_user'],
                'username'    => $user['username'],
                'role'        => $user['role'],
                'nama_kasir'  => $user['nama_kasir'],
                'logged_in'   => true
            ]);

            if ($user['role'] === 'admin') {
                return redirect()->to('/admin/dashboard');
            }

            return redirect()->to('/kasir/dashboard');
        }

        if (!$cache->save($failureKey, $failureCount + 1, self::LOGIN_FAILURE_WINDOW)) {
            log_message('error', 'Unable to save login failure counter for client {client}.', [
                'client' => $failureKey,
            ]);

            return $this->response
                ->setStatusCode(503)
                ->setBody('Login sementara tidak tersedia. Silakan coba lagi.');
        }

        return redirect()
            ->back()
            ->with('msg', 'Username / Password salah');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/');
    }
}