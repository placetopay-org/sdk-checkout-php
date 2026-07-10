<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Webhooks;

final class NotificationVerifier
{
    private function __construct()
    {
    }

    /**
     * Verify signature using SHA-256 hex (preferred).
     * Algorithm: hex(sha256(requestId . status.status . status.date . secretKey))
     */
    public static function verifySha256(NotificationPayload $payload, string $secretKey): bool
    {
        return self::verify($payload, $secretKey, 'sha256');
    }

    /**
     * Verify signature using SHA-1 hex (legacy; use only for backward compatibility).
     *
     * @deprecated Use verifySha256; SHA-1 will be removed in v2
     */
    #[\Deprecated('Use verifySha256; SHA-1 will be removed in v2')]
    public static function verifySha1Legacy(NotificationPayload $payload, string $secretKey): bool
    {
        return self::verify($payload, $secretKey, 'sha1');
    }

    private static function verify(NotificationPayload $payload, string $secretKey, string $algorithm): bool
    {
        if ($payload->signature === null || $payload->status === null) {
            return false;
        }

        $requestId = (string) ($payload->requestId ?? '');
        $statusCode = $payload->status->status->value;
        $date = $payload->status->date ?? '';

        $base = $requestId . $statusCode . $date . $secretKey;
        $expected = hash($algorithm, $base);

        // Constant-time comparison
        return hash_equals($expected, $payload->signature);
    }
}
