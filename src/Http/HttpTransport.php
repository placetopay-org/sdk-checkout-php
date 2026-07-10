<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Http;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use PlaceToPay\Checkout\Auth\AuthSigner;
use PlaceToPay\Checkout\CheckoutConfig;
use PlaceToPay\Checkout\Exceptions\AuthenticationException;
use PlaceToPay\Checkout\Exceptions\NetworkException;
use PlaceToPay\Checkout\Exceptions\NotFoundException;
use PlaceToPay\Checkout\Exceptions\RateLimitException;
use PlaceToPay\Checkout\Exceptions\ServerException;
use PlaceToPay\Checkout\Exceptions\ValidationException;
use PlaceToPay\Checkout\Internal\JsonHelper;
use PlaceToPay\Checkout\Models\Common\Status;
use Psr\Http\Message\ResponseInterface;

final class HttpTransport
{
    private readonly ClientInterface $client;
    private readonly ?RedactingLogger $redactingLogger;
    private readonly string $userAgent;

    public function __construct(
        private readonly CheckoutConfig $config,
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? $config->httpClient ?? $this->buildDefaultClient();
        $this->redactingLogger = $config->logger !== null
            ? new RedactingLogger($config->logger)
            : null;
        $this->userAgent = $this->buildUserAgent();
    }

    /**
     * POST JSON with auth injected and full retry logic.
     *
     * @template T
     * @param array<string, mixed> $body
     * @param class-string<T> $responseClass
     * @return T
     */
    public function postJson(
        string $path,
        array $body,
        string $responseClass,
        ?string $idempotencyKey = null,
    ): mixed {
        $auth = AuthSigner::signNow(
            $this->config->login,
            $this->config->secretKey,
            $this->config->authAdditional,
        );

        $fullBody = array_merge(['auth' => $auth->toArray()], $body);

        return $this->executeWithRetry($path, $fullBody, $responseClass, $idempotencyKey);
    }

    /**
     * POST with only the auth block in the body (e.g., querySession, cancelSession).
     *
     * @template T
     * @param class-string<T> $responseClass
     * @return T
     */
    public function postAuthOnly(string $path, string $responseClass): mixed
    {
        $auth = AuthSigner::signNow(
            $this->config->login,
            $this->config->secretKey,
            $this->config->authAdditional,
        );

        return $this->executeWithRetry($path, ['auth' => $auth->toArray()], $responseClass, null);
    }

