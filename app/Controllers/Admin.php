<?php

namespace App\Controllers;

use App\Libraries\Antrean;

class Admin extends BaseController
{
    public function index()
    {
        $hari = date('Y-m-d');
        $perJam = $this->db->query('SELECT HOUR(dibuat_at) j, COUNT(*) n FROM antrean WHERE tanggal = ? GROUP BY j ORDER BY j', [$hari])->getResultArray();
        $tren = $this->db->query("SELECT tanggal, COUNT(*) n FROM antrean WHERE tanggal >= ? - INTERVAL 13 DAY GROUP BY tanggal ORDER BY tanggal", [$hari])->getResultArray();
        return $this->render('admin/dashboard', ['poli' => $this->antrean->ringkasan($hari), 'perJam' => $perJam, 'tren' => $tren]);
    }

    public function poli()
    {
        $ubah = $this->request->getGet('ubah') ? $this->db->query('SELECT * FROM poli WHERE id = ?', [(int) $this->request->getGet('ubah')])->getRowArray() : null;
        $rows = $this->db->query('SELECT p.*, (SELECT COUNT(*) FROM antrean a WHERE a.poli_id = p.id) dipakai FROM poli p ORDER BY p.urutan, p.id')->getResultArray();
        return $this->render('admin/poli', ['rows' => $rows, 'ubah' => $ubah]);
    }

