# Changelog

All notable changes to the PlaceToPay Checkout PHP SDK are documented here. The
format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this
project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- Clarified and validated support for heterogeneous
  `payment[].processorFields[].value` payloads (string, number, boolean, object)
  in `querySession` / `collect` transaction responses.

## [1.0.0] - 2026-05-03

Ground-up rewrite of the legacy `dnetix/redirection` SDK. **Not
backwards-compatible** — the namespace changes from `Dnetix\Redirection` to
`PlaceToPay\Checkout`, the auth algorithm moves from SHA-1 to SHA-256, and
the API surface is redesigned with PHP 8.2 readonly classes and backed enums.

### Added
- All six PlaceToPay Checkout endpoints:
  `POST /api/session`, `POST /api/session/{id}`, `POST /api/session/{id}/cancel`,
  `POST /api/collect`, `POST /api/reverse`, `POST /api/instrument/invalidate`.
- SHA-256 signing with cryptographic 16-byte nonces sourced from
  `random_bytes(16)` (PHP's CSPRNG).
- `Environment` backed enum (sandbox / production) plus `Country` backed enum
  (Colombia / Ecuador) with first-class locale + currency + baseUrl overrides.
- Typed exception hierarchy (`AuthenticationException`, `ValidationException`,
  `NotFoundException`, `RateLimitException`, `NetworkException`,
  `ServerException`) rooted at `PlaceToPayException`.
- Retry policy with exponential backoff + jitter, honoring the `Retry-After`
  response header on HTTP 429.
- PSR-3 debug logging with automatic PCI/PII field redaction
  (PAN last-4, CVV, tranKey, nonce, tokens, PIN, password, secretKey).
- Webhook signature verification in `NotificationVerifier` with both the
  modern SHA-256 path and a deprecated SHA-1 legacy fallback.
- `DocumentValidator` for client-side LatAm document type validation
  (CC, NIT, CPF, CI, RUT, RUC, and more).
- Reproducible cross-SDK auth test vector (see `ARCHITECTURE.md` §3).

### Removed
- SHA-1 signing for outgoing requests (only retained in
  `NotificationVerifier::verifySha1Legacy()` for inbound webhook compatibility).
- Array-based entity pattern of the legacy SDK — all request/response objects
  use readonly classes.

### Security
- Cryptographically-secure nonce generation via `random_bytes(16)`
  (legacy used `md5(rand())` — not cryptographic).
- HTTPS-only; plain `http://` base URLs are rejected at configuration time
  (except localhost / 127.0.0.1 in test environments).
- Fields named `tranKey`, `nonce`, `cvv`, `pin`, `password`, `secretKey`,
  `token`, `number` are automatically redacted in PSR-3 DEBUG-level log output.

[1.0.0]: https://github.com/placetopay/checkout-php/releases/tag/v1.0.0
