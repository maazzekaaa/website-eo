<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>@yield('title') — FZM Production</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset_v('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset_v('assets/css/document.css') }}">
</head>
<body class="doc-body">
  <div class="doc-toolbar">
    <a class="btn btn-light" href="@yield('back')">← Kembali</a>
    <a class="btn btn-light" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}">Unduh PDF ↓</a>
    <button class="btn btn-red" type="button" data-print>Cetak</button>
  </div>

  @yield('content')

  <script src="{{ asset_v('assets/js/document.js') }}"></script>
</body>
</html>