    /**
     * @template T
     * @param array<string, mixed> $body
     * @param class-string<T> $responseClass
     * @return T
     */
    private function executeWithRetry(
        string $path,
        array $body,
        string $responseClass,
        ?string $idempotencyKey,
    ): mixed {
        $maxAttempts = $this->config->retryMaxAttempts;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                return $this->doRequest($path, $body, $responseClass, $idempotencyKey);
            } catch (NetworkException $e) {
                $lastException = $e;
                if ($attempt >= $maxAttempts) {
                    break;
                }
                $delayMs = $this->calculateDelay($attempt);
                $this->redactingLogger?->logRetry($attempt, $delayMs, 'network error: ' . $e->getMessage());
                usleep($delayMs * 1000);
            } catch (ServerException $e) {
                $lastException = $e;
                if ($attempt >= $maxAttempts) {
                    break;
                }
                $delayMs = $this->calculateDelay($attempt);
                $this->redactingLogger?->logRetry($attempt, $delayMs, 'server error ' . $e->getHttpStatusCode());
                usleep($delayMs * 1000);
            } catch (RateLimitException $e) {
                $lastException = $e;
                if ($attempt >= $maxAttempts) {
                    break;
                }
                $delayMs = $e->getRetryAfterSeconds() !== null
                    ? $e->getRetryAfterSeconds() * 1000
                    : $this->calculateDelay($attempt);
                $this->redactingLogger?->logRetry($attempt, $delayMs, 'rate limited');
                usleep($delayMs * 1000);
            }
        }

        throw $lastException ?? new NetworkException('Unknown error after retries');
    }

    /**
     * @template T
     * @param array<string, mixed> $body
     * @param class-string<T> $responseClass
     * @return T
     */
    private function doRequest(
        string $path,
        array $body,
        string $responseClass,
        ?string $idempotencyKey,
    ): mixed {
        $url = $this->config->baseUrl . $path;
        $jsonBody = JsonHelper::encodeBody($body);

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => $this->userAgent,
        ], $this->config->additionalHeaders);

        if ($idempotencyKey !== null) {
            $headers['X-Idempotency-Key'] = $idempotencyKey;
        }

        $this->redactingLogger?->logRequest('POST', $url, $jsonBody);

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'body' => $jsonBody,
                'connect_timeout' => $this->config->connectTimeout,
                'timeout' => $this->config->readTimeout,
                'http_errors' => false,
            ]);
        } catch (ConnectException $e) {
            throw new NetworkException('Network error: ' . $e->getMessage(), previous: $e);
        } catch (GuzzleException $e) {
            throw new NetworkException('HTTP error: ' . $e->getMessage(), previous: $e);
        }

        $statusCode = $response->getStatusCode();
        $rawBody = (string) $response->getBody();

        $this->redactingLogger?->logResponse($statusCode, $rawBody);

        if ($statusCode >= 200 && $statusCode < 300) {
            return $this->deserialize($rawBody, $responseClass);
        }

        $this->throwForStatus($statusCode, $rawBody, $response);
    }

    private function throwForStatus(int $statusCode, string $rawBody, ResponseInterface $response): never
    {
        $status = null;
        try {
            $data = JsonHelper::decode($rawBody);
            if (isset($data['status'])) {
                $status = Status::fromArray($data['status']);
            }
        } catch (\JsonException) {
            // ignore
        }

        $message = $status?->message ?? $rawBody;

        switch ($statusCode) {
            case 400:
                throw new ValidationException($message, $status, rawBody: $rawBody);
            case 401:
                throw new AuthenticationException($message, $status, rawBody: $rawBody);
            case 404:
                throw new NotFoundException($message, $status, rawBody: $rawBody);
            case 429:
                $retryAfter = $this->parseRetryAfter($response->getHeaderLine('Retry-After'));
                throw new RateLimitException($message, $retryAfter, $status, rawBody: $rawBody);
            default:
                if ($statusCode >= 500) {
                    throw new ServerException($message, $statusCode, $status, rawBody: $rawBody);
                }
                throw new ServerException("Unexpected HTTP {$statusCode}", $statusCode, $status, rawBody: $rawBody);
        }
    }

    private function parseRetryAfter(string $value): ?int
    {
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        // HTTP-date format
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return max(0, $timestamp - time());
        }
        return null;
    }

    /**
     * @template T
     * @param class-string<T> $responseClass
     * @return T
     */
    private function deserialize(string $json, string $responseClass): mixed
    {
        $data = JsonHelper::decode($json);
        return $responseClass::fromArray($data);
    }

    private function calculateDelay(int $attempt): int
    {
        $base = min(
            $this->config->retryMaxDelayMs,
            $this->config->retryInitialDelayMs * (2 ** ($attempt - 1))
        );
        // Add jitter: 50%..100% of base
        $jitter = 0.5 + (random_int(0, PHP_INT_MAX) / PHP_INT_MAX) * 0.5;
        return (int) ($base * $jitter);
    }

    private function buildDefaultClient(): Client
    {
        return new Client([
            'connect_timeout' => $this->config->connectTimeout,
            'timeout' => $this->config->readTimeout,
        ]);
    }

    private function buildUserAgent(): string
    {
        try {
            $sdkVersion = InstalledVersions::getVersion('placetopay/checkout') ?? 'dev';
        } catch (\OutOfBoundsException) {
            $sdkVersion = 'dev';
        }

        $phpVersion = PHP_VERSION;

        try {
            $guzzleVersion = InstalledVersions::getVersion('guzzlehttp/guzzle') ?? 'unknown';
        } catch (\OutOfBoundsException) {
            $guzzleVersion = 'unknown';
        }

        $ua = "placetopay-php/{$sdkVersion} PHP/{$phpVersion} (guzzlehttp/{$guzzleVersion})";

        if ($this->config->userAgentSuffix !== null) {
            $ua .= ' ' . $this->config->userAgentSuffix;
        }

        return $ua;
    }
}
