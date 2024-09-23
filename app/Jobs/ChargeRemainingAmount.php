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
            // Charge the remaining amount
            $remainingAmount = $this->product->price / 2;
    
            \Log::info('Charging remaining amount: $' . $remainingAmount);
    
            // Confirm the payment with the saved payment method
            $paymentIntent = PaymentIntent::create([
                'amount' => $remainingAmount * 100, // Convert to cents
                'currency' => 'usd', // Consider making this dynamic
                'payment_method' => $this->product->stripe_payment_method_id,
                'confirm' => true,
                'off_session' => true,
            ]);
    
            \Log::info('Remaining amount charged successfully for product: ' . $this->product->id);
        } catch (\Exception $e) {
            \Log::error('Error charging remaining amount: ' . $e->getMessage());
        }
    }
}
