<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Api\AppController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Main SPA catch-all (keeps frontend working)
Route::get('/{any}', function () {
    return view('shaun_core::app');
})->where('any', '^(?!api).*$');

// Ensure /api/app exists even if routes/api.php isn't loaded
Route::prefix('api')->group(function () {
    // Temporary health route to prove path works
    Route::get('/app-ping', function () {
        return response()->json(['ok' => true, 'ts' => now()->toIso8601String()]);
    });

    // Real endpoint the SPA expects
    Route::get('/app', [AppController::class, 'index']);
    Route::get('/v1/app', [AppController::class, 'index']);
});
