# PlacetoPay PHP SDK

With this code you will be able to quickly connect with the PlacetoPay Checkout service.

In order to see more comprehensive examples of how it works, please refer to the examples and the [documentation](https://docs-gateway.placetopay.com/)

### Migration from version 1

- [ ] Change the settings parameter `url` for `baseUrl`
- [ ] If you have set the `timeout` for rest then change it to the root of the settings
- [ ] `PlacetoPayServiceException` is thrown instead of returning an ERROR status response

## Installation

Requires PHP 8.1 or higher.

Using composer from your project

```
composer require placetopay/placetopay-sdk
```

Or If you just want to run the examples contained in this project run "composer install" to load the vendor autoload

## Usage

Create an object with the configuration required for that instance

```php 
$placetopay = new PlacetoPay\PlacetoPaySdk\PlacetoPay([
    'login' => 'YOUR_LOGIN', // Provided by PlacetoPay
    'tranKey' => 'YOUR_TRANSACTIONAL_KEY', // Provided by PlacetoPay
    'baseUrl' => 'https://THE_BASE_URL_TO_POINT_AT',
    'timeout' => 10, // (optional) 15 by default
    'algorithm' => 'sha256', // (optional) tranKey digest, sha256 by default as documented, sha1 is still accepted by Checkout
]);
```

The `api/gateway/*` operations (`tokenInformation()`, `instrumentInformation()`) always digest with sha256, the service rejects sha1 there.

### Creating a new Payment Request to obtain a Session Payment URL

Just provide the information of the payment needed and you will get a process url if its successful, for this example we are using the MINIMUM INFORMATION that needs to be provided, to see the full structure refer to the documentation or the example on [examples/basic/payment.php](examples/basic/payment.php)

```
$reference = 'COULD_BE_THE_PAYMENT_ORDER_ID";
$request = [
    'payment' => [
        'reference' => $reference,
        'description' => 'Testing payment',
        'amount' => [
            'currency' => 'USD',
            'total' => 120,
        ],
    ],
    'expiration' => date('c', strtotime('+2 days')),
    'returnUrl' => 'http://example.com/response?reference=' . $reference,
    'ipAddress' => '127.0.0.1',
    'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
];

$response = $placetopay->request($request);
if ($response->isSuccessful()) {
    // STORE THE $response->requestId() and $response->processUrl() on your DB associated with the payment order
    // Redirect the client to the processUrl or display it on the JS extension
    $response->processUrl();
} else {
    // There was some error so check the message and log it
    $response->status()->message();
}
```

### Obtain information about a previously created session

```
$response = $placetopay->query('THE_REQUEST_ID_TO_QUERY');

if ($response->isSuccessful()) {
    // In order to use the functions please refer to the PlacetoPay\PlacetoPaySdk\Message\RedirectInformation class

    if ($response->status()->isApproved()) {
        // The payment has been approved
    }
} else {
    // There was some error with the connection so check the message
    print_r($response->status()->message() . "\n");
}
```

### Refund a transaction

Without an amount the whole transaction is refunded, with one only that part.

```php
$placetopay->reverse($internalReference);
$placetopay->reverse($internalReference, ['currency' => 'COP', 'total' => 3000]);
```

### Cancel a session

Cancels a session the user has not completed, its status changes to `REJECTED`.

```php
$response = $placetopay->cancelSession($requestId);

if ($response->isSuccessful()) {
    $response->session()->status()->status(); // REJECTED
}
```

### Preauthorization (checkin, reauthorization and checkout)

Create the session with `type` set to `checkin`; once approved, the funds are held and the transaction can be adjusted or captured with its `internalReference`.

```php
$response = $placetopay->request([
    'type' => 'checkin',
    'payment' => ['reference' => $reference, 'description' => 'Hotel', 'amount' => ['currency' => 'USD', 'total' => 500]],
    // returnUrl, ipAddress, userAgent...
]);

// Adjust the held amount
$placetopay->reauthorize($internalReference, ['currency' => 'USD', 'total' => 700]);

// Capture it, a total of 0 releases the held funds
$response = $placetopay->checkout($internalReference, ['currency' => 'USD', 'total' => 650]);
$response->isApproved();
$response->payment(); // The resulting transaction
```

### Confirm or cancel a transaction

Sessions created with `'metadata' => ['requiresConfirmation' => true]` leave the transaction in `PENDING_CONFIRMATION` until the merchant decides.

```php
$placetopay->confirm($internalReference);
$placetopay->cancelTransaction($internalReference);
```

Every transaction action, including `reverse`, can also be sent with `$placetopay->transaction(['internalReference' => 123, 'action' => 'checkout', 'amount' => [...]])`, see `PlacetoPay\PlacetoPaySdk\Enums\TransactionAction`.

### Tokens

Use the token (or subtoken) obtained in a subscription session.

```php
$instrument = ['instrument' => ['token' => ['token' => $token]]];

// Franchise, last digits, bin, expiration and owner
$information = $placetopay->tokenInformation($instrument);
$information->token()->bin();

// Card type, credits and installments available to pay with the token
$information = $placetopay->instrumentInformation($instrument);
foreach ($information->credits() as $credit) {
    $credit->installments(); // [1, 2, 3, ...]
}

// Charge with the chosen credit and installments, without them a single installment is charged
$placetopay->collect([
    // payer, payment, ipAddress, userAgent...
    'instrument' => [
        'token' => ['token' => $token],
        'credit' => $information->credits()[0]->toCredit(3),
    ],
]);

// The token can no longer be used
$placetopay->invalidateToken($instrument)->isSuccessful();
```
