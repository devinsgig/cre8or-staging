<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| We expose the SPA frontend and required API fallbacks.
|--------------------------------------------------------------------------
*/

// --- API endpoints required by the SPA ---
Route::prefix('api')->group(function () {
    // Health
    Route::get('/app-ping', fn () => response()->json(['ok' => true, 'ts' => now()->toIso8601String()]));

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

    // Current user endpoint
    Route::get('/user/me', function () {
        $u = Auth::user();
        if (!$u) {
            return response()->json(['user' => null, 'auth' => false]);
        }
        return response()->json([
            'user' => [
                'id'    => $u->id,
                'name'  => $u->name ?? ($u->username ?? null),
                'email' => $u->email ?? null,
            ],
            'auth' => true,
        ]);
    });
});

// --- SPA catch-all (must exclude /api/*) ---
Route::get('/{any}', function () {
    return view('shaun_core::app');
})->where('any', '^(?!api).*$');
