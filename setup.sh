#!/usr/bin/env bash
# Setup Antrean Klinik (Linux / macOS). Pastikan MySQL menyala.
set -e
cd "$(dirname "$0")"
php -v >/dev/null 2>&1 || { echo "PHP belum terpasang (butuh PHP 8.2+)."; exit 1; }
php -m | grep -qi '^intl$' || { echo "Ekstensi PHP intl belum aktif. Ubuntu: sudo apt install php-intl  | macOS (brew): sudah termasuk."; exit 1; }
if [ ! -f vendor/autoload.php ]; then
  command -v composer >/dev/null || { echo "Composer belum terpasang: https://getcomposer.org"; exit 1; }
  composer install --no-dev --optimize-autoloader --no-interaction
fi
[ -f .env ] || cp .env.example .env
mkdir -p writable/cache writable/logs writable/session writable/debugbar
php spark db:create antrean_klinik >/dev/null 2>&1 || true
php spark migrate --all
php spark db:seed DatabaseSeeder
chmod -R ug+rw writable
echo
echo "Selesai. Jalankan: php spark serve   lalu buka http://localhost:8080"
echo "Login admin: admin / admin123   petugas loket: petugas / petugas123"
echo "Alamat kios & layar TV ada di menu Pengaturan."
