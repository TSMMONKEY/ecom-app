<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <h1>Checkout</h1>
    <p>You're about to make a deposit payment. The remaining balance will be charged automatically after 5 minutes.</p>
    <button id="checkout-button">Proceed to Payment</button>

    <script>
        var stripe = Stripe('{{ config('services.stripe.key') }}');
        var checkoutButton = document.getElementById('checkout-button');

        checkoutButton.addEventListener('click', function() {
            stripe.redirectToCheckout({
                sessionId: '{{ $sessionId }}'
            }).then(function (result) {
                if (result.error) {
                    alert(result.error.message);
                }
            });
        });
    </script>
</body>
</html>