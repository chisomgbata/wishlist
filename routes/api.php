<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // product resource routes, in a typical production app other routes will be enabled
    Route::resource('products', ProductController::class)->only(['index', 'show']);

    // Authentication routes
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login')->middleware(['throttle:5,1']);
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('auth/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // wishlist routes
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    });
});
