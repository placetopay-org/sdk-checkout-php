<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Entities\AmountBase;
use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Enums\TransactionAction;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Message\TransactionRequest;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;
use PlacetoPay\PlacetoPaySdk\Tests\Mocks\RestCarrierMock;

class ServiceTransactionTest extends BaseTestCase
{
    public function testItCapturesAPreauthorization()
    {
        $response = $this->getService()->checkout(640, ['currency' => 'USD', 'total' => 500]);

        $this->assertSame([
            'internalReference' => 640,
            'action' => 'checkout',
            'amount' => ['currency' => 'USD', 'total' => 500],
        ], $this->sentTransaction());
        $this->assertTrue($response->isApproved());
        $this->assertSame(641, $response->payment()->internalReference());
        $this->assertSame('550044', $response->payment()->authorization());
    }

    public function testItCapturesAPreauthorizationWithoutAmount()
    {
        $this->getService()->checkout(640);

        $this->assertSame(['internalReference' => 640, 'action' => 'checkout'], $this->sentTransaction());
    }

    public function testItReleasesAPreauthorizationWithAZeroAmount()
    {
        $this->getService()->checkout(640, new AmountBase(['currency' => 'USD', 'total' => 0]));

        $this->assertEquals(['currency' => 'USD', 'total' => 0], $this->sentTransaction()['amount']);
    }

    public function testItReauthorizesAPreauthorization()
    {
        $response = $this->getService()->reauthorize(640, ['currency' => 'USD', 'total' => 700]);

        $this->assertSame('reauthorization', $this->sentTransaction()['action']);
        $this->assertSame(['currency' => 'USD', 'total' => 700], $this->sentTransaction()['amount']);
        $this->assertTrue($response->isApproved());
        $this->assertEquals(700, $response->payment()->amount()->to()->total());
    }

    public function testItConfirmsATransaction()
    {
        $response = $this->getService()->confirm(84);

        $this->assertSame(['internalReference' => 84, 'action' => 'confirm'], $this->sentTransaction());
        $this->assertTrue($response->isApproved());
    }

    public function testItCancelsATransaction()
    {
        $response = $this->getService()->cancelTransaction(84);

        $this->assertSame(['internalReference' => 84, 'action' => 'cancel'], $this->sentTransaction());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRejected());
        $this->assertSame('?C', $response->payment()->status()->reason());
    }

    public function testItAcceptsATransactionRequest()
    {
        $response = $this->getService()->transaction(new TransactionRequest([
            'internalReference' => 640,
            'action' => TransactionAction::CHECKOUT,
        ]));

        $this->assertTrue($response->isApproved());
    }

    public function testItAcceptsTheActionAsString()
    {
        $this->getService()->transaction(['internalReference' => 84, 'action' => 'confirm']);

        $this->assertSame('confirm', $this->sentTransaction()['action']);
    }

    public function testItHandlesAnErrorOnTransactionCall()
    {
        $response = $this->getService()->confirm(404);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(StatusType::FAILED, $response->status()->type());
        $this->assertSame('No existe la transacción que busca', $response->status()->message());
        $this->assertNull($response->payment());
    }

    public function testItRejectsAnUnknownAction()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('Unknown transaction action refund');

        $this->getService()->transaction(['internalReference' => 84, 'action' => 'refund']);
    }

    public function testItRequiresTheInternalReferenceAndTheAction()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('The internalReference and the action are required');

        $this->getService()->transaction(['action' => 'confirm']);
    }

    public function testItThrowsExceptionWhenBadArgumentProvided()
    {
        $this->expectException(PlacetoPayException::class);

        $this->getService()->transaction((object)['internalReference' => 84, 'action' => 'confirm']);
    }

    private function sentTransaction(): array
    {
        $parameters = RestCarrierMock::instance()->parameters();
        unset($parameters['auth']);

        return $parameters;
    }
}
