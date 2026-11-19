<?php

namespace App\Controllers;

class Profil extends BaseController
{
    public function index()
    {
        return $this->render('profil');
    }

    public function password()
    {
        $u = $this->db->query('SELECT password FROM pengguna WHERE id = ?', [$this->user()['id']])->getRowArray();
        $baru = (string) $this->request->getPost('baru');
        if (! password_verify((string) $this->request->getPost('lama'), $u['password'])) return redirect()->back()->with('error', 'Kata sandi lama salah.');
        if (strlen($baru) < 8 || ! preg_match('/[A-Za-z]/', $baru) || ! preg_match('/\d/', $baru)) return redirect()->back()->with('error', 'Kata sandi baru minimal 8 karakter berisi huruf dan angka.');
        if ($baru !== (string) $this->request->getPost('ulang')) return redirect()->back()->with('error', 'Ulangi kata sandi baru dengan isian yang sama.');
        $this->db->query('UPDATE pengguna SET password = ? WHERE id = ?', [password_hash($baru, PASSWORD_DEFAULT), $this->user()['id']]);
        return redirect()->to(site_url('profil'))->with('ok', 'Kata sandi diganti.');
    }
}
