# Migration — PHP SDK: `dnetix/redirection` → `placetopay/checkout`

The legacy `dnetix/redirection` SDK targeted PHP 7.4+, used SHA-1 signatures,
and modeled all request/response objects as array-backed entities. The new
`placetopay/checkout` package targets PHP 8.2+, ships through Packagist, and
has a redesigned API surface using readonly classes and backed enums.

---

## TL;DR

Update your `composer.json`:

```diff
- "require": {
-     "dnetix/redirection": "^1.0"
- }
+ "require": {
+     "placetopay/checkout": "^1.0"
+ }
```

Run:

```bash
composer update
```

---

## 1. Namespace rename

```diff
- use Dnetix\Redirection\PlacetoPay;
- use Dnetix\Redirection\Entities\Amount;
- use Dnetix\Redirection\Message\RedirectRequest;
+ use PlaceToPay\Checkout\Checkout;
+ use PlaceToPay\Checkout\CheckoutConfig;
+ use PlaceToPay\Checkout\Environment;
+ use PlaceToPay\Checkout\Country;
+ use PlaceToPay\Checkout\Models\Common\Amount;
+ use PlaceToPay\Checkout\Models\Session\CreateSessionRequest;
```

## 2. Construction

**Before**

```php
$gateway = new \Dnetix\Redirection\PlacetoPay([
    'login'     => 'your-login',
    'tranKey'   => 'your-secret',
    'baseUrl'   => 'https://checkout.placetopay.com/',
    'type'      => \Dnetix\Redirection\PlacetoPay::TP_REST,
]);
```

**After**

```php
$checkout = Checkout::builder()
    ->login('your-login')
    ->secretKey('your-secret')          // raw credential; tranKey is computed on each request
    ->environment(Environment::PRODUCTION)
    ->country(Country::COLOMBIA)        // sets default locale + currency
    ->build();
```

> **Note:** `secretKey` in the new SDK is the raw gateway secret, not the
> pre-computed `tranKey`. The SDK computes the HMAC digest on every request.

## 3. Method renames

| Legacy (`PlacetoPay`)              | New (`Checkout`)                                          |
| ---------------------------------- | --------------------------------------------------------- |
| `request(array)`                   | `createSession(CreateSessionRequest)`                     |
| `query(string)`                    | `querySession(int)`                                       |
| `collect(array)`                   | `collect(CollectRequest)`                                 |
| `reverse(string)`                  | `reverse(string)`                                         |
| *(not available)*                  | `cancelSession(int)` **new**                              |
| *(not available)*                  | `invalidateToken(InvalidateTokenRequest)` **new**         |

## 4. Typed readonly classes instead of arrays

The legacy SDK passed raw PHP arrays to every method and returned
`RedirectResponse` / `RedirectInformation` objects backed by `__get` magic.
The new SDK uses readonly classes with documented properties:

**Before**

```php
$response = $gateway->request([
    'returnUrl' => 'https://example.com/return',
    'payment'   => [
        'reference' => 'ORDER-001',
        'description' => 'Demo',
        'amount' => ['currency' => 'COP', 'total' => 50000],
    ],
    'ipAddress' => '127.0.0.1',
    'userAgent' => 'MyApp/1.0',
]);

if ($response->isSuccessful()) {
    header("Location: " . $response->processUrl());
}
```

**After**

```php
use PlaceToPay\Checkout\Models\Common\Amount;
use PlaceToPay\Checkout\Models\Common\StatusCode;
use PlaceToPay\Checkout\Models\Session\CreateSessionRequest;
use PlaceToPay\Checkout\Models\Session\Payment;

$response = $checkout->createSession(
    CreateSessionRequest::builder()
        ->returnUrl('https://example.com/return')
        ->ipAddress('127.0.0.1')
        ->userAgent('MyApp/1.0')
        ->payment(Payment::of('ORDER-001', 'Demo', new Amount('COP', 50000)))
        ->build()
);

if ($response->status->status === StatusCode::OK) {
    header('Location: ' . $response->processUrl);
}
```

## 5. Checking session status

**Before**

```php
$info = $gateway->query($requestId);

if ($info->isApproved()) {
    // fulfil order
}
```

