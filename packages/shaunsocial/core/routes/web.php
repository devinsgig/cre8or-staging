<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Web\DiscoveryController;
use Packages\ShaunSocial\Core\Http\Controllers\Web\PageController;
use Packages\ShaunSocial\Core\Http\Controllers\Web\PostController;
use Packages\ShaunSocial\Core\Http\Controllers\Web\HomeController;
use Packages\ShaunSocial\Core\Http\Controllers\Web\OpenidController;
use Packages\ShaunSocial\Core\Http\Controllers\Web\UserController;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    //home url
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    //home url
    Route::get('/discovery', [DiscoveryController::class, 'index'])->name('discovery.index');

    //openid url
    Route::get('/openid/auth/{name}/{app?}', [OpenidController::class, 'auth'])->name('openid.auth');

    //openid delete user url
    Route::get('/openid/delete_user/{name}', [OpenidController::class, 'delete_user'])->name('openid.delete_user');

    //register url
    Route::get('/signup', [UserController::class, 'signup'])->name('user.signup');

    //profile url
    Route::get('/'.config('shaun_core.core.prefix_profile').'{user_name}/{any?}', [UserController::class, 'profile'])->where('user_name', config('shaun_core.regex.user_name_router'))->name('user.profile');
    
    //post url
    Route::get('/post/{id}/{comment_id?}/{reply_id?}', [PostController::class, 'detail'])->where(['id' => '[0-9]+', 'comment_id' => '[0-9]+', 'reply_id' => '[0-9]+'])->name('post.detail');

    //notifications url
    Route::get('/notifications', function () {
        return view('shaun_core::app');
    })->name('notification.list');

    //login url
    Route::get('/login', function () {
        return view('shaun_core::app', [
            'title' => __('Login')
        ]);
    })->name('user.login');

    //unsubscribe url
    Route::get('/unsubscribe/{email}/{hash}', function () {
        return view('shaun_core::app');
    })->name('unsubscribe.email');

    //page url
    Route::get('/sp/{slug}', [PageController::class, 'detail'])->name('page.detail');

    //Verify email url
    Route::get('/email_verify/{code}', function () {
        return view('shaun_core::app');
    })->name('user.email_verify');

    //Recover password url
    Route::get('/recover', function () {
        return view('shaun_core::app', [
            'title' => __('Recover Account')
        ]);
    })->name('user.recover');

    //User download url
    Route::get('/download_copy', function () {
        return view('shaun_core::app');
    })->name('user.download_copy');

    //Contact url
    Route::get('/contact', function () {
        return view('shaun_core::app', [
            'title' => __('Contact')
        ]);
    })->name('contact.create');
});
