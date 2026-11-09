<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-labelledby="lbTitle" hidden>
    <div class="lightbox-card">
      <div class="lb-media">
        <div class="lb-stage">
          <img id="lbImg" alt="" hidden>
          <div class="lb-empty" id="lbEmpty">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/></svg>
            <strong id="lbCounter">Foto 1 dari 1</strong>
            <small>Foto hasil acara diunggah admin</small>
          </div>
          <button class="lb-nav lb-prev" type="button" id="lbPrev" aria-label="Foto sebelumnya"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
          <button class="lb-nav lb-next" type="button" id="lbNext" aria-label="Foto berikutnya"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
        </div>
        <div class="lb-thumbs" id="lbThumbs"></div>
      </div>
      <div class="lb-info">
        <div class="lb-head">
          <span class="badge" id="lbCat">Kategori</span>
          <button class="icon-btn" type="button" id="lbClose" aria-label="Tutup galeri" style="box-shadow: inset 0 0 0 1px var(--line-2)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
        </div>
        <h3 id="lbTitle">Judul acara</h3>
        <dl>
          <dt>Tanggal</dt><dd id="lbDate"></dd>
          <dt>Klien</dt><dd id="lbClient"></dd>
          <dt>Lokasi</dt><dd id="lbLoc"></dd>
          <dt>Layanan</dt><dd id="lbServices"></dd>
        </dl>
        <p id="lbStory"></p>
        <a class="btn btn-red" href="{{ route('pesan.create') }}">Pesan layanan serupa <span class="arrow" aria-hidden="true">→</span></a>
      </div>
    </div>
  </div>
