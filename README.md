# Sistem Antrean Klinik (CodeIgniter 4 + MySQL)

Antrean untuk klinik, puskesmas, praktik dokter bersama, atau layanan apa pun yang punya beberapa loket: pasien mengambil nomor di **kios layar sentuh**, petugas memanggil dari **halaman loket**, nomor diumumkan dengan **suara** dan tampil besar di **layar TV** ruang tunggu, dan pasien bisa **memantau antrean dari HP** lewat QR di tiket.

## Isi folder
```
app/Libraries/Antrean.php   aturan antrean: ambil nomor, panggil, lewati, selesai, ringkasan
app/Controllers/            Kios, Layar (TV), Loket (petugas), Admin, Auth, Profil
app/Views/                  tampilan (kios, tiket 58 mm, status HP, layar TV, loket, admin)
public/assets/js/layar.js   layar TV: ambil data tiap 2 detik, nada + suara panggilan
app/Database/               migrasi tabel & data contoh (4 poli, riwayat 14 hari)
setup.bat / setup.sh        pasang dependensi, buat database, tabel, dan data contoh
```

## Instalasi (XAMPP / Laragon)
Kebutuhan: PHP 8.2+ dengan ekstensi **intl**, MySQL/MariaDB, dan Composer.
1. Salin folder `antrean-klinik` ke `C:\xampp\htdocs\`.
2. Aktifkan intl: buka `C:\xampp\php\php.ini`, hapus `;` di depan `extension=intl`, restart Apache.
3. Nyalakan Apache & MySQL, lalu klik dua kali `setup.bat` (Linux/macOS: `bash setup.sh`).
4. Buka `http://localhost/antrean-klinik`.

| Peran | Username | Sandi |
|---|---|---|
| Admin | `admin` | `admin123` |
| Petugas loket | `petugas` | `petugas123` |

Alamat **kios** dan **layar TV** (dibuka tanpa login, dilindungi kode acak) ada di menu **Pengaturan** dan tombol di halaman Ringkasan admin.

## Perangkat yang disarankan
- **Kios**: tablet / PC layar sentuh + printer thermal 58 mm. Buka alamat kios di Chrome, aktifkan cetak tanpa dialog (`--kiosk-printing`) bila ingin tiket langsung tercetak.
- **Layar TV**: Smart TV / mini PC dengan Chrome, sambungkan ke speaker. Tekan **Aktifkan** sekali agar suara boleh diputar, lalu tekan F11.
- **Loket**: komputer atau HP petugas. Pilih poli dan tulis nama ruang (mis. Ruang Periksa 1); nama ini yang dibacakan di TV.

## Cara kerja singkat
- Nomor dimulai dari 1 setiap hari per poli (A-001, B-001, ...). Kuota harian per poli bisa dibatasi.
- Saat beberapa kios menekan bersamaan atau dua loket memanggil bersamaan, baris poli dikunci di database sehingga nomor tidak pernah dobel dan dua loket tidak memanggil pasien yang sama.
- "Panggil berikutnya" otomatis menandai pasien sebelumnya di loket itu selesai. Pasien yang tidak hadir bisa dilewati lalu dipanggil lagi.
- Layar TV membaca nomor angka sebagai kata ("A, dua belas") lewat suara Bahasa Indonesia dari browser, didahului nada ding-dong.

## Keamanan
CSRF di semua form, password_hash, batas percobaan login & ambil tiket per IP (database), hak akses admin/petugas, akun nonaktif langsung keluar, kios & TV memakai kode acak yang bisa diganti, token tiket acak untuk cek status, folder `app/`, `writable/`, dan `.env` tidak bisa dibuka dari browser, dan CSV laporan aman dari formula injection.
