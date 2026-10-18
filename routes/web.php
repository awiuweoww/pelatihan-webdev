<?php

use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('users', UserController::class)->except(['show']);
Route::resource('peminjaman', PeminjamanController::class)->except(['show']);

