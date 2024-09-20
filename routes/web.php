<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController; 
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('pages.home');
Route::get('/home', [PageController::class, 'index'])->name('home');
Route::get('/send-mail', [MailController::class, 'index']);
Route::get('/cart/add/{id}', [ProductController::class, 'addToCart'])->name('cart.add');

// stripe checkout
use Illuminate\Support\Facades\Auth;

Route::middleware('auth')->group(function () {
    Route::get('/checkout', function (Request $request) {
        $stripePriceId = 'price_1Q0NXpCZEqNPIkIzNSESVGX4';
        $quantity = 1;

        return $request->user()->checkout([$stripePriceId => $quantity], [
            'success_url' => route('checkout-success'),
            'cancel_url' => route('checkout-cancel'),
        ]);
    })->name('checkout');
});

 
Route::view('/checkout/success', 'checkout.success')->name('checkout-success');
Route::view('/checkout/cancel', 'checkout.cancel')->name('checkout-cancel');

// Web hooks
route::post('/webhook', [StripeWebhookController::class, 'handleWebhook']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
// Home Page
// Route::get('/home', [ProductController::class, 'index'])->middleware(['auth'])->name('home');


// Products
Route::group(['prefix' => 'products'], function () { 
    Route::get('/', [PageController::class, 'products'])->name('pages.products');
    Route::get('/single', function () {
        return view('pages.single-product');
    })->name('product.show');
});

// Products Admin
Route::group(['middleware' => [ 'auth',EnsureIsAdmin::class]], function () {
    Route::get('/manage-products', [ProductController::class, 'index'])->name('home');
    Route::get('/add-products', [ProductController::class, 'index'])->name('product.add');
    Route::get('/edit-product/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::post('/edit-product/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/delete-product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
});


Route::get('/add-product', function () {
    return view('products.add-product');
})->name('product.add');

Route::post('/add-product', [ProductController::class, 'create'])->name('product.store');

// profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
