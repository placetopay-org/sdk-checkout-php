<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Models\Common\Status;
use PlaceToPay\Checkout\Models\Common\StatusCode;
use PlaceToPay\Checkout\Webhooks\NotificationPayload;
use PlaceToPay\Checkout\Webhooks\NotificationVerifier;

class NotificationVerifierTest extends TestCase
{
    private function makePayload(
        int $requestId,
        string $statusCode,
        string $date,
        string $secretKey,
        string $algorithm = 'sha256',
    ): NotificationPayload {
        $base = $requestId . $statusCode . $date . $secretKey;
        $signature = hash($algorithm, $base);

        return new NotificationPayload(
            requestId: $requestId,
            signature: $signature,
            status: new Status(
                status: StatusCode::fromString($statusCode),
                date: $date,
            ),
        );
    }

    public function testSha256ValidSignatureVerifies(): void
    {
        $payload = $this->makePayload(
            requestId: 12345,
            statusCode: 'APPROVED',
            date: '2026-04-22T15:30:45+00:00',
            secretKey: 'mySecret',
            algorithm: 'sha256',
        );

        $this->assertTrue(NotificationVerifier::verifySha256($payload, 'mySecret'));
    }

    public function testSha256WrongSecretFails(): void
    {
        $payload = $this->makePayload(
            requestId: 12345,
            statusCode: 'APPROVED',
            date: '2026-04-22T15:30:45+00:00',
            secretKey: 'mySecret',
        );

        $this->assertFalse(NotificationVerifier::verifySha256($payload, 'wrongSecret'));
    }

    public function testSha256TamperedStatusFails(): void
    {
        $payload = $this->makePayload(
            requestId: 12345,
            statusCode: 'APPROVED',
            date: '2026-04-22T15:30:45+00:00',
            secretKey: 'mySecret',
        );

        // Tamper with status
        $tamperedPayload = new NotificationPayload(
            requestId: $payload->requestId,
            signature: $payload->signature,
            status: new Status(StatusCode::REJECTED, date: $payload->status?->date),
        );

        $this->assertFalse(NotificationVerifier::verifySha256($tamperedPayload, 'mySecret'));
    }

    #[\PHPUnit\Framework\Attributes\IgnoreDeprecations]
    public function testSha1LegacyValidSignatureVerifies(): void
    {
        $payload = $this->makePayload(
            requestId: 99,
            statusCode: 'OK',
            date: '2026-01-01T00:00:00+00:00',
            secretKey: 'legacySecret',
            algorithm: 'sha1',
        );

        /** @phpstan-ignore-next-line */
        $this->assertTrue(NotificationVerifier::verifySha1Legacy($payload, 'legacySecret'));
    }

    public function testSha1SignatureRejectedBySha256Verifier(): void
    {
        // Build payload signed with SHA-1
        $payload = $this->makePayload(
            requestId: 99,
            statusCode: 'OK',
            date: '2026-01-01T00:00:00+00:00',
            secretKey: 'mySecret',
            algorithm: 'sha1',
        );

        // SHA-256 verifier should reject the SHA-1 signature
        $this->assertFalse(NotificationVerifier::verifySha256($payload, 'mySecret'));
    }

    public function testNullSignatureReturnsFalse(): void
    {
        $payload = new NotificationPayload(
            requestId: 123,
            status: new Status(StatusCode::APPROVED, date: '2026-01-01T00:00:00+00:00'),
        );

        $this->assertFalse(NotificationVerifier::verifySha256($payload, 'secret'));
    }

    public function testNullStatusReturnsFalse(): void
    {
        $payload = new NotificationPayload(
            requestId: 123,
            signature: 'somesig',
        );

        $this->assertFalse(NotificationVerifier::verifySha256($payload, 'secret'));
    }

    public function testFromArrayPayload(): void
    {
        $secretKey = 'testKey';
        $requestId = 555;
        $statusCode = 'APPROVED';
        $date = '2026-06-01T10:00:00+00:00';
        $sig = hash('sha256', $requestId . $statusCode . $date . $secretKey);

        $payload = NotificationPayload::fromArray([
            'requestId' => $requestId,
            'signature' => $sig,
            'status' => [
                'status' => $statusCode,
                'date' => $date,
            ],
        ]);

        $this->assertTrue(NotificationVerifier::verifySha256($payload, $secretKey));
    }
}
