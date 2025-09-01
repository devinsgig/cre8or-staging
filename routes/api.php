<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Api\AppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/app', [AppController::class, 'index']);   // alias used by the SPA
Route::get('/v1/app', [AppController::class, 'index']); // optional alias

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
