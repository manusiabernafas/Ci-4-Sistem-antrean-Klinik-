<?= $this->extend('layout') ?>
<?= $this->section('isi') ?>
<div class="judul-hal"><div><h1>Petugas</h1><p>Petugas memakai halaman Loket untuk memanggil pasien. Admin mengatur poli, laporan, dan pengaturan.</p></div></div>
<div class="grid dua lebar-kiri">
  <section class="kartu rata"><div class="tabel-wrap"><table class="tabel"><thead><tr><th>Nama</th><th>Peran</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $u): ?><tr><td><b><?= esc($u['nama']) ?></b><div class="kecil muted"><?= esc($u['username']) ?></div></td><td><?= $u['role'] === 'admin' ? 'Admin' : 'Petugas' ?></td><td><?= $u['aktif'] ? '<span class="chip ok">aktif</span>' : '<span class="chip muted">nonaktif</span>' ?></td>
      <td class="kanan"><a class="ibtn" href="<?= site_url('admin/pengguna') ?>?ubah=<?= (int) $u['id'] ?>" aria-label="Ubah <?= esc($u['nama']) ?>"><?= ikon('edit') ?></a></td></tr><?php endforeach; ?>
  </tbody></table></div></section>
  <form method="post" class="kartu">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($ubah['id'] ?? 0) ?>">
    <h2><?= $ubah ? 'Ubah ' . esc($ubah['nama']) : 'Petugas baru' ?></h2>
    <label class="f"><span>Nama</span><input name="nama" value="<?= esc($ubah['nama'] ?? old('nama') ?? '') ?>" required maxlength="100"></label>
    <label class="f"><span>Username</span><input name="username" value="<?= esc($ubah['username'] ?? old('username') ?? '') ?>" required maxlength="40" autocomplete="off"></label>
    <label class="f"><span>Peran</span><select name="role"><option value="petugas">Petugas</option><option value="admin" <?= ($ubah['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option></select></label>
    <label class="f"><span>Kata sandi<?= $ubah ? ' baru (kosongkan bila tetap)' : '' ?></span><input type="password" name="password" <?= $ubah ? '' : 'required' ?> autocomplete="new-password"></label>
    <label class="cek"><input type="checkbox" name="aktif" value="1" <?= ! $ubah || $ubah['aktif'] ? 'checked' : '' ?>> Akun aktif</label>
    <button class="btn utama"><?= ikon('cek') ?>Simpan</button>
  </form>
</div>
<?= $this->endSection() ?>
