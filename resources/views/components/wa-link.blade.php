{{-- Komponen: nomor WhatsApp yang bisa diketuk (membuka wa.me di tab baru; tautan diisi site.js). --}}
@props(['text' => 'Halo FZM, saya ingin bertanya tentang layanan.'])
<a {{ $attributes->class('wa-link') }} href="https://wa.me/{{ setting('wa_number') }}" data-wa="{{ setting('wa_number') }}" data-text="{{ $text }}">{{ $slot->isEmpty() ? setting('wa_number') : $slot }}</a>
