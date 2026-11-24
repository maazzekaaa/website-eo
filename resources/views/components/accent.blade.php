{{-- Komponen: kata aksen merah. serif (bawaan) = Instrument Serif miring seperti hero; :serif="false" = tebal merah. --}}
@props(['serif' => true])<span {{ $attributes->class([$serif ? 'serif' : 'accent']) }}>{{ $slot }}</span>
