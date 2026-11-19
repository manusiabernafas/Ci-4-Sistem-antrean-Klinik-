<?php

namespace App\Controllers;

use RuntimeException;

/** Kios ambil nomor (layar sentuh di ruang tunggu), tiket cetak, dan cek status dari HP. */
class Kios extends BaseController
{
    public function index(string $kunci)
    {
        $this->cekKunci($kunci);
        return $this->render('kios/index', ['kunci' => $kunci, 'poli' => $this->antrean->ringkasan(date('Y-m-d'))]);
    }

    public function ambil(string $kunci)
    {
        $this->cekKunci($kunci);
        // Cegah tombol ditekan berulang-ulang (anak-anak, atau sengaja menghabiskan kuota)
        $batas = 'ambil:' . $this->request->getIPAddress();
        if ($this->terkunci($batas, 30, 60)) return redirect()->to(site_url('kios/' . $kunci))->with('error', 'Terlalu banyak tiket dalam satu menit. Tunggu sebentar.');
        $this->catatCoba($batas, 60);
        try {
            $a = $this->antrean->ambil((int) $this->request->getPost('poli_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('kios/' . $kunci))->with('error', $e->getMessage());
        }
        session()->setFlashdata('kunci_kios', $kunci);
        return redirect()->to(site_url('tiket/' . $a['token']) . '?cetak=1');
    }

    public function tiket(string $token)
    {
        $a = $this->cari($token);
        return $this->render('kios/tiket', ['a' => $a, 'didepan' => $this->antrean->didepan($a), 'cetak' => $this->request->getGet('cetak') === '1', 'kunci' => session()->getFlashdata('kunci_kios')]);
    }

    public function status(string $token)
    {
        $a = $this->cari($token);
        $sekarang = $this->db->query("SELECT nomor, loket FROM antrean WHERE tanggal = ? AND poli_id = ? AND status IN ('dipanggil','selesai') ORDER BY dipanggil_at DESC LIMIT 1", [$a['tanggal'], $a['poli_id']])->getRowArray();
        return $this->render('kios/status', ['a' => $a, 'didepan' => $this->antrean->didepan($a), 'sekarang' => $sekarang]);
    }

    private function cari(string $token): array
    {
        $id = preg_match('/^[a-f0-9]{10}$/', $token) ? $this->db->query('SELECT id FROM antrean WHERE token = ?', [$token])->getRow('id') : null;
        $a = $id ? $this->antrean->detail((int) $id) : null;
        if (! $a) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tiket tidak ditemukan.');
        return $a;
    }
}
