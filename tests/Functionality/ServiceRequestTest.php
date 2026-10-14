<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Entities\PaymentModifier;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayServiceException;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;
use PlacetoPay\PlacetoPaySdk\Tests\Mocks\RestCarrierMock;

class ServiceRequestTest extends BaseTestCase
{
    public function testItHandlesTheHeaders()
    {
        $this->getService([
            'headers' => [
                'X-TEST-HEADER' => 'SOME_VALUE',
            ],
        ])->request($this->baseRequest());

        $this->assertArrayHasKey('X-TEST-HEADER', RestCarrierMock::instance()->headers());
    }

    public function testItFailsIfTheRequestIsNotAccepted()
    {
        $this->expectException(PlacetoPayException::class);
        $this->getService()->request((object)$this->baseRequest());
    }

    public function testItCreatesABasicSession()
    {
        $response = $this->getService()->request($this->baseRequest());

        $this->assertEquals(Status::ST_OK, $response->status()->status());
        $this->assertTrue($response->status()->isSuccessful());
        $this->assertEquals('120000', $response->toArray()['requestId']);
        $this->assertEquals('120000', $response->requestId());
        $this->assertNotEmpty($response->processUrl());
    }

    public function testItHandlesCorrectlyAnExceptionThrown()
    {
        $this->expectException(PlacetoPayServiceException::class);
        $this->getService()->request($this->baseRequest([
            'payment' => [
                'reference' => 'MAKE_EXCEPTION',
            ],
        ]));
    }

    public function testItHandlesASubscriptionRequest()
    {
        $response = $this->getService()->request($this->baseRequest([
            'payment' => null,
            'subscription' => [
                'reference' => 'SOME_REFERENCE',
                'description' => 'Testing subscription',
                'fields' => [
                    [
                        'keyword' => 'no_empty',
                        'value' => 'no_empty_value',
                        'displayOn' => 'none',
                    ],
                ],
            ],
        ]));
        $this->assertTrue($response->status()->isSuccessful());
    }

    public function testItHandlesARequestWithPaymentModifiers(): void
    {
        $response = $this->getService()->request($this->baseRequest([
            'payment' => [
                'modifiers' => [
                    [
                        'type' => PaymentModifier::TYPE_FEDERAL_GOVERNMENT,
                        'code' => 17934,
                        'additional' => [
                            'invoice' => '12545',
                        ],
                    ],
                ],
            ],
        ]));

        $this->assertTrue($response->status()->isSuccessful());
    }
}
