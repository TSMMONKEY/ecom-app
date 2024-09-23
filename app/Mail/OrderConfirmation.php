<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    // Pass the order data in the constructor
    public function __construct($order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Your Order is Being Processed')
                    ->view('email.orderConfirmEmail')  // Make sure this points to the correct view
                    ->with('order', $this->order);     // Pass the order data to the view
    }
}
