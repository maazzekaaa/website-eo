{{-- Gaya dokumen bermerek FZM (invoice & rekap). Dipakai di PDF (dompdf) dan pratinjau layar.
     Semua selektor di bawah .doc agar tidak bocor ke halaman admin. dompdf tidak mendukung flex/grid,
     jadi tata letak memakai tabel. Warna flat: hitam #101010, merah #C2272D, abu #5F5F5F. --}}
<style>
  .doc { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #101010; line-height: 1.45; }
  .doc table { width: 100%; border-collapse: collapse; }
  .doc td, .doc th { vertical-align: top; }
  .doc .muted { color: #5F5F5F; }
  .doc .red { color: #C2272D; }
  .doc .num { text-align: right; white-space: nowrap; }
  .doc .center { text-align: center; }

  /* Kepala dokumen */
  .doc .wordmark { font-size: 21px; font-weight: bold; letter-spacing: -1px; line-height: 1; }
  .doc .wordmark span { color: #C2272D; }
  .doc .company { margin-top: 6px; font-size: 8.5px; color: #5F5F5F; line-height: 1.5; }
  .doc .doc-title { font-size: 26px; font-weight: bold; letter-spacing: -1px; line-height: 1; text-align: right; }
  .doc .doc-no { margin-top: 5px; font-size: 10px; font-weight: bold; text-align: right; }
  .doc .rule { height: 3px; background: #C2272D; margin: 14px 0 16px; }

  /* Label kecil berhuruf kapital (tanpa nomor) */
  .doc .label { display: block; margin-bottom: 3px; font-size: 7px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #5F5F5F; }
  .doc .eyebrow { font-size: 7.5px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; color: #C2272D; }
  .doc h2 { margin: 20px 0 8px; font-size: 12px; letter-spacing: -0.2px; }
  .doc .value { font-size: 10px; font-weight: bold; }

  /* Pil status */
  .doc .pill { display: inline-block; margin-top: 6px; padding: 3px 9px; border-radius: 10px; font-size: 8px; font-weight: bold; }
  .doc .pill-unpaid { background: #F7E3E4; color: #9E1B20; }
  .doc .pill-dp { background: #FBEFD9; color: #7A4A00; }
  .doc .pill-paid { background: #E4F1E7; color: #1C6B37; }

  /* Kotak info */
  .doc .card { background: #F4F4F4; border-radius: 8px; padding: 10px 12px; }
  .doc .info td { padding-right: 10px; }
  .doc .info td:last-child { padding-right: 0; }

  /* Tabel rincian: kepala merah (sesuai contoh invoice), garis tipis */
  .doc .items th { background: #C2272D; color: #FFFFFF; font-size: 8px; font-weight: bold; text-align: left; padding: 7px 8px; }
  .doc .items th.num { text-align: right; }
  .doc .items td { padding: 7px 8px; border-bottom: 1px solid #E0E0E0; }
  .doc .items tfoot td { border-bottom: 0; border-top: 1.5px solid #101010; font-weight: bold; }
  .doc .items-dark th { background: #101010; }
  .doc .small th { font-size: 7px; padding: 5px 6px; }
  .doc .small td { font-size: 8px; padding: 5px 6px; }

  /* Ringkasan total */
  .doc .totals td { padding: 4px 0; }
  .doc .totals .grand td { padding: 8px 10px; background: #101010; color: #FFFFFF; font-size: 11px; font-weight: bold; }
  .doc .totals .rest td { padding: 7px 10px; background: #F7E3E4; color: #9E1B20; font-size: 10.5px; font-weight: bold; }

  /* Info pembayaran: garis merah di kiri */
  .doc .payinfo { margin-top: 16px; padding: 10px 12px; background: #F4F4F4; border-left: 3px solid #C2272D; }

  /* Cap status (sedikit miring) dan tanda tangan */
  .doc .stamp { display: inline-block; padding: 6px 14px; border: 2.5px solid #C2272D; border-radius: 6px; color: #C2272D; font-size: 15px; font-weight: bold; letter-spacing: 2px; transform: rotate(-8deg); }
  .doc .stamp--paid { border-color: #1C6B37; color: #1C6B37; }
  .doc .sign { text-align: center; }
  .doc .sign .space { height: 58px; }

  /* Kartu angka (rekap) */
  .doc .kpi td.k { width: 25%; padding: 0 4px; }
  .doc .kpi td.k:first-child { padding-left: 0; }
  .doc .kpi td.k:last-child { padding-right: 0; }
  .doc .kpi .box { background: #F4F4F4; border-radius: 8px; padding: 10px 12px; height: 52px; }
  .doc .kpi .box-dark { background: #101010; color: #FFFFFF; }
  .doc .kpi .box-dark .label { color: #BDBDBD; }
  .doc .kpi .big { font-size: 14px; font-weight: bold; letter-spacing: -0.4px; }
  .doc .kpi .note { margin-top: 3px; font-size: 7.5px; color: #5F5F5F; }
  .doc .kpi .box-dark .note { color: #BDBDBD; }
  .doc .up { color: #1C6B37; } .doc .down { color: #C2272D; }
  .doc .kpi .box-dark .up { color: #7FD39A; } .doc .kpi .box-dark .down { color: #FF8A8F; }

  /* Grafik batang: hitam, batang tertinggi merah */
  .doc .chart { table-layout: fixed; }
  .doc .chart td { vertical-align: bottom; padding: 0 1px; height: 96px; }
  .doc .chart .bar { background: #101010; border-radius: 2px 2px 0 0; }
  .doc .chart .bar-peak { background: #C2272D; }
  .doc .chart .bar-zero { background: #E0E0E0; height: 1px; }
  .doc .chart-axis td { padding: 3px 0 0; font-size: 6px; color: #8C8C8C; text-align: center; border-top: 1px solid #CFCFCF; }

  /* Kaki dokumen */
  .doc .doc-footer { margin-top: 22px; padding-top: 8px; border-top: 1px solid #E0E0E0; font-size: 7.5px; color: #8C8C8C; }
  .doc .thanks { font-size: 10px; font-weight: bold; color: #101010; }
  .doc .empty { padding: 14px 8px; color: #8C8C8C; text-align: center; }
</style>
