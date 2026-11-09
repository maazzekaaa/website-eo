# Alur Program — FZM Production Mediatama (v0.2)

Diagram menggunakan Mermaid (render di GitHub, VS Code, atau mermaid.live).

> **Perubahan dari v0.1:** keranjang multi-layanan, DP 50% sebelum konfirmasi, pencatatan pembayaran, invoice & kuitansi, grafik pendapatan, interaksi folder portofolio, pengaturan konten oleh admin.

## 1. Arsitektur (Laravel MVC)
```mermaid
flowchart LR
    U[Pengunjung / Admin] --> R[routes/web.php]
    R --> M[Middleware: auth admin, throttle, CSRF]
    M --> C[Controller]
    C --> S[Service: CartPricingService, BookingService,<br/>PaymentService, DocumentNumberService,<br/>RevenueService, WhatsAppService]
    S --> MD[Model Eloquent]
    MD --> DB[(MySQL)]
    C --> V[View Blade + CSS/JS prototipe]
    C --> PDF[dompdf: invoice & kuitansi]
    V --> U
    C --> ST[(Storage: foto, logo, bukti transfer, tanda tangan)]
```

## 2. Alur Pengunjung (Keseluruhan)
```mermaid
flowchart TD
    A[Buka website] --> B[Beranda - navbar melayang transparan]
    B --> C{Mau apa?}
    C -->|Lihat layanan & harga| D[Layanan & Harga]
    C -->|Lihat karya| E[Portofolio - folder per kategori]
    C -->|Lihat mitra| F[Klien & proyek]
    C -->|Tanya cepat| W[Klik WhatsApp -> wa.me]
    D -->|Tambah ke pesanan| H[Halaman Pesan /pesan]
    E --> H
    B -->|Pesan Sekarang| H
    H --> I[Alur Pemesanan]
```

## 3. Interaksi Folder Portofolio
```mermaid
flowchart TD
    G[Grid folder + filter kategori] --> H{Perangkat}
    H -->|Mouse| HV[Hover: folder naik 6-8px + bayangan,<br/>2-3 thumbnail mengintip]
    HV --> CL[Klik]
    H -->|Sentuh| CL
    CL --> LB[Folder terbuka -> lightbox galeri]
    LB --> NV[Geser / panah kiri-kanan antar foto]
    LB --> INF[Info: judul, tanggal, klien, cerita]
    LB --> X[Tutup: tombol X / Esc / klik latar]
    X --> G
```

## 4. Alur Pemesanan Publik (Keranjang + Cek Jadwal)
```mermaid
flowchart TD
    S([Mulai]) --> P[Pilih layanan:<br/>tier = pilih tingkat, per_unit = isi meter]
    P --> R[Ringkasan: subtotal, total, DP 50% - dihitung otomatis di browser]
    R --> K[Kalender: tanggal confirmed diarsir terisi]
    K --> T[Pilih tanggal]
    T --> F[Isi nama, WA, jam, lokasi, catatan]
    F --> SUB[Klik Kirim]
    SUB --> V1{Validasi form + honeypot + rate limit<br/>+ item valid & aktif}
    V1 -->|Gagal| E1[Tampilkan error di form] --> F
    V1 -->|Lolos| CALC[Server hitung ulang harga dari DB]
    CALC --> V2{Tanggal sudah confirmed?}
    V2 -->|Ya| E2[Pesan: jadwal terisi, pilih tanggal lain] --> K
    V2 -->|Tidak| DB[Simpan booking pending + booking_items snapshot<br/>+ kode booking + nomor invoice]
    DB --> WA[Bangun URL wa.me: item, total, DP]
    WA --> OK[Halaman sukses + tombol ke WhatsApp]
    OK --> X([Selesai])
```

## 5. Sequence — Submit Booking
```mermaid
sequenceDiagram
    actor Pg as Pengunjung
    participant Br as Browser
    participant Ct as BookingController
    participant Pr as CartPricingService
    participant Sv as BookingService
    participant Db as MySQL
    participant Wa as WhatsApp

    Pg->>Br: Pilih layanan, tanggal, isi data & kirim
    Br->>Ct: POST /pesan (items[], data, CSRF)
    Ct->>Ct: Validasi (FormRequest) + throttle
    Ct->>Pr: price(items)
    Pr->>Db: Ambil harga layanan/opsi aktif
    Pr-->>Ct: lines, subtotal, tax, total, dp
    Ct->>Sv: create(data, priced)
    Sv->>Db: BEGIN
    Sv->>Db: Cek booking confirmed pada tanggal
    alt Tanggal terisi
        Sv-->>Ct: DateUnavailable
        Ct-->>Br: Kembali + pesan error
    else Tersedia
        Sv->>Db: INSERT bookings (pending, booking_code, invoice_number)
        Sv->>Db: INSERT booking_items (snapshot harga)
        Sv->>Db: COMMIT
        Sv-->>Ct: Booking
        Ct->>Ct: WhatsAppService::buildUrl(booking)
        Ct-->>Br: Halaman sukses + link wa.me
        Br->>Wa: Buka WhatsApp (pesan terisi)
    end
```

