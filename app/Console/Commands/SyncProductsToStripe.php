<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price as StripePrice;
use App\Models\Product as LocalProduct;

class SyncProductsToStripe extends Command
{
    protected $signature = 'stripe:sync-products-to-stripe';
    protected $description = 'Synchronize products and prices from local database to Stripe';

    public function __construct()
    {
        parent::__construct();
        Stripe::setApiKey(env('STRIPE_SECRET')); // Set your Stripe secret key
    }

    public function handle()
    {
        $localProducts = LocalProduct::all(); // Retrieve all products from the local database

        foreach ($localProducts as $localProduct) {
            // Check if the product already exists in Stripe
            if ($localProduct->stripe_product_id) {
                // Update the existing product in Stripe
                $stripeProduct = StripeProduct::update(
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

            // Sync the product price with Stripe
            $this->syncPriceToStripe($localProduct);
        }

        $this->info('Products and prices synchronized to Stripe successfully.');
    }

    /**
     * Sync product price to Stripe.
     * 
     * @param LocalProduct $localProduct
     */
    protected function syncPriceToStripe(LocalProduct $localProduct)
    {
        // Check if the product has an existing Stripe price ID
        if ($localProduct->stripe_price_id) {
            // Update the existing price in Stripe (Stripe prices are immutable, so we cannot modify them)
            // Typically, you'd create a new price instead of updating an existing one.
            $this->info("Price already exists for {$localProduct->name}, Price ID: {$localProduct->stripe_price_id}");
        } else {
            // Create a new price for the product in Stripe
            $stripePrice = StripePrice::create([
                'unit_amount' => $localProduct->price * 100, // Convert price to cents
                'currency' => 'usd', // Specify the currency (adjust as needed)
                'product' => $localProduct->stripe_product_id,
            ]);

            // Save the Stripe price ID and price to the local database
            $localProduct->stripe_price_id = $stripePrice->id;
            $localProduct->save();

            $this->info("Synchronized price for {$localProduct->name}, Price ID: {$stripePrice->id}, Amount: {$localProduct->price}");
        }
    }
}
