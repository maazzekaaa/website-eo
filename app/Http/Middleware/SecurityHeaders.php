<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Header keamanan dasar untuk semua halaman. */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // Header yang sudah diisi lebih ketat (mis. admin: X-Frame-Options DENY) tidak ditimpa
        foreach ([
            'X-Content-Type-Options' => 'nosniff',                              // browser tidak menebak jenis file
            'X-Frame-Options' => 'SAMEORIGIN',                                  // tidak bisa disisipkan di iframe situs lain
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ] as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
