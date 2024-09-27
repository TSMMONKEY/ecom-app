<?php

namespace App\Jobs;

use App\Models\Order;
use Stripe\StripeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChargeRemainingAmount
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    
        $remainingAmount = ($this->order->total_price * 100) / 2; // Calculate remaining amount
    
        try {
            // Use the stored Stripe customer ID
            $charge = $stripe->charges->create([
                'amount' => $remainingAmount,
                'currency' => 'usd',
                // 'customer' => $this->order->stripe_customer_id, // Use the stored customer ID
                'description' => 'Remaining payment for order ' . $this->order->id,
            ]);
    
            // Update order status to paid
            $this->order->status = 'paid';
            $this->order->save();
    
            // Send second confirmation email
            Mail::to($this->order->user->email)->send(new OrderConfirmation($this->order));
    
        } catch (\Exception $e) {
            \Log::error('Failed to charge remaining amount: ' . $e->getMessage());
        }
    }
    
}