<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| TEMP: Ensure all SPA boot APIs exist (incl. Sanctum CSRF cookie)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    // App config
    Route::get('/app', function () {
        $appUrl   = rtrim(config('app.url') ?: url('/'), '/');
        $assetUrl = rtrim(config('app.asset_url') ?: $appUrl, '/');
        $commit   = trim(@shell_exec('git rev-parse --short HEAD') ?: '');
        return response()->json([
            'app'       => config('app.name'),
            'url'       => $appUrl,
            'asset_url' => $assetUrl,
            'version'   => $commit ?: null,
            'time'      => now()->toIso8601String(),
        ]);
    });
    Route::get('/v1/app', fn () => redirect('/api/app'));

    // Init endpoint expected by SPA
    Route::get('/init', fn () => response()->json(['ok' => true]));

    // Current user endpoint (guest-safe)
    Route::get('/user/me', function () {
        $u = Auth::user();
        return response()->json([
            'user' => $u ? [
                'id'    => $u->id,
                'name'  => $u->name ?? ($u->username ?? null),
                'email' => $u->email ?? null,
            ] : null,
            'auth' => (bool) $u,
        ]);
    });

    // TEMP: 200 for any other /api/* (log once)
    Route::any('/{any}', function (Request $req, string $any) {
        Log::info('API Fallback', [
            'method' => $req->method(),
            'path'   => '/api/' . $any,
            'query'  => $req->query(),
        ]);
        return response()->json([
            'ok'      => true,
            'path'    => '/api/' . $any,
            'message' => 'temporary fallback',
        ]);
    })->where('any', '.*');
});

// Sanctum CSRF cookie (some builds request this before calling /api/*)
Route::get('/sanctum/csrf-cookie', function () {
    $token = Str::random(40);
    $domain = config('session.domain') ?: parse_url(config('app.url') ?: url('/'), PHP_URL_HOST);
    // 2 hours, Secure+SameSite=Lax
    return response()->json(['ok' => true])->withCookie(
        cookie(name: 'XSRF-TOKEN', value: $token, minutes: 120, path: '/', domain: $domain, secure: true, httpOnly: false, raw: false, sameSite: 'lax')
    );
});

/*
|--------------------------------------------------------------------------
| SPA catch-all (exclude /api/*)
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function () {
    return view('shaun_core::app');
})->where('any', '^(?!api).*$');
