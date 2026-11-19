<?php

namespace App\Controllers;

/** Layar TV ruang tunggu: nomor yang dipanggil + suara. Data diambil berkala lewat JSON. */
class Layar extends BaseController
{
    public function index(string $kunci)
    {
        $this->cekKunci($kunci);
        return $this->render('layar/index', ['kunci' => $kunci]);
    }

    public function data(string $kunci)
    {
        $this->cekKunci($kunci);
        $hari = date('Y-m-d');
        $baru = $this->db->query("SELECT g.id, g.loket, a.nomor, p.kode kode_poli, p.nama nama_poli, p.warna FROM panggilan g JOIN antrean a ON a.id = g.antrean_id JOIN poli p ON p.id = a.poli_id
                                  WHERE a.tanggal = ? ORDER BY g.id DESC LIMIT 6", [$hari])->getResultArray();
        foreach ($baru as &$b) $b['kode'] = $b['kode_poli'] . '-' . str_pad((string) $b['nomor'], 3, '0', STR_PAD_LEFT);
        $poli = array_map(fn($p) => ['kode' => $p['kode'], 'nama' => $p['nama'], 'warna' => $p['warna'], 'terakhir' => $p['terakhir'], 'menunggu' => (int) $p['menunggu']], $this->antrean->ringkasan($hari));
        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON([
            'server' => round(microtime(true) * 1000), 'panggilan' => $baru, 'poli' => $poli,
            'teks' => $this->antrean->get('teks_berjalan'), 'suara' => $this->antrean->get('suara') === '1', 'kecepatan' => (float) $this->antrean->get('kecepatan_suara'),
        ]);
    }
}
