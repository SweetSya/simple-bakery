<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('guest/Index', [
        'title' => 'Guestbook',
        'description' => 'A simple guestbook application built with Laravel and Inertia.js.',
    ]);
})->name('home');

// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
