<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function beranda()
    {
        $u = $this->user();
        if ($u) return redirect()->to(site_url($u['role'] === 'admin' ? 'admin' : 'loket'));
        return redirect()->to(site_url('login'));
    }

    public function login()
    {
        if ($this->user()) return $this->beranda();
        return $this->render('auth/login');
    }

    public function prosesLogin()
    {
        $kunci = 'login:' . $this->request->getIPAddress();
        if ($this->terkunci($kunci, 5, 600)) return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan masuk. Coba lagi 10 menit lagi.');
        $username = strtolower(trim((string) $this->request->getPost('username')));
        $u = $this->db->query('SELECT * FROM pengguna WHERE username = ?', [$username])->getRowArray();
        if (! $u || ! password_verify((string) $this->request->getPost('password'), $u['password'])) {
            $this->catatCoba($kunci, 600);
            return redirect()->back()->withInput()->with('error', 'Username atau kata sandi salah.');
        }
        if (! $u['aktif']) return redirect()->back()->with('error', 'Akun ini dinonaktifkan.');
        $this->db->query('DELETE FROM batas_coba WHERE kunci = ?', [$kunci]);
        session()->regenerate(true);
        session()->set('user', ['id' => (int) $u['id'], 'nama' => $u['nama'], 'username' => $u['username'], 'role' => $u['role']]);
        return redirect()->to(site_url($u['role'] === 'admin' ? 'admin' : 'loket'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('ok', 'Anda sudah keluar.');
    }
}
