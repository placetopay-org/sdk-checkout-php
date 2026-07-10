<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Auth;

final class AuthSigner
{
    private function __construct()
    {
    }

    /**
     * Generate 16 cryptographically-secure random bytes.
     */
    public static function generateNonce(): string
    {
        return random_bytes(16);
    }

    /**
     * Produce the current ISO-8601 seed (UTC, numeric offset, no Z).
     * Format: 2026-04-22T15:30:45+00:00
     */
    public static function isoSeedNow(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:sP');
    }

    /**
     * Build an Auth object from the given inputs. Pure function; given the same arguments
     * it always produces the same output. Used for both live signing and deterministic test vectors.
     *
     * @param string $login      merchant login
     * @param string $rawNonce   16 raw bytes (NOT base64)
     * @param string $seed       pre-formatted ISO-8601 seed string
     * @param string $secretKey  merchant secret — never transmitted
     * @param array<string, string>|null $additional optional auth metadata
     */
    public static function sign(
        string $login,
        string $rawNonce,
        string $seed,
        string $secretKey,
        ?array $additional = null,
    ): Auth {
        $digestIn = $rawNonce . $seed . $secretKey;
        $tranKey = base64_encode(hash('sha256', $digestIn, true));
        $wireNonce = base64_encode($rawNonce);

        return new Auth(
            login: $login,
            tranKey: $tranKey,
            nonce: $wireNonce,
            seed: $seed,
            additional: $additional,
        );
    }

    /**
     * Convenience: generate a fresh nonce + seed and sign. Prefer this in production.
     * Use sign() in tests for determinism.
     *
     * @param array<string, string>|null $additional
     */
    public static function signNow(
        string $login,
        string $secretKey,
        ?array $additional = null,
    ): Auth {
        return self::sign(
            login: $login,
            rawNonce: self::generateNonce(),
            seed: self::isoSeedNow(),
            secretKey: $secretKey,
            additional: $additional,
        );
    }
}
