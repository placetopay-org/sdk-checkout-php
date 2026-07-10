<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Auth\Auth;
use PlaceToPay\Checkout\Auth\AuthSigner;

class AuthSignerTest extends TestCase
{
    /**
     * Deterministic test vector:
     * rawNonce  = 16 zero bytes (0x00 * 16)
     * seed      = "2026-04-22T15:30:45+00:00"
     * secretKey = "testSecret"
     * digestIn  = rawNonce || seed || secretKey
     * tranKey   = base64(sha256(digestIn))
     */
    public function testDeterministicVector(): void
    {
        $rawNonce = str_repeat("\x00", 16);
        $seed = '2026-04-22T15:30:45+00:00';
        $secretKey = 'testSecret';

        $digestIn = $rawNonce . $seed . $secretKey;
        $expectedTranKey = base64_encode(hash('sha256', $digestIn, true));

        $auth = AuthSigner::sign('testLogin', $rawNonce, $seed, $secretKey);

        $this->assertSame('testLogin', $auth->login);
        $this->assertSame($expectedTranKey, $auth->tranKey);
        $this->assertSame(base64_encode($rawNonce), $auth->nonce);
        $this->assertSame($seed, $auth->seed);
        $this->assertNull($auth->additional);
    }

    public function testSignNowReturnsAuthWithCorrectLogin(): void
    {
        $auth = AuthSigner::signNow('myLogin', 'mySecret');

        $this->assertInstanceOf(Auth::class, $auth);
        $this->assertSame('myLogin', $auth->login);
        $this->assertNotEmpty($auth->tranKey);
        $this->assertNotEmpty($auth->nonce);
        $this->assertNotEmpty($auth->seed);
    }

    public function testNonceIsBase64Of16Bytes(): void
    {
        $auth = AuthSigner::signNow('login', 'secret');

        $decoded = base64_decode($auth->nonce, strict: true);
        $this->assertNotFalse($decoded, 'nonce should be valid base64');
        $this->assertSame(16, strlen($decoded), 'decoded nonce should be 16 bytes');
    }

    public function testGenerateNonceReturns16Bytes(): void
    {
        $nonce = AuthSigner::generateNonce();
        $this->assertSame(16, strlen($nonce));
    }

    public function testIsoSeedNowFormat(): void
    {
        $seed = AuthSigner::isoSeedNow();
        // Format: 2026-04-22T15:30:45+00:00
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/',
            $seed,
            'seed should be ISO-8601 with +00:00 offset'
        );
    }

    public function testSignWithAdditional(): void
    {
        $rawNonce = str_repeat("\x01", 16);
        $seed = '2026-04-22T15:30:45+00:00';
        $additional = ['key' => 'value'];

        $auth = AuthSigner::sign('login', $rawNonce, $seed, 'secret', $additional);

        $this->assertSame($additional, $auth->additional);
    }

    public function testToArrayOmitsNullAdditional(): void
    {
        $auth = AuthSigner::sign('login', str_repeat("\x00", 16), '2026-01-01T00:00:00+00:00', 'secret');
        $arr = $auth->toArray();

        $this->assertArrayNotHasKey('additional', $arr);
        $this->assertArrayHasKey('login', $arr);
        $this->assertArrayHasKey('tranKey', $arr);
        $this->assertArrayHasKey('nonce', $arr);
        $this->assertArrayHasKey('seed', $arr);
    }

    public function testToArrayIncludesAdditionalWhenSet(): void
    {
        $additional = ['env' => 'test'];
        $auth = AuthSigner::signNow('login', 'secret', $additional);
        $arr = $auth->toArray();

        $this->assertArrayHasKey('additional', $arr);
        $this->assertSame($additional, $arr['additional']);
    }

    public function testDifferentNoncesProduceDifferentTranKeys(): void
    {
        $seed = '2026-04-22T15:30:45+00:00';
        $secretKey = 'sharedSecret';

        $auth1 = AuthSigner::sign('login', str_repeat("\x01", 16), $seed, $secretKey);
        $auth2 = AuthSigner::sign('login', str_repeat("\x02", 16), $seed, $secretKey);

        $this->assertNotSame($auth1->tranKey, $auth2->tranKey);
    }

    public function testCanonicalCrossSdkVector(): void
    {
        // From ARCHITECTURE.md — must match byte-exactly across Java/.NET/Python/PHP
        $login     = 'SDK_TEST_LOGIN';
        $secretKey = 'SDK_TEST_SECRET';
        $rawNonce  = hex2bin('00112233445566778899aabbccddeeff');
        $seed      = '2026-04-22T15:30:45+00:00';

        $auth = AuthSigner::sign($login, $rawNonce, $seed, $secretKey);

        // Wire values that all SDKs must produce identically
        $this->assertSame('ABEiM0RVZneImaq7zN3u/w==', $auth->nonce);
        $expectedTranKey = base64_encode(hash('sha256', $rawNonce . $seed . $secretKey, true));
        $this->assertSame($expectedTranKey, $auth->tranKey);
        $this->assertSame($seed, $auth->seed);
        $this->assertSame($login, $auth->login);
    }
}
