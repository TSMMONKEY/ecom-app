@extends('layouts.admin')

@section('title', 'Order Complete')
@section('content')
<style>
    .success-container {
        text-align: center;
        padding: 50px;
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: max-content;
    }
    .success-title {
        font-size: 2rem;
        color: #28a745;
    }
    .success-message {
        margin-top: 20px;
        font-size: 1.25rem;
        color: #555;
    }
    .order-details {
        margin-top: 30px;
        text-align: left;
    }
    .order-details dt {
        font-weight: bold;
    }
</style>

<div class="container">
    <div class="success-container">
        <h1 class="success-title">Order Complete!</h1>
        <p class="success-message">Thank you for your purchase! Your order has been successfully processed.</p>
        
        <div class="order-details">
            <h4>Order Summary</h4>
            <dl>
                <dt>Order ID:</dt>
                <dd style="overflow: auto;">{{ $session->id }}</dd>
                <dt>Amount Paid:</dt>
                <dd>${{ number_format($session->amount_total / 100, 2) }} USD</dd>
                {{-- <dt>Customer Email:</dt> --}}
                {{-- <dd>{{ $customer->email }}</dd> --}}
                <dt>Status:</dt>
                <dd class="text-success">Processing...</dd>
            </dl>
        </div>
        
        <a href="/" class="btn btn-primary mt-4">Continue Shopping</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection
