# Contributing — PlaceToPay Checkout PHP SDK

Thanks for taking the time to contribute. This document walks through local
setup, the expected workflow for changes, and the quality bar we enforce.

## Local setup

You need:

- **PHP 8.2+** — `brew install php` (macOS) or follow https://php.net/downloads
- **Composer 2** — https://getcomposer.org/download/

```bash
git clone https://github.com/placetopay/checkout-php.git
cd checkout-php
composer install
```

## Building and testing

```bash
composer test:unit          # unit tests only
composer test               # all tests (unit + integration if env vars set)
composer stan               # PHPStan static analysis
composer cs:check           # PHP CS Fixer dry-run (no modifications)
composer cs                 # PHP CS Fixer — applies formatting
```

The CI gate runs `test:unit` + `stan` + `cs:check` on every push and PR.
Tests must pass on PHP 8.2, 8.3, and 8.4.

## Running the integration tests

Integration tests are skipped by default unless credentials are set:

```bash
export PLACETOPAY_LOGIN=your-login
export PLACETOPAY_SECRET_KEY=your-secret
composer test:integration   # runs SandboxSmokeTest against checkout-test.placetopay.com
```

## Coding standards

- **PHP 8.2+ idioms** — readonly classes and properties, backed enums,
  constructor property promotion, named arguments, intersection types.
- **No additional HTTP client abstraction** — we use Guzzle 7 exclusively.
  Don't add `symfony/http-client`, `nette/http`, or `curl` wrappers.
- **One PSR logger** injected via `CheckoutConfig` — don't call `error_log()`
  or `file_put_contents()` directly in SDK code.
- **Readonly classes expose named constructors** (`of(...)`, `builder()`,
  `fromArray(...)`) rather than public setters.
- **Internal helpers stay internal** — prefer `internal/` namespace for
  anything the SDK consumer doesn't need to touch.
- The public surface is deliberately small: `Checkout`, `CheckoutConfig`,
  `Environment`, `Country`, the `Models/**` readonly classes, and the
  exception hierarchy.

## Logs and PCI/PII

All sensitive fields are redacted by `Http\RedactingLogger`. Before adding a
new field that might carry PAN, CVV, token, pin, or password content, add the
property name to the redaction sets. Unit-test in
`tests/Unit/RedactingLoggerTest.php`.

## The cross-SDK auth test vector

If you touch `Auth\AuthSigner`, `AuthSignerTest` must continue to produce
identical output to the Java and .NET SDKs for these inputs:

```
login     = "SDK_TEST_LOGIN"
secretKey = "SDK_TEST_SECRET"
rawNonce  = hex2bin("00112233445566778899aabbccddeeff")
seed      = "2026-04-22T15:30:45+00:00"

→ nonce   = "ABEiM0RVZneImaq7zN3u/w=="
→ tranKey = "ktUB+Efbix9v8H/DxdxtDQu9u33E4BLGr6so+LrvAjs="
```

Any change in that output is a **cross-SDK contract break** and must be
discussed + mirrored in `checkout-java` and `checkout-dotnet` before merging.

## Pull requests

1. Branch off `main`.
2. Open a PR with a short description of **why** the change is needed.
3. `composer test:unit` and `composer stan` must pass locally.
4. Add tests for new behavior. `ModelRoundtripTest.php` is the right place
   for new public readonly classes.
5. Update `CHANGELOG.md` under an `Unreleased` section (Keep a Changelog
   format).
6. Keep the auth test vector green.

## Reporting issues

GitHub issue tracker for bugs and feature requests. For **security issues**,
do NOT open a public issue — email security@placetopay.com per
[SECURITY.md](../SECURITY.md).

## License

By contributing you agree that your changes are licensed under the MIT
license (see [LICENSE](LICENSE)).
