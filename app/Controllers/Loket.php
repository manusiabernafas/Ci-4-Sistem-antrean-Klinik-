<?php

namespace App\Controllers;

/** Meja petugas: pilih poli & nama loket, lalu panggil pasien berikutnya. */
class Loket extends BaseController
{
    public function index()
    {
        $hari = date('Y-m-d');
        $poli = $this->antrean->ringkasan($hari);
        $pilih = session('loket') ?? null;
        $data = ['poli' => $poli, 'pilih' => $pilih, 'aktif' => null, 'menunggu' => [], 'lewat' => [], 'selesai' => []];
        if ($pilih) {
            $data['aktif'] = $this->db->query("SELECT a.*, p.kode kode_poli FROM antrean a JOIN poli p ON p.id = a.poli_id WHERE a.tanggal = ? AND a.poli_id = ? AND a.status = 'dipanggil' AND a.loket = ? ORDER BY a.dipanggil_at DESC LIMIT 1", [$hari, $pilih['poli_id'], $pilih['loket']])->getRowArray();
            foreach (['menunggu' => 'a.nomor', 'lewat' => 'a.nomor', 'selesai' => 'a.selesai_at DESC'] as $st => $urut) {
                $data[$st] = $this->db->query("SELECT a.*, p.kode kode_poli FROM antrean a JOIN poli p ON p.id = a.poli_id WHERE a.tanggal = ? AND a.poli_id = ? AND a.status = ? ORDER BY $urut LIMIT 40", [$hari, $pilih['poli_id'], $st])->getResultArray();
            }
            $data['lain'] = $this->db->query("SELECT a.loket, a.nomor, p.kode kode_poli FROM antrean a JOIN poli p ON p.id = a.poli_id WHERE a.tanggal = ? AND a.poli_id = ? AND a.status = 'dipanggil' AND a.loket <> ?", [$hari, $pilih['poli_id'], $pilih['loket']])->getResultArray();
        }
        return $this->render('loket/index', $data);
    }

    public function atur()
    {
        $poliId = (int) $this->request->getPost('poli_id');
        $loket = trim((string) $this->request->getPost('loket'));
        $p = $this->db->query('SELECT * FROM poli WHERE id = ? AND aktif = 1', [$poliId])->getRowArray();
        if (! $p || $loket === '' || mb_strlen($loket) > 40) return redirect()->to(site_url('loket'))->with('error', 'Pilih poli dan isi nama ruang / loket (mis. Ruang Periksa 1).');
        session()->set('loket', ['poli_id' => (int) $p['id'], 'nama_poli' => $p['nama'], 'kode' => $p['kode'], 'loket' => $loket]);
        return redirect()->to(site_url('loket'))->with('ok', 'Anda melayani ' . $p['nama'] . ' di ' . $loket . '.');
    }

    public function panggil()
    {
        $l = $this->wajibLoket();
        if (! is_array($l)) return $l;
        $a = $this->antrean->panggilBerikutnya($l['poli_id'], $l['loket'], $this->user()['id']);
        return redirect()->to(site_url('loket'))->with($a ? 'ok' : 'info', $a ? 'Memanggil ' . $this->antrean::kode($a) . ' ke ' . $l['loket'] . '.' : 'Tidak ada pasien yang menunggu.');
    }

    public function ulang(int $id)
    {
        $l = $this->wajibLoket();
        if (! is_array($l)) return $l;
        $a = $this->milikPoli($id, $l) ? $this->antrean->panggilUlang($id, $l['loket'], $this->user()['id']) : null;
        return redirect()->to(site_url('loket'))->with($a ? 'ok' : 'error', $a ? 'Memanggil ulang ' . $this->antrean::kode($a) . ' (panggilan ke-' . $a['panggil_ke'] . ').' : 'Nomor itu tidak bisa dipanggil.');
    }

    public function selesai(int $id)
    {
        return $this->ubah($id, 'selesai', 'selesai dilayani');
    }

    public function lewati(int $id)
    {
        return $this->ubah($id, 'lewat', 'dilewati (bisa dipanggil lagi nanti)');
    }

    private function ubah(int $id, string $status, string $pesan)
    {
        $l = $this->wajibLoket();
        if (! is_array($l)) return $l;
        $ok = $this->milikPoli($id, $l) && $this->antrean->ubahStatus($id, $status);
        $a = $this->antrean->detail($id);
        return redirect()->to(site_url('loket'))->with($ok ? 'ok' : 'error', $ok ? $this->antrean::kode($a) . ' ' . $pesan . '.' : 'Status nomor itu tidak bisa diubah.');
    }

    private function milikPoli(int $id, array $l): bool
    {
        $a = $this->antrean->detail($id);
        return $a && (int) $a['poli_id'] === (int) $l['poli_id'];
    }

    private function wajibLoket()
    {
        return session('loket') ?? redirect()->to(site_url('loket'))->with('error', 'Pilih poli dan loket terlebih dahulu.');
    }
}
