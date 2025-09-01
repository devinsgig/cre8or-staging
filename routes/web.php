<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Keep the SPA catch-all, but first expose /api/app.
|
*/

// --- API config the SPA needs ---
Route::prefix('api')->group(function () {
    // Health ping
    Route::get('/app-ping', function () {
        return response()->json(['ok' => true, 'ts' => now()->toIso8601String()]);
    });

    // Guaranteed app config (fallback; no controller needed)
    Route::get('/app', function (Request $request) {
        $appUrl    = rtrim(config('app.url') ?: url('/'), '/');
        $assetUrl  = rtrim(config('app.asset_url') ?: $appUrl, '/');
        $commit    = trim(@shell_exec('git rev-parse --short HEAD') ?: '');

        return response()->json([
            'app'       => config('app.name'),
            'url'       => $appUrl,
            'asset_url' => $assetUrl,
            'version'   => $commit ?: null,
            'time'      => now()->toIso8601String(),
        ]);
    });

    // Backward-compat alias
    Route::get('/v1/app', fn () => redirect('/api/app'));
});

// --- SPA catch-all (must exclude /api/*) ---
Route::get('/{any}', function () {
    return view('shaun_core::app');
})->where('any', '^(?!api).*$');
