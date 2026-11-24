{{-- Komponen: footer tiga kolom + wordmark FZM PRODUCTION. compact = hanya wordmark & hak cipta. --}}
@props(['compact' => false])
<footer class="footer" @if ($compact) style="padding-top:0" @endif>
  <div class="container">
    @unless ($compact)
      <div class="footer-top">
        <div>
          <p class="footer-intro">Kami menata panggung, Anda menikmati <span class="accent">momennya.</span></p>
          <span class="footer-status">Menerima pesanan</span>
        </div>
        <div>
          <h4>Navigasi</h4>
          <ul>
            <li><a class="link-arrow" href="{{ route('home') }}#layanan"><span>Layanan</span></a></li>
            <li><a class="link-arrow" href="{{ route('home') }}#portofolio"><span>Portofolio</span></a></li>
            <li><a class="link-arrow" href="{{ route('pesan.create') }}"><span>Pesan</span></a></li>
            <li><a class="link-arrow" href="{{ route('cari') }}"><span>Cari</span></a></li>
            <li><a class="link-arrow" href="{{ route('home') }}#kontak"><span>Kontak</span></a></li>
          </ul>
        </div>
        <div>
          <h4>Kontak</h4>
          <ul><li>{{ setting('address') }}</li><li>WhatsApp <x-wa-link class="accent" /></li><li>{{ setting('opening_hours') }}</li></ul>
        </div>
        @if (setting('instagram_url') || setting('tiktok_url') || setting('youtube_url'))
          <div>
            <h4>Sosial</h4>
            <ul>
              @if (setting('instagram_url'))<li><a class="link-arrow" href="{{ setting('instagram_url') }}" target="_blank" rel="noopener"><span>Instagram</span></a></li>@endif
              @if (setting('tiktok_url'))<li><a class="link-arrow" href="{{ setting('tiktok_url') }}" target="_blank" rel="noopener"><span>TikTok</span></a></li>@endif
              @if (setting('youtube_url'))<li><a class="link-arrow" href="{{ setting('youtube_url') }}" target="_blank" rel="noopener"><span>YouTube</span></a></li>@endif
            </ul>
          </div>
        @endif
      </div>
    @endunless
    {{-- Wordmark besar selebar kontainer: ukuran huruf mengikuti lebar (cqw), huruf rapat --}}
    <div class="footer-mark" aria-hidden="true"><span>FZM</span> <span>PRODUCTION</span></div>
    <div class="footer-bottom"><span class="meta">© <span data-year>{{ date('Y') }}</span> {{ setting('company_name') }}</span><span class="meta">Event organizer &amp; produksi panggung</span></div>
  </div>
</footer>
