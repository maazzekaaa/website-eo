# FZM Production Mediatama

Website portofolio dan pemesanan event organizer, dibuat dengan Laravel dan MySQL.

## Menjalankan di MacBook (Herd + DBngin)

1. Nyalakan server MySQL di DBngin. Proyek ini memakai port `3310`, user `root`, tanpa kata sandi.
2. Pastikan database `fzm_production` ada, lalu jalankan:

```bash
composer install
php artisan migrate:fresh --seed
php artisan storage:link
herd link fzm-website
```

Buka `http://fzm-website.test`.
