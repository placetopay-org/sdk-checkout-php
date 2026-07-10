<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Checkout;
use PlaceToPay\Checkout\CheckoutConfig;
use PlaceToPay\Checkout\Country;
use PlaceToPay\Checkout\Environment;
use PlaceToPay\Checkout\Models\Collect\CollectRequest;
use PlaceToPay\Checkout\Models\Common\Amount;
use PlaceToPay\Checkout\Models\Instrument\Instrument;
use PlaceToPay\Checkout\Models\Instrument\InvalidateTokenRequest;
use PlaceToPay\Checkout\Models\Instrument\Token;
use PlaceToPay\Checkout\Models\Session\CreateSessionRequest;
use PlaceToPay\Checkout\Models\Session\Payment;

class CheckoutClientTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $container = [];

    private function makeCheckout(MockHandler $mock, array $configOptions = []): Checkout
    {
        $this->container = [];
        $history = Middleware::history($this->container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new Client(['handler' => $stack]);

        $builder = CheckoutConfig::builder()
            ->login('testLogin')
            ->secretKey('testSecret')
            ->environment(Environment::SANDBOX)
            ->httpClient($client)
            ->retryMaxAttempts(1); // disable retries in tests

        foreach ($configOptions as $method => $value) {
            $builder->$method($value);
        }

        $config = $builder->build();
        return Checkout::of($config);
    }

    private function sessionResponse(string $status = 'OK'): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'status' => ['status' => $status, 'reason' => '00', 'message' => 'OK', 'date' => '2026-01-01T00:00:00+00:00'],
            'requestId' => 12345,
            'processUrl' => 'https://checkout-test.placetopay.com/session/12345',
        ]));
    }

    private function sessionInfoResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'requestId' => 12345,
            'status' => ['status' => 'PENDING', 'reason' => 'PT', 'message' => 'Pending', 'date' => '2026-01-01T00:00:00+00:00'],
            'payment' => [],
        ]));
    }

    private function cancelResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'status' => ['status' => 'OK', 'reason' => '00', 'message' => 'Cancelled', 'date' => '2026-01-01T00:00:00+00:00'],
        ]));
    }

    private function reverseResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'status' => ['status' => 'APPROVED', 'reason' => '00', 'message' => 'Reversed', 'date' => '2026-01-01T00:00:00+00:00'],
        ]));
    }

    private function statusResponse(): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'status' => [
                'status' => 'OK',
                'reason' => '00',
                'message' => 'Invalidated',
                'date' => '2026-01-01T00:00:00+00:00',
            ],
        ]));
    }

    public function testCreateSessionInjectsAuth(): void
    {
        $mock = new MockHandler([$this->sessionResponse()]);
        $checkout = $this->makeCheckout($mock);

        $request = CreateSessionRequest::builder()
            ->returnUrl('https://example.com/return')
            ->payment(Payment::of('REF001', 'Test payment', new Amount('COP', 50000)))
            ->ipAddress('127.0.0.1')
            ->userAgent('TestAgent/1.0')
            ->build();

        $response = $checkout->createSession($request);

        $this->assertSame(12345, $response->requestId);
        $this->assertCount(1, $this->container);

        // Verify auth was injected
        $body = json_decode((string) $this->container[0]['request']->getBody(), true);
        $this->assertArrayHasKey('auth', $body);
        $this->assertArrayHasKey('login', $body['auth']);
        $this->assertArrayHasKey('tranKey', $body['auth']);
        $this->assertSame('testLogin', $body['auth']['login']);
    }

    public function testCreateSessionAppliesCountryLocale(): void
    {
        $mock = new MockHandler([$this->sessionResponse()]);
        $checkout = $this->makeCheckout($mock, ['country' => Country::COLOMBIA]);

        $request = CreateSessionRequest::builder()
            ->returnUrl('https://example.com/return')
            ->payment(Payment::of('REF001', 'Test', new Amount('COP', 1000)))
            ->build();

        $checkout->createSession($request);

        $this->assertCount(1, $this->container);
        $body = json_decode((string) $this->container[0]['request']->getBody(), true);
        $this->assertSame('es_CO', $body['locale']);
    }

    public function testCreateSessionRespectsExistingLocale(): void
    {
        $mock = new MockHandler([$this->sessionResponse()]);
        $checkout = $this->makeCheckout($mock, ['country' => Country::COLOMBIA]);

        $request = CreateSessionRequest::builder()
            ->locale('en_US')
            ->returnUrl('https://example.com/return')
            ->payment(Payment::of('REF001', 'Test', new Amount('COP', 1000)))
            ->build();

        $checkout->createSession($request);

        $this->assertCount(1, $this->container);
        $body = json_decode((string) $this->container[0]['request']->getBody(), true);
        $this->assertSame('en_US', $body['locale']);
    }

    public function testQuerySessionPostsToCorrectEndpoint(): void
    {
        $mock = new MockHandler([$this->sessionInfoResponse()]);
        $checkout = $this->makeCheckout($mock);

        $result = $checkout->querySession(12345);

        $this->assertSame(12345, $result->requestId);
        $this->assertCount(1, $this->container);

        $uri = (string) $this->container[0]['request']->getUri();
        $this->assertStringContainsString('/api/session/12345', $uri);
    }

    public function testQuerySessionBodyContainsOnlyAuth(): void
    {
        $mock = new MockHandler([$this->sessionInfoResponse()]);
        $checkout = $this->makeCheckout($mock);

        $checkout->querySession(99);

        $this->assertCount(1, $this->container);
        $body = json_decode((string) $this->container[0]['request']->getBody(), true);
        $this->assertArrayHasKey('auth', $body);
        // Only auth should be in body (no other fields)
        $this->assertCount(1, $body);
    }

    public function testCancelSessionPostsToCorrectEndpoint(): void
    {
        $mock = new MockHandler([$this->cancelResponse()]);
        $checkout = $this->makeCheckout($mock);

        $result = $checkout->cancelSession(77);

        $this->assertCount(1, $this->container);
        $uri = (string) $this->container[0]['request']->getUri();
        $this->assertStringContainsString('/api/session/77/cancel', $uri);
        $this->assertNotNull($result->status);
    }

    public function testCollectPostsToCorrectEndpoint(): void
    {
        $mock = new MockHandler([$this->sessionInfoResponse()]);
        $checkout = $this->makeCheckout($mock);

        $collectRequest = CollectRequest::builder()
            ->payment(Payment::of('REF001', 'Collect test', new Amount('USD', 100)))
            ->instrument(Instrument::ofToken(new Token(token: 'tok_abc123')))
            ->provider('STRIPE')
            ->build();

        $checkout->collect($collectRequest);

        $this->assertCount(1, $this->container);
        $uri = (string) $this->container[0]['request']->getUri();
        $this->assertStringContainsString('/api/collect', $uri);

        $body = json_decode((string) $this->container[0]['request']->getBody(), true);
        $this->assertSame('STRIPE', $body['provider']);
    }

    public function testReversePostsToCorrectEndpoint(): void
    {
        $mock = new MockHandler([$this->reverseResponse()]);
        $checkout = $this->makeCheckout($mock);

        $checkout->reverse('INT_REF_001');

        $this->assertCount(1, $this->container);
        $uri = (string) $this->container[0]['request']->getUri();
        $this->assertStringContainsString('/api/reverse', $uri);

        $body = json_decode((string) $this->container[0]['request']->getBody(), true);
        $this->assertSame('INT_REF_001', $body['internalReference']);
    }

    public function testInvalidateTokenPostsToCorrectEndpoint(): void
    {
        $mock = new MockHandler([$this->statusResponse()]);
        $checkout = $this->makeCheckout($mock);

        $request = InvalidateTokenRequest::ofToken('tok_to_invalidate');
        $checkout->invalidateToken($request);

        $this->assertCount(1, $this->container);
        $uri = (string) $this->container[0]['request']->getUri();
        $this->assertStringContainsString('/api/instrument/invalidate', $uri);
    }

    public function testIdempotencyKeyHeaderSentWhenProvided(): void
    {
        $mock = new MockHandler([$this->sessionResponse()]);
        $checkout = $this->makeCheckout($mock);

        $request = CreateSessionRequest::builder()
            ->returnUrl('https://example.com/return')
            ->payment(Payment::of('REF001', 'Test', new Amount('COP', 1000)))
            ->build();

        $checkout->createSession($request, 'idempotency-key-123');

        $this->assertCount(1, $this->container);
        $headers = $this->container[0]['request']->getHeaders();
        $this->assertArrayHasKey('X-Idempotency-Key', $headers);
        $this->assertSame('idempotency-key-123', $headers['X-Idempotency-Key'][0]);
    }

    public function testIdempotencyKeyNotSentWhenNull(): void
    {
        $mock = new MockHandler([$this->sessionResponse()]);
        $checkout = $this->makeCheckout($mock);

        $request = CreateSessionRequest::builder()
            ->returnUrl('https://example.com/return')
            ->payment(Payment::of('REF001', 'Test', new Amount('COP', 1000)))
            ->build();

        $checkout->createSession($request);

        $this->assertCount(1, $this->container);
        $headers = $this->container[0]['request']->getHeaders();
        $this->assertArrayNotHasKey('X-Idempotency-Key', $headers);
    }
}
