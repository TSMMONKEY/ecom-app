<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Product;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Support\Facades\Mail;

class ChargeRemainingAmount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function handle()
    {
        \Log::info('Running ChargeRemainingAmount for product: ' . $this->product->id);
        
        Stripe::setApiKey(config('services.stripe.secret'));
        
        try {
            $paymentMethodId = $this->product->stripe_payment_method_id;
    
            if (empty($paymentMethodId)) {
                \Log::error('Payment method ID is missing for product: ' . $this->product->id);
                return;
            }
    
            // Calculate the remaining amount (50% of the total price)
            $remainingAmount = $this->product->price / 2; 
            \Log::info('Charging remaining amount: $' . $remainingAmount);
    
            // Create the PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $remainingAmount * 100, // Convert to cents
                'currency' => 'usd',
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);
    
            \Log::info('PaymentIntent Response: ', $paymentIntent->toArray());
    
            if ($paymentIntent->status === 'succeeded') {
                Mail::to($this->product->user->email)->later(now()->addMinutes(5), new PaymentConfirmationMail($this->product));
                \Log::info('Confirmation email queued for product: ' . $this->product->id);
            }
        } catch (\Exception $e) {
            \Log::error('Error charging remaining amount: ' . $e->getMessage());
        }
    }
    
}