## 6. Alur Admin
```mermaid
flowchart TD
    A([Buka /admin]) --> B{Sudah login?}
    B -->|Tidak| L[Halaman Login]
    L --> V{Kredensial benar?}
    V -->|Tidak| L
    V -->|Ya| D[Dashboard: kartu hari ini & bulan ini,<br/>grafik harian & bulanan]
    B -->|Ya| D
    D --> M{Menu}
    M --> BK[Pemesanan]
    M --> SV[Layanan & Harga - CRUD]
    M --> PF[Portofolio - CRUD + warna folder]
    M --> CL[Klien/Mitra - CRUD]
    M --> ST[Pengaturan & Konten]
    M --> LO[Logout]
    BK --> DT[Detail booking]
    DT --> PY[Catat pembayaran]
    PY --> KW[Kuitansi terbit otomatis -> cetak/PDF]
    DT --> INV[Cetak/PDF invoice]
    DT --> Q{Keputusan}
    Q -->|Konfirmasi| DPC{Sudah bayar >= DP 50%?}
    DPC -->|Belum| ER1[Tolak: catat DP dulu]
    DPC -->|Sudah| CK{Tanggal sudah dikunci booking lain?}
    CK -->|Ya| ER2[Tolak: tampilkan peringatan]
    CK -->|Tidak| CF[status = confirmed, tanggal terkunci]
    Q -->|Tolak| RJ[status = rejected]
    Q -->|Batalkan| CN[status = cancelled, tanggal terbuka]
    Q -->|Selesai| LN{Status bayar = paid?}
    LN -->|Belum| ER3[Tolak: masih ada sisa tagihan]
    LN -->|Ya| CP[status = completed]
```

## 7. Alur Pencatatan Pembayaran
```mermaid
flowchart TD
    A[Detail booking] --> B[Form: nominal, jenis DP/pelunasan,<br/>metode, tanggal bayar, bukti, catatan]
    B --> V{Nominal > 0 dan <= sisa tagihan?}
    V -->|Tidak| E[Error di form] --> B
    V -->|Ya| T[Transaksi DB]
    T --> N[Generate KWT/YYYY/MM/NNN]
    N --> S[Simpan payment]
    S --> R[Hitung ulang payment_status:<br/>unpaid / dp / paid]
    R --> K[Tampilkan kuitansi -> cetak / PDF]
    K --> W[Opsional: kirim ke pelanggan via WA]
```

## 8. State Booking & Status Bayar
```mermaid
stateDiagram-v2
    [*] --> pending: Pengunjung submit
    pending --> confirmed: Admin konfirmasi (syarat DP 50%, tanggal dikunci)
    pending --> rejected: Admin tolak
    pending --> cancelled: Dibatalkan
    confirmed --> cancelled: Admin batalkan (tanggal terbuka)
    confirmed --> completed: Acara selesai (syarat lunas)
    rejected --> [*]
    cancelled --> [*]
    completed --> [*]
```

```mermaid
stateDiagram-v2
    [*] --> unpaid
    unpaid --> dp: dibayar >= 50% total
    dp --> paid: dibayar = total
    unpaid --> paid: bayar penuh sekaligus
    paid --> dp: pembayaran di-void
    dp --> unpaid: pembayaran di-void
```

## 9. Pseudocode Inti

**Hitung keranjang (server, sumber kebenaran harga)**
```php
$lines = collect($request->items)->map(function ($item) {
    $service = Service::active()->findOrFail($item['service_id']);

    if ($service->pricing_type === 'tier') {
        $opt = $service->options()->active()->findOrFail($item['option_id']);
        return ['service_id' => $service->id, 'service_option_id' => $opt->id,
                'description' => "{$service->name} – {$opt->name}",
                'qty' => 1, 'unit' => 'Paket', 'unit_price' => $opt->price,
                'subtotal' => $opt->price];
    }

    $qty = (int) $item['qty'];             // divalidasi 1..500
    return ['service_id' => $service->id, 'service_option_id' => null,
            'description' => $service->name,
            'qty' => $qty, 'unit' => $service->unit_label,
            'unit_price' => $service->unit_price,
            'subtotal' => $service->unit_price * $qty];
});

$subtotal = $lines->sum('subtotal');
$taxRate  = setting('tax_enabled') ? setting('tax_rate') : 0;   // persen
$tax      = intdiv($subtotal * $taxRate + 50, 100);             // pembulatan
$total    = $subtotal + $tax;
$dp       = (int) (ceil($total / 2 / 1000) * 1000);              // 50%, ke atas ke ribuan
```

**Konfirmasi booking (DP + anti double booking)**
```php
DB::transaction(function () use ($booking) {
    $booking = Booking::lockForUpdate()->findOrFail($booking->id);

    if ($booking->paidAmount() < $booking->dp_amount) throw new DpNotPaid();

    $taken = Booking::where('event_date', $booking->event_date)
        ->where('status', 'confirmed')
        ->where('id', '!=', $booking->id)
        ->lockForUpdate()
        ->exists();
    if ($taken) throw new DateAlreadyConfirmed();

    $booking->update(['status' => 'confirmed']);
});
// Pengaman tambahan: kolom generated `confirmed_date`
// (= event_date jika status confirmed, selain itu NULL) + UNIQUE index.
```

