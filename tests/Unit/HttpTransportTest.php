<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\CheckoutConfig;
use PlaceToPay\Checkout\Environment;
use PlaceToPay\Checkout\Exceptions\AuthenticationException;
use PlaceToPay\Checkout\Exceptions\NetworkException;
use PlaceToPay\Checkout\Exceptions\NotFoundException;
use PlaceToPay\Checkout\Exceptions\RateLimitException;
use PlaceToPay\Checkout\Exceptions\ServerException;
use PlaceToPay\Checkout\Exceptions\ValidationException;
use PlaceToPay\Checkout\Http\HttpTransport;
use PlaceToPay\Checkout\Models\Session\SessionInformation;

/**
 * Exercises the retry loop, auth injection and typed-error mapping
 * without requiring a live sandbox.
 */
class HttpTransportTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $container = [];

    private function makeTransport(MockHandler $mock, int $retryMaxAttempts = 3): HttpTransport
    {
        $this->container = [];
        $history = Middleware::history($this->container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new Client(['handler' => $stack]);

        $config = CheckoutConfig::builder()
            ->login('test-login')
            ->secretKey('test-secret')
            ->environment(Environment::SANDBOX)
            ->baseUrl('http://localhost')
            ->retryMaxAttempts($retryMaxAttempts)
            ->retryInitialDelayMs(1)
            ->retryMaxDelayMs(5)
            ->httpClient($client)
            ->build();

        return new HttpTransport($config);
    }

    private static function okBody(): string
    {
        return json_encode([
            'requestId' => 42,
            'status'    => ['status' => 'OK', 'reason' => '00', 'message' => 'ok', 'date' => '2026-04-22T15:30:45+00:00'],
        ]);
    }

    private static function errorBody(string $status = 'ERROR'): string
    {
        return json_encode(['status' => ['status' => $status, 'reason' => 'X1', 'message' => 'err']]);
    }

    // ─── Retry ──────────────────────────────────────────────────────────────

    public function testRetriesOn500ThenSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(500),
            new Response(200, ['Content-Type' => 'application/json'], self::okBody()),
        ]);
        $transport = $this->makeTransport($mock, retryMaxAttempts: 3);

        $info = $transport->postAuthOnly('/api/session/42', SessionInformation::class);

        $this->assertSame(42, $info->requestId);
        $this->assertCount(2, $this->container);
    }

    public function testFailsAfterMaxAttemptsOn500(): void
    {
        $mock = new MockHandler([
            new Response(500),
            new Response(500),
            new Response(500),
        ]);
        $transport = $this->makeTransport($mock, retryMaxAttempts: 3);

        $this->expectException(ServerException::class);
        $transport->postAuthOnly('/api/session/42', SessionInformation::class);
    }

    public function testExhaustsExactlyMaxAttempts(): void
    {
        $mock = new MockHandler([
            new Response(500),
            new Response(500),
            new Response(500),
        ]);
        $transport = $this->makeTransport($mock, retryMaxAttempts: 3);

        try {
            $transport->postAuthOnly('/api/session/42', SessionInformation::class);
        } catch (ServerException) {
        }

        $this->assertCount(3, $this->container);
    }

    public function testDoesNotRetryOn4xx(): void
    {
        $mock = new MockHandler([
            new Response(400, ['Content-Type' => 'application/json'], self::errorBody('FAILED')),
        ]);
        $transport = $this->makeTransport($mock, retryMaxAttempts: 3);

        $this->expectException(ValidationException::class);
        $transport->postAuthOnly('/api/session/42', SessionInformation::class);

        $this->assertCount(1, $this->container);
    }

    public function testRetriesOn429(): void
    {
        $mock = new MockHandler([
            new Response(429, ['Retry-After' => '0']),
            new Response(200, ['Content-Type' => 'application/json'], self::okBody()),
        ]);
        $transport = $this->makeTransport($mock, retryMaxAttempts: 3);

        $info = $transport->postAuthOnly('/api/session/42', SessionInformation::class);

        $this->assertSame(42, $info->requestId);
        $this->assertCount(2, $this->container);
    }

    // ─── Error mapping ───────────────────────────────────────────────────────

    public function testMaps400ToValidationException(): void
    {
        $mock = new MockHandler([new Response(400, ['Content-Type' => 'application/json'], self::errorBody())]);
        $this->expectException(ValidationException::class);
        $this->makeTransport($mock, 1)->postAuthOnly('/api/x', SessionInformation::class);
    }

    public function testMaps401ToAuthenticationException(): void
    {
        $mock = new MockHandler([new Response(401, ['Content-Type' => 'application/json'], self::errorBody())]);
        $this->expectException(AuthenticationException::class);
        $this->makeTransport($mock, 1)->postAuthOnly('/api/x', SessionInformation::class);
    }

    public function testMaps404ToNotFoundException(): void
    {
        $mock = new MockHandler([new Response(404, ['Content-Type' => 'application/json'], self::errorBody())]);
        $this->expectException(NotFoundException::class);
        $this->makeTransport($mock, 1)->postAuthOnly('/api/x', SessionInformation::class);
    }

    public function testMaps429ToRateLimitException(): void
    {
        $mock = new MockHandler([
            new Response(429, ['Retry-After' => '7']),
            new Response(429, ['Retry-After' => '7']),
            new Response(429, ['Retry-After' => '7']),
        ]);
        $this->expectException(RateLimitException::class);
        $this->makeTransport($mock, 3)->postAuthOnly('/api/x', SessionInformation::class);
    }

    public function testRateLimitExceptionExposesRetryAfter(): void
    {
        $mock = new MockHandler([
            new Response(429, ['Retry-After' => '7']),
            new Response(429, ['Retry-After' => '7']),
            new Response(429, ['Retry-After' => '7']),
        ]);

        try {
            $this->makeTransport($mock, 3)->postAuthOnly('/api/x', SessionInformation::class);
        } catch (RateLimitException $e) {
            $this->assertSame(7, $e->getRetryAfterSeconds());
            return;
        }
        $this->fail('Expected RateLimitException');
    }

    public function testMaps5xxToServerException(): void
    {
        $mock = new MockHandler([new Response(503)]);
        $this->expectException(ServerException::class);
        $this->makeTransport($mock, 1)->postAuthOnly('/api/x', SessionInformation::class);
    }

    public function testServerExceptionCarriesHttpStatusCode(): void
    {
        $mock = new MockHandler([new Response(502)]);

        try {
            $this->makeTransport($mock, 1)->postAuthOnly('/api/x', SessionInformation::class);
        } catch (ServerException $e) {
            $this->assertSame(502, $e->getHttpStatusCode());
            return;
        }
        $this->fail('Expected ServerException');
    }

    // ─── Auth injection ───────────────────────────────────────────────────────

    public function testInjectsAuthOnEveryRequest(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], self::okBody()),
            new Response(200, ['Content-Type' => 'application/json'], self::okBody()),
        ]);
        $transport = $this->makeTransport($mock);

        $transport->postAuthOnly('/api/session/42', SessionInformation::class);
        $transport->postAuthOnly('/api/session/42', SessionInformation::class);

        $body1 = json_decode((string) $this->container[0]['request']->getBody(), true);
        $body2 = json_decode((string) $this->container[1]['request']->getBody(), true);

        $this->assertArrayHasKey('auth', $body1);
        $this->assertSame('test-login', $body1['auth']['login']);
        $this->assertArrayHasKey('auth', $body2);

        // Fresh nonces on every request
        $this->assertNotSame($body1['auth']['nonce'], $body2['auth']['nonce']);
    }

    public function testUserAgentHeaderIsSet(): void
    {
        $mock = new MockHandler([new Response(200, ['Content-Type' => 'application/json'], self::okBody())]);
        $this->makeTransport($mock)->postAuthOnly('/api/session/42', SessionInformation::class);

        $ua = $this->container[0]['request']->getHeaderLine('User-Agent');
        $this->assertStringContainsString('placetopay-php/', $ua);
        $this->assertStringContainsString('PHP/', $ua);
    }

    public function testContentTypeAndAcceptHeadersSet(): void
    {
        $mock = new MockHandler([new Response(200, ['Content-Type' => 'application/json'], self::okBody())]);
        $this->makeTransport($mock)->postAuthOnly('/api/session/42', SessionInformation::class);

        $req = $this->container[0]['request'];
        $this->assertSame('application/json', $req->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $req->getHeaderLine('Accept'));
    }

    // ─── Network failures ─────────────────────────────────────────────────────

    public function testNetworkFailureWrappedAsNetworkException(): void
    {
        $mock = new MockHandler([
            new ConnectException('connection refused', new Request('POST', 'http://localhost')),
            new ConnectException('connection refused', new Request('POST', 'http://localhost')),
            new ConnectException('connection refused', new Request('POST', 'http://localhost')),
        ]);
        $this->expectException(NetworkException::class);
        $this->makeTransport($mock, 3)->postAuthOnly('/api/session/42', SessionInformation::class);
    }

    public function testNetworkErrorRetriesUpToMaxAttempts(): void
    {
        $mock = new MockHandler([
            new ConnectException('fail', new Request('POST', 'http://localhost')),
            new ConnectException('fail', new Request('POST', 'http://localhost')),
            new Response(200, ['Content-Type' => 'application/json'], self::okBody()),
        ]);
        $transport = $this->makeTransport($mock, 3);

        $info = $transport->postAuthOnly('/api/session/42', SessionInformation::class);
        $this->assertSame(42, $info->requestId);
        $this->assertCount(3, $this->container);
    }
}
