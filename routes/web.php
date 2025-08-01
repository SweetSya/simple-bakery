<?php

use App\Http\Controllers\Auth\AuthenticateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/login', [AuthenticateController::class, 'create'])->name('login');
Route::post('/login', [AuthenticateController::class, 'attempt'])->name('login');

Route::get('/logout', [AuthenticateController::class, 'destroy'])->name('logout');
// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
