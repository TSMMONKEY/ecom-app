<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Auth;

class CheckoutController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function initiateCheckout(Request $request, Product $product)
    {
        $session = $this->stripeService->createTwoPhaseCheckoutSession(
            $product,
            route('checkout.success'),
            route('checkout.cancel')
        );

        if (!$session) {
            return back()->with('error', 'Unable to initiate checkout. Please try again.');
        }

        return view('checkout', ['sessionId' => $session->id]);
    }

    public function handleSuccess(Request $request)
    {
        // Capture the order details (mock or from Stripe if available)
        $order = [
            'product_name' => 'Sample Product', // Replace with actual product info
            'amount' => '100.00',              // Replace with actual amount
            'currency' => 'USD',                // Replace with actual currency
            'payment_status' => 'Processing',   // Example status
        ];

        // Get the logged-in user
        $user = Auth::user();

        // Send the email with the order details
        Mail::to($user->email)->send(new OrderConfirmation($order));

        // Redirect the user to a success page with a success message
        return redirect()->route('checkout.success-page')
                         ->with('success', 'Your payment is being processed. A confirmation email has been sent.');
    }
}
