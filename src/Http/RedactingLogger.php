<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Http;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class RedactingLogger
{
    /** Fully masked — value replaced with *** */
    private const FULLY_MASKED = ['tranKey', 'nonce', 'cvv', 'pin', 'password', 'secretKey', 'code'];

    /** PAN — keep last 4 digits, mask the rest */
    private const PAN_FIELD = 'number';

    /** Token — keep last 4 chars, mask the rest */
    private const TOKEN_FIELD = 'token';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function logRequest(string $method, string $url, string $body): void
    {
        $this->logger->debug('PlaceToPay request', [
            'method' => $method,
            'url'    => $url,
            'body'   => $this->redact($body),
        ]);
    }

    public function logResponse(int $statusCode, string $body): void
    {
        $this->logger->debug('PlaceToPay response', [
            'statusCode' => $statusCode,
            'body'       => $this->redact($body),
        ]);
    }

    public function logRetry(int $attempt, int $delayMs, string $reason): void
    {
        $this->logger->log(LogLevel::WARNING, 'PlaceToPay retry', [
            'attempt' => $attempt,
            'delayMs' => $delayMs,
            'reason'  => $reason,
        ]);
    }

    public function redact(string $json): string
    {
        if ($json === '' || trim($json) === '') {
            return $json;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '<non-json body, ' . strlen($json) . ' bytes>';
        }

        if (!is_array($data)) {
            return $json;
        }

        $redacted = $this->redactArray($data);
        return json_encode($redacted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $json;
    }

    /**
     * @param  array<mixed> $data
     * @return array<mixed>
     */
    private function redactArray(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->redactArray($value);
            } elseif (is_string($value) && is_string($key)) {
                $result[$key] = $this->redactValue($key, $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private function redactValue(string $key, string $value): string
    {
        if ($key === self::PAN_FIELD) {
            return $this->maskPan($value);
        }
        if ($key === self::TOKEN_FIELD) {
            return $this->maskToken($value);
        }
        if (in_array($key, self::FULLY_MASKED, true)) {
            return '***';
        }
        return $value;
    }

    private function maskPan(string $number): string
    {
        $len = strlen($number);
        if ($len <= 4) {
            return '***';
        }
        return str_repeat('*', $len - 4) . substr($number, -4);
    }

    private function maskToken(string $token): string
    {
        $len = strlen($token);
        if ($len <= 4) {
            return $token;
        }
        return str_repeat('*', $len - 4) . substr($token, -4);
    }
}
