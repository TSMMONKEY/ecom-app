<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use App\Models\Product as LocalProduct;

class SyncProductsToStripe extends Command
{
    protected $signature = 'stripe:sync-products-to-stripe';
    protected $description = 'Synchronize products from local database to Stripe';

    public function __construct()
    {
        parent::__construct();
        Stripe::setApiKey(env('STRIPE_SECRET')); // Set your Stripe secret key
    }

    public function handle()
    {
        $localProducts = LocalProduct::all(); // Retrieve all products from local database

        foreach ($localProducts as $localProduct) {
            // Check if the product already exists in Stripe
            if ($localProduct->stripe_product_id) {
                // Update the existing product in Stripe
                StripeProduct::update(
                    $localProduct->stripe_product_id,
                    [
                        'name' => $localProduct->name,
                        'description' => $localProduct->description,
                    ]
                );
            } else {
                // Create a new product in Stripe
                $stripeProduct = StripeProduct::create([
                    'name' => $localProduct->name,
                    'description' => $localProduct->description,
                ]);

                // Save the Stripe product ID to the local database
                $localProduct->stripe_product_id = $stripeProduct->id;
                $localProduct->save();
            }
        }

        $this->info('Products synchronized to Stripe successfully.');
    }
}
