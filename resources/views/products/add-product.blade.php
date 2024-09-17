@extends('layouts.admin')

@section('title', 'Add Product')
@section('content')
    <style>
        .form-control {
            border: 1px solid #ced4da; /* Add border */
            background-color: #ffffff; /* Ensure background is white */
            color: #495057; /* Text color */
        }
        .form-control:focus { /* Add focus styles */
            background-color: #ffffff; /* Keep background white on focus */
            border-color: #80bdff; /* Change border color on focus */
            outline: 0; /* Remove default outline */
        }
        .form-label {
            font-weight: bold; /* Make labels bold */
        }
    </style>
    <form action="{{route('product.store')}}" method="POST" class="form-group" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Product Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price:</label>
            <input type="number" id="price" name="price" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image:</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*" required style="width: 186px; padding: 10px;">
            <div style="margin-top: 10px;"></div> <!-- Added space between choose file and no file chosen -->
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
@endsection