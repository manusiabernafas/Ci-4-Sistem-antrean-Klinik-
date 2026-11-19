<?php

namespace App\Libraries;

use RuntimeException;

/** Aturan antrean: pengaturan, ambil nomor, panggil, lewati, selesai, statistik. */
class Antrean
{
    public const PENGATURAN_AWAL = [
        'nama_klinik' => 'Klinik Sehat', 'alamat' => '', 'telepon' => '', 'jam_layanan' => 'Senin - Sabtu, 07.30 - 14.00',
        'teks_berjalan' => 'Selamat datang. Mohon menunggu nomor Anda dipanggil.', 'kunci_layar' => '', 'catatan_tiket' => 'Simpan tiket ini sampai pemeriksaan selesai.',
        'suara' => '1', 'kecepatan_suara' => '0.9',
    ];
    public const WARNA = ['hijau' => 'Hijau', 'koral' => 'Koral', 'biru' => 'Biru', 'ungu' => 'Ungu', 'kuning' => 'Kuning', 'tosca' => 'Tosca'];
    public const STATUS = ['menunggu' => ['Menunggu', 'info'], 'dipanggil' => ['Dipanggil', 'warn'], 'selesai' => ['Selesai', 'ok'], 'lewat' => ['Dilewati', 'bad']];

    private $db;
    private ?array $set = null;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function semua(): array
    {
        if ($this->set === null) {
            $this->set = self::PENGATURAN_AWAL;
            foreach ($this->db->table('pengaturan')->get()->getResultArray() as $r) $this->set[$r['kunci']] = (string) $r['nilai'];
        }
        return $this->set;
    }

    public function get(string $k): string
    {
        return $this->semua()[$k] ?? '';
    }

    public function simpan(array $data): void
    {
        foreach ($data as $k => $v) {
            if (array_key_exists($k, self::PENGATURAN_AWAL)) {
                $this->db->query('INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)', [$k, (string) $v]);
            }
        }
        $this->set = null;
    }

