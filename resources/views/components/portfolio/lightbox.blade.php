{{-- Detail proyek portofolio (diisi oleh portfolio.js) --}}
<div class="pf-detail" id="pfDetail" role="dialog" aria-modal="true" aria-labelledby="pdTitle" hidden>
  <div class="pd-card">
    <span class="pd-grab" aria-hidden="true"></span>
    <div class="pd-head">
      <div>
        <span class="pd-cat" id="pdCat"></span>
        <h2 id="pdTitle"></h2>
      </div>
      <button class="pd-close" type="button" id="pdClose" aria-label="Tutup detail"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
    </div>
    <div class="pd-photo">
      <img id="pdImg" alt="" hidden>
      <span class="ph" id="pdEmpty" hidden></span>
      <button class="pd-nav pd-prev" type="button" id="pdPrev" aria-label="Foto sebelumnya" hidden><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
      <button class="pd-nav pd-next" type="button" id="pdNext" aria-label="Foto berikutnya" hidden><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
      <span class="pd-count" id="pdCount" aria-live="polite"></span>
    </div>
    <dl class="pd-info">
      <div><dt>Klien</dt><dd id="pdClient"></dd></div>
      <div><dt>Tanggal</dt><dd id="pdDate"></dd></div>
      <div><dt>Lokasi</dt><dd id="pdLoc"></dd></div>
      <div><dt>Layanan</dt><dd id="pdSvc"></dd></div>
    </dl>
    <a class="link-arrow" id="pdLink" href="#"><span>Lihat halaman proyek</span></a>
  </div>
</div>
