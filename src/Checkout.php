<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout;

use PlaceToPay\Checkout\Http\HttpTransport;
use PlaceToPay\Checkout\Models\Collect\CollectRequest;
use PlaceToPay\Checkout\Models\Common\Status;
use PlaceToPay\Checkout\Models\Instrument\InvalidateTokenRequest;
use PlaceToPay\Checkout\Models\Reverse\ReverseResponse;
use PlaceToPay\Checkout\Models\Session\CancelSessionResponse;
use PlaceToPay\Checkout\Models\Session\CreateSessionRequest;
use PlaceToPay\Checkout\Models\Session\CreateSessionResponse;
use PlaceToPay\Checkout\Models\Session\SessionInformation;

/**
 * Primary client for the PlaceToPay Checkout API.
 *
 * Example:
 *   $checkout = Checkout::builder()
 *       ->login('YOUR_LOGIN')
 *       ->secretKey('YOUR_SECRET')
 *       ->environment(Environment::SANDBOX)
 *       ->build();
 *
 *   $response = $checkout->createSession($request);
 */
final class Checkout
{
    private readonly HttpTransport $transport;

    private function __construct(
        private readonly CheckoutConfig $config,
    ) {
        $this->transport = new HttpTransport($config);
    }

    public static function builder(): CheckoutConfigBuilder
    {
        return CheckoutConfig::builder();
    }

    public static function of(CheckoutConfig $config): self
    {
        return new self($config);
    }

    public function config(): CheckoutConfig
    {
        return $this->config;
    }

    // ─── Session lifecycle ───────────────────────────────────────────────

    /**
     * POST /api/session — create a new checkout session.
     */
    public function createSession(
        CreateSessionRequest $request,
        ?string $idempotencyKey = null,
    ): CreateSessionResponse {
        // Apply country default locale if request has none
        if ($request->locale === null && $this->config->country !== null) {
            $request = $request->withLocale($this->config->country->defaultLocale());
        }

        return $this->transport->postJson(
            '/api/session',
            $request->toArray(),
            CreateSessionResponse::class,
            $idempotencyKey,
        );
    }

    /**
     * POST /api/session/{requestId} — fetch session state.
     */
    public function querySession(int $requestId): SessionInformation
    {
        return $this->transport->postAuthOnly(
            "/api/session/{$requestId}",
            SessionInformation::class,
        );
    }

    /**
     * POST /api/session/{requestId}/cancel — cancel a session.
     */
    public function cancelSession(int $requestId): CancelSessionResponse
    {
        return $this->transport->postAuthOnly(
            "/api/session/{$requestId}/cancel",
            CancelSessionResponse::class,
        );
    }

    /**
     * POST /api/collect — collect payment using stored instrument.
     */
    public function collect(
        CollectRequest $request,
        ?string $idempotencyKey = null,
    ): SessionInformation {
        return $this->transport->postJson(
            '/api/collect',
            $request->toArray(),
            SessionInformation::class,
            $idempotencyKey,
        );
    }

    /**
     * POST /api/reverse — reverse a transaction.
     */
    public function reverse(
        string $internalReference,
        ?string $idempotencyKey = null,
    ): ReverseResponse {
        return $this->transport->postJson(
            '/api/reverse',
            ['internalReference' => $internalReference],
            ReverseResponse::class,
            $idempotencyKey,
        );
    }

    /**
     * POST /api/instrument/invalidate — invalidate a stored token.
     * The API returns {"status": {...}}, so we unwrap the envelope.
     */
    public function invalidateToken(InvalidateTokenRequest $request): Status
    {
        $resp = $this->transport->postJson(
            '/api/instrument/invalidate',
            $request->toArray(),
            \PlaceToPay\Checkout\Internal\ErrorResponse::class,
            null,
        );
        return $resp->status ?? new Status(\PlaceToPay\Checkout\Models\Common\StatusCode::UNKNOWN);
    }
}
