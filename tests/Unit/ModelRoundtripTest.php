<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Models\Common\Status;
use PlaceToPay\Checkout\Models\Common\StatusCode;
use PlaceToPay\Checkout\Models\Session\CancelSessionResponse;
use PlaceToPay\Checkout\Models\Session\CreateSessionResponse;
use PlaceToPay\Checkout\Models\Session\SessionInformation;
use PlaceToPay\Checkout\Models\Transaction\Recurring;

class ModelRoundtripTest extends TestCase
{
    public function testCreateSessionResponseRoundtrip(): void
    {
        $original = [
            'status' => [
                'status' => 'OK',
                'reason' => '00',
                'message' => 'Session created',
                'date' => '2026-04-22T15:30:45+00:00',
            ],
            'requestId' => 99999,
            'processUrl' => 'https://checkout-test.placetopay.com/session/99999',
        ];

        $response = CreateSessionResponse::fromArray($original);

        $this->assertSame(99999, $response->requestId);
        $this->assertSame('https://checkout-test.placetopay.com/session/99999', $response->processUrl);
        $this->assertSame(StatusCode::OK, $response->status->status);
        $this->assertSame('00', $response->status->reason);

        $arr = $response->toArray();
        $this->assertSame(99999, $arr['requestId']);
        $this->assertSame('OK', $arr['status']['status']);
    }

    public function testSessionInformationFromArray(): void
    {
        $data = [
            'requestId' => 12345,
            'status' => [
                'status' => 'PENDING',
                'reason' => 'PT',
                'message' => 'Pending',
                'date' => '2026-04-22T00:00:00+00:00',
            ],
            'payment' => [
                [
                    'status' => ['status' => 'REJECTED', 'reason' => 'D03', 'message' => 'Declined', 'date' => '2026-01-01T00:00:00+00:00'],
                    'reference' => 'REF001',
                    'authorization' => null,
                ],
            ],
        ];

        $info = SessionInformation::fromArray($data);

        $this->assertSame(12345, $info->requestId);
        $this->assertSame(StatusCode::PENDING, $info->status?->status);
        $this->assertCount(1, $info->payment);
        $this->assertSame('REF001', $info->payment[0]->reference);
        $this->assertTrue($info->payment[0]->isRejected());
    }

    public function testStatusFromArrayWithEachStatusCode(): void
    {
        $codes = [
            'OK' => StatusCode::OK,
            'FAILED' => StatusCode::FAILED,
            'APPROVED' => StatusCode::APPROVED,
            'APPROVED_PARTIAL' => StatusCode::APPROVED_PARTIAL,
            'REJECTED' => StatusCode::REJECTED,
            'PENDING' => StatusCode::PENDING,
            'PENDING_VALIDATION' => StatusCode::PENDING_VALIDATION,
            'REFUNDED' => StatusCode::REFUNDED,
            'ERROR' => StatusCode::ERROR,
            'UNKNOWN' => StatusCode::UNKNOWN,
        ];

        foreach ($codes as $value => $expectedCode) {
            $status = Status::fromArray(['status' => $value]);
            $this->assertSame(
                $expectedCode,
                $status->status,
                "Expected {$value} to map to {$expectedCode->value}"
            );
        }
    }

    public function testCancelSessionResponseRoundtrip(): void
    {
        $data = [
            'status' => ['status' => 'OK', 'reason' => '00', 'message' => 'Cancelled', 'date' => '2026-01-01T00:00:00+00:00'],
        ];

        $response = CancelSessionResponse::fromArray($data);
        $this->assertSame(StatusCode::OK, $response->status->status);
        $this->assertNull($response->session);

        $arr = $response->toArray();
        $this->assertArrayHasKey('status', $arr);
        $this->assertArrayNotHasKey('session', $arr);
    }

    public function testUnknownStatusCodeFallsBackToUnknown(): void
    {
        $status = Status::fromArray(['status' => 'NONEXISTENT_CODE']);
        $this->assertSame(StatusCode::UNKNOWN, $status->status);
    }

    public function testStatusToArrayOmitsNullFields(): void
    {
        $status = new Status(StatusCode::OK);
        $arr = $status->toArray();

        $this->assertSame('OK', $arr['status']);
        $this->assertArrayNotHasKey('reason', $arr);
        $this->assertArrayNotHasKey('message', $arr);
        $this->assertArrayNotHasKey('date', $arr);
    }

    public function testStatusFromArrayCoercesNumericReasonMessageAndDateToStrings(): void
    {
        $status = Status::fromArray([
            'status' => 'OK',
            'reason' => 0,
            'message' => 12345,
            'date' => 20260622,
        ]);

        $this->assertSame('0', $status->reason);
        $this->assertSame('12345', $status->message);
        $this->assertSame('20260622', $status->date);
    }

    public function testRecurringToArrayDefaultsPeriodicityWhenNotificationUrlIsPresent(): void
    {
        $recurring = new Recurring(
            interval: 1,
            maxPeriods: 1,
            notificationUrl: 'https://webhook.site/test',
        );

        $arr = $recurring->toArray();

        $this->assertSame('D', $arr['periodicity']);
        $this->assertSame(1, $arr['interval']);
        $this->assertSame(1, $arr['maxPeriods']);
        $this->assertSame('https://webhook.site/test', $arr['notificationUrl']);
    }
}
