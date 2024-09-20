<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use App\Models\Product as LocalProduct;

class SyncProductsFromStripe extends Command
{
    protected $signature = 'stripe:sync-products';
    protected $description = 'Synchronize products from Stripe';

    public function __construct()
    {
        parent::__construct();
        Stripe::setApiKey(env('STRIPE_SECRET')); // Set your Stripe secret key
    }

    public function handle()
    {
        $this->info('Starting synchronization...');

        $hasMore = true;
        $startingAfter = null;

        while ($hasMore) {
            // Fetch Stripe products in paginated manner
            $stripeProducts = Product::all([
                'limit' => 100, // Fetch up to 100 products per request (Stripe's max limit)
                'starting_after' => $startingAfter
            ]);

            foreach ($stripeProducts->data as $stripeProduct) {
                // Sync the product to your local database
                $localProduct = LocalProduct::updateOrCreate(
                    ['stripe_product_id' => $stripeProduct->id], // Use the Stripe Product ID as a unique key
                    [
                        'name' => $stripeProduct->name,
                        'description' => $stripeProduct->description,
                        'stripe_product_id' => $stripeProduct->id,
                    ]
                );

                // Fetch associated prices for the product
                $prices = Price::all(['product' => $stripeProduct->id]);

                if (!empty($prices->data)) {
                    // For simplicity, assume only one price per product
                    $price = $prices->data[0];

                    // Update or save the stripe_price_id and price
                    $localProduct->update([
                        'stripe_price_id' => $price->id,
                        'price' => $price->unit_amount / 100, // Stripe stores price in cents
                    ]);
                }

                $this->info("Synchronized product: {$stripeProduct->name}");
            }

            $hasMore = $stripeProducts->has_more;
            if ($hasMore) {
                $startingAfter = end($stripeProducts->data)->id; // Set the last product ID for pagination
            }
        }

        $this->info('Products synchronized successfully.');
    }
}