    public static function kode(array $a): string
    {
        return $a['kode_poli'] . '-' . str_pad((string) $a['nomor'], 3, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil nomor berikutnya untuk poli hari ini. Baris poli dikunci (FOR UPDATE) sehingga dua kios
     * yang menekan bersamaan tidak mendapat nomor sama; kunci unik di tabel menjadi pengaman kedua.
     */
    public function ambil(int $poliId): array
    {
        $poli = $this->db->query('SELECT * FROM poli WHERE id = ? AND aktif = 1', [$poliId])->getRowArray();
        if (! $poli) throw new RuntimeException('Poli tidak tersedia.');
        $hari = date('Y-m-d');
        // Dua kios bersamaan bisa saling mengunci (deadlock) pada baris pertama hari itu: cukup ulangi.
        for ($coba = 1; $coba <= 3; $coba++) {
            try {
                $this->db->transStart();
                // Kunci baris poli dulu: semua pengambilan nomor poli ini antre satu per satu (tanpa deadlock)
                $this->db->query('SELECT id FROM poli WHERE id = ? FOR UPDATE', [$poliId]);
                $akhir = (int) ($this->db->query('SELECT MAX(nomor) n FROM antrean WHERE tanggal = ? AND poli_id = ? FOR UPDATE', [$hari, $poliId])->getRow()->n ?? 0);
                if ($poli['kuota'] > 0 && $akhir >= $poli['kuota']) {
                    $this->db->transComplete();
                    throw new RuntimeException('Kuota ' . $poli['nama'] . ' hari ini sudah habis (' . $poli['kuota'] . ' pasien).');
                }
                $this->db->table('antrean')->insert([
                    'tanggal' => $hari, 'poli_id' => $poliId, 'nomor' => $akhir + 1, 'token' => bin2hex(random_bytes(5)), 'dibuat_at' => date('Y-m-d H:i:s'),
                ]);
                $id = (int) $this->db->insertID();
                $this->db->transComplete();
                if ($this->db->transStatus() && $id) return $this->detail($id);
            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->db->transRollback();
            }
            usleep(random_int(20000, 80000));
        }
        throw new RuntimeException('Gagal mengambil nomor, silakan coba lagi.');
    }

    public function detail(int $id): ?array
    {
        return $this->db->query('SELECT a.*, p.kode kode_poli, p.nama nama_poli, p.dokter, p.warna FROM antrean a JOIN poli p ON p.id = a.poli_id WHERE a.id = ?', [$id])->getRowArray();
    }

    /** Jumlah pasien yang masih menunggu di depan nomor ini. */
    public function didepan(array $a): int
    {
        return (int) $this->db->query("SELECT COUNT(*) n FROM antrean WHERE tanggal = ? AND poli_id = ? AND status = 'menunggu' AND nomor < ?", [$a['tanggal'], $a['poli_id'], $a['nomor']])->getRow()->n;
    }

    /**
     * Panggil pasien menunggu dengan nomor terkecil. Pasien yang sedang dipanggil di loket ini otomatis
     * dianggap selesai. Baris dikunci agar dua petugas di poli yang sama tidak memanggil pasien yang sama.
     */
    public function panggilBerikutnya(int $poliId, string $loket, int $petugasId): ?array
    {
        $hari = date('Y-m-d');
        $this->db->transStart();
        // Kunci baris poli lebih dulu agar dua loket yang menekan bersamaan diproses bergantian
        $this->db->query('SELECT id FROM poli WHERE id = ? FOR UPDATE', [$poliId]);
        $this->db->query("UPDATE antrean SET status = 'selesai', selesai_at = ? WHERE tanggal = ? AND poli_id = ? AND status = 'dipanggil' AND loket = ?", [date('Y-m-d H:i:s'), $hari, $poliId, $loket]);
        $a = $this->db->query("SELECT id FROM antrean WHERE tanggal = ? AND poli_id = ? AND status = 'menunggu' ORDER BY nomor LIMIT 1 FOR UPDATE", [$hari, $poliId])->getRowArray();
        if ($a) $this->tandaiDipanggil((int) $a['id'], $loket, $petugasId);
        $this->db->transComplete();
        return $a ? $this->detail((int) $a['id']) : null;
    }

    private function tandaiDipanggil(int $id, string $loket, int $petugasId): void
    {
        $this->db->query("UPDATE antrean SET status = 'dipanggil', loket = ?, petugas_id = ?, panggil_ke = panggil_ke + 1, dipanggil_at = COALESCE(dipanggil_at, ?) WHERE id = ?", [$loket, $petugasId, date('Y-m-d H:i:s'), $id]);
        $this->db->table('panggilan')->insert(['antrean_id' => $id, 'waktu' => date('Y-m-d H:i:s'), 'loket' => $loket]);
    }

    /** Panggil ulang pasien yang sedang dipanggil, atau panggil lagi pasien yang tadi dilewati. */
    public function panggilUlang(int $id, string $loket, int $petugasId): ?array
    {
        $a = $this->detail($id);
        if (! $a || $a['tanggal'] !== date('Y-m-d') || ! in_array($a['status'], ['dipanggil', 'lewat'], true)) return null;
        $this->db->transStart();
        $this->tandaiDipanggil($id, $loket, $petugasId);
        $this->db->transComplete();
        return $this->detail($id);
    }

    public function ubahStatus(int $id, string $status): bool
    {
        $a = $this->detail($id);
        if (! $a || $a['status'] !== 'dipanggil' || ! in_array($status, ['selesai', 'lewat'], true)) return false;
        // Semua waktu memakai jam PHP (zona waktu aplikasi), bukan NOW() MySQL yang bisa berbeda zona di hosting
        $this->db->query("UPDATE antrean SET status = ?, selesai_at = ? WHERE id = ? AND status = 'dipanggil'", [$status, $status === 'selesai' ? date('Y-m-d H:i:s') : null, $id]);
        return $this->db->affectedRows() === 1;
    }

    /** Ringkasan per poli untuk tanggal tertentu. */
    public function ringkasan(string $tgl): array
    {
        return $this->db->query("SELECT p.*, COUNT(a.id) total, SUM(a.status = 'menunggu') menunggu, SUM(a.status = 'selesai') selesai, SUM(a.status = 'lewat') lewat,
            (SELECT CONCAT(p.kode, '-', LPAD(x.nomor, 3, '0')) FROM antrean x WHERE x.tanggal = ? AND x.poli_id = p.id AND x.status IN ('dipanggil','selesai') ORDER BY x.dipanggil_at DESC LIMIT 1) terakhir,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, a.dibuat_at, a.dipanggil_at))) tunggu
            FROM poli p LEFT JOIN antrean a ON a.poli_id = p.id AND a.tanggal = ? WHERE p.aktif = 1 GROUP BY p.id ORDER BY p.urutan, p.id", [$tgl, $tgl])->getResultArray();
    }
}
