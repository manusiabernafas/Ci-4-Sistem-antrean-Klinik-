<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Wajib login. Argumen opsional: role yang diizinkan, mis. 'auth:admin'. */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');
        // Baca ulang dari database: akun yang dinonaktifkan langsung keluar
        if ($user) {
            $u = db_connect()->query('SELECT id, nama, username, role, aktif FROM pengguna WHERE id = ?', [$user['id']])->getRowArray();
            if (! $u || ! $u['aktif']) {
                session()->remove('user');
                return redirect()->to(site_url('login'))->with('error', 'Akun Anda tidak aktif.');
            }
            $user = ['id' => (int) $u['id'], 'nama' => $u['nama'], 'username' => $u['username'], 'role' => $u['role']];
            session()->set('user', $user);
        }
        if (! $user) {
            if (strtolower($request->getMethod()) === 'get') {
                session()->set('setelah_login', current_url(true)->__toString());
            }
            return redirect()->to(site_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if ($arguments && ! in_array($user['role'], $arguments, true)) {
            return redirect()->to(site_url('loket'))->with('error', 'Halaman itu khusus admin.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
