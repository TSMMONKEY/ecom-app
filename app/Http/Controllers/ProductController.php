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
            "price" => "required|numeric|min:0", // Ensure price is numeric and non-negative
            "description" => "required|max:255",
            "image" => "required|image|max:2048|mimes:jpeg,png,jpg,svg",
        ]);

        // Create the product instance
        $product = new Product();
        $product->name = $validated['name']; // Use validated data
        $product->catalog = 3; // Set catalog ID (as per your logic)
        $product->price = $validated['price']; // Use validated data
        $product->paragraph = $validated['description']; // Fixed property name from paragraph to description

        // Check if the uploaded image is valid and store it
        if ($request->file('image')->isValid()) {
            // Create a unique image name to avoid conflicts
            $imageName = $validated['name'] . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();
            $product->image = $request->file('image')->storeAs('images', $imageName, 'public'); // Store the image
        } else {
            // Return an error if the image is invalid
            return back()->withErrors(['image' => 'Invalid image upload.']);
        }

        // Save the product in the database
        $product->save();

        // OPTIONAL: Sync the product to Stripe
        try {
            // Use Artisan to run the stripe:sync-products-to-stripe command after saving the product
            \Artisan::call('stripe:sync-products-to-stripe');
        } catch (\Exception $e) {
            // Handle potential errors from syncing to Stripe
            return back()->withErrors(['stripe' => 'Failed to sync product to Stripe: ' . $e->getMessage()]);
        }

        // Redirect to the product management page with a success message
        return redirect(route("product.manage"))->with("success", "Product created and synchronized with Stripe successfully!");
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
        return redirect()->route('product.manage')->with('success', 'Product updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, $id)
    {
        $product = Product::find($id);

        $product->delete();

        return redirect()->route('product.manage')->with('success', 'Product Deleted Successfully!');
    }
}
