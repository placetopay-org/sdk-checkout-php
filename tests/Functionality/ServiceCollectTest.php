<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Entities\Transaction;
use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;
use PlacetoPay\PlacetoPaySdk\Tests\Mocks\RestCarrierMock;

class ServiceCollectTest extends BaseTestCase
{
    private const PAYER = [
        'documentType' => 'CC',
        'document' => '1040035020',
        'name' => 'Cassie',
        'surname' => 'Walsh',
        'email' => 'lance04@lockman.com',
    ];

    public function testItHandlesAnApprovedCollect()
    {
        $response = $this->getService()->collect($this->baseRequest([
            'payer' => self::PAYER,
            'instrument' => [
                'token' => [
                    'token' => 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6',
                    'installments' => '3',
                ],
            ],
        ]));

        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->requestId());
        $this->assertTrue($response->status()->isApproved());
        $this->assertInstanceOf(Transaction::class, $response->lastApprovedTransaction());
    }

    public function testItHandlesABadCollectRequest()
    {
        $response = $this->getService()->collect($this->baseRequest([
            'payer' => self::PAYER,
            'payment' => [
                'reference' => 'PENDING',
            ],
            'instrument' => [
                'token' => [
                    'token' => 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6',
                    'installments' => '3',
                ],
            ],
        ]));

        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->requestId());
        $this->assertSame(StatusType::PENDING, $response->status()->type());
    }

    public function testItThrowsExceptionWhenBadArgumentProvided()
    {
        $this->expectException(PlacetoPayException::class);
        $this->getService()->collect((object)$this->baseRequest([
            'instrument' => [
                'token' => [
                    'token' => 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6',
                    'installments' => '3',
                ],
            ],
        ]));
    }

    public function testItRequiresThePayerBeforeCallingTheService()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('The payer is required to collect');

        $this->getService()->collect($this->baseRequest([
            'instrument' => [
                'token' => [
                    'token' => 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6',
                ],
            ],
        ]));
    }

    public function testItOnlySendsTheInstallmentsWhenRequested()
    {
        $token = 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6';

        $this->getService()->collect($this->baseRequest(['payer' => self::PAYER, 'instrument' => ['token' => ['token' => $token]]]));
        $this->assertSame(['token' => ['token' => $token]], RestCarrierMock::instance()->parameters()['instrument']);

        $this->getService()->collect($this->baseRequest(['payer' => self::PAYER, 'instrument' => ['token' => ['token' => $token, 'installments' => '3']]]));
        $this->assertSame(['token' => ['token' => $token, 'installments' => 3]], RestCarrierMock::instance()->parameters()['instrument']);
    }

    public function testItSendsTheCreditChosenFromTheInstrumentInformation()
    {
        $token = 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6';
        $option = $this->getService()->instrumentInformation(['instrument' => ['token' => ['token' => $token]]])->credits()[0];

        $this->getService()->collect($this->baseRequest([
            'payer' => self::PAYER,
            'instrument' => ['token' => ['token' => $token], 'credit' => $option->toCredit(6)],
        ]));

        $this->assertSame(
            ['code' => '0', 'type' => '00', 'groupCode' => 'C', 'installment' => 6],
            RestCarrierMock::instance()->parameters()['instrument']['credit'],
        );
    }

    public function testItSendsThePocketOfTheCard()
    {
        $token = 'cbdfb8e3304270a8ef107eeb19729f397a1fe1e045bf2e704e4f62c5242e91b6';

        $this->getService()->collect($this->baseRequest(['payer' => self::PAYER, 'instrument' => ['pocket' => 10, 'token' => ['token' => $token]]]));
        $this->assertSame('10', RestCarrierMock::instance()->parameters()['instrument']['pocket']);

        $this->getService()->collect($this->baseRequest(['payer' => self::PAYER, 'instrument' => ['token' => ['token' => $token]]]));
        $this->assertArrayNotHasKey('pocket', RestCarrierMock::instance()->parameters()['instrument']);
    }
}
