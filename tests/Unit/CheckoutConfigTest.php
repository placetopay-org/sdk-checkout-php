<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\CheckoutConfig;
use PlaceToPay\Checkout\Country;
use PlaceToPay\Checkout\Environment;

class CheckoutConfigTest extends TestCase
{
    public function testMissingLoginThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('login is required');

        CheckoutConfig::builder()
            ->secretKey('secret')
            ->build();
    }

    public function testMissingSecretKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('secretKey is required');

        CheckoutConfig::builder()
            ->login('myLogin')
            ->build();
    }

    public function testEmptyLoginThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CheckoutConfig::builder()
            ->login('')
            ->secretKey('secret')
            ->build();
    }

    public function testDefaultsToSandbox(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->build();

        $this->assertSame(Environment::SANDBOX, $config->environment);
        $this->assertStringContainsString('checkout-test', $config->baseUrl);
    }

    public function testProductionEnvironment(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->environment(Environment::PRODUCTION)
            ->build();

        $this->assertSame('https://checkout.placetopay.com', $config->baseUrl);
    }

    public function testHttpNonLocalhostThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insecure HTTP URL');

        CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->baseUrl('http://remote.example.com')
            ->build();
    }

    public function testHttpLocalhostAllowed(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->baseUrl('http://localhost:8080')
            ->build();

        $this->assertStringStartsWith('http://localhost', $config->baseUrl);
    }

    public function testHttp127Allowed(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->baseUrl('http://127.0.0.1:8080')
            ->build();

        $this->assertStringStartsWith('http://127.0.0.1', $config->baseUrl);
    }

    public function testEcuadorSandboxUrlOverride(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->country(Country::ECUADOR)
            ->environment(Environment::SANDBOX)
            ->build();

        $this->assertSame('https://checkout-test.placetopay.ec', $config->baseUrl);
    }

    public function testEcuadorProductionUrlOverride(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->country(Country::ECUADOR)
            ->environment(Environment::PRODUCTION)
            ->build();

        $this->assertSame('https://checkout.placetopay.ec', $config->baseUrl);
    }

    public function testColombiaHasNoUrlOverride(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->country(Country::COLOMBIA)
            ->environment(Environment::PRODUCTION)
            ->build();

        $this->assertSame('https://checkout.placetopay.com', $config->baseUrl);
    }

    public function testExplicitBaseUrlTakesPrecedenceOverCountry(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->country(Country::ECUADOR)
            ->baseUrl('https://custom.checkout.example.com')
            ->build();

        $this->assertSame('https://custom.checkout.example.com', $config->baseUrl);
    }

    public function testTrailingSlashNormalized(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->baseUrl('https://custom.checkout.example.com/')
            ->build();

        $this->assertStringEndsNotWith('/', $config->baseUrl);
    }

    public function testDefaultTimeouts(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->build();

        $this->assertSame(10, $config->connectTimeout);
        $this->assertSame(30, $config->readTimeout);
    }

    public function testDefaultRetrySettings(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->build();

        $this->assertSame(3, $config->retryMaxAttempts);
        $this->assertSame(200, $config->retryInitialDelayMs);
        $this->assertSame(5000, $config->retryMaxDelayMs);
    }

    public function testRetryMaxAttemptsZeroThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('retryMaxAttempts must be >= 1');

        CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->retryMaxAttempts(0)
            ->build();
    }

    public function testDefaultCurrencyAndLocaleNullWithoutCountry(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->build();

        $this->assertNull($config->defaultCurrency());
        $this->assertNull($config->defaultLocale());
    }

    public function testDefaultCurrencyAndLocaleFromCountryColombia(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->country(Country::COLOMBIA)
            ->build();

        $this->assertSame('COP', $config->defaultCurrency());
        $this->assertSame('es_CO', $config->defaultLocale());
    }

    public function testDefaultCurrencyAndLocaleFromCountryEcuador(): void
    {
        $config = CheckoutConfig::builder()
            ->login('login')
            ->secretKey('secret')
            ->country(Country::ECUADOR)
            ->build();

        $this->assertSame('USD', $config->defaultCurrency());
        $this->assertSame('es_EC', $config->defaultLocale());
    }
}
