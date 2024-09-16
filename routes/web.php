<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController; // {{ edit_1 }}
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [PageController::class,'index'])->name('pages.home');

Route::get('/single-product', function () {
    return view('pages.single-product');
})->name(('product.show'));

Route::get('/home', function () {
    return view('dashboard');
})->middleware(['auth'])->name('home');

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
