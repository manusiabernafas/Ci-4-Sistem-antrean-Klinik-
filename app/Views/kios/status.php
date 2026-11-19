<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<?php $kode = \App\Libraries\Antrean::kode($a); $st = \App\Libraries\Antrean::STATUS[$a['status']]; ?>
<meta http-equiv="refresh" content="20">
<div class="status-hp">
  <p class="muted"><?= esc($set['nama_klinik']) ?> · <?= esc(tgl_indo($a['tanggal'], true)) ?></p>
  <section class="kartu tiket-hp w-<?= esc($a['warna']) ?>">
    <small>Nomor Anda</small>
    <b class="nomor-hp"><?= esc($kode) ?></b>
    <span><?= esc($a['nama_poli']) ?></span>
    <span class="chip <?= $st[1] ?>"><?= esc($st[0]) ?></span>
  </section>
  <?php if ($a['status'] === 'menunggu'): ?>
    <div class="dua-kotak">
      <div class="kartu"><small>Sedang dipanggil</small><b><?= $sekarang ? esc($a['kode_poli'] . '-' . str_pad((string) $sekarang['nomor'], 3, '0', STR_PAD_LEFT)) : '-' ?></b></div>
      <div class="kartu"><small>Di depan Anda</small><b><?= $didepan ?></b></div>
    </div>
    <p class="muted tengah"><?= $didepan <= 3 ? 'Giliran Anda sudah dekat, mohon bersiap di ruang tunggu.' : 'Anda bisa menunggu di luar, halaman ini diperbarui otomatis tiap 20 detik.' ?></p>
  <?php elseif ($a['status'] === 'dipanggil'): ?>
    <p class="pesan ok besar-teks"><?= ikon('toa') ?><span>Nomor Anda sedang dipanggil. Silakan menuju <b><?= esc($a['loket']) ?></b>.</span></p>
  <?php elseif ($a['status'] === 'lewat'): ?>
    <p class="pesan error"><?= ikon('alert') ?><span>Nomor Anda sudah dipanggil tetapi tidak ada di tempat. Segera lapor ke petugas <?= esc($a['nama_poli']) ?> agar dipanggil lagi.</span></p>
  <?php else: ?>
    <p class="pesan ok"><?= ikon('cek') ?><span>Pelayanan selesai. Terima kasih, semoga lekas sehat.</span></p>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
