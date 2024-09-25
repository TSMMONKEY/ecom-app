<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
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
            $imageName = $validated['name'] . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();
            $imagePath = $request->file('image')->storeAs('images', $imageName, 'public');

            // Generate the full image URL
            $imageUrl = url('storage/' . $imagePath);

        } else {
            return back()->withErrors(['image' => 'Invalid image upload.']);
        }

        // Create the product in Stripe
        try {
            // Set Stripe API key
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            // Prepare product data
            $productData = [
                'name' => $validated['name'],
                'description' => $validated['description'], // Pass 'description' to Stripe
            ];

            // Add the image URL to the product data
            if (!empty($imageUrl)) {
                $productData['images'] = [$imageUrl];
            }

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
        // Retrieve the product using the ID
        $product = Product::findOrFail($id); // Fetch the product or throw an error if not found

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        // 
        // $lineItems = [];
        $totalPrice = $product->price;

        // Convert product price to cents (Stripe requires price in cents)
        $unitAmount = (int) ($product->price * 100); // Ensure this is an integer

        // Prepare the line items for the Stripe Checkout session
        // dd([$product->paragraph, $request->user()->id]);

        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name, // Dynamic product name
                        'description' => $product->paragraph, // Dynamic product name
                        'images' => !empty($product->image) ? [$product->image] : null, // Ensure it's an array or null
                    ],
                    'unit_amount' => $unitAmount, // Dynamic product price in cents
                ],
                'quantity' => 1, // You can make quantity dynamic if needed
            ],
        ];

        // Remove images key if it's null
        if (is_null($lineItems[0]['price_data']['product_data']['images'])) {
            unset($lineItems[0]['price_data']['product_data']['images']);
        }

        try {
            $userEmail = $request->user()->email;

            // Create the Stripe Checkout Session
            $checkout_session = $stripe->checkout->sessions->create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}", // Change to your success URL
                'cancel_url' => route('checkout.cancel'), // Change to your cancel URL
                'customer_email' => $userEmail,
            ]);
            \Log::info('Creating Stripe Checkout session for product ID: ' . $product->id);


            $order = new Order();
            $order->user_id = auth()->id();
            $order->status = 'unpaid';
            $order->total_price = $totalPrice;
            $order->session_id = $checkout_session->id;
            $order->save();

            // Redirect to the Stripe Checkout page
            return redirect($checkout_session->url);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            \Log::error('Stripe Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Stripe error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('General Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing your request.');
        }
    }

    public function success(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $sessionId = $request->get('session_id');

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            // $customer = $stripe->customers->retrieve($session->customer);

            $order = Order::where('session_id', $session->id)->where('status', 'unpaid')->first();
            if (!$order) {
                throw new NotFoundHttpException();
            }
            if ($order) {
                $order->status = 'paid';
                $order->save();

                try {
                    // Mail::to(auth()->email())->send(new OrderConfirmation($order));
                    Mail::to(auth()->user()->email)->send(new OrderConfirmation($order));
                } catch (\Exception $e) {
                    \Log::error('Email sending failed: ' . $e->getMessage());
                    return back()->withErrors(['email' => 'Failed to send confirmation email.']);
                }
            }

            // dd($order);

            return view('checkout.success', ['session' => $session]);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Bad excess' . $e->getMessage());
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
