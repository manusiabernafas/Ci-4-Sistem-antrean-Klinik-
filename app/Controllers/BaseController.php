<?php

namespace App\Controllers;

use App\Libraries\Antrean;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** @var CLIRequest|IncomingRequest */
    protected $request;

    protected $helpers = ['form', 'url', 'tanggal', 'ikon'];
    protected $db;
    protected Antrean $antrean;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = db_connect();
        $this->antrean = new Antrean();
    }

    protected function user(): array
    {
        return session('user') ?? [];
    }

    protected function isAdmin(): bool
    {
        return ($this->user()['role'] ?? '') === 'admin';
    }

    /** Kembali ke halaman sebelumnya dengan pesan error validasi + input lama. */
    protected function gagal(string $pesan = 'Periksa kembali isian yang ditandai.')
    {
        return redirect()->back()->withInput()->with('error', $pesan);
    }

    /**
     * Aturan validasi untuk input berkas opsional. Catatan: "permit_empty" pada field
     * berkas melewati SEMUA aturan (karena berkas tidak ada di POST), jadi aturan
     * hanya dipasang bila memang ada berkas yang diunggah.
     */
    protected function aturanBerkas(string $field, string $label, string $aturan): array
    {
        $f = $this->request->getFile($field);
        $ada = $f && $f->getError() !== UPLOAD_ERR_NO_FILE;
        return ['label' => $label, 'rules' => $ada ? "uploaded[{$field}]|{$aturan}" : 'permit_empty'];
    }

    /** Batas percobaan per IP disimpan di database (tidak bisa diakali dengan menghapus cookie). */
    protected function terkunci(string $kunci, int $maks, int $detik): bool
    {
        $r = $this->db->query('SELECT n, mulai FROM batas_coba WHERE kunci = ?', [$kunci])->getRowArray();
        if (! $r) return false;
        if (strtotime($r['mulai']) < time() - $detik) {
            $this->db->query('DELETE FROM batas_coba WHERE kunci = ?', [$kunci]);
            return false;
        }
        return (int) $r['n'] >= $maks;
    }

    protected function catatCoba(string $kunci, int $detik): void
    {
        $this->db->query('INSERT INTO batas_coba (kunci, n, mulai) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE n = IF(mulai < NOW() - INTERVAL ? SECOND, 1, n + 1), mulai = IF(mulai < NOW() - INTERVAL ? SECOND, NOW(), mulai)', [$kunci, $detik, $detik]);
    }

    /** Kios & layar TV dibuka tanpa login, dilindungi kunci rahasia di alamatnya. */
    protected function cekKunci(string $kunci): void
    {
        $asli = $this->antrean->get('kunci_layar');
        if ($asli === '' || ! hash_equals($asli, $kunci)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Alamat layar tidak dikenal.');
        }
    }

    protected function render(string $view, array $data = []): string
    {
        $data['user'] = $this->user();
        $data['set'] = $this->antrean->semua();
        return view($view, $data);
    }
}
