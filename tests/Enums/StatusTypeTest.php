<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Enums;

use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;

class StatusTypeTest extends BaseTestCase
{
    public function testItResolvesUnknownStatusesFromTheService()
    {
        $this->assertSame(StatusType::APPROVED, StatusType::fromService('APPROVED'));
        $this->assertSame(StatusType::UNKNOWN, StatusType::fromService('SOMETHING_NEW'));
        $this->assertSame(StatusType::PENDING_CONFIRMATION, StatusType::fromService('PENDING_CONFIRMATION'));
        $this->assertSame(StatusType::PARTIAL_EXPIRED, StatusType::fromService('PARTIAL_EXPIRED'));
    }

    public function testTheStatusEntityExposesItsType()
    {
        $status = Status::quick(StatusType::REJECTED, '05');

        $this->assertSame(StatusType::REJECTED, $status->type());
        $this->assertSame('REJECTED', $status->status());
        $this->assertTrue($status->isRejected());
        $this->assertSame(StatusType::ERROR, (new Status([]))->type());
    }
}
