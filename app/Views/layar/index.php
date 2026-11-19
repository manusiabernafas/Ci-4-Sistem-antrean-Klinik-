<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Layar antrean · <?= esc($set['nama_klinik']) ?></title>
<link rel="icon" href="<?= base_url('assets/img/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=1') ?>">
</head>
<body class="hal-layar">
<div class="layar" id="layar" data-sumber="<?= esc(site_url('layar/' . $kunci . '/data')) ?>">
  <header class="layar-atas">
    <span class="logo"><?= ikon('stetoskop') ?></span>
    <div><h1><?= esc($set['nama_klinik']) ?></h1><p><?= esc($set['alamat']) ?></p></div>
    <div class="layar-jam"><b id="jam">--:--</b><span><?= esc(tgl_indo(date('Y-m-d'), true)) ?></span></div>
  </header>
  <section class="dipanggil" id="dipanggil">
    <p class="label">Nomor dipanggil</p>
    <b class="nomor-besar" id="nomor">---</b>
    <p class="ke" id="ke">Menunggu panggilan pertama</p>
  </section>
  <section class="riwayat-panggil"><h2>Panggilan sebelumnya</h2><ol id="riwayat"></ol></section>
  <section class="poli-layar" id="poli"></section>
  <footer class="berjalan"><span id="teks"><?= esc($set['teks_berjalan']) ?></span></footer>
</div>
<div class="tirai" id="tirai">
  <div class="kartu tengah">
    <span class="logo besar"><?= ikon('volume') ?></span>
    <h2>Aktifkan suara panggilan</h2>
    <p class="muted">Browser hanya boleh memutar suara setelah ada klik. Tekan sekali, lalu jadikan layar penuh (F11).</p>
    <button class="btn utama besar" id="aktifkan"><?= ikon('volume') ?>Aktifkan</button>
  </div>
</div>
<script src="<?= base_url('assets/js/layar.js?v=1') ?>"></script>
</body>
</html>
