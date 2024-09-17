<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch products from the database
        $products = Product::all(); // Ensure you have the correct model and method to fetch products

        return view('products.manage', compact('products')); // Pass the products variable to the view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            "name"=> "required|max:255",
            "price"=> "required|numeric|min:0", // Changed to ensure price is numeric and non-negative
            "description"=> "required|max:255",
            "image"=> "required|image|max:2048|mimes:jpeg,png,jpg,svg",
        ]);

        $imageName = $validated['name'] . '.' . $request->file('image')->getClientOriginalExtension(); // Create image name
        $product = new Product();
        $product->name = $validated['name']; // Use validated data
        $product->catalog = 3; // Use validated data
        $product->price = $validated['price']; // Use validated data
        $product->paragraph = $validated['description']; // Fixed property name from paragraph to description
        if ($request->file('image')->isValid()) { // Check if the uploaded file is valid
            $product->image = $request->file('image')->storeAs('images', $imageName, 'public'); // Store the image with the product name
        } else {
            // Handle the error (e.g., throw an exception or return an error response)
            return back()->withErrors(['image' => 'Invalid image upload.']);
        }
        $product->save();

        return redirect(route("product.manage"))->with("success", "Product created successfully!");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
