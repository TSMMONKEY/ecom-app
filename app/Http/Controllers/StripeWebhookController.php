<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use Stripe\StripeClient;

class StripeWebhookController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function handle(Request $request)
    {
        Log::info('Stripe webhook received:', ['request' => $request->all()]);

        $payload = $request->all();
        $event = $payload['type'] ?? '';

        switch ($event) {
            case 'product.created':
            case 'product.updated':
                $this->syncProduct($payload['data']['object']);
                break;

            default:
                Log::info('Unhandled event type:', $event);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function syncProduct($stripeProduct)
    {
        // Find or create the product in your app
        $product = Product::updateOrCreate(
            ['stripe_product_id' => $stripeProduct['id']],
            [
                'name' => $stripeProduct['name'],
                'description' => $stripeProduct['description'],
                'price' => $stripeProduct['metadata']['price'] ?? null, // Adjust based on how you store prices
                'image' => $stripeProduct['images'][0] ?? null, // Adjust based on your data structure
            ]
        );

        Log::info('Product synchronized:', $product->toArray());
    }
}
