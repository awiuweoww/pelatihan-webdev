<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Definisi routing web utama aplikasi untuk halaman dashboard, manajemen pengguna,
 *               dan modul peminjaman alat gunung.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi rute resource user, peminjaman, serta rute dashboard.
 */

use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('users', UserController::class)->except(['show']);
Route::resource('peminjaman', PeminjamanController::class)->except(['show']);

