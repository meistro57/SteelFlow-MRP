<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication Routes
Route::get('/login/microsoft', [AuthController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [AuthController::class, 'handleProviderCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/projects', function () {
        return Inertia::render('Projects/Index');
    })->name('projects');

    Route::get('/inventory', function () {
        return Inertia::render('Inventory/Index');
    })->name('inventory');
});
