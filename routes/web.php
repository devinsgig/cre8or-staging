<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| TEMP: make the SPA boot no matter what by answering any /api/* call.
| We'll see exactly which endpoints it wants in the logs, then wire
| the real controllers after it loads.
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    // Known essentials
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
    Route::get('/init', fn () => response()->json(['ok' => true]));
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

    // TEMP: return 200 for ANY other /api/* request and log it
    Route::any('/{any}', function (Request $req, string $any) {
        Log::info('API Fallback', [
            'method' => $req->method(),
            'path'   => '/api/' . $any,
            'query'  => $req->query(),
        ]);

        // Minimal sane default shape so frontend won’t crash
        return response()->json([
            'ok'      => true,
            'path'    => '/api/' . $any,
            'message' => 'temporary fallback',
        ]);
    })->where('any', '.*');
});

/*
|--------------------------------------------------------------------------
| SPA catch-all (exclude /api/*)
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function () {
    return view('shaun_core::app');
})->where('any', '^(?!api).*$');
