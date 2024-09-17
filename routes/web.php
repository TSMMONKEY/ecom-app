<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController; 
use App\Http\Controllers\ProductController; 

use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('pages.home');

// Removed commented-out routes for clarity

Route::get('/home', function () {
    return view('dashboard');
})->middleware(['auth'])->name('home');

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Products
Route::group(['prefix' => 'products'], function () { 
    Route::get('/', [PageController::class, 'products'])->name('pages.products');
    Route::get('/single', function () {
        return view('pages.single-product');
    })->name('product.show');
});

Route::get('/manage-products', function () {
    return view('products.manage');
})->name('product.manage');

Route::get('/add-product', function () {
    return view('products.add-product');
})->name('product.add');

Route::post('/add-product', [ProductController::class, 'create'])->name('product.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
