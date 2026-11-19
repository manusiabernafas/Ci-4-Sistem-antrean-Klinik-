<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<?php $d = $ubah ?? ['id' => 0, 'kode' => old('kode') ?? '', 'nama' => old('nama') ?? '', 'dokter' => old('dokter') ?? '', 'kuota' => old('kuota') ?? 0, 'warna' => old('warna') ?? 'hijau', 'urutan' => old('urutan') ?? count($rows) + 1, 'aktif' => 1]; ?>
<div class="judul-hal"><div><h1>Poli</h1><p>Setiap poli punya huruf nomor sendiri (A-001, B-001, ...) dan nomor dimulai dari 1 setiap hari.</p></div></div>
<div class="grid dua lebar-kiri">
  <section class="kartu rata"><div class="tabel-wrap"><table class="tabel">
    <thead><tr><th>Kode</th><th>Poli</th><th>Kuota/hari</th><th>Status</th><th></th></tr></thead>
    <tbody><?php foreach ($rows as $p): ?>
      <tr><td><span class="huruf kecil w-<?= esc($p['warna']) ?>"><?= esc($p['kode']) ?></span></td><td><b><?= esc($p['nama']) ?></b><div class="kecil muted"><?= esc($p['dokter']) ?></div></td><td><?= $p['kuota'] ? (int) $p['kuota'] : 'tanpa batas' ?></td>
        <td><?= $p['aktif'] ? '<span class="chip ok">aktif</span>' : '<span class="chip muted">nonaktif</span>' ?></td>
        <td class="kanan nowrap"><a class="ibtn" href="<?= site_url('admin/poli') ?>?ubah=<?= (int) $p['id'] ?>" aria-label="Ubah <?= esc($p['nama']) ?>"><?= ikon('edit') ?></a>
          <?php if (! $p['dipakai']): ?><form method="post" class="inline" data-yakin="Hapus poli <?= esc($p['nama']) ?>?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="ibtn" aria-label="Hapus <?= esc($p['nama']) ?>"><?= ikon('hapus') ?></button></form><?php endif; ?></td></tr>
    <?php endforeach; ?></tbody></table></div></section>
  <form method="post" class="kartu">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
    <h2><?= $ubah ? 'Ubah poli' : 'Poli baru' ?></h2>
    <div class="baris-f"><label class="f"><span>Kode huruf</span><input name="kode" value="<?= esc($d['kode']) ?>" maxlength="2" required placeholder="A"></label>
      <label class="f"><span>Warna</span><select name="warna"><?php foreach (\App\Libraries\Antrean::WARNA as $k => $l): ?><option value="<?= $k ?>" <?= $d['warna'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label></div>
    <label class="f"><span>Nama poli</span><input name="nama" value="<?= esc($d['nama']) ?>" required maxlength="80" placeholder="Poli Umum"></label>
    <label class="f"><span>Dokter / petugas</span><input name="dokter" value="<?= esc($d['dokter']) ?>" maxlength="100"></label>
    <div class="baris-f"><label class="f"><span>Kuota per hari</span><input type="number" name="kuota" value="<?= (int) $d['kuota'] ?>" min="0" max="999"><small>0 = tanpa batas</small></label>
      <label class="f"><span>Urutan</span><input type="number" name="urutan" value="<?= (int) $d['urutan'] ?>"></label></div>
    <label class="cek"><input type="checkbox" name="aktif" value="1" <?= $d['aktif'] ? 'checked' : '' ?>> Tampil di kios</label>
    <button class="btn utama"><?= ikon('cek') ?>Simpan</button>
  </form>
</div>
<?= $this->endSection() ?>
