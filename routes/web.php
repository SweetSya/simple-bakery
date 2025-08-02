<?php

use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/login', [AuthenticateController::class, 'view'])->name('login');
Route::post('/login', [AuthenticateController::class, 'attempt'])->name('login');
Route::get('/logout', [AuthenticateController::class, 'destroy'])->name('logout');


Route::get('/register', [RegisterController::class, 'view'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register');
// require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
