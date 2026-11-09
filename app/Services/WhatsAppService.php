<?php

namespace App\Services;

use App\Models\Booking;

class WhatsAppService
{
    /** Samakan nomor ke format 62…: 0812… dan +62 812… menjadi 62812…. */
    public function normalize(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        return match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    public function bookingMessage(Booking $booking): string
    {
        $items = $booking->items->map(fn ($item) => "- {$item->description} ({$item->qty} {$item->unit}): ".rupiah($item->subtotal))->implode("\n");
        $date = $booking->event_date->translatedFormat('j F Y').($booking->event_time ? ", {$booking->event_time}" : '');
        $tax = $booking->tax_amount ? "PPN {$booking->tax_rate}%: ".rupiah($booking->tax_amount)."\n" : '';

        return "Halo FZM, saya ingin memesan.\n"
            ."Kode: {$booking->booking_code}\n{$items}\n"
            .$tax
            .'Total: '.rupiah($booking->total)."\n"
            .'DP 50%: '.rupiah($booking->dp_amount)."\n"
            ."Tanggal: {$date}\n"
            ."Lokasi: {$booking->event_location}\n"
            ."Nama: {$booking->customer_name}";
    }

    public function bookingUrl(Booking $booking): string
    {
        return 'https://wa.me/'.setting('wa_number').'?text='.rawurlencode($this->bookingMessage($booking));
    }
}
