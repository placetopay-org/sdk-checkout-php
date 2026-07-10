<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Models\Common\AmountBase;
use PlaceToPay\Checkout\Models\Common\AmountConversion;
use PlaceToPay\Checkout\Models\Common\Status;
use PlaceToPay\Checkout\Models\Common\StatusCode;
use PlaceToPay\Checkout\Models\Session\SessionInformation;
use PlaceToPay\Checkout\Models\Transaction\Transaction;

class ImprovementsTest extends TestCase
{
    // ─── Status predicates ───────────────────────────────────────────────

    public function testStatusIsApproved(): void
    {
        $status = new Status(StatusCode::APPROVED);
        $this->assertTrue($status->isApproved());
        $this->assertFalse($status->isRejected());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isPending());
    }

    public function testStatusIsRejected(): void
    {
        $status = new Status(StatusCode::REJECTED);
        $this->assertTrue($status->isRejected());
        $this->assertFalse($status->isApproved());
    }

    public function testStatusIsSuccessful(): void
    {
        $this->assertTrue((new Status(StatusCode::OK))->isSuccessful());
        $this->assertTrue((new Status(StatusCode::APPROVED))->isSuccessful());
        $this->assertTrue((new Status(StatusCode::APPROVED_PARTIAL))->isSuccessful());
        $this->assertFalse((new Status(StatusCode::REJECTED))->isSuccessful());
        $this->assertFalse((new Status(StatusCode::PENDING))->isSuccessful());
        $this->assertFalse((new Status(StatusCode::ERROR))->isSuccessful());
    }

    public function testStatusIsPending(): void
    {
        $this->assertTrue((new Status(StatusCode::PENDING))->isPending());
        $this->assertTrue((new Status(StatusCode::PENDING_VALIDATION))->isPending());
        $this->assertFalse((new Status(StatusCode::APPROVED))->isPending());
    }

    public function testStatusIsError(): void
    {
        $this->assertTrue((new Status(StatusCode::ERROR))->isError());
        $this->assertFalse((new Status(StatusCode::APPROVED))->isError());
    }

    // ─── Transaction predicates ────────────────────────────────��─────────

    private function makeTransaction(StatusCode $code): Transaction
    {
        $amountConversion = new AmountConversion(
            from: new AmountBase('COP', 50000),
            to: new AmountBase('COP', 50000),
        );
        return new Transaction(
            status: new Status($code),
            reference: 'REF001',
            authorization: 'AUTH' . $code->value,
            amount: $amountConversion,
        );
    }

    public function testTransactionIsApproved(): void
    {
        $t = $this->makeTransaction(StatusCode::APPROVED);
        $this->assertTrue($t->isApproved());
        $this->assertFalse($t->isRejected());
    }

    public function testTransactionIsRejected(): void
    {
        $t = $this->makeTransaction(StatusCode::REJECTED);
        $this->assertTrue($t->isRejected());
        $this->assertFalse($t->isApproved());
    }

    public function testTransactionIsSuccessful(): void
    {
        $this->assertTrue($this->makeTransaction(StatusCode::APPROVED)->isSuccessful());
        $this->assertTrue($this->makeTransaction(StatusCode::OK)->isSuccessful());
        $this->assertFalse($this->makeTransaction(StatusCode::REJECTED)->isSuccessful());
    }

    // ─── SessionInformation helpers ──────────────────────────────────────

    private function makeSession(array $statusCodes): SessionInformation
    {
        $transactions = [];
        foreach ($statusCodes as $code) {
            $transactions[] = $this->makeTransaction(StatusCode::fromString($code));
        }
        return new SessionInformation(
            requestId: 1,
            status: new Status(StatusCode::PENDING),
            payment: $transactions,
        );
    }

    public function testLastTransactionReturnsLastElement(): void
    {
        $session = $this->makeSession(['REJECTED', 'APPROVED']);
        $last = $session->lastTransaction();
        $this->assertNotNull($last);
        $this->assertTrue($last->isApproved());
    }

    public function testLastTransactionApprovedOnly(): void
    {
        $session = $this->makeSession(['APPROVED', 'REJECTED']);
        $approved = $session->lastTransaction(true);
        $this->assertNotNull($approved);
        $this->assertTrue($approved->isApproved());
    }

    public function testLastTransactionApprovedOnlyReturnsNullWhenNone(): void
    {
        $session = $this->makeSession(['REJECTED', 'FAILED']);
        $this->assertNull($session->lastTransaction(true));
    }

    public function testLastApprovedTransaction(): void
    {
        $session = $this->makeSession(['PENDING', 'APPROVED', 'REJECTED']);
        $approved = $session->lastApprovedTransaction();
        $this->assertNotNull($approved);
        $this->assertTrue($approved->isApproved());
    }

    public function testLastAuthorizationReturnsCode(): void
    {
        $session = $this->makeSession(['APPROVED']);
        $this->assertSame('AUTHAPPROVED', $session->lastAuthorization());
    }

    public function testLastAuthorizationReturnsEmptyStringWhenNoApproved(): void
    {
        $session = $this->makeSession(['REJECTED']);
        $this->assertSame('', $session->lastAuthorization());
    }

    public function testLastTransactionReturnsNullWhenNoPayments(): void
    {
        $session = new SessionInformation(requestId: 1, status: new Status(StatusCode::PENDING));
        $this->assertNull($session->lastTransaction());
        $this->assertSame('', $session->lastAuthorization());
    }
}
