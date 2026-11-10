# CLAUDE.md — Website Event Organizer FZM Production Mediatama

Konteks proyek untuk Claude Code. Baca ini dulu sebelum mengerjakan apa pun.
Bahasa kerja: **Indonesia** (UI, komentar kode, pesan commit).

## Ringkasan
Website resmi EO **FZM Production Mediatama** (tugas Kelompok 12: Gigih, Dhimas).
- Portofolio acara berbentuk folder ala macOS + daftar klien/mitra.
- Katalog layanan & harga, pemesanan **tanpa login** dengan keranjang multi-layanan dan cek tanggal → diteruskan ke WhatsApp.
- Panel admin: login, dashboard pendapatan, kelola booking & pembayaran (DP/pelunasan), invoice & kuitansi.

Dokumen lengkap: `docs/PRD.md` (v0.2) dan `docs/ALUR-PROGRAM.md` (v0.2). Panduan frontend: `docs/FRONTEND.md`.

## Status saat ini
- Aplikasi Laravel 13 + MySQL. Lihat `README.md` untuk cara menjalankan.
- Rancangan: `docs/superpowers/specs/2026-11-09-laravel-migration-design.md`.

## Struktur
```
app/Services/         logika bisnis: harga, booking, pembayaran, nomor dokumen, pendapatan, WhatsApp, gambar
app/Http/Controllers/ publik, dan Admin/ untuk panel
resources/views/      site/, admin/, layouts/, partials/
public/assets/        CSS dan JS dari prototipe, tanpa build tool
database/seeders/     DatabaseSeeder (data awal), DemoSeeder (data contoh)
```

## Aturan bisnis (jangan diubah tanpa persetujuan tim)
- Layanan punya `pricing_type`: `tier` (pilih 1 paket) atau `per_unit` (harga × jumlah).
  - Sound 3/5/7/10 jt · Lighting 1,5/2,25/3 jt (**contoh, menunggu FZM**) · Multimedia 1,5/2,5/3 jt
  - Videotron indoor Rp400rb/m² · outdoor Rp650rb/m² · Rigging Rp150rb/m
- Satu booking = beberapa item; harga di-*snapshot* ke `booking_items`. **Server wajib menghitung ulang** total (JS hanya pratinjau).
- **DP = 50% total**, dibulatkan ke atas ke ribuan. PPN nonaktif default (sakelar 11% di pengaturan).
- Status booking: `pending → confirmed → completed`, `pending → rejected`, `pending|confirmed → cancelled`.
  - `confirmed` hanya jika DP 50% sudah tercatat; mengunci tanggal (1 tanggal = 1 booking confirmed, unique index + transaksi).
  - `completed` hanya jika lunas. `pending` tidak mengunci tanggal.
- Status bayar dihitung: `unpaid` → `dp` (≥ 50%) → `paid` (= total). Pembayaran tidak boleh melebihi sisa; salah input di-*void*, bukan dihapus.
- Pendapatan = jumlah pembayaran tercatat berdasarkan `paid_at` (Asia/Jakarta). Kartu "hari ini" & "bulan ini" mulai dari 0 tiap periode; data lama tetap disimpan.
- Dokumen: **invoice per booking** `INV/YYYY/MM/NNN` (jatuh tempo = tanggal acara) + **kuitansi per pembayaran** `KWT/YYYY/MM/NNN`. Format invoice mengikuti `docs/design-reference/contoh-invoice.png` (header tabel merah, tanpa branding pihak lain).
- Kode booking `FZM-YYYYMMDD-XXXX`. Nomor WA dinormalisasi ke `62…`, pesan lewat `https://wa.me/<nomor>?text=…`.

## Desain
- Warna: putih, hitam, merah (`--red #E11D2E`, teks merah di atas putih pakai `--red-ink #C8102E`). Gradasi di `base.css`: `--g-red`, `--g-dark`, `--g-ember`, `--g-blush`.
- Font: Inter (teks), Inter Tight (judul), JetBrains Mono (label kecil `.meta`).
- Gaya editorial bersih (terinspirasi template Framer "Farline"): judul besar, label monospace, tautan `↗`, footer dengan wordmark besar.
- Animasi: easing `--ease: cubic-bezier(.22,1,.36,1)`; `data-reveal` + `--d` untuk muncul saat scroll; `.split > .line > span` untuk judul per baris. Selalu hormati `prefers-reduced-motion`.
- Beranda, klien, dan portofolio memakai judul besar `.poster` (font Inter Tight seperti halaman lain), navbar kapsul bening di bawah (`.nav`). Navbar selalu tampil saat di-scroll. Beranda: tumpukan foto yang bergeser ke belakang (`stage.js`). Klien: daftar dengan pratinjau foto (`ledger.js`). Portofolio: maksimal 15 foto tersebar acak yang bisa digeser (`portfolio-table.js`).
- Warna folder portofolio: kelas `folder--red | folder--black | folder--maroon | folder--silver` (kolom `folder_color`).
- Aksesibilitas: target sentuh ≥ 44px, label form, fokus terlihat, kontras teks ≥ 4.5:1.

## Konvensi kode frontend
- JS vanilla dalam IIFE, tanpa dependensi. Data dari server diletakkan di blok `<script type="application/json">` (`dashData`, `bookingData`) atau `window.FZM_CONFIG` dengan `@json`.
- `admin.js` adalah shell bersama (penjaga login, sidebar, dropdown, toast, `window.FZM`); muat sebelum skrip halaman admin.
- Placeholder data yang belum ada ditulis `[…]`. Nomor WA admin sementara `62XXXXXXXXXX`.
- Rupiah sebagai bilangan bulat; format `Rp` + `toLocaleString('id-ID')`.

## Pertanyaan terbuka (lihat PRD §16)
Hosting · kapasitas per tanggal (1 tanggal = 1 event?) · tingkat harga Lighting final · kebijakan DP jika batal · data perusahaan (alamat, NPWP, rekening) · bahasa selain Indonesia.
