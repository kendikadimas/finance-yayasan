<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token Sanctum yang valid tidak otomatis berarti klien masih boleh akses: admin bisa
 * menonaktifkan klien API tanpa mencabut tokennya. Middleware ini menolak request dari
 * klien yang is_aktif-nya false, dan mencatat last_used_at untuk audit.
 */
class EnsureApiClientActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user('sanctum');

        abort_unless($client instanceof ApiClient && $client->is_aktif, 403, 'Klien API tidak aktif.');

        $client->forceFill(['last_used_at' => now()])->saveQuietly();

        return $next($request);
    }
}
