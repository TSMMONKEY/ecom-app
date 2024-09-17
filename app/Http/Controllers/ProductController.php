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
        return view("product.main");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        dd($request);
        $validated = $request->validate([
            "name"=> "required|max:255",
            "price"=> "required|numeric|min:0", // Changed to ensure price is numeric and non-negative
            "description"=> "required|max:255",
            "image"=> "required|file|size:900",
        ]);

        $product = new Product();
        $product->name = $validated['name']; // Use validated data
        $product->price = $validated['price']; // Use validated data
        $product->paragraph = $validated['description']; // Fixed property name from paragraph to description
        $product->image = $request->file('image')->store('images'); // Store the image and save the path
        $product->save();

        return redirect(route("product.manage"));
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
