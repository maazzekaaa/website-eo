<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pelindung semua halaman /admin (termasuk halaman masuk).
 * - ADMIN_ALLOWED_IPS (opsional, dipisah koma) → hanya IP tersebut yang bisa membuka admin;
 *   IP lain mendapat 404 seolah halaman tidak ada. Kosong = semua IP boleh (tetap wajib login).
 * - Halaman admin tidak diindeks mesin pencari, tidak disimpan cache browser
 *   (tombol Kembali setelah keluar tidak menampilkan data), dan tidak bisa dibingkai iframe.
 */
class ProtectAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = array_filter(array_map('trim', explode(',', (string) config('app.admin_allowed_ips'))));
        if ($allowed && ! in_array($request->ip(), $allowed, true)) {
            abort(404);
        }

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Frame-Options', 'DENY');

        return $response;
    }
}
