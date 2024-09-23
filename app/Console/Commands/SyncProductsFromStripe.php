<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
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
            $stripeProducts = StripeProduct::all([
                'limit' => 100,
                'starting_after' => $startingAfter
            ]);

            foreach ($stripeProducts->data as $stripeProduct) {
                // Update or create the local product only if it exists in Stripe
                $localProduct = LocalProduct::updateOrCreate(
                    ['stripe_product_id' => $stripeProduct->id],
                    [
                        'name' => $stripeProduct->name,
                        'paragraph' => $stripeProduct->description ?? null,
                    ]
                );

                // Fetch associated prices
                $prices = Price::all(['product' => $stripeProduct->id]);

                if (!empty($prices->data)) {
                    // Assuming only one price per product
                    $price = $prices->data[0];

                    // Update the product with the price information
                    $localProduct->update([
                        'stripe_price_id' => $price->id,
                        'price' => $price->unit_amount / 100,
                    ]);
                }

                $this->info("Synchronized product: {$stripeProduct->name}");
            }

            $hasMore = $stripeProducts->has_more;
            if ($hasMore) {
                $startingAfter = end($stripeProducts->data)->id;
            }
        }

        $this->info('Products synchronized successfully.');
    }

}
