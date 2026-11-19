<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<div class="masuk">
  <section class="masuk-sapa">
    <span class="logo besar"><?= ikon('stetoskop') ?></span>
    <h1><?= esc($set['nama_klinik']) ?></h1>
    <p>Antrean tertib tanpa berdesakan: ambil nomor di kios, nomor dipanggil dengan suara, pasien memantau dari HP.</p>
    <div class="contoh-nomor" aria-hidden="true"><span>A-012</span><span>B-004</span><span>C-021</span></div>
  </section>
  <form method="post" action="<?= site_url('login') ?>" class="kartu">
    <?= csrf_field() ?>
    <h2>Masuk petugas</h2>
    <label class="f"><span>Username</span><input name="username" value="<?= esc(old('username') ?? '') ?>" autocomplete="username" required autofocus></label>
    <label class="f"><span>Kata sandi</span><input name="password" type="password" autocomplete="current-password" required></label>
    <button class="btn utama blok">Masuk</button>
  </form>
</div>
<?= $this->endSection() ?>
