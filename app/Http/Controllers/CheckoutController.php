<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\Request;

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
}