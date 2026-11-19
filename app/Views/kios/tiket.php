<?php $kode = \App\Libraries\Antrean::kode($a); $url = site_url('status/' . $a['token']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Tiket <?= esc($kode) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=1') ?>">
</head>
<body class="hal-tiket">
<article class="tiket">
  <p class="t-klinik"><b><?= esc($set['nama_klinik']) ?></b><br><?= esc($set['alamat']) ?></p>
  <p class="t-label">Nomor antrean</p>
  <p class="t-nomor"><?= esc($kode) ?></p>
  <p class="t-poli"><?= esc($a['nama_poli']) ?><?= $a['dokter'] ? '<br><small>' . esc($a['dokter']) . '</small>' : '' ?></p>
  <p class="t-depan"><?= $didepan ?> antrean di depan Anda</p>
  <div class="qr" data-url="<?= esc($url) ?>"></div>
  <p class="t-kecil">Pindai untuk memantau antrean dari HP</p>
  <p class="t-kecil"><?= esc(tgl_indo($a['tanggal'], true)) ?> · <?= date('H.i', strtotime($a['dibuat_at'])) ?></p>
  <p class="t-kecil"><?= esc($set['catatan_tiket']) ?></p>
</article>
<div class="tiket-aksi">
  <button class="btn utama" onclick="window.print()"><?= ikon('cetak') ?>Cetak tiket</button>
  <a class="btn" href="<?= esc($url) ?>"><?= ikon('jam') ?>Pantau antrean</a>
  <?php if ($kunci): ?><a class="btn" href="<?= site_url('kios/' . $kunci) ?>" data-kembali="12"><?= ikon('home') ?>Kembali ke kios <span data-hitung>12</span></a><?php endif; ?>
</div>
<script src="<?= base_url('assets/js/qrcode.js') ?>"></script>
<script>
document.querySelectorAll('.qr').forEach(function (el) { var q = qrcode(0, 'M'); q.addData(el.dataset.url); q.make(); el.innerHTML = q.createSvgTag({ cellSize: 3, margin: 0, scalable: true }); });
<?php if ($cetak): ?>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });<?php endif; ?>
// Kios kembali ke layar awal otomatis agar pasien berikutnya bisa mengambil nomor
var k = document.querySelector('[data-kembali]');
if (k) { var n = +k.dataset.kembali, h = k.querySelector('[data-hitung]'); setInterval(function () { n--; h.textContent = n; if (n <= 0) location.href = k.href; }, 1000); }
</script>
</body>
</html>
