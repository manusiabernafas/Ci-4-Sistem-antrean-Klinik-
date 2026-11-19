<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/** Data contoh: 4 poli, 3 akun, riwayat 14 hari, dan antrean hari ini sampai jam sekarang. */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        mt_srand(2026);
        $db = $this->db;
        foreach (['panggilan', 'antrean', 'pengguna', 'poli', 'pengaturan', 'batas_coba'] as $t) $db->query("DELETE FROM $t");
        $set = [
            'nama_klinik' => 'Klinik Pratama Sehat Bersama', 'alamat' => 'Jl. Kaliurang Km 7 No. 21, Sleman', 'telepon' => '(0274) 885 210',
            'jam_layanan' => 'Senin - Sabtu, 07.30 - 14.00 WIB', 'kunci_layar' => bin2hex(random_bytes(8)),
            'teks_berjalan' => 'Selamat datang di Klinik Pratama Sehat Bersama. Mohon menunggu nomor Anda dipanggil. Gunakan masker bila sedang batuk atau pilek. Pendaftaran BPJS mohon membawa KTP dan kartu JKN.',
            'catatan_tiket' => 'Simpan tiket ini. Nomor yang tidak hadir saat dipanggil akan dilewati.', 'suara' => '1', 'kecepatan_suara' => '0.9',
        ];
        foreach ($set as $k => $v) $db->table('pengaturan')->insert(['kunci' => $k, 'nilai' => $v]);
        $poli = [['A', 'Poli Umum', 'dr. Rina Kartika', 0, 'hijau'], ['B', 'Poli Gigi', 'drg. Hendra Wijaya', 25, 'biru'], ['C', 'KIA & KB', 'Bidan Sari Lestari, Amd.Keb.', 0, 'koral'], ['D', 'Poli Anak', 'dr. Fajar Nugroho, Sp.A', 30, 'ungu']];
        foreach ($poli as $i => [$kd, $nm, $dk, $kuota, $w]) $db->table('poli')->insert(['kode' => $kd, 'nama' => $nm, 'dokter' => $dk, 'kuota' => $kuota, 'warna' => $w, 'urutan' => $i + 1, 'aktif' => 1]);
        $db->table('pengguna')->insertBatch([
            ['nama' => 'Dewi Anggraini', 'username' => 'admin', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'role' => 'admin', 'aktif' => 1],
            ['nama' => 'Yuni Rahmawati', 'username' => 'petugas', 'password' => password_hash('petugas123', PASSWORD_DEFAULT), 'role' => 'petugas', 'aktif' => 1],
            ['nama' => 'Bagus Prakoso', 'username' => 'bagus', 'password' => password_hash('petugas123', PASSWORD_DEFAULT), 'role' => 'petugas', 'aktif' => 1],
        ]);
        $ids = array_column($db->query('SELECT id, kode FROM poli')->getResultArray(), 'id', 'kode');
        $petugas = (int) $db->query("SELECT id FROM pengguna WHERE username = 'petugas'")->getRow('id');
        $ruang = ['A' => ['Ruang Periksa 1', 'Ruang Periksa 2'], 'B' => ['Ruang Gigi'], 'C' => ['Ruang KIA'], 'D' => ['Ruang Anak']];
        $banyak = ['A' => [45, 70], 'B' => [12, 22], 'C' => [15, 28], 'D' => [18, 30]];
        $layan = ['A' => [5, 12], 'B' => [15, 30], 'C' => [8, 18], 'D' => [7, 15]];
        $sekarang = time();
        for ($h = 14; $h >= 0; $h--) {
            $tgl = date('Y-m-d', strtotime("-$h day"));
            if (date('N', strtotime($tgl)) == 7) continue; // Minggu libur
            foreach ($ids as $kd => $pid) {
                $n = mt_rand(...$banyak[$kd]);
                $buka = strtotime("$tgl 07:15");
                $loketBebas = array_fill_keys($ruang[$kd], strtotime("$tgl 07:30"));
                $no = 0;
                for ($i = 0; $i < $n; $i++) {
                    $ambil = $buka + (int) ($i * (6.2 * 3600 / $n)) + mt_rand(0, 300);
                    if ($ambil > $sekarang) break;
                    asort($loketBebas); $lk = array_key_first($loketBebas);
                    $panggil = max($ambil + 60 * mt_rand(2, 6), $loketBebas[$lk]);
                    $lama = 60 * mt_rand(...$layan[$kd]);
                    $no++;
                    $status = 'selesai'; $dp = $panggil; $sl = $panggil + $lama;
                    if ($panggil > $sekarang) { $status = 'menunggu'; $dp = null; $sl = null; }
                    elseif ($sl > $sekarang) { $status = 'dipanggil'; $sl = null; }
                    elseif (mt_rand(1, 100) <= 4) { $status = 'lewat'; $sl = null; $lama = 60; }
                    if ($dp) $loketBebas[$lk] = $panggil + $lama;
                    $db->table('antrean')->insert([
                        'tanggal' => $tgl, 'poli_id' => $pid, 'nomor' => $no, 'token' => bin2hex(random_bytes(5)), 'status' => $status,
                        'loket' => $dp ? $lk : '', 'panggil_ke' => $dp ? 1 : 0, 'petugas_id' => $dp ? $petugas : null,
                        'dibuat_at' => date('Y-m-d H:i:s', $ambil), 'dipanggil_at' => $dp ? date('Y-m-d H:i:s', $dp) : null, 'selesai_at' => $sl ? date('Y-m-d H:i:s', $sl) : null,
                    ]);
                    if ($dp) $db->table('panggilan')->insert(['antrean_id' => $db->insertID(), 'waktu' => date('Y-m-d H:i:s', $dp), 'loket' => $lk]);
                }
            }
        }
        // Panggilan diurutkan sesuai waktu agar layar TV menampilkan yang terbaru di atas
        $db->query('CREATE TEMPORARY TABLE g AS SELECT antrean_id, waktu, loket FROM panggilan ORDER BY waktu');
        $db->query('DELETE FROM panggilan');
        $db->query('ALTER TABLE panggilan AUTO_INCREMENT = 1');
        $db->query('INSERT INTO panggilan (antrean_id, waktu, loket) SELECT antrean_id, waktu, loket FROM g ORDER BY waktu');
    }
}
