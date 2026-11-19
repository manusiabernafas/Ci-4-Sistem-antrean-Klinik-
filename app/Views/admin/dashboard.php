<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<?php $tot = array_sum(array_column($poli, 'total')); $sel = array_sum(array_column($poli, 'selesai')); $mng = array_sum(array_column($poli, 'menunggu')); $tw = array_filter(array_column($poli, 'tunggu'), fn($x) => $x !== null); $kunci = $set['kunci_layar']; ?>
<div class="judul-hal"><div><h1>Ringkasan hari ini</h1><p><?= esc(tgl_indo(date('Y-m-d'), true)) ?></p></div>
  <div class="aksi"><a class="btn" href="<?= site_url('kios/' . $kunci) ?>" target="_blank"><?= ikon('tiket') ?>Buka kios</a><a class="btn utama" href="<?= site_url('layar/' . $kunci) ?>" target="_blank"><?= ikon('monitor') ?>Buka layar TV</a></div></div>
<div class="statistik">
  <div class="stat"><small>Tiket diambil</small><b><?= $tot ?></b></div>
  <div class="stat"><small>Selesai dilayani</small><b><?= $sel ?></b></div>
  <div class="stat koral"><small>Masih menunggu</small><b><?= $mng ?></b></div>
  <div class="stat"><small>Rata-rata tunggu</small><b><?= $tw ? round(array_sum($tw) / count($tw)) : 0 ?><span> mnt</span></b></div>
</div>
<div class="grid-poli">
  <?php foreach ($poli as $p): ?>
    <section class="kartu poli-kartu w-<?= esc($p['warna']) ?>">
      <div class="poli-kepala"><span class="huruf"><?= esc($p['kode']) ?></span><div><b><?= esc($p['nama']) ?></b><small><?= esc($p['dokter']) ?></small></div></div>
      <p class="poli-sekarang"><small>Terakhir dipanggil</small><b><?= esc($p['terakhir'] ?? '-') ?></b></p>
      <div class="poli-angka"><span><b><?= (int) $p['total'] ?></b>tiket</span><span><b><?= (int) $p['menunggu'] ?></b>menunggu</span><span><b><?= (int) $p['selesai'] ?></b>selesai</span><span><b><?= $p['tunggu'] !== null ? (int) $p['tunggu'] : '-' ?></b>mnt tunggu</span></div>
      <?php if ($p['kuota']): ?><div class="kuota"><i style="width:<?= min(100, round($p['total'] / $p['kuota'] * 100)) ?>%"></i></div><small class="muted">kuota <?= (int) $p['total'] ?>/<?= (int) $p['kuota'] ?></small><?php endif; ?>
    </section>
  <?php endforeach; ?>
</div>
<div class="grid dua">
  <section class="kartu"><h2>Tiket per jam (hari ini)</h2>
    <?php $mx = max([1] + array_column($perJam, 'n')); ?>
    <div class="grafik"><?php foreach (range(7, 16) as $j): $n = 0; foreach ($perJam as $x) if ((int) $x['j'] === $j) $n = (int) $x['n']; ?><div><b style="height:<?= max(2, round($n / $mx * 100)) ?>%"><em><?= $n ?: '' ?></em></b><span><?= sprintf('%02d', $j) ?></span></div><?php endforeach; ?></div>
  </section>
  <section class="kartu"><h2>14 hari terakhir</h2>
    <?php $mx2 = max([1] + array_column($tren, 'n')); ?>
    <div class="grafik"><?php foreach ($tren as $x): ?><div><b style="height:<?= max(2, round($x['n'] / $mx2 * 100)) ?>%"><em><?= (int) $x['n'] ?></em></b><span><?= date('j', strtotime($x['tanggal'])) ?></span></div><?php endforeach; ?></div>
  </section>
</div>
<?= $this->endSection() ?>
