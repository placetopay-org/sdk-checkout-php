<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Message\RedirectInformation;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;

class ServiceCancelSessionTest extends BaseTestCase
{
    public function testItCancelsASession()
    {
        $response = $this->getService()->cancelSession(10012);

        $this->assertTrue($response->isSuccessful());
        $this->assertSame(StatusType::OK, $response->status()->type());
        $this->assertInstanceOf(RedirectInformation::class, $response->session());
        $this->assertSame(10012, $response->session()->requestId());
        $this->assertSame(StatusType::REJECTED, $response->session()->status()->type());
        $this->assertSame('MC', $response->session()->status()->reason());
        $this->assertSame('Ord1001', $response->session()->request()->payment()->reference());
        $this->assertSame(10012, $response->toArray()['session']['requestId']);
    }

    public function testItHandlesAnErrorOnCancelSessionCall()
    {
        $response = $this->getService()->cancelSession(99999);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(StatusType::FAILED, $response->status()->type());
        $this->assertNull($response->session());
    }
}
