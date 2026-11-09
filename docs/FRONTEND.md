# FZM Production — Frontend v2 (HTML, CSS, JS)

Gaya editorial yang bersih (judul besar, label monospace, tanda ↗, form dua langkah 01 / 02,
nama brand besar di footer), dengan warna dan gradasi putih · hitam · merah.
Tanpa framework atau build tool: cukup buka `index.html` di browser.

## Halaman

| File | Isi |
|---|---|
| `index.html` | Navbar melayang transparan (sembunyi saat scroll ke bawah), hero, marquee layanan, daftar layanan & harga, portofolio folder (filter + galeri), klien, CTA, footer |
| `kontak.html` | Info kontak + form pertanyaan 2 langkah → WhatsApp |
| `pesan.html` | Keranjang layanan (total & DP 50% beranimasi), kalender ketersediaan, data pemesan, halaman sukses |
| `admin/login.html` | Login admin (tampil/sembunyi sandi, ingat saya, status memuat, getar saat gagal) |
| `admin/index.html` | Dashboard: kartu count-up + sparkline, grafik harian/bulanan (navigasi periode + tooltip), donut status bayar, jadwal, tabel (tab + cari) dengan panel samping |
| `admin/booking.html` | Detail booking: alur status, cincin progres bayar, riwayat pembayaran, dialog catat pembayaran, aksi dengan konfirmasi |
| `admin/invoice.html`, `admin/kuitansi.html` | Invoice A4 & kuitansi A5, siap cetak / PDF |

**Akun demo prototipe:** `admin@fzm.id` / `admin123`. Halaman admin otomatis mengalihkan ke login
bila belum masuk.

## Struktur aset

```
assets/css/  base.css (token, gradasi, tombol, form, animasi) · site.css (publik) · admin.css · document.css
assets/js/   site.js · kontak.js · pesan.js · login.js · admin.js · dashboard.js · booking.js · document.js
```

## Animasi

- Muncul halus saat di-scroll: `data-reveal` (atur jeda dengan `style="--d:2"`), judul per baris dengan `.split > .line > span`.
- Semua animasi memakai easing `cubic-bezier(.22, 1, .36, 1)` dan otomatis mati bila pengguna memilih
  "kurangi gerakan" di sistem operasinya.

## Yang perlu diganti

- Nomor WhatsApp `62XXXXXXXXXX` (cari di semua file HTML).
- Teks bertanda `[…]`: alamat, email, NPWP, rekening, peta, nama mitra.
- Data contoh: portofolio, klien, pendapatan, booking, harga Lighting.
- Foto portofolio: isi `data-photos='["/media/…/1.webp", …]'` di tiap `.folder`.

## Pindah ke Django

1. Salin `assets/` ke folder `static/`, lalu di template: `{% load static %}` dan
   `<link rel="stylesheet" href="{% static 'assets/css/base.css' %}">`.
2. Pisahkan bagian yang sama ke template dasar:
   - publik: `templates/base.html` (navbar, footer, tombol WA) → `{% extends "base.html" %}`
   - admin: `templates/admin_panel/base.html` (sidebar + topbar)
3. Ulangi elemen dengan `{% for %}`: layanan (`.svc-item`), portofolio (`.folder`, kelas warna
   `folder--{{ p.folder_color }}`), klien, baris paket di `pesan.html`.
4. Data untuk JavaScript sudah disiapkan di blok JSON. Ganti isinya dengan filter `json_script`:
   - `pesan.html` → `window.FZM_CONFIG` (`adminWa`, `today`, `locked` dari endpoint ketersediaan)
   - `admin/index.html` → `{{ dashboard|json_script:"dashData" }}`
   - `admin/booking.html` → `{{ booking_data|json_script:"bookingData" }}`
5. Form: tambahkan `{% csrf_token %}`, hapus `e.preventDefault()` pada submit di `pesan.js`,
   `booking.js`, dan `login.js` agar data dikirim ke view.
6. Login: ganti logika demo di `login.js` dengan `django.contrib.auth` + `@login_required`;
   hapus penjaga login berbasis `localStorage` di awal `admin.js`.

> Perhitungan harga, DP 50%, dan aturan status di JavaScript hanya pratinjau.
> Server (view/service Django) tetap wajib menghitung ulang dan memvalidasi (PRD v0.2 FR-6, FR-13–FR-15).

## Catatan

- Font Inter, Inter Tight, dan JetBrains Mono dimuat dari Google Fonts; tanpa internet, browser memakai font sistem.
- Dialog admin memakai elemen `<dialog>` bawaan browser (Chrome, Edge, Firefox, Safari versi terbaru).
