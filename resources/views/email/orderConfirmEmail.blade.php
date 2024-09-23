<!DOCTYPE html>
<html>

<head>
    <title>Order Confirmation</title>
</head>

<body>
    <h1>Order Confirmation</h1>
    <p>Thank you for your purchase!</p>
    <p>Product: {{ $order['product_name'] }}</p>
    <p>Amount: {{ $order['amount'] }} {{ $order['currency'] }}</p>
    <p>Status: {{ $order['payment_status'] }}</p>
    <p>We are processing your order and will notify you once it’s completed.</p>

</body>

</html>
