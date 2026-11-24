# DESIGN.md — FZM Production Mediatama

Panduan desain website Event Organizer FZM Production. Struktur dan konten mengikuti repo `website-eo` (Laravel + Blade). Gayanya dipadukan dengan nuansa Apple (lapang, tipografi besar, kapsul melayang), tanpa meniru.

Kanvas desain: https://claude.ai/artifact/Tbtxyek7AgwfJCPEMePNxw

---

## 1. Prinsip

- Simpel, modern, minimalis.
- Flat color. Tidak ada gradasi, pola garis, atau bayangan dekoratif. Satu-satunya bayangan: navbar kapsul melayang di sisi user.
- Banyak ruang kosong, judul besar dengan letter-spacing rapat.
- Merah hanya untuk aksen dan aksi utama.
- Tidak ada label bernomor seperti "(01) Layanan". Judul berdiri sendiri.
- Semua target sentuh minimal 44 px.

## 2. Warna

| Token | Hex | Pemakaian |
|---|---|---|
| `--bg` | `#FAFAFA` | Latar halaman |
| `--ink` | `#101010` | Teks, tombol gelap, blok CTA |
| `--red` | `#C2272D` | Aksen, tombol utama, kata serif di hero |
| `--muted` | `#5F5F5F` | Teks sekunder |
| `--hairline` | `#E0E0E0` | Garis pemisah |
| `--surface` | `#FFFFFF` | Kartu, navbar, input |
| `--tile-1..6` | `#E6E0D8` `#D9DDE3` `#EBD3D4` `#DADADA` `#E4D8D0` `#D8E0DA` | Placeholder foto (flat) |
| `--taken` | `#EAD0D2` | Tanggal terisi di kalender |

Admin memakai latar `#FAFAFA` dengan kartu putih berradius 24 px.

## 3. Tipografi

Dua font dipadukan:

- **Open Sauce One** (400, 500, 600, 700, 800) untuk semua teks utama.
- **Instrument Serif** italic untuk kata aksen di hero, berwarna merah.

Fallback: `system-ui, -apple-system, sans-serif` dan `Georgia, serif`.

| Peran | Ukuran | Berat | Letter-spacing |
|---|---|---|---|
| Hero | `clamp(56px, 11vw, 176px)` | 800 (serif aksen 400 italic) | -0.05em |
| Judul halaman | `clamp(52px, 9vw, 128px)` | 800 | -0.05em |
| Judul seksi | `clamp(38px, 5.4vw, 72px)` | 700 | -0.04em |
| Judul layanan | 26 px | 700 | -0.025em |
| Isi | 17–21 px | 400 | normal |

Hero berbunyi **Rangkai *Cipta,* Hidupkan *Karya.*** dengan layout rata tengah. "Rangkai" dan "Hidupkan" memakai Open Sauce One, "Cipta," dan "Karya." memakai Instrument Serif italic merah.

## 4. Bentuk dan jarak

- Radius: tombol dan navbar `999px`, kartu `24–32px`, blok CTA `40px`.
- Lebar konten maksimum `1360px` (user) dan `1100px` (admin).
- Padding sisi `clamp(20px, 3.5vw, 48px)`.
- Jarak antar seksi besar: 160 px.
- Fokus keyboard: outline 3 px merah.

## 5. Komponen user

- **Pil merek** (kiri atas): logo FZM + teks, latar putih, radius penuh.
- **Navbar kapsul melayang** (bawah layar, semua halaman user): Beranda, Layanan, Portofolio, Tentang, Kontak, lalu tombol merah "Pesan". Halaman aktif berlatar hitam.
- **Tombol utama**: merah, teks putih, radius penuh, tinggi 52–58 px.
- **Tombol sekunder**: outline 1,5 px hitam.
- **Baris layanan**: nomor, nama, deskripsi, harga, panah. Hover berlatar putih.
- **Blok CTA**: latar hitam flat, teks putih, tombol merah.
- **Footer**: tiga kolom (tagline, navigasi, kontak), lalu teks raksasa **FZM PRODUCTION** yang pas selebar halaman (SVG dengan `textLength`).

