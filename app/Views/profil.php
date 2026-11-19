<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<div class="judul-hal"><div><h1>Akun</h1><p><?= esc($user['nama']) ?> · <?= esc($user['username']) ?> · <?= $user['role'] === 'admin' ? 'Admin' : 'Petugas' ?></p></div></div>
<form method="post" class="kartu sempit">
  <?= csrf_field() ?>
  <h2>Ganti kata sandi</h2>
  <label class="f"><span>Kata sandi lama</span><input type="password" name="lama" required autocomplete="current-password"></label>
  <label class="f"><span>Kata sandi baru</span><input type="password" name="baru" required minlength="8" autocomplete="new-password"></label>
  <label class="f"><span>Ulangi kata sandi baru</span><input type="password" name="ulang" required autocomplete="new-password"></label>
  <button class="btn utama"><?= ikon('cek') ?>Simpan</button>
</form>
<?= $this->endSection() ?>
