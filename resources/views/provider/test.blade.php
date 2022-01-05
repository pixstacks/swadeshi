<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <form action="{{ route('user.testSubmit') }}" method="post" id="myForm">
        @csrf
        <input id="cardholder-name" type="text" name="name">
        <input type="text" name="payment_method_id" id="payment_method_id" value="" style="display: none;">
        <div id="card-element"></div>
        <div id="card-result"></div>
        <button type="submit" id="card-button">Save Card</button>
    </form>

    <script>
        var stripe = Stripe('{{ config('constants.stripe_publishable_key') }}');

        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        var cardholderName = document.getElementById('cardholder-name');
        var cardButton = document.getElementById('card-button');
        var resultContainer = document.getElementById('card-result');

        document.getElementById("myForm").addEventListener("submit", saveCard);

        function saveCard(e) 
        {
            stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: cardholderName.value,
                    },
                }
            ).then(function(result) {
                if (result.error) {
                    // Display error.message in your UI
                    resultContainer.textContent = result.error.message;
                    return false;
                } else {
                    // You have successfully created a new PaymentMethod
                    document.getElementById('payment_method_id').value = result.paymentMethod.id;
                    document.getElementById('myForm').submit();
                }
            });
        }

    </script>
</body>
</html>