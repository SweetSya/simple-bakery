<?php

use App\Http\Controllers\Auth\RoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['prefix' => 'admin-panel', 'middleware' => ['checkAuth:administrator,staff']], function () {
    Route::get('/dashboard', function () {
        return Inertia::render('auth/Dashboard');
    })->name('dashboard');

    Route::group(['prefix' => 'role'], function () {
        Route::get('/', [RoleController::class, 'view'])->name('role.view');
        Route::get('/all', [RoleController::class, 'all'])->name('role.all');
        Route::post('/create', [RoleController::class, 'create'])->name('role.create');
        Route::get('/get', [RoleController::class, 'get'])->name('role.get');
        Route::put('/update', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/delete', [RoleController::class, 'delete'])->name('role.delete');
    });
});
