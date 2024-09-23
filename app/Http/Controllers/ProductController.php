<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StripeService;

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
            dd($imageUrl);
            die();
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
}
