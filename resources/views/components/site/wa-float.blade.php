{{-- Komponen: tombol WhatsApp melayang (pojok kanan bawah). Tautan diisi site.js dari data-wa. --}}
@props(['text' => 'Halo FZM, saya ingin bertanya tentang layanan.', 'label' => 'Tanya via WhatsApp'])
<a class="wa-float" href="#" data-wa="{{ setting('wa_number') }}" data-text="{{ $text }}" aria-label="{{ $label }}">
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-5A8 8 0 1 1 21 12z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5"/></svg>
  <span>{{ $label }}</span>
</a>
