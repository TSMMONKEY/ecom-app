<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Thank you for your order!</h1>
    <p>Your order (ID: {{ $order->id }}) has been confirmed and is being processed.</p>
    <p>Total amount: ${{ number_format($order->total_amount, 2) }}</p>
    <!-- Add more order details as needed -->
</body>
</html>