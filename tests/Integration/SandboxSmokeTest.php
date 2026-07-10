<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Checkout;
use PlaceToPay\Checkout\Environment;
use PlaceToPay\Checkout\Models\Common\Amount;
use PlaceToPay\Checkout\Models\Session\CreateSessionRequest;
use PlaceToPay\Checkout\Models\Session\Payment;

class SandboxSmokeTest extends TestCase
{
    private static function credentialsAvailable(): bool
    {
        return getenv('PLACETOPAY_LOGIN') !== false
            && getenv('PLACETOPAY_SECRET_KEY') !== false;
    }

    protected function setUp(): void
    {
        if (!self::credentialsAvailable()) {
            $this->markTestSkipped(
                'Set PLACETOPAY_LOGIN, PLACETOPAY_SECRET_KEY (and optionally PLACETOPAY_BASE_URL) to run integration tests.'
            );
        }
    }

    public function testCreateRealSession(): void
    {
        $login = (string) getenv('PLACETOPAY_LOGIN');
        $secretKey = (string) getenv('PLACETOPAY_SECRET_KEY');
        $baseUrl = getenv('PLACETOPAY_BASE_URL') ?: null;

        $builder = Checkout::builder()
            ->login($login)
            ->secretKey($secretKey)
            ->environment(Environment::SANDBOX);

        if ($baseUrl !== null) {
            $builder->baseUrl($baseUrl);
        }

        $checkout = $builder->build();

        $request = CreateSessionRequest::builder()
            ->returnUrl('https://example.com/return')
            ->cancelUrl('https://example.com/cancel')
            ->ipAddress('127.0.0.1')
            ->userAgent('PlaceToPay PHP SDK Smoke Test')
            ->payment(
                Payment::of(
                    'SMOKE_TEST_' . time(),
                    'SDK Smoke Test',
                    new Amount('COP', 10000)
                )
            )
            ->build();

        $response = $checkout->createSession($request);

        $this->assertNotNull($response->requestId, 'requestId should not be null');
        $this->assertNotNull($response->processUrl, 'processUrl should not be null');
        $this->assertStringStartsWith('https://', $response->processUrl);
    }
}