**After**

```php
use PlaceToPay\Checkout\Models\Common\StatusCode;

$info = $checkout->querySession((int) $requestId);

if ($info->status->status === StatusCode::APPROVED) {
    // fulfil order
}

// Convenience helpers also exist:
if ($info->status->isApproved()) { ... }
if ($info->lastApprovedTransaction() !== null) { ... }
```

## 6. Signing algorithm

The legacy SDK used **SHA-1** for the `tranKey` digest:

```php
$tranKey = base64_encode(sha1($nonce . $seed . $secretKey, true));
```

The new SDK uses **SHA-256**:

```
rawNonce = random_bytes(16)
seed     = ISO 8601 UTC with +00:00 offset
digestIn = rawNonce . seed . secretKey   (binary concatenation)
tranKey  = base64_encode(hash('sha256', $digestIn, true))
wireNonce= base64_encode($rawNonce)
```

This is a wire-visible change: your gateway account must be configured to
accept SHA-256 signatures (all production accounts since 2023 are).

## 7. Error handling

The legacy SDK returned an error status on `$response->status()->message()`;
the new SDK throws typed exceptions:

```diff
- $response = $gateway->request($data);
- if (!$response->isSuccessful()) {
-     log_error($response->status()->message());
- }
+ try {
+     $response = $checkout->createSession($req);
+ } catch (\PlaceToPay\Checkout\Exceptions\ValidationException $e) {
+     // HTTP 400 — server-side validation failed; $e->getMessage()
+ } catch (\PlaceToPay\Checkout\Exceptions\AuthenticationException $e) {
+     // HTTP 401 — digest mismatch / bad credentials
+ } catch (\PlaceToPay\Checkout\Exceptions\RateLimitException $e) {
+     $retryAfter = $e->getRetryAfter(); // \DateInterval|null
+ } catch (\PlaceToPay\Checkout\Exceptions\NetworkException $e) {
+     // wraps the underlying GuzzleException as $e->getPrevious()
+ }
```

All inherit `\PlaceToPay\Checkout\Exceptions\PlaceToPayException`.

## 8. Notification / webhook verification

**Before**

```php
$notification = new \Dnetix\Redirection\Message\Notification(
    $_POST,
    'your-secret'
);
if ($notification->isValidNotification()) { ... }  // SHA-1
```

**After**

```php
use PlaceToPay\Checkout\Webhooks\NotificationVerifier;

$payload = NotificationVerifier::fromArray($_POST);

if (NotificationVerifier::verifySha256($payload, 'your-secret')) {
    // process webhook
}
// Legacy SHA-1 servers (deprecated, removable in v2):
if (NotificationVerifier::verifySha1Legacy($payload, 'your-secret')) { ... }
```

## 9. Dependency changes

| Legacy                            | New                                              |
| --------------------------------- | ------------------------------------------------ |
| PHP 7.4+                          | PHP 8.2+                                         |
| `guzzlehttp/guzzle` ^6\|^7        | `guzzlehttp/guzzle` ^7                           |
| SHA-1 auth signing                | SHA-256 auth signing                             |
| `md5(rand())` nonce               | `random_bytes(16)` CSPRNG nonce                  |
| Array-backed entity pattern       | Readonly classes with typed properties           |
| No retry                          | Exponential backoff + jitter, `Retry-After` support |
| No log redaction                  | PSR-3 debug logging with PCI/PII field redaction |

## 10. New features

- `cancelSession(int)` — stop a pending session (not in `dnetix/redirection`).
- `invalidateToken(InvalidateTokenRequest)` — revoke a stored token.
- `Country::ECUADOR` preset with the correct base URL + currency.
- `DocumentValidator` for client-side LatAm document validation
  (CC, NIT, CPF, CI, RUT, RUC, and more) — ported from `dnetix/redirection`
  `DocumentHelper`.
- `Status::isApproved() / isRejected() / isError() / isPending()` helpers.
- `SessionInformation::lastTransaction() / lastApprovedTransaction() /
  lastAuthorization()` helpers.
- Typed `AutopayRequest` for recurring-with-token flows.
- `X-Idempotency-Key` support on `createSession`.
