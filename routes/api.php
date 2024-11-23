<?php

use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PaketController;
use App\Http\Controllers\User\PesananController;
use App\Http\Controllers\User\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * route "/register"
 * @method "POST"
 */
Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

/**
 * route "/user"
 * @method "GET"
 */
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', App\Http\Controllers\Api\LogoutController::class)->name('logout');

Route::prefix('user')->middleware('auth:api')->group(function () {
    // Profile routes
    Route::apiResource('profiles', UserProfileController::class);
    Route::get('/check-profile', [UserProfileController::class, 'checkProfile']);

    // Pesanan routes
    Route::apiResource('pesanan', PesananController::class);
    Route::post('pesanan/{id}/batal', [PesananController::class, 'cancelOrder']);
});

Route::prefix('admin')->middleware('auth:api')->group(function () {
    // Admin routes for kategori, menu, paket
    Route::apiResource('kategori', KategoriController::class);
    Route::apiResource('menu', MenuController::class);
    Route::apiResource('paket', PaketController::class);
    Route::get('pesanan', [PesananController::class, 'adminIndex']); // Fetch all orders for admin
});
