<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LinkController as AdminLinkController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', HomeController::class)->name('home');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');

// Admin redirect route
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('admin');

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('links', AdminLinkController::class);
    Route::resource('posts', AdminPostController::class);
    Route::get('posts/{post}/preview', [AdminPostController::class, 'preview'])->name('posts.preview');
    Route::post('posts/{post}/send-newsletter', [AdminPostController::class, 'sendNewsletter'])->name('posts.send-newsletter');

    // Newsletter
    Route::get('newsletter', [AdminNewsletterController::class, 'index'])->name('newsletter.index');
    Route::get('newsletter/{newsletter}', [AdminNewsletterController::class, 'show'])->name('newsletter.show');
    Route::post('newsletter/{newsletter}/toggle', [AdminNewsletterController::class, 'toggleStatus'])->name('newsletter.toggle');
    Route::delete('newsletter/{newsletter}', [AdminNewsletterController::class, 'destroy'])->name('newsletter.destroy');
});

// Legacy dashboard route (redirect to new admin dashboard)
Route::get('dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