## 6. Halaman user

| Kode | Halaman | Isi |
|---|---|---|
| U1 | Beranda | Hero tipografi saja, rata tengah, tanpa gambar. Lalu daftar layanan dengan harga, strip logo klien, CTA hitam, footer |
| U2 | Layanan & Pesan | Pilih Paket 1, 2, dst, isi ukuran per meter, kalender tanggal, form data (nama, WhatsApp, jam mulai acara, jam selesai acara, lokasi), ringkasan dengan PPN opsional dan DP 50%, kirim lewat WhatsApp. Tanpa akun |
| U3 | Portofolio | Dua baris foto berjalan berlawanan arah (kiri dan kanan), berhenti saat kursor di atasnya, filter kategori, klik foto membuka detail (klien, tanggal, lokasi, layanan). Tanpa folder |

Halaman Klien dihapus. Strip logo klien tetap di Beranda.

## 7. Admin

Navbar admin **menempel penuh di dasar halaman** (bukan kapsul melayang): latar putih, garis atas 1 px, isi Dashboard, Pemesanan, Layanan, Portofolio, Pengaturan, Keluar. Halaman aktif berlatar hitam.

| Kode | Halaman | Isi |
|---|---|---|
| A1 | Masuk | Login admin |
| A2 | Dashboard | Sapaan "Halo, Admin", tiga angka ringkasan (pesanan menunggu, pendapatan bulan ini, sisa tagihan), **grafik batang Rekap pendapatan** dengan tombol Harian/Bulanan, **donut Status pembayaran** (lunas, baru DP, belum bayar), daftar pesanan terbaru |
| A3 | Pemesanan | Filter status (Semua, Menunggu, Dikonfirmasi, Selesai), daftar, panel aksi saat baris dipilih: Catat DP, Konfirmasi (aktif setelah DP), Selesai, Tolak |
| A4 | Layanan | Daftar layanan: nama, tipe atau paket, harga, Ubah |

Grafik dan donut dipertahankan untuk memudahkan rekap. Grafik memakai batang hitam dengan batang terakhir merah. Donut memakai hitam, merah, dan abu-abu, semuanya flat.

## 8. Data harga (dari seeder repo)

| Layanan | Tipe | Harga |
|---|---|---|
| Sound System | Paket 1–4 | Rp3.000.000 / 5.000.000 / 7.000.000 / 10.000.000 |
| Lighting | Paket 1–3 | Rp1.500.000 / 2.250.000 / 3.000.000 (angka contoh) |
| Multimedia Broadcasting System | Paket 1–3 | Rp1.500.000 / 2.500.000 / 3.000.000 |
| Videotron Indoor | per m² | Rp400.000 |
| Videotron Outdoor | per m² | Rp650.000 |
| Rigging | per meter | Rp150.000 |

## 9. Aturan bisnis yang dijaga

- Tanpa login pengguna.
- DP 50%, PPN bisa dinyalakan.
- Status: menunggu, dikonfirmasi, selesai, ditolak, dibatalkan.
- Konfirmasi butuh DP dan mengunci tanggal.
- Pembayaran dibatalkan (void), tidak dihapus.
- Pesanan diteruskan lewat WhatsApp dengan pesan terisi otomatis.

## 10. Catatan implementasi

- Gerak portofolio: animasi CSS `translateX` linear, 48 detik (baris atas) dan 56 detik (baris bawah), nonaktif jika `prefers-reduced-motion`.
- Teks dalam `[KURUNG SIKU]` dan semua angka di admin adalah isian contoh. Ganti dengan data asli.
- Font dipanggil lewat Google Fonts. Ketersediaan Open Sauce One di sana belum diverifikasi. Jika tidak ada, pasang secara lokal dengan `@font-face`.
