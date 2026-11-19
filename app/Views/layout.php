<?php $u = $user ?? []; $hal = uri_string(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title><?= esc($judul ?? 'Antrean') ?> · <?= esc($set['nama_klinik']) ?></title>
<link rel="icon" href="<?= base_url('assets/img/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=1') ?>">
</head>
<body class="hal-<?= esc(explode('/', $hal ?: 'awal')[0]) ?>">
<?php if ($u): ?>
<header class="atas">
  <a class="merek" href="<?= site_url('/') ?>"><span class="logo"><?= ikon('stetoskop') ?></span><span><b><?= esc($set['nama_klinik']) ?></b><small>Sistem antrean</small></span></a>
  <nav class="menu">
    <a href="<?= site_url('loket') ?>" class="<?= str_starts_with($hal, 'loket') ? 'aktif' : '' ?>"><?= ikon('toa') ?><span>Loket</span></a>
    <?php if ($u['role'] === 'admin'): ?>
      <a href="<?= site_url('admin') ?>" class="<?= $hal === 'admin' ? 'aktif' : '' ?>"><?= ikon('rekap') ?><span>Ringkasan</span></a>
      <a href="<?= site_url('admin/poli') ?>" class="<?= $hal === 'admin/poli' ? 'aktif' : '' ?>"><?= ikon('stetoskop') ?><span>Poli</span></a>
      <a href="<?= site_url('admin/laporan') ?>" class="<?= $hal === 'admin/laporan' ? 'aktif' : '' ?>"><?= ikon('kalender') ?><span>Laporan</span></a>
      <a href="<?= site_url('admin/pengguna') ?>" class="<?= $hal === 'admin/pengguna' ? 'aktif' : '' ?>"><?= ikon('pengguna') ?><span>Petugas</span></a>
      <a href="<?= site_url('admin/pengaturan') ?>" class="<?= $hal === 'admin/pengaturan' ? 'aktif' : '' ?>"><?= ikon('atur') ?><span>Pengaturan</span></a>
    <?php endif; ?>
  </nav>
  <div class="saya">
    <a href="<?= site_url('profil') ?>" class="saya-nama"><span class="av"><?= esc(inisial($u['nama'])) ?></span><span><?= esc($u['nama']) ?></span></a>
    <form method="post" action="<?= site_url('logout') ?>"><?= csrf_field() ?><button class="ibtn" title="Keluar" aria-label="Keluar"><?= ikon('keluar') ?></button></form>
  </div>
</header>
<?php endif; ?>
<main class="isi">
  <?php foreach (['ok' => 'cek', 'error' => 'alert', 'info' => 'jam'] as $t => $ik): if ($m = session()->getFlashdata($t)): ?>
    <div class="pesan <?= $t ?>" role="status"><?= ikon($ik) ?><span><?= esc($m) ?></span></div>
  <?php endif; endforeach; ?>
  <?= $this->renderSection('isi') ?>
</main>
<script src="<?= base_url('assets/js/app.js?v=1') ?>"></script>
<?= $this->renderSection('skrip') ?>
</body>
</html>
