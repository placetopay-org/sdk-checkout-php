# PlaceToPay Checkout - SDK PHP

[![CI](https://github.com/placetopay/checkout-php/actions/workflows/ci.yml/badge.svg)](https://github.com/placetopay/checkout-php/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/placetopay/checkout.svg)](https://packagist.org/packages/placetopay/checkout)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

SDK oficial de PHP 8.2+ para **PlaceToPay Web Checkout**.

Esta libreria te permite integrar pagos de forma simple: crear sesion,
redirigir al comprador y confirmar el estado final de la transaccion desde tu
aplicacion.

Terminología estándar del proyecto: ver `../docs/GLOSARIO.md`.

> Si vienes del SDK previo, revisa `MIGRATION.md` para mapear metodos y modelos.

---

## Instalación

```bash
composer require placetopay/checkout
```

Requiere **PHP 8.2+**.

## Versiones soportadas

| Versión SDK | PHP | Estado | Parches de seguridad hasta |
|-------------|-----|--------|----------------------------|
| `1.x` | 8.2, 8.3, 8.4 | **Actual** | Hasta el siguiente major |
| `0.x` | - | Pre-release | End-of-life |

## Quickstart (5 minutos)

Con este ejemplo puedes crear una sesion y obtener el `processUrl` para
enviar al comprador al checkout de PlaceToPay.

```php
use PlaceToPay\Checkout\Checkout;
use PlaceToPay\Checkout\Country;
use PlaceToPay\Checkout\Environment;
use PlaceToPay\Checkout\Models\Common\Amount;
use PlaceToPay\Checkout\Models\Session\CreateSessionRequest;
use PlaceToPay\Checkout\Models\Session\Payment;

$checkout = Checkout::builder()
    ->login('YOUR_LOGIN')
    ->secretKey('YOUR_SECRET_KEY')
    ->environment(Environment::SANDBOX)
    ->country(Country::COLOMBIA)
    ->build();

$response = $checkout->createSession(
    CreateSessionRequest::builder()
        ->ipAddress('127.0.0.1')
        ->userAgent('MyApp/1.0')
        ->returnUrl('https://example.com/return')
        ->payment(Payment::of('ORDER-0001', 'Demo purchase', new Amount('COP', 50000)))
        ->build()
);

echo $response->processUrl;
echo $response->requestId;
```

Después del pago:

```php
use PlaceToPay\Checkout\Models\Common\StatusCode;

$info = $checkout->querySession($response->requestId);

if ($info->status->status === StatusCode::APPROVED) {
    // completar orden
}
```

## Que puedes hacer con este SDK

| Endpoint | Método | Notas |
|---|---|---|
| `POST /api/session` | `createSession` | Soporta `autopay`, `metadata`, `dispersion` |
| `POST /api/session/{id}` | `querySession` | |
| `POST /api/session/{id}/cancel` | `cancelSession` | Nuevo vs SDK de referencia |
| `POST /api/collect` | `collect` | Cobro con instrumento tokenizado |
| `POST /api/reverse` | `reverse` | Por `internalReference` |
| `POST /api/instrument/invalidate` | `invalidateToken` | Nuevo vs SDK de referencia |
| Verificación webhook SHA-256 | `NotificationVerifier::verifySha256` | Recomendado |
| Verificación webhook SHA-1 | `NotificationVerifier::verifySha1Legacy` | Solo compatibilidad legacy |

## Flujo de integracion recomendado

Flujo de sesion (checkout web):

1. `createSession` para iniciar el checkout y obtener `requestId` + `processUrl`.
2. `querySession` para confirmar estado despues del retorno del pagador.
3. `cancelSession` para invalidar una sesion pendiente cuando aplica.

Flujo de tokenizacion y post-pago:

1. `collect` para cobrar con instrumento tokenizado.
2. `reverse` para reversar por `internalReference`.
3. `invalidateToken` para revocar el token.

## Funcionalidades incluidas en v1

- Soporte de `auth.additional` via `CheckoutConfig::builder()->authAdditional(...)`.
- Soporte de `CollectRequest.provider` para override opcional de procesador.
- Helpers de estado en `Status` y `Transaction` (`isApproved`, `isRejected`, `isError`, `isSuccessful`).
- Helpers de sesion en `SessionInformation` (`lastTransaction`, `lastApprovedTransaction`, `lastAuthorization`).
- Compatibilidad con valores heterogeneos en `processorFields[].value`.
- Validacion local de documentos LatAm con `DocumentValidator`.

## Casos de uso comunes

- Checkout estandar en web con redireccion (`createSession` + `querySession`).
- Cobro con token guardado (`collect`) para compras recurrentes.
- Reversion de pagos (`reverse`) y revocacion de token (`invalidateToken`).

### Limitaciones por pais

- Ecuador: la funcionalidad `autopay` no esta habilitada en WebCheckout para este pais. Si se envia en `createSession`, puede ser rechazada por el gateway.
- Ecuador: la recurrencia en pago unico no esta disponible actualmente.

### Compatibilidad de respuestas del gateway

- En respuestas de `querySession` / `collect`, `payment[].processorFields[].value` puede llegar como string, numero, booleano u objeto JSON.
- El SDK ya modela ese valor como `mixed` para soportar metadatos de procesador no-string.

## Configuración

`CheckoutConfig::builder()` / `Checkout::builder()`:

| Metodo | Default | Uso |
|---|---|---|
| `login(string)` | requerido | Login de comercio |
| `secretKey(string)` | requerido | Secret de comercio |
| `environment(Environment)` | `SANDBOX` | Sandbox/Producción |
| `country(?Country)` | `null` | Defaults por pais |
| `baseUrl(?string)` | - | Override explícito (solo HTTPS) |
| `connectTimeout(int)` | `10` | Segundos |
| `readTimeout(int)` | `30` | Segundos |
| `retryMaxAttempts(int)` | `3` | Mínimo 1 |
| `retryInitialDelayMs(int)` | `200` | Backoff inicial |
| `retryMaxDelayMs(int)` | `5000` | Tope de backoff |
| `userAgentSuffix(?string)` | `null` | Sufijo user-agent |
| `additionalHeaders(array)` | `[]` | Headers extra |
| `authAdditional(?array)` | `null` | Campo `auth.additional` |
| `logger(?LoggerInterface)` | `null` | DEBUG con payload redactado |
| `httpClient(?ClientInterface)` | SDK-owned | Cliente inyectado |

## Manejo de errores

Todas las excepciones extienden `PlaceToPayException`:

- `AuthenticationException`
- `ValidationException`
- `NotFoundException`
- `RateLimitException`
- `NetworkException`
- `ServerException`

## Verificación de webhook

```php
use PlaceToPay\Checkout\Webhooks\NotificationPayload;
use PlaceToPay\Checkout\Webhooks\NotificationVerifier;

$payload = NotificationPayload::fromArray($request->all());

if (!NotificationVerifier::verifySha256($payload, $secretKey)) {
    return response('Invalid signature', 403);
}
```

> Las notificaciones webhook de PlaceToPay están confirmadas sobre SHA-256.

## Idempotencia

```php
$checkout->createSession($request, idempotencyKey: 'order-42-attempt-1');
$checkout->collect($collectRequest, idempotencyKey: 'collect-42-attempt-1');
$checkout->reverse('INT_REF_001', idempotencyKey: 'reverse-42-attempt-1');
```

## Seguridad

- Firma: SHA-256.
- Nonce: 16 bytes criptográficos via `random_bytes()`.
- TLS: HTTPS obligatorio.
- Logging: redacción automática de PAN, CVV, `tranKey`, `nonce`, token, PIN, password y `secretKey`.
- Disclosure: ver [SECURITY.md](SECURITY.md).

## Pruebas locales con app de ejemplo

El directorio `examples/portal/` contiene un portal liviano para validar flujos end-to-end. Ver [`examples/README.md`](examples/README.md).

```bash
php -S 127.0.0.1:8090 -t examples/portal examples/portal/router.php
# http://127.0.0.1:8090
```

Ingresa `login` y `secret_key` desde el UI en la sección Connect antes de invocar acciones del API.

## Instalar desde código fuente

```bash
git clone https://github.com/placetopay/checkout-php.git
cd checkout-php
composer install
composer test
composer stan
composer cs:check
```

## Contribuir

Ver [CONTRIBUTING.md](CONTRIBUTING.md).

## Licencia

MIT, ver [LICENSE](LICENSE).
