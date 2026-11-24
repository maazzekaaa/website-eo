<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Invoice {{ $booking->invoice_number }}</title>
  @include('admin.documents.pdf.brand')
  <style>@page { margin: 32px 36px; } body { margin: 0; }</style>
</head>
<body>
  @include('admin.documents.pdf.invoice-body')
</body>
</html>
