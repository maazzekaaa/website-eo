{{-- Komponen: bagian Kontak di beranda. Kiri: daftar kontak (WhatsApp bisa diketuk) + peta.
     Kanan: formulir pertanyaan 2 langkah → WhatsApp (kontak.js). --}}
<section class="section" id="kontak">
  <div class="container contact">
    <div>
      <x-section-head eyebrow="Kontak" lead="Punya rencana acara, butuh sewa peralatan, atau ingin menjajaki kerja sama? Ceritakan kepada kami.">
        Mari <x-accent>bicara.</x-accent>
      </x-section-head>

      <ul class="contact-list">
        <li><span class="meta">WhatsApp</span><x-wa-link /></li>
        <li><span class="meta">Email</span><a class="link-arrow" href="mailto:{{ setting('email') }}"><span>{{ setting('email') }}</span></a></li>
        <li><span class="meta">Alamat</span><span>{{ setting('address') }}</span></li>
        <li><span class="meta">Jam</span><span>{{ setting('opening_hours') }}</span></li>
        @if (setting('instagram_url') || setting('tiktok_url'))
          <li><span class="meta">Sosial</span><span>
            @if (setting('instagram_url'))<a class="link-arrow" href="{{ setting('instagram_url') }}" target="_blank" rel="noopener"><span>Instagram</span></a> &nbsp; @endif
            @if (setting('tiktok_url'))<a class="link-arrow" href="{{ setting('tiktok_url') }}" target="_blank" rel="noopener"><span>TikTok</span></a>@endif
          </span></li>
        @endif
      </ul>

      @if (setting('map_embed'))
        <div class="contact-media">
          <iframe src="{{ setting('map_embed') }}" title="Peta lokasi {{ setting('company_name') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      @endif
    </div>

    <div class="enquiry" aria-labelledby="enqTitle" data-reveal>
      <div class="enquiry-head">
        <h3 id="enqTitle">Pertanyaan baru</h3>
        <span class="meta" id="enqStep" aria-live="polite">Langkah 1 dari 2</span>
      </div>
      <div class="enquiry-progress" aria-hidden="true"><i id="enqBar"></i></div>

      <form id="enquiry" novalidate>
        <div class="enquiry-step" data-step="1">
          <div class="field">
            <label for="eName">Nama</label>
            <input class="input" id="eName" name="name" type="text" autocomplete="name" placeholder="Nama kamu atau instansi">
            <span class="field-error" id="eNameErr" hidden>Nama wajib diisi.</span>
          </div>
          <div class="field">
            <label for="eWa">Nomor WhatsApp</label>
            <input class="input" id="eWa" name="wa" type="tel" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx">
            <span class="field-error" id="eWaErr" hidden>Nomor WA tidak valid (10–14 digit).</span>
          </div>
          <fieldset class="field" style="border:0;padding:0;margin:0">
            <legend class="field-label" style="margin-bottom:8px">Jenis acara</legend>
            <div class="chips">
              <label class="chip"><input type="radio" name="type" value="Pernikahan" checked><span>Pernikahan</span></label>
              <label class="chip"><input type="radio" name="type" value="Korporat"><span>Korporat</span></label>
              <label class="chip"><input type="radio" name="type" value="Lainnya"><span>Lainnya</span></label>
            </div>
          </fieldset>
          <div class="enquiry-actions">
            <a class="link-arrow" href="{{ route('pesan.create') }}"><span>Sudah tahu kebutuhannya? Langsung pesan</span></a>
            <button class="btn" type="button" id="enqNext">Lanjut <span class="arrow" aria-hidden="true">→</span></button>
          </div>
        </div>

        <div class="enquiry-step" data-step="2" hidden>
          <div class="field">
            <label for="eDate">Rencana tanggal (opsional)</label>
            <input class="input" id="eDate" name="date" type="date">
          </div>
          <div class="field">
            <label for="eMsg">Pesan</label>
            <textarea class="textarea" id="eMsg" name="message" placeholder="Ceritakan acaramu: lokasi, jumlah tamu, kebutuhan sound/lighting/videotron…"></textarea>
            <span class="field-error" id="eMsgErr" hidden>Tulis pesan minimal 10 karakter.</span>
          </div>
          <div class="honeypot" aria-hidden="true"><label for="eSite">Website</label><input id="eSite" type="text" tabindex="-1" autocomplete="off"></div>
          <div class="enquiry-actions">
            <button class="btn btn-ghost" type="button" id="enqBack"><span aria-hidden="true">←</span> Kembali</button>
            <button class="btn btn-red" type="submit">Kirim via WhatsApp <span class="arrow" aria-hidden="true">→</span></button>
          </div>
        </div>
      </form>

      <div class="enquiry-done" id="enqDone" hidden>
        <span class="check"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
        <h3 style="font-size:28px">Pesan siap dikirim</h3>
        <p style="color:var(--muted);max-width:340px">WhatsApp terbuka di tab baru. Kalau tidak muncul, ketuk tombol di bawah.</p>
        <a class="btn btn-red" id="enqWa" href="#" target="_blank" rel="noopener">Buka WhatsApp <span class="arrow" aria-hidden="true">→</span></a>
        <button class="btn btn-ghost btn-sm" type="button" id="enqAgain">Tulis pertanyaan lain</button>
      </div>
    </div>
  </div>
</section>

@push('scripts')
  <script>window.FZM_CONFIG = @json(['adminWa' => setting('wa_number')]);</script>
  <script src="{{ asset_v('assets/js/kontak.js') }}"></script>
@endpush
