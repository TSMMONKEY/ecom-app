@extends('layouts.admin')

@section('title', 'Add Product')
@section('content')
    <style>
        .form-control {
            border: 1px solid #ced4da;
            /* Add border */
            background-color: #ffffff;
            /* Ensure background is white */
            color: #495057;
            /* Text color */
        }

        .form-control:focus {
            /* Add focus styles */
            background-color: #ffffff;
            /* Keep background white on focus */
            border-color: #80bdff;
            /* Change border color on focus */
            outline: 0;
            /* Remove default outline */
        }

        .form-label {
            font-weight: bold;
            /* Make labels bold */
        }
    </style>
    <form action="{{ route('product.update', $product->id) }}" method="POST" class="form-group" enctype="multipart/form-data">
        @csrf
        {{-- @if ($errors->any()) <!-- Check for errors -->
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <!-- Loop through errors -->
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        <div class="mb-3">
            <label for="name" class="form-label">Product Name:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $product->name) }}"
                required>
            @if ($errors->has('name'))
                <!-- Error message for name -->
                <div class="text-danger">{{ $errors->first('name') }}</div>
            @endif
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price:</label>
            <input type="number" id="price" name="price" class="form-control"
                value="{{ old('price', $product->price) }}" step="0.01" required>
            @if ($errors->has('price'))
                <!-- Error message for price -->
                <div class="text-danger">{{ $errors->first('price') }}</div>
            @endif
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" required>{{ old('description', $product->paragraph) }}</textarea>
            @if ($errors->has('description'))
                <!-- Error message for description -->
                <div class="text-danger">{{ $errors->first('description') }}</div>
            @endif
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image:</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*"
                style="width: 186px; padding: 10px;" value="{{ old('image', $product->image) }}">
            <div style="margin-top: 10px;"></div> <!-- Added space between choose file and no file chosen -->
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" width="100">
            @endif
            @if ($errors->has('image'))
                <!-- Error message for image -->
                <div class="text-danger">{{ $errors->first('image') }}</div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
        
        <a href="/manage-products" class="btn btn-primary">Cancel</a>
    </form>
@endsection
