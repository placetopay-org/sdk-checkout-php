# PlaceToPay Checkout - Ejemplos PHP

Portal local de demostración para validar el SDK PHP de punta a punta contra PlaceToPay sandbox.

Esta carpeta es solo para validación local y no se publica como parte del paquete en Packagist.

## Estructura

- `portal/index.php`: UI demo y handlers de requests.
- `portal/router.php`: router para servidor built-in de PHP.

## Ejecutar

Desde la raíz del repositorio:

```bash
php -S 127.0.0.1:8090 -t examples/portal examples/portal/router.php
```

Abrir: `http://127.0.0.1:8090`

## Credenciales requeridas

1. Ir a sección **Connect**.
2. Ingresar `login` y `secret_key` de sandbox.
3. Guardar y ejecutar flujos create/query/cancel/collect/reverse/invalidate.

## Flujos soportados

- Create session
- Query session
- Cancel session
- Collect
- Reverse
- Invalidate token

## Transacciones cubiertas

Este portal cubre los 2 bloques funcionales de Checkout:

- Sesion: `createSession`, `querySession`, `cancelSession`.
- Token/post-pago: `collect`, `reverse`, `invalidateToken`.

## Funcionalidades destacadas del ejemplo

- Firma SHA-256 y nonce criptografico manejados por el SDK.
- Verificacion de estado de sesion/transaccion desde la UI.
- Prueba local de los 6 endpoints soportados por el contrato OpenAPI.
