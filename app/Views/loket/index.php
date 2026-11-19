<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<?php $kd = fn($r) => \App\Libraries\Antrean::kode($r); ?>
<div class="judul-hal">
  <div><h1>Loket</h1><p><?= $pilih ? 'Melayani <b>' . esc($pilih['nama_poli']) . '</b> di <b>' . esc($pilih['loket']) . '</b>' : 'Pilih poli dan nama ruang / loket Anda' ?></p></div>
  <form method="post" action="<?= site_url('loket/atur') ?>" class="atur-loket">
    <?= csrf_field() ?>
    <select name="poli_id" aria-label="Poli" required><option value="">Pilih poli</option><?php foreach ($poli as $p): ?><option value="<?= (int) $p['id'] ?>" <?= $pilih && (int) $pilih['poli_id'] === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['kode'] . ' · ' . $p['nama']) ?></option><?php endforeach; ?></select>
    <input name="loket" value="<?= esc($pilih['loket'] ?? '') ?>" placeholder="Ruang Periksa 1" maxlength="40" required aria-label="Nama loket / ruang">
    <button class="btn"><?= ikon('cek') ?>Pakai</button>
  </form>
</div>
<?php if ($pilih): ?>
<div class="grid-loket">
  <section class="kartu panggil-kartu">
    <p class="label">Sedang dilayani di <?= esc($pilih['loket']) ?></p>
    <?php if ($aktif): ?>
      <b class="nomor-loket"><?= esc($kd($aktif)) ?></b>
      <p class="muted">Dipanggil <?= date('H.i', strtotime($aktif['dipanggil_at'])) ?><?= $aktif['panggil_ke'] > 1 ? ' · sudah ' . (int) $aktif['panggil_ke'] . 'x' : '' ?></p>
      <div class="baris-tombol">
        <form method="post" action="<?= site_url('loket/ulang/' . $aktif['id']) ?>"><?= csrf_field() ?><button class="btn"><?= ikon('ulang') ?>Panggil ulang</button></form>
        <form method="post" action="<?= site_url('loket/lewati/' . $aktif['id']) ?>"><?= csrf_field() ?><button class="btn"><?= ikon('lompat') ?>Tidak hadir</button></form>
        <form method="post" action="<?= site_url('loket/selesai/' . $aktif['id']) ?>"><?= csrf_field() ?><button class="btn"><?= ikon('cek') ?>Selesai</button></form>
      </div>
    <?php else: ?>
      <b class="nomor-loket kosong">---</b><p class="muted">Belum ada pasien yang dipanggil.</p>
    <?php endif; ?>
    <form method="post" action="<?= site_url('loket/panggil') ?>" data-satu-kali><?= csrf_field() ?>
      <button class="btn koral raksasa" <?= ! $menunggu ? 'disabled' : '' ?>><?= ikon('toa') ?>Panggil berikutnya<?= $menunggu ? ' · ' . esc($kd($menunggu[0])) : '' ?></button>
    </form>
    <p class="kecil muted">Memanggil berikutnya otomatis menandai pasien saat ini selesai.</p>
    <?php if (! empty($lain)): ?><p class="kecil muted">Loket lain: <?= esc(implode(', ', array_map(fn($x) => $x['loket'] . ' ' . $x['kode_poli'] . '-' . str_pad((string) $x['nomor'], 3, '0', STR_PAD_LEFT), $lain))) ?></p><?php endif; ?>
  </section>
  <section class="kartu">
    <h2>Menunggu <span class="hitung"><?= count($menunggu) ?></span></h2>
    <ol class="daftar-nomor"><?php foreach ($menunggu as $m): ?><li><b><?= esc($kd($m)) ?></b><small>ambil <?= date('H.i', strtotime($m['dibuat_at'])) ?> · <?= (int) round((time() - strtotime($m['dibuat_at'])) / 60) ?> mnt</small></li><?php endforeach; ?><?php if (! $menunggu): ?><li class="muted">Tidak ada.</li><?php endif; ?></ol>
  </section>
  <section class="kartu">
    <h2>Dilewati <span class="hitung"><?= count($lewat) ?></span></h2>
    <ol class="daftar-nomor"><?php foreach ($lewat as $m): ?><li><b><?= esc($kd($m)) ?></b><form method="post" action="<?= site_url('loket/ulang/' . $m['id']) ?>"><?= csrf_field() ?><button class="btn kecil"><?= ikon('ulang') ?>Panggil lagi</button></form></li><?php endforeach; ?><?php if (! $lewat): ?><li class="muted">Tidak ada.</li><?php endif; ?></ol>
    <h2 class="mt">Selesai <span class="hitung"><?= count($selesai) ?></span></h2>
    <p class="nomor-selesai"><?= esc(implode(' · ', array_map($kd, array_slice($selesai, 0, 15)))) ?: '-' ?></p>
  </section>
</div>
<?php else: ?>
<div class="grid-poli">
  <?php foreach ($poli as $p): ?>
    <section class="kartu poli-kartu w-<?= esc($p['warna']) ?>">
      <div class="poli-kepala"><span class="huruf"><?= esc($p['kode']) ?></span><div><b><?= esc($p['nama']) ?></b><small><?= esc($p['dokter']) ?></small></div></div>
      <p class="poli-sekarang"><small>Menunggu</small><b><?= (int) $p['menunggu'] ?></b></p>
    </section>
  <?php endforeach; ?>
</div>
<p class="muted">Pilih poli dan tulis nama ruang Anda di atas (mis. <b>Ruang Periksa 1</b>). Nama ruang ini yang dibacakan di layar TV saat memanggil pasien.</p>
<?php endif; ?>
<?= $this->endSection() ?>