**Catat pembayaran**
```php
DB::transaction(function () use ($booking, $data) {
    $booking = Booking::lockForUpdate()->findOrFail($booking->id);
    $sisa = $booking->total - $booking->paidAmount();
    if ($data['amount'] <= 0 || $data['amount'] > $sisa) throw new InvalidAmount();

    $payment = $booking->payments()->create($data + [
        'receipt_number' => DocumentNumber::next('KWT'),   // KWT/2026/11/031
        'admin_id'       => auth()->id(),
    ]);

    $paid = $booking->paidAmount();
    $booking->update(['payment_status' =>
        $paid >= $booking->total ? 'paid' : ($paid >= $booking->dp_amount ? 'dp' : 'unpaid')]);
    return $payment;
});
```

**Nomor dokumen (berurutan per bulan)**
```php
// tabel document_sequences(prefix, period 'YYYY-MM', last_number) dikunci FOR UPDATE
// INV/2026/11/012 , KWT/2026/11/031
```

**Rekap pendapatan (Asia/Jakarta)**
```php
// Kartu
$today = Payment::valid()->whereDate('paid_at', today())->sum('amount');
$month = Payment::valid()->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');

// Grafik harian (bulan dipilih) — hari tanpa pembayaran diisi 0
$daily = Payment::valid()->whereYear('paid_at', $y)->whereMonth('paid_at', $m)
    ->selectRaw('DAY(paid_at) d, SUM(amount) total')->groupBy('d')->pluck('total', 'd');

// Grafik bulanan (tahun dipilih)
$monthly = Payment::valid()->whereYear('paid_at', $y)
    ->selectRaw('MONTH(paid_at) m, SUM(amount) total')->groupBy('m')->pluck('total', 'm');
// "Reset" = periode berjalan otomatis berganti; data lama tetap ada.
```

**URL WhatsApp**
```php
$items = $b->items->map(fn ($i) => "- {$i->description} ({$i->qty} {$i->unit}): " . rupiah($i->subtotal))->implode("\n");
$text = "Halo FZM, saya ingin memesan.\n"
      . "Kode: {$b->booking_code}\n{$items}\n"
      . "Total: " . rupiah($b->total) . "\nDP 50%: " . rupiah($b->dp_amount) . "\n"
      . "Tanggal: {$b->event_date->translatedFormat('d F Y')}\nLokasi: {$b->event_location}\nNama: {$b->customer_name}";
$url = "https://wa.me/" . setting('wa_number') . "?text=" . rawurlencode($text);
```

## 10. Peta Rute
| Method | URL | Controller | Akses |
|---|---|---|---|
| GET | `/` | HomeController@index | Publik |
| GET | `/layanan`, `/layanan/{slug}` | ServiceController | Publik |
| GET | `/portofolio`, `/portofolio/{slug}` | PortfolioController | Publik |
| GET | `/klien`, `/klien/{id}` | ClientController | Publik |
| GET | `/tentang`, `/kontak` | PageController | Publik |
| GET | `/api/availability?month=YYYY-MM` | AvailabilityController | Publik |
| POST | `/api/cart/quote` | CartController@quote (pratinjau total) | Publik (throttle) |
| GET/POST | `/pesan` | BookingController@create/store | Publik (throttle) |
| GET | `/pesan/sukses/{kode}` | BookingController@success | Publik |
| GET/POST | `/admin/login` | Admin\AuthController | Tamu |
| GET | `/admin` | Admin\DashboardController | Admin |
| GET | `/admin/revenue?period=daily|monthly&date=` | Admin\RevenueController (data grafik) | Admin |
| ANY | `/admin/bookings`, `/admin/services`, `/admin/portfolios`, `/admin/clients`, `/admin/settings` | Admin\* (resource) | Admin |
| POST | `/admin/bookings/{id}/confirm|reject|cancel|complete` | Admin\BookingController | Admin |
| POST | `/admin/bookings/{id}/payments` | Admin\PaymentController@store | Admin |
| POST | `/admin/payments/{id}/void` | Admin\PaymentController@void | Admin |
| GET | `/admin/bookings/{id}/invoice` (`?pdf=1`) | Admin\DocumentController@invoice | Admin |
| GET | `/admin/payments/{id}/receipt` (`?pdf=1`) | Admin\DocumentController@receipt | Admin |

## 11. Urutan Implementasi per Sprint
1. **Sprint 1:** migrasi + seeder (layanan & tingkat harga, settings) → Home (navbar melayang), Layanan & Harga (read-only).
2. **Sprint 2:** Portofolio (folder + filter + lightbox), Klien, Tentang.
3. **Sprint 3:** Availability API → CartPricingService → form booking (keranjang) → BookingService → WhatsAppService → halaman sukses.
4. **Sprint 4:** Auth admin → dashboard & RevenueService (grafik) → CRUD layanan/portofolio/klien/pengaturan → PaymentService + nomor dokumen → invoice & kuitansi (print + PDF) → aksi status booking → promo, responsif, pencarian.
