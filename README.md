# FZM Production Mediatama

Website portofolio dan pemesanan event organizer, dibuat dengan Laravel 13 dan MySQL.

## Menjalankan di MacBook (Herd + DBngin + TablePlus)

1. Buka DBngin dan nyalakan server MySQL. Proyek ini memakai port `3310`, user `root`, tanpa kata sandi. Jika berbeda, ubah `DB_PORT` di `.env` dan `phpunit.xml`.
2. Buat database `fzm_production`, dan `fzm_production_test` untuk pengujian (lewat TablePlus atau perintah di bawah):

```bash
/Users/Shared/DBngin/mysql/9.7.2_arm64/bin/mysql -h127.0.0.1 -P3310 -uroot -e "CREATE DATABASE IF NOT EXISTS fzm_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS fzm_production_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

3. Siapkan proyek:

```bash
composer install
cp -n .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
herd link fzm-website
```

4. Buka `http://fzm-website.test`. Jika situs tidak merespons, jalankan `herd start`.

Panel admin ada di `http://fzm-website.test/admin`, dengan akun lokal `admin@fzm.id` / `admin123`.

Herd membatasi unggahan 2 MB secara default, baik di nginx maupun PHP. Agar foto portofolio sampai 5 MB bisa diunggah, naikkan batasnya di pengaturan PHP aplikasi Herd, misalnya ke 64 MB. Sebelum itu, panel admin menolak unggahan di atas batas dengan pesan yang jelas.

## Melihat database di TablePlus

Buat koneksi MySQL baru: host `127.0.0.1`, port `3310`, user `root`, kata sandi kosong, database `fzm_production`.

## Data demo

```bash
php artisan db:seed --class=DemoSeeder
```

Mengisi portofolio, klien, booking, dan pendapatan contoh. Untuk menghapusnya dan kembali ke data awal:

```bash
php artisan migrate:fresh --seed
```

## Pengujian

```bash
php artisan test
```

Test memakai database `fzm_production_test`, jadi data di `fzm_production` aman. MySQL harus menyala.

## Wajib diganti sebelum online

- Kata sandi admin (menu Konten & Pengaturan).
- Nomor WhatsApp `62XXXXXXXXXX`. Sebelum diganti, semua tautan WhatsApp tidak berfungsi.
- Teks bertanda `[…]`: alamat, email, NPWP, rekening, jam operasional.
- Harga Lighting yang masih contoh.
- Data demo, jika pernah dijalankan.
- `APP_ENV=production` dan `APP_DEBUG=false` di `.env`.
