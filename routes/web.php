<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
use App\Mail\OrderConfirmation;
use App\Jobs\ChargeRemainingAmount;

use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('pages.home');
// Route::get('/home', [PageController::class, 'index'])->name('home');
Route::get('/send-mail', [MailController::class, 'index']);
Route::get('/cart/add/{id}', [ProductController::class, 'addToCart'])->name('cart.add');

// stripe checkout
use Illuminate\Support\Facades\Auth;

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{id}', function (Request $request, $id) {
        // Retrieve the product by its ID
        $product = Product::findOrFail($id);

        $stripePriceId = $product->stripe_price_id;
        $quantity = 1;

        // Calculate the amount to charge (50% of the product price)
        $amountToCharge = $product->price * 0.5 * 100; // Convert to cents

        // Create a PaymentIntent for the initial charge
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amountToCharge,
            'currency' => 'usd', // Adjust as necessary
            'payment_method_types' => ['card'],
            // Additional options as needed
        ]);

        // Proceed with the checkout
        return $request->user()->checkout([$stripePriceId => $quantity], [
            'success_url' => route('thankYou', $id),
            'cancel_url' => route('checkout-cancel'),
        ]);
    })->name('checkout');
});


Route::get('/thank-you/{id}', function (Request $request, $id) {
    // Get the logged-in user
    $user = $request->user();
    $product = Product::findOrFail($id);

    // Dispatch the job to charge the remaining amount after 5 minutes
// Assuming you have the product instance available here
    ChargeRemainingAmount::dispatch($product)->delay(now()->addMinutes(1));


    // Create the order array with actual details for the first email
    $order = [
        'product_name' => $product->name,
        'amount' => $product->price,
        'currency' => $product->currency,
        'payment_status' => 'Processing',
    ];

    // Send the first email confirmation
    Mail::to($user->email)->send(new OrderConfirmation($order));

    // Show a thank-you page or success message
    return view('checkout.success')->with('success', 'Your initial payment is being processed. A confirmation email has been sent.');
})->name('thankYou');


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
Route::group(['middleware' => ['auth', EnsureIsAdmin::class]], function () {
    Route::get('/manage-products', [ProductController::class, 'index'])->name('home');
    Route::get('/add-products', [ProductController::class, 'index'])->name('product.add');
    Route::get('/edit-product/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::post('/edit-product/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/delete-product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
});


// Route::get('/add-product', function () {
//     return view('products.add-product');
// })->name('product.add');

// Route::post('/add-product', [ProductController::class, 'create'])->name('product.store');

// profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
