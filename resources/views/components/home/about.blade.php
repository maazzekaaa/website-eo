{{-- Komponen: bagian Tentang di beranda. Profil diambil dari Admin → Pengaturan (about). --}}
<section class="section" id="tentang">
  <div class="container">
    <x-section-head eyebrow="Tentang kami" lead="Kru produksi panggung yang percaya bahwa detail kecil menentukan kesan besar.">
      Di balik setiap<br><x-accent>momen.</x-accent>
    </x-section-head>
    <div class="detail">
      <x-detail-card data-reveal>
        <p class="prose">{{ setting('about') }}</p>
      </x-detail-card>
      <x-detail-card title="Kantor kami" data-reveal style="--d:1">
        <ul class="price-list">
          <li><span>Alamat</span><strong>{{ setting('address') }}</strong></li>
          <li><span>Jam</span><strong>{{ setting('opening_hours') }}</strong></li>
          <li><span>WhatsApp</span><strong><x-wa-link class="accent" /></strong></li>
        </ul>
      </x-detail-card>
    </div>
  </div>
</section>
