<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => ['nullable', 'array'],
            'qty' => ['nullable', 'array'],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_wa' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
                $digits = strlen(preg_replace('/\D/', '', (string) $value));
                if ($digits < 10 || $digits > 14) {
                    $fail('Nomor WA tidak valid (10–14 digit).');
                }
            }],
            'event_time' => ['nullable', 'date_format:H:i'],
            'event_location' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_date.*' => 'Pilih tanggal acara yang masih tersedia.',
            'customer_name.required' => 'Nama wajib diisi.',
            'customer_name.max' => 'Nama terlalu panjang.',
            'customer_wa.required' => 'Nomor WA tidak valid (10–14 digit).',
            'event_time.*' => 'Jam mulai tidak valid.',
            'event_location.required' => 'Lokasi wajib diisi.',
            'event_location.max' => 'Lokasi terlalu panjang.',
            'note.max' => 'Catatan terlalu panjang.',
        ];
    }
}
