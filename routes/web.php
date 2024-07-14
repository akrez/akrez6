<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\ActiveBlogMiddleware;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [SiteController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get(AppServiceProvider::HOME, [HomeController::class, 'index'])->name('home');
    Route::patch('blogs/{id}/active', [BlogController::class, 'active'])->name('blogs.active');
    Route::resource('blogs', BlogController::class)->parameter('blogs', 'id');
    Route::middleware(ActiveBlogMiddleware::class)->group(function () {
        Route::resource('products', ProductController::class)->parameter('products', 'id');
    });
});
