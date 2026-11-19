<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<div class="judul-hal"><div><h1>Laporan</h1><p><?= esc(tgl_indo($dari)) ?> - <?= esc(tgl_indo($sampai)) ?></p></div>
  <div class="aksi"><a class="btn" href="<?= site_url('admin/laporan/csv') ?>?dari=<?= esc($dari) ?>&amp;sampai=<?= esc($sampai) ?>"><?= ikon('unduh') ?>CSV</a></div></div>
<form method="get" class="kartu filter"><label class="f"><span>Dari</span><input type="date" name="dari" value="<?= esc($dari) ?>"></label><label class="f"><span>Sampai</span><input type="date" name="sampai" value="<?= esc($sampai) ?>"></label><button class="btn"><?= ikon('rekap') ?>Tampilkan</button></form>
<section class="kartu rata"><div class="tabel-wrap"><table class="tabel">
  <thead><tr><th>Poli</th><th class="kanan">Tiket</th><th class="kanan">Selesai</th><th class="kanan">Tidak hadir</th><th class="kanan">Rata tunggu</th><th class="kanan">Tunggu terlama</th><th class="kanan">Rata layanan</th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?><tr><td><b><?= esc($r['kode']) ?></b> <?= esc($r['nama']) ?></td><td class="kanan"><?= (int) $r['total'] ?></td><td class="kanan"><?= (int) $r['selesai'] ?></td><td class="kanan"><?= (int) $r['lewat'] ?></td>
    <td class="kanan"><?= $r['tunggu'] !== null ? (int) $r['tunggu'] . ' mnt' : '-' ?></td><td class="kanan"><?= $r['tunggu_maks'] !== null ? (int) $r['tunggu_maks'] . ' mnt' : '-' ?></td><td class="kanan"><?= $r['layan'] !== null ? (int) $r['layan'] . ' mnt' : '-' ?></td></tr><?php endforeach; ?></tbody>
</table></div></section>
<?= $this->endSection() ?>
