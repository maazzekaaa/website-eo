@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

      <div class="page-head" data-reveal>
        <div>
          <span class="meta" id="todayLabel"></span>
          <h1 id="greeting" style="margin-top:6px">Halo, Admin</h1>
          <p>Ringkasan pesanan dan pendapatan FZM hari ini</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn btn-light" href="{{ route('admin.recap') }}" id="exportBtn" data-url="{{ route('admin.recap') }}" download>Unduh rekap <span aria-hidden="true">↓</span></a>
          <a class="btn btn-red" href="{{ route('admin.bookings.index', ['status' => 'pending', 'payment_status' => 'dp']) }}">Booking siap dikonfirmasi <span class="arrow" aria-hidden="true">→</span></a>
        </div>
      </div>

      <!-- Kartu statistik -->
      <section class="grid grid-4" aria-label="Statistik">
        <article class="card stat stat--red" data-reveal>
          <div class="stat-top"><span class="stat-label">Pendapatan hari ini</span><span class="stat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span></div>
          <div class="stat-value" id="stToday">Rp0</div>
          <div class="stat-foot"><div><span class="delta" id="stTodayDelta">—</span><br><small>vs kemarin</small></div><svg class="spark" id="sparkToday" viewBox="0 0 96 34" aria-hidden="true"><path stroke="#C2272D"/></svg></div>
        </article>
        <article class="card stat" data-reveal>
          <div class="stat-top"><span class="stat-label">Pendapatan bulan ini</span><span class="stat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 6-6"/></svg></span></div>
          <div class="stat-value" id="stMonth">Rp0</div>
          <div class="stat-foot"><div><span class="delta" id="stMonthDelta">—</span><br><small id="stMonthNote">vs periode sama bulan lalu</small></div><svg class="spark" id="sparkMonth" viewBox="0 0 96 34" aria-hidden="true"><path stroke="#101010"/></svg></div>
        </article>
        <article class="card stat stat--accent" data-reveal>
          <div class="stat-top"><span class="stat-label">Booking menunggu</span><span class="stat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div>
          <div class="stat-value" id="stPending">0</div>
          <div class="stat-foot"><small id="stPendingNote">—</small></div>
        </article>
        <article class="card stat" data-reveal>
          <div class="stat-top"><span class="stat-label">Sisa tagihan</span><span class="stat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="3"/><path d="M3 10h18M7 15h3"/></svg></span></div>
          <div class="stat-value" id="stRest">Rp0</div>
          <div class="stat-foot"><small id="stRestNote">—</small></div>
        </article>
      </section>

      <!-- Grafik | status pembayaran | jadwal terkunci, berdampingan -->
      <section class="grid grid-dash">
        <!-- Grafik pendapatan -->
          <article class="card" data-reveal aria-labelledby="chartTitle">
            <div class="card-head">
              <div><h2 id="chartTitle">Rekap pendapatan</h2><p id="chartSub">Berdasarkan pembayaran yang dicatat (DP &amp; pelunasan)</p></div>
              <div class="seg" role="tablist" aria-label="Periode grafik" id="modeSeg">
                <span class="seg-thumb" aria-hidden="true"></span>
                <button type="button" role="tab" aria-selected="true" data-mode="daily">Harian</button>
                <button type="button" role="tab" aria-selected="false" data-mode="monthly">Bulanan</button>
              </div>
            </div>
            <div class="chart-top">
              <div><span class="meta" id="chartTotalLabel">Total bulan ini</span><div class="chart-total" id="chartTotal">Rp0</div></div>
              <div class="period">
                <button class="icon-btn" type="button" id="perPrev" aria-label="Periode sebelumnya"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
                <span id="perLabel" aria-live="polite">—</span>
                <button class="icon-btn" type="button" id="perNext" aria-label="Periode berikutnya"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
              </div>
            </div>
            <div class="chart">
              <div class="chart-y" aria-hidden="true"><span id="y0"></span><span id="y1"></span><span id="y2"></span><span id="y3"></span><span>0</span></div>
              <div class="chart-plot">
                <div class="chart-grid" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                <div class="bars" id="bars" role="group" aria-label="Batang pendapatan"></div>
                <div class="tooltip" id="tip" aria-hidden="true"><span id="tipLabel"></span><b id="tipValue"></b></div>
              </div>
            </div>
            <div class="chart-x" id="xAxis" aria-hidden="true"></div>
          </article>

        <!-- Donut status pembayaran -->
          <article class="card" data-reveal style="--d:1" aria-labelledby="donutTitle">
            <div class="card-head"><div><h2 id="donutTitle">Status pembayaran</h2><p>Booking aktif</p></div></div>
            <div class="donut-wrap">
              <div class="donut">
                <svg viewBox="0 0 160 160" aria-hidden="true"><circle class="track" cx="80" cy="80" r="62"/><g id="donutArcs"></g></svg>
                <div class="donut-center"><b id="donutNum">0</b><small id="donutLbl">booking</small></div>
              </div>
              <ul class="legend" id="legend"></ul>
            </div>
          </article>

        <!-- Agenda -->
          <article class="card" data-reveal style="--d:2" aria-labelledby="agTitle">
            <div class="card-head"><div><h2 id="agTitle">Jadwal terkunci</h2><p id="agMonth"></p></div></div>
            <div class="mini-cal" id="miniCal" aria-hidden="true"></div>
            <ul class="agenda" id="agenda"></ul>
          </article>
      </section>

      <!-- Tabel booking: selebar penuh agar semua kolom terlihat -->
        <article class="card dash-table" data-reveal aria-labelledby="tblTitle">
          <div class="card-head">
            <div><h2 id="tblTitle">Booking terbaru</h2><p>Klik baris untuk melihat ringkasan cepat</p></div>
          </div>
          <div class="tabs" role="tablist" aria-label="Filter status" id="tabs">
            <span class="tabs-line" aria-hidden="true"></span>
            <button type="button" role="tab" aria-selected="true" data-tab="all">Semua <span></span></button>
            <button type="button" role="tab" aria-selected="false" data-tab="pending">Menunggu <span></span></button>
            <button type="button" role="tab" aria-selected="false" data-tab="confirmed">Dikonfirmasi <span></span></button>
            <button type="button" role="tab" aria-selected="false" data-tab="completed">Selesai <span></span></button>
          </div>
          <div class="table-wrap">
            <table class="table">
              <thead><tr><th>Pemesan</th><th>Kode</th><th>Tanggal acara</th><th class="num">Total</th><th>Status</th><th>Bayar</th><th><span class="sr-only">Buka</span></th></tr></thead>
              <tbody id="tbody"></tbody>
            </table>
          </div>
        </article>

      <!-- Drawer ringkasan booking -->
      <div class="drawer" id="drawer" aria-hidden="true">
        <div class="drawer-scrim" data-close></div>
        <aside class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="dwTitle">
          <div class="drawer-head">
            <div class="row"><span class="meta" id="dwCode">FZM-…</span><button class="icon-btn" type="button" data-close aria-label="Tutup"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button></div>
            <h3 id="dwTitle">Nama pemesan</h3>
            <div style="display:flex;gap:8px;margin-top:12px" id="dwBadges"></div>
          </div>
          <div class="drawer-body">
            <dl class="kv">
              <dt>Tanggal acara</dt><dd id="dwDate"></dd>
              <dt>Layanan</dt><dd id="dwItems"></dd>
              <dt>WhatsApp</dt><dd id="dwWa"></dd>
            </dl>
            <div style="display:flex;flex-direction:column;gap:10px">
              <div style="display:flex;justify-content:space-between;font-size:14px"><span class="meta">Pembayaran</span><b id="dwPct">0%</b></div>
              <div class="bar-pay"><i id="dwBar"></i></div>
              <dl class="kv"><dt>Total</dt><dd id="dwTotal"></dd><dt>Dibayar</dt><dd id="dwPaid"></dd><dt>Sisa</dt><dd id="dwRest"></dd></dl>
            </div>
            <p class="field-hint" id="dwHint"></p>
          </div>
          <div class="drawer-foot">
            <button class="btn btn-red" type="button" id="dwConfirm">Konfirmasi &amp; kunci tanggal</button>
            <a class="btn btn-light" href="#" id="dwOpen">Buka detail <span class="arrow" aria-hidden="true">→</span></a>
          </div>
        </aside>
      </div>
      <form id="confirmForm" method="post" hidden>@csrf</form>
@endsection

@push('scripts')
  <script id="dashData" type="application/json">@json($dashboard)</script>
  <script src="{{ asset_v('assets/js/dashboard.js') }}"></script>
@endpush
