<?php

use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['prefix' => 'admin-panel', 'middleware' => ['checkAuth:administrator,staff,guest']], function () {
    Route::get('/dashboard', [DashboardController::class, 'view'])->name('dashboard');
    Route::get('/download/{filename}', [DownloadController::class, 'download'])
        ->name('download.file');
    Route::group(['prefix' => 'role'], function () {
        Route::get('/', [RoleController::class, 'view'])->name('role.view');
        Route::get('/all', [RoleController::class, 'all'])->name('role.all');
        Route::get('/create', [RoleController::class, 'create_view'])->name('role.create');
        Route::post('/create', [RoleController::class, 'create'])->name('role.create');
        Route::get('/update', [RoleController::class, 'update_view'])->name('role.update');
        Route::put('/update', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/delete', [RoleController::class, 'delete'])->name('role.delete');
    });
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'view'])->name('user.view');
        Route::get('/all', [UserController::class, 'all'])->name('user.all');
        Route::get('/create', [UserController::class, 'create_view'])->name('user.create');
        Route::post('/create', [UserController::class, 'create'])->name('user.create');
        Route::get('/detail', [UserController::class, 'detail_view'])->name('user.detail');
        Route::get('/update', [UserController::class, 'update_view'])->name('user.update');
        Route::put('/update', [UserController::class, 'update'])->name('user.update');
        Route::delete('/delete', [UserController::class, 'delete'])->name('user.delete');
        Route::post('/export', [UserController::class, 'export'])->name('user.export');
        Route::post('/import/preview', [UserController::class, 'importPreview'])->name('user.import.preview');
        Route::post('/import', [UserController::class, 'import'])->name('user.import');
    });
});
