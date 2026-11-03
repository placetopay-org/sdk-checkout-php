<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Entities\AmountBase;
use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Message\ReverseResponse;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;
use PlacetoPay\PlacetoPaySdk\Tests\Mocks\RestCarrierMock;

class ServiceReverseTest extends BaseTestCase
{
    public function testItReversesATransaction()
    {
        $data = [
            'status' => [
                'status' => 'APPROVED',
                'reason' => '00',
                'message' => 'Aprobada',
                'date' => '2021-09-14T21:20:06-05:00',
            ],
            'payment' => [
                'status' => [
                    'status' => 'APPROVED',
                    'reason' => '00',
                    'message' => 'Aprobada',
                    'date' => '2021-09-14T21:20:06-05:00',
                ],
                'internalReference' => 1519102359,
                'paymentMethod' => 'master',
                'paymentMethodName' => 'Master',
                'issuerName' => 'Banco del Pacifico, S.A.',
                'amount' => [
                    'from' => [
                        'currency' => 'COP',
                        'total' => '2009000.00',
                    ],
                    'to' => [
                        'currency' => 'COP',
                        'total' => '2009000.00',
                    ],
                    'factor' => 1,
                ],
                'authorization' => '000000',
                'reference' => '800166551',
                'receipt' => 72406,
                'franchise' => 'RM_MC',
                'refunded' => false,
            ],
        ];
        $response = $this->getService()->reverse(1519097768);
        $this->assertEquals($data, $response->toArray());
    }

    public function testItHandlesAnErrorOnReverseCall()
    {
        $response = $this->getService()->reverse(0);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame(StatusType::FAILED, $response->status()->type());
        $this->assertEmpty($response->payment());
    }

    public function testItReversesTheWholeTransactionWithoutAnAmount()
    {
        $this->getService()->reverse(1519097768);

        $this->assertArrayNotHasKey('amount', RestCarrierMock::instance()->parameters());
    }

    public function testItReversesPartOfTheTransaction()
    {
        $response = $this->getService()->reverse(1519097768, ['currency' => 'COP', 'total' => 3000]);

        $parameters = RestCarrierMock::instance()->parameters();
        $this->assertSame('reverse', $parameters['action']);
        $this->assertSame(1519097768, $parameters['internalReference']);
        $this->assertEquals(['currency' => 'COP', 'total' => 3000], $parameters['amount']);
        $this->assertInstanceOf(ReverseResponse::class, $response);
        $this->assertTrue($response->isApproved());
    }

    public function testItAcceptsTheAmountAsAnEntity()
    {
        $this->getService()->reverse(1519097768, new AmountBase(['currency' => 'USD', 'total' => 12.5]));

        $this->assertEquals(['currency' => 'USD', 'total' => 12.5], RestCarrierMock::instance()->parameters()['amount']);
    }
}
