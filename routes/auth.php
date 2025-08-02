<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['middleware' => ['checkAuth:administrator,staff']], function () {
    Route::get('/dashboard', function () {
        return Inertia::render('auth/Dashboard');
    })->name('dashboard');
});
