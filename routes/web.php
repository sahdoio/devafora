<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', HomeController::class)->name('home');
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');

// Admin Routes
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
