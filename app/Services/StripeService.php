<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price as StripePrice;

class StripeService
{
    public function __construct()
    {
        // Set the Stripe API key from the environment variable
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a product on Stripe and associate it with the local product.
     *
     * @param \App\Models\Product $product
     * @return \Stripe\Product|null
     */
    public function createProduct($product)
    {
        try {
            // Create the product in Stripe
            $stripeProduct = StripeProduct::create([
                'name' => $product->name,
                'description' => $product->paragraph, // Correct property
                'price' => $product->price,
                'images' => [$this->getImageUrl($product->image)], // Ensure the image URL is valid
                
            ]);

            // Create the price in Stripe
            $stripePrice = StripePrice::create([
                'product' => $stripeProduct->id,
                'unit_amount' => $product->price * 100, // Convert price to cents
                'currency' => 'usd', // Use your desired currency
            ]);

            // Save Stripe product and price IDs in the local database
            $product->stripe_product_id = $stripeProduct->id;
            $product->stripe_price_id = $stripePrice->id;
            $product->save();

            return $stripeProduct;
        } catch (\Exception $e) {
            // Handle exceptions or errors
            \Log::error('Failed to create product on Stripe: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the full URL of the image for Stripe.
     *
     * @param string $imagePath
     * @return string
     */
    private function getImageUrl($imagePath)
    {
        return asset('storage/' . $imagePath); // Ensure the image URL is publicly accessible
    }
}
