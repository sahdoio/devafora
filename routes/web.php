<?php

use App\Http\Controllers\Admin\LinkController as AdminLinkController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', HomeController::class)->name('home');
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('links', AdminLinkController::class);
    Route::resource('posts', AdminPostController::class);
    Route::get('posts/{post}/preview', [AdminPostController::class, 'preview'])->name('posts.preview');

    // Settings
    Route::get('settings/theme', [SettingController::class, 'theme'])->name('settings.theme');
    Route::put('settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme.update');
});

// Legacy dashboard route (redirect to new admin dashboard)
Route::get('dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
