<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Http\RedactingLogger;
use Psr\Log\NullLogger;

class RedactingLoggerTest extends TestCase
{
    private RedactingLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new RedactingLogger(new NullLogger());
    }

    public function testMasksTranKeyAndNonce(): void
    {
        $json = json_encode([
            'auth' => [
                'login'    => 'visible',
                'tranKey'  => 'super-secret-digest',
                'nonce'    => 'super-secret-nonce',
                'seed'     => '2026-04-22T15:30:45+00:00',
            ],
        ]);

        $out = $this->logger->redact($json);
        $data = json_decode($out, true);

        $this->assertSame('visible', $data['auth']['login']);
        $this->assertSame('***', $data['auth']['tranKey']);
        $this->assertSame('***', $data['auth']['nonce']);
        $this->assertSame('2026-04-22T15:30:45+00:00', $data['auth']['seed']);
    }

    public function testMasksCardPanKeepingLast4(): void
    {
        $json = json_encode(['instrument' => ['card' => ['number' => '4111111111111111', 'cvv' => '123']]]);

        $out = $this->logger->redact($json);
        $data = json_decode($out, true);

        $this->assertSame('************1111', $data['instrument']['card']['number']);
        $this->assertSame('***', $data['instrument']['card']['cvv']);
    }

    public function testMasksTokenKeepingLast4(): void
    {
        $json = json_encode(['instrument' => ['token' => ['token' => 'tk-abcdefghij']]]);

        $out = $this->logger->redact($json);
        $data = json_decode($out, true);

        $token = $data['instrument']['token']['token'];
        $this->assertStringEndsWith('ghij', $token);
        $this->assertGreaterThanOrEqual(4, substr_count($token, '*'));
    }

    public function testMasksPinPasswordSecretKey(): void
    {
        $json = json_encode([
            'pin'       => '1234',
            'password'  => 'secret',
            'secretKey' => 'never-log',
        ]);

        $out = $this->logger->redact($json);
        $data = json_decode($out, true);

        $this->assertSame('***', $data['pin']);
        $this->assertSame('***', $data['password']);
        $this->assertSame('***', $data['secretKey']);
    }

    public function testRecursesIntoNestedArrays(): void
    {
        $json = json_encode(['payments' => [
            ['instrument' => ['card' => ['cvv' => '999']]],
            ['instrument' => ['card' => ['cvv' => '111']]],
        ]]);

        $out = $this->logger->redact($json);
        $data = json_decode($out, true);

        $this->assertSame('***', $data['payments'][0]['instrument']['card']['cvv']);
        $this->assertSame('***', $data['payments'][1]['instrument']['card']['cvv']);
    }

    public function testLeavesNonSensitiveFieldsUntouched(): void
    {
        $json = json_encode(['reference' => 'ORDER-1', 'amount' => ['currency' => 'COP', 'total' => 50000]]);

        $this->assertSame($json, $this->logger->redact($json));
    }

    public function testReturnsPlaceholderForNonJson(): void
    {
        $out = $this->logger->redact('not valid json {');

        $this->assertStringStartsWith('<non-json body,', $out);
    }

    public function testPassesThroughEmpty(): void
    {
        $this->assertSame('', $this->logger->redact(''));
        $this->assertSame('   ', $this->logger->redact('   '));
    }

    public function testShortPanIsMaskedFully(): void
    {
        $json = json_encode(['card' => ['number' => '1234']]);

        $out = $this->logger->redact($json);
        $data = json_decode($out, true);

        $this->assertSame('***', $data['card']['number']);
    }

    public function testDoesNotMutateInput(): void
    {
        $original = json_encode(['auth' => ['tranKey' => 'secret']]);

        $this->logger->redact($original);

        // Original string is unchanged (it's a string, so no mutation)
        $this->assertStringContainsString('secret', $original);
    }
}
