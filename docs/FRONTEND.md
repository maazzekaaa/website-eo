# FZM Production — Frontend v2 (HTML, CSS, JS)

Gaya editorial yang bersih (judul besar, label monospace, tanda ↗, form dua langkah 01 / 02,
nama brand besar di footer), dengan warna dan gradasi putih · hitam · merah.
Tanpa framework atau build tool di sisi browser.

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

## Di Laravel

- Aset ada di `public/assets/`, dimuat dengan `asset('assets/...')`, tanpa Vite.
- Layout: `layouts/site` (publik), `layouts/admin` (sidebar + topbar), `layouts/document` (cetak).
- Data ke JavaScript lewat `window.FZM_CONFIG` atau blok JSON (`dashData`, `bookingData`) dengan `@json`.
- Perhitungan di JavaScript hanya pratinjau. Server selalu menghitung ulang dan memvalidasi.

## Catatan

- Font Inter, Inter Tight, dan JetBrains Mono dimuat dari Google Fonts; tanpa internet, browser memakai font sistem.
- Dialog admin memakai elemen `<dialog>` bawaan browser (Chrome, Edge, Firefox, Safari versi terbaru).
