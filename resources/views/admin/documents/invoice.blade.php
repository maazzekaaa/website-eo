@extends('layouts.document')

@section('title', 'Invoice '.$booking->invoice_number)
@section('back', route('admin.bookings.show', $booking))

{{-- Pratinjau layar memakai isi & gaya yang sama dengan PDF (admin/documents/pdf/invoice-body), jadi yang dicetak = yang diunduh. --}}
@section('content')
  @include('admin.documents.pdf.brand')
  <article class="sheet sheet--a4 sheet--doc">
    @include('admin.documents.pdf.invoice-body', [
        'signature' => setting('signature_path') ? Storage::disk('public')->url(setting('signature_path')) : null,
    ])
  </article>
@endsection
