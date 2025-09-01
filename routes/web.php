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
Route::prefix
