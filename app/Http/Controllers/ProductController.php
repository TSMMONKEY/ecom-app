<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;

use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Mail\OrderConfirmation;
use App\Jobs\ChargeRemainingAmount;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class ProductController extends Controller
{

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function index()
    {
        // Fetch products from the database
        $products = Product::all(); // Ensure you have the correct model and method to fetch products

        return view('products.manage', compact('products')); // Pass the products variable to the view
    }

    // Create new product
    public function create(Request $request)
    {
        // Validate the product input
        $validated = $request->validate([
            "name" => "required|max:255",
            "price" => "required|numeric|min:0",
            "description" => "required|max:255", // Validate description input
            "image" => "required|image|max:2048|mimes:jpeg,png,jpg,svg",
        ]);
    
        // Check if the uploaded image is valid and store it
        if ($request->file('image')->isValid()) {
            // Replace spaces with underscores in the product name
            $imageName = str_replace(' ', '_', $validated['name']) . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();
            // Store the image in the public storage
            $imagePath = $request->file('image')->storeAs('images', $imageName, 'public');
    
            // Generate the full image URL for Stripe
            $imageUrl = url('storage/' . $imagePath); // Generates the URL for the stored image
        } else {
            return back()->withErrors(['image' => 'Invalid image upload.']);
        }
    // dd($imageUrl);
        // Create the product in Stripe
        try {
            // Set Stripe API key
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
            // Prepare product data
            $productData = [
                'name' => $validated['name'],
                'description' => $validated['description'], // Pass 'description' to Stripe
                'images' => [$imageUrl], // Add the generated image URL
            ];
    
            // Create Stripe Product
            $stripeProduct = \Stripe\Product::create($productData);
    
            // Create a price for the product
            $stripePrice = \Stripe\Price::create([
                'unit_amount' => $validated['price'] * 100, // Convert to cents
                'currency' => 'usd', // Change currency as needed
                'product' => $stripeProduct->id,
            ]);
    
            // Sync products from Stripe to your database after creating the product
            \Artisan::call('stripe:sync-products');
    
            // Redirect to the product management page with a success message
            return redirect(route("home"))->with("success", "Product created in Stripe and synchronized successfully!");
        } catch (\Exception $e) {
            return back()->withErrors(['stripe' => 'Failed to create product on Stripe: ' . $e->getMessage()]);
        }
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function addToCart(Request $request, $id)
    {
        $product = Product::find($id);

        // add to cart
        $cart = session()->get('cart', []);
        $cart[$id] = [
            'name' => $product->name,
            'price' => $product->price,
        ];
        session()->put('cart', $cart);

        dd($cart);
        die();
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Product $product, $id)
    {
        // Find the product by ID
        $product = Product::find($id);

        // Check if the product exists
        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }


        return view('products.edit', compact('product', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);

        // Validate the request
        $validated = $request->validate([
            "name" => "required|max:255",
            "price" => "required|numeric|min:0",
            "description" => "required|max:255",
            "image" => "nullable|image|max:2048|mimes:jpeg,png,jpg,svg", // Image is now nullable
        ]);

        // Update product attributes
        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->paragraph = $validated['description'];

        // Handle the image upload if a new image is provided
        if ($request->hasFile('image')) { // Check if the file input exists
            if ($request->file('image')->isValid()) { // Check if the uploaded file is valid
                $imageName = $validated['name'] . '.' . $request->file('image')->getClientOriginalExtension(); // Create image name
                $product->image = $request->file('image')->storeAs('images', $imageName, 'public'); // Store the image
            } else {
                // Handle the case where the uploaded file is not valid
                return back()->withErrors(['image' => 'The uploaded image is not valid.']);
            }
        }

        // Save the updated product data
        $product->save();

        // Redirect back with a success message
        return redirect()->route('home')->with('success', 'Product updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, $id)
    {
        $product = Product::find($id);

        $product->delete();

        return redirect()->route('home')->with('success', 'Product Deleted Successfully!');
    }

    public function checkout(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $amount = $product->price * 100; // Amount in cents
        $halfAmount = $amount / 2;
    
        // Use the logged-in user's email
        $userEmail = auth()->user()->email;
    
        // Create a Stripe client
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    
        // Create a customer in Stripe using the user's email
        $customer = $stripe->customers->create([
            'email' => $userEmail,
        ]);
    
        // Prepare image data (ensure it's not empty)
        $images = [];
        if (!empty($product->image_url)) {
            $images[] = $product->image_url; // Add only if not empty
        }
    
        // Create a Stripe Checkout session for the first half payment
        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                        'images' => $images, // Use the prepared images array
                    ],
                    'unit_amount' => $halfAmount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
            'customer_email' => auth()->user()->email,
            

        ]);
    
        // Store order with half payment status and customer ID
        $order = Order::create([
            'session_id' => $session->id,
            // 'product_id' => $product->id,
            'user_id' => auth()->id(),
            'status' => 'half_paid',
            'total_price' => $product->price,
            'stripe_customer_id' => $customer->id, // Store the customer ID
        ]);
    
        // Redirect to Stripe Checkout
        return redirect($session->url);
    }
    public function success(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $sessionId = $request->get('session_id');
    
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            $order = Order::where('session_id', $session->id)->where('status', 'half_paid')->first();
    
            if (!$order) {
                throw new NotFoundHttpException();
            }
    
            // Update order status to half_paid (this assumes you want to keep it half_paid for now)
            $order->status = 'half_paid'; // You can keep this or mark it as paid if you prefer
            $order->save();
    
            // Send first confirmation email
            Mail::to(auth()->user()->email)->send(new OrderConfirmation($order));
    
            // Dispatch job to charge the remaining amount after 5 minutes
            ChargeRemainingAmount::dispatch($order)->delay(now()->addMinutes(1));
    
            return view('checkout.success', ['session' => $session]);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Bad excess: ' . $e->getMessage());
        }
    }
    
    

    public function handleWebhook(Request $request)
    {
        \Log::info('Webhook received: ', $request->all());
        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('400');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('400');
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $session = $event->data->object;
                $sessionId = $session->id;

                $order = Order::where('session_id', $session->id)->first();
                if ($order && $order->status == 'unpaid') {
                    $order->status = 'paid';
                    $order->save();
                }

            // dd($order);
            // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return response();
    }
}
