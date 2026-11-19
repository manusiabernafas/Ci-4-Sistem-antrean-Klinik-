<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<?php $k = $set['kunci_layar']; ?>
<div class="judul-hal"><div><h1>Pengaturan</h1></div></div>
<div class="grid dua">
  <form method="post" class="kartu">
    <?= csrf_field() ?>
    <h2>Klinik & tiket</h2>
    <label class="f"><span>Nama klinik</span><input name="nama_klinik" value="<?= esc($set['nama_klinik']) ?>" required maxlength="120"></label>
    <label class="f"><span>Alamat</span><input name="alamat" value="<?= esc($set['alamat']) ?>" maxlength="200"></label>
    <div class="baris-f"><label class="f"><span>Telepon</span><input name="telepon" value="<?= esc($set['telepon']) ?>" maxlength="40"></label>
      <label class="f"><span>Jam layanan</span><input name="jam_layanan" value="<?= esc($set['jam_layanan']) ?>" maxlength="80"></label></div>
    <label class="f"><span>Catatan di tiket</span><input name="catatan_tiket" value="<?= esc($set['catatan_tiket']) ?>" maxlength="200"></label>
    <label class="f"><span>Teks berjalan di layar TV</span><textarea name="teks_berjalan" rows="2" maxlength="300"><?= esc($set['teks_berjalan']) ?></textarea></label>
    <div class="baris-f"><label class="cek"><input type="checkbox" name="suara" value="1" <?= $set['suara'] === '1' ? 'checked' : '' ?>> Suara panggilan di layar TV</label>
      <label class="f"><span>Kecepatan suara</span><input name="kecepatan_suara" value="<?= esc($set['kecepatan_suara']) ?>" inputmode="decimal"></label></div>
    <button class="btn utama"><?= ikon('cek') ?>Simpan</button>
  </form>
  <section class="kartu">
    <h2>Alamat kios & layar TV</h2>
    <p class="muted kecil">Dibuka tanpa login, jadi dilindungi kode acak di alamatnya. Simpan sebagai bookmark di komputer kios dan TV.</p>
    <label class="f"><span>Kios ambil nomor (layar sentuh)</span><input readonly value="<?= esc(site_url('kios/' . $k)) ?>" onclick="this.select()"></label>
    <label class="f"><span>Layar TV ruang tunggu</span><input readonly value="<?= esc(site_url('layar/' . $k)) ?>" onclick="this.select()"></label>
    <form method="post" data-yakin="Ganti alamat? Kios dan TV yang sedang terbuka harus dibuka ulang dengan alamat baru."><?= csrf_field() ?><input type="hidden" name="aksi" value="kunci_baru"><button class="btn"><?= ikon('kunci') ?>Buat alamat baru</button></form>
  </section>
</div>
<?= $this->endSection() ?>