    public function simpanPoli()
    {
        $id = (int) $this->request->getPost('id');
        if ($this->request->getPost('aksi') === 'hapus') {
            $dipakai = (int) $this->db->query('SELECT COUNT(*) n FROM antrean WHERE poli_id = ?', [$id])->getRow('n');
            if ($dipakai) return redirect()->to(site_url('admin/poli'))->with('error', 'Poli ini sudah punya riwayat antrean. Nonaktifkan saja.');
            $this->db->query('DELETE FROM poli WHERE id = ?', [$id]);
            return redirect()->to(site_url('admin/poli'))->with('ok', 'Poli dihapus.');
        }
        $aturan = [
            'kode' => ['label' => 'Kode', 'rules' => 'required|regex_match[/^[A-Z]{1,2}$/]'],
            'nama' => ['label' => 'Nama poli', 'rules' => 'required|max_length[80]'],
            'dokter' => ['label' => 'Dokter', 'rules' => 'permit_empty|max_length[100]'],
            'kuota' => ['label' => 'Kuota', 'rules' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[999]'],
            'warna' => ['label' => 'Warna', 'rules' => 'required|in_list[' . implode(',', array_keys(Antrean::WARNA)) . ']'],
            'urutan' => ['label' => 'Urutan', 'rules' => 'required|integer'],
        ];
        $post = $this->request->getPost();
        $post['kode'] = strtoupper(trim((string) ($post['kode'] ?? '')));
        if (! $this->validateData($post, $aturan)) return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        if ($this->db->query('SELECT id FROM poli WHERE kode = ? AND id <> ?', [$post['kode'], $id])->getRow()) return redirect()->back()->withInput()->with('error', 'Kode ' . $post['kode'] . ' sudah dipakai poli lain.');
        $data = ['kode' => $post['kode'], 'nama' => trim($post['nama']), 'dokter' => trim((string) ($post['dokter'] ?? '')), 'kuota' => (int) $post['kuota'], 'warna' => $post['warna'], 'urutan' => (int) $post['urutan'], 'aktif' => empty($post['aktif']) ? 0 : 1];
        $id ? $this->db->table('poli')->update($data, ['id' => $id]) : $this->db->table('poli')->insert($data);
        return redirect()->to(site_url('admin/poli'))->with('ok', 'Poli ' . $data['nama'] . ' disimpan.');
    }

    public function pengguna()
    {
        $ubah = $this->request->getGet('ubah') ? $this->db->query('SELECT * FROM pengguna WHERE id = ?', [(int) $this->request->getGet('ubah')])->getRowArray() : null;
        return $this->render('admin/pengguna', ['rows' => $this->db->query('SELECT * FROM pengguna ORDER BY role, nama')->getResultArray(), 'ubah' => $ubah]);
    }

    public function simpanPengguna()
    {
        $id = (int) $this->request->getPost('id');
        $nama = trim((string) $this->request->getPost('nama'));
        $un = strtolower(trim((string) $this->request->getPost('username')));
        $role = $this->request->getPost('role') === 'admin' ? 'admin' : 'petugas';
        $aktif = $this->request->getPost('aktif') ? 1 : 0;
        $pw = (string) $this->request->getPost('password');
        if ($id === $this->user()['id']) { $role = 'admin'; $aktif = 1; }
        $err = null;
        if ($nama === '' || ! preg_match('/^[a-z0-9._]{3,40}$/', $un)) $err = 'Isi nama dan username (3-40 huruf kecil, angka, titik, garis bawah).';
        elseif ($this->db->query('SELECT id FROM pengguna WHERE username = ? AND id <> ?', [$un, $id])->getRow()) $err = 'Username sudah dipakai.';
        elseif ((! $id || $pw !== '') && (strlen($pw) < 8 || ! preg_match('/[A-Za-z]/', $pw) || ! preg_match('/\d/', $pw))) $err = 'Kata sandi minimal 8 karakter berisi huruf dan angka.';
        if ($err) return redirect()->back()->withInput()->with('error', $err);
        $data = ['nama' => $nama, 'username' => $un, 'role' => $role, 'aktif' => $aktif];
        if ($pw !== '') $data['password'] = password_hash($pw, PASSWORD_DEFAULT);
        $id ? $this->db->table('pengguna')->update($data, ['id' => $id]) : $this->db->table('pengguna')->insert($data);
        return redirect()->to(site_url('admin/pengguna'))->with('ok', 'Akun ' . $un . ' disimpan.');
    }

    public function pengaturan()
    {
        return $this->render('admin/pengaturan');
    }

    public function simpanPengaturan()
    {
        if ($this->request->getPost('aksi') === 'kunci_baru') {
            $this->antrean->simpan(['kunci_layar' => bin2hex(random_bytes(8))]);
            return redirect()->to(site_url('admin/pengaturan'))->with('ok', 'Alamat kios & layar TV diganti. Buka ulang kios dan TV dengan alamat baru.');
        }
        $d = array_map(fn($v) => mb_substr(trim((string) $v), 0, 300), array_intersect_key($this->request->getPost(), Antrean::PENGATURAN_AWAL));
        unset($d['kunci_layar']);
        $d['suara'] = $this->request->getPost('suara') ? '1' : '0';
        $d['kecepatan_suara'] = (string) max(0.5, min(1.5, (float) str_replace(',', '.', (string) $this->request->getPost('kecepatan_suara'))));
        if (($d['nama_klinik'] ?? '') === '') return redirect()->back()->withInput()->with('error', 'Nama klinik wajib diisi.');
        $this->antrean->simpan($d);
        return redirect()->to(site_url('admin/pengaturan'))->with('ok', 'Pengaturan disimpan.');
    }

    private function dataLaporan(): array
    {
        $dari = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->request->getGet('dari')) ? $this->request->getGet('dari') : date('Y-m-d', strtotime('-6 day'));
        $sampai = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->request->getGet('sampai')) ? $this->request->getGet('sampai') : date('Y-m-d');
        $rows = $this->db->query("SELECT p.kode, p.nama, COUNT(a.id) total, SUM(a.status = 'selesai') selesai, SUM(a.status = 'lewat') lewat,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, a.dibuat_at, a.dipanggil_at))) tunggu, MAX(TIMESTAMPDIFF(MINUTE, a.dibuat_at, a.dipanggil_at)) tunggu_maks,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, a.dipanggil_at, a.selesai_at))) layan
            FROM poli p LEFT JOIN antrean a ON a.poli_id = p.id AND a.tanggal BETWEEN ? AND ? GROUP BY p.id ORDER BY p.urutan, p.id", [$dari, $sampai])->getResultArray();
        return compact('dari', 'sampai', 'rows');
    }

    public function laporan()
    {
        return $this->render('admin/laporan', $this->dataLaporan());
    }

    public function laporanCsv()
    {
        $d = $this->dataLaporan();
        $f = fopen('php://temp', 'r+');
        fwrite($f, "\xEF\xBB\xBF");
        fputcsv($f, ['kode', 'poli', 'total', 'selesai', 'dilewati', 'rata_tunggu_menit', 'tunggu_terlama_menit', 'rata_layanan_menit'], ';', '"', '');
        foreach ($d['rows'] as $r) fputcsv($f, array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v, array_values($r)), ';', '"', '');
        rewind($f);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')->setHeader('Content-Disposition', 'attachment; filename="laporan-antrean-' . $d['dari'] . '-' . $d['sampai'] . '.csv"')->setBody(stream_get_contents($f));
    }
}
