{{-- Halaman 404. Juga ditampilkan ke tamu yang membuka halaman admin tanpa login
     (bootstrap/app.php), agar panel admin tampak seperti tidak ada. --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <x-favicon />
  <title>Halaman tidak ditemukan — FZM Production</title>
  <link rel="stylesheet" href="{{ asset_v('assets/css/base.css') }}">
  <style>
    .nf { min-height: 100vh; min-height: 100svh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; background: var(--bg, #FAFAFA); text-align: center; }
    .nf main { max-width: 520px; display: flex; flex-direction: column; align-items: center; gap: 18px; }
    .nf-code { font-size: 15px; font-weight: 600; letter-spacing: .08em; color: var(--red-ink, #C2272D); }
    .nf h1 { font-size: clamp(30px, 6vw, 44px); line-height: 1.05; letter-spacing: -0.04em; }
    .nf p { color: var(--muted, #5F5F5F); font-size: 17px; }
    .nf .btn { margin-top: 8px; }
  </style>
</head>
<body class="nf">
  <main>
    <span class="nf-code">404</span>
    <h1>Halaman tidak ditemukan</h1>
    <p>Halaman yang Anda cari tidak ada atau sudah dipindahkan. Silakan kembali ke beranda.</p>
    <a class="btn btn-red" href="{{ url('/') }}">Kembali ke beranda</a>
  </main>
</body>
</html>
