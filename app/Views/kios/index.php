<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<div class="kios">
  <header class="kios-atas"><span class="logo"><?= ikon('stetoskop') ?></span><div><h1><?= esc($set['nama_klinik']) ?></h1><p>Sentuh poli tujuan untuk mengambil nomor antrean</p></div><b class="jam-kios" data-jam>--:--</b></header>
  <form method="post" class="pilihan-poli" data-satu-kali>
    <?= csrf_field() ?>
    <?php foreach ($poli as $p): $habis = $p['kuota'] > 0 && $p['total'] >= $p['kuota']; ?>
      <button name="poli_id" value="<?= (int) $p['id'] ?>" class="tombol-poli w-<?= esc($p['warna']) ?>" <?= $habis ? 'disabled' : '' ?>>
        <span class="huruf"><?= esc($p['kode']) ?></span>
        <span class="info"><b><?= esc($p['nama']) ?></b><small><?= esc($p['dokter']) ?></small></span>
        <span class="angka"><b><?= (int) $p['menunggu'] ?></b><small>menunggu</small></span>
        <?php if ($habis): ?><span class="habis">Kuota habis</span><?php elseif ($p['kuota']): ?><span class="sisa">sisa <?= (int) ($p['kuota'] - $p['total']) ?></span><?php endif; ?>
      </button>
    <?php endforeach; ?>
  </form>
  <p class="kios-bawah"><?= esc($set['jam_layanan']) ?></p>
</div>
<?= $this->endSection() ?>
