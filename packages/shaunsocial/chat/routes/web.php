<?php


use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/chat/{id}', function () {
        return view('shaun_core::app');
    })->name('chat.detail');

    Route::get('/chat/request', function () {
        return view('shaun_core::app');
    })->name('chat.request');

    Route::get('/chat/request/{id}', function () {
        return view('shaun_core::app');
    })->name('chat.request_detail');
});
