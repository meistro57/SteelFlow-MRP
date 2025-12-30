<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/projects', function () {
    return Inertia::render('Projects/Index');
})->name('projects');

Route::get('/inventory', function () {
    return Inertia::render('Inventory/Index');
})->name('inventory');
