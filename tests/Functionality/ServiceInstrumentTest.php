<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Entities\CreditOption;
use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Message\InstrumentInformation;
use PlacetoPay\PlacetoPaySdk\Message\InstrumentRequest;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;
use PlacetoPay\PlacetoPaySdk\Tests\Mocks\RestCarrierMock;

class ServiceInstrumentTest extends BaseTestCase
{
    private const TOKEN = 'faketoken12f233bd8f5d42138d681bf07ea8295429df07a4af287703e30c891';

    public function testItInvalidatesAToken()
    {
        $response = $this->getService()->invalidateToken([
            'locale' => 'en_US',
            'instrument' => ['token' => ['token' => self::TOKEN]],
        ]);

        $parameters = RestCarrierMock::instance()->parameters();
        $this->assertSame(['token' => ['token' => self::TOKEN]], $parameters['instrument']);
        $this->assertSame('en_US', $parameters['locale']);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(StatusType::OK, $response->status()->type());
    }

    public function testItInvalidatesASubtoken()
    {
        $this->getService()->invalidateToken(['instrument' => ['token' => ['subtoken' => '8740257204881112']]]);

        $this->assertSame(['token' => ['subtoken' => '8740257204881112']], RestCarrierMock::instance()->parameters()['instrument']);
        $this->assertArrayNotHasKey('locale', RestCarrierMock::instance()->parameters());
    }

    public function testItHandlesAnInvalidToken()
    {
        $response = $this->getService()->invalidateToken(['instrument' => ['token' => ['token' => 'invalid_token']]]);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('XN', $response->status()->reason());
    }

    public function testItObtainsTheInstrumentInformation()
    {
        $response = $this->getService()->instrumentInformation(new InstrumentRequest([
            'instrument' => ['token' => ['token' => self::TOKEN]],
        ]));

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('CREDIBANCO', $response->provider());
        $this->assertSame('CR_VS', $response->serviceCode());
        $this->assertSame('C', $response->cardType());
        $this->assertSame(['C', 'R', 'P'], $response->cardTypes());
        $this->assertSame('optional', $response->threeDS());
        $this->assertTrue($response->requireCvv2());
        $this->assertFalse($response->requireOtp());
        $this->assertFalse($response->requirePin());

        $credit = $response->credits()[0];
        $this->assertInstanceOf(CreditOption::class, $credit);
        $this->assertSame('Cuotas', $credit->description());
        $this->assertSame([1, 2, 3, 6, 12], $credit->installments());
        $this->assertSame(['code' => '0', 'type' => '00', 'groupCode' => 'C', 'installment' => 6], $credit->toCredit(6)->toArray());
    }

    public function testItReadsTheRequirementsAsSpelledByTheSpecification()
    {
        $information = new InstrumentInformation([
            'status' => ['status' => 'OK', 'reason' => '00'],
            'requiereOtp' => true,
            'requiereCvv2' => 'false',
        ]);

        $this->assertTrue($information->requireOtp());
        $this->assertFalse($information->requireCvv2());
        $this->assertSame(['requireOtp' => true, 'requireCvv2' => false], array_intersect_key($information->toArray(), ['requireOtp' => 0, 'requireCvv2' => 0]));
    }

    public function testItObtainsTheTokenInformation()
    {
        $response = $this->getService()->tokenInformation(['instrument' => ['token' => ['token' => self::TOKEN]]]);

        $this->assertTrue($response->isSuccessful());
        $this->assertSame(1, $response->id());
        $this->assertSame('card', $response->type());
        $this->assertSame(self::TOKEN, $response->token()->token());
        $this->assertSame('fakesubtoken00005', $response->token()->subtoken());
        $this->assertSame('411111', $response->token()->bin());
        $this->assertSame('1111', $response->token()->lastDigits());
        $this->assertSame('John Doe', $response->token()->owner());
        $this->assertSame('12/29', $response->token()->validUntil());
        $this->assertSame('card', $response->toArray()['data']['type']);
    }

    public function testItRequiresATokenOrSubtoken()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('The instrument token or subtoken is required');

        $this->getService()->tokenInformation(['instrument' => ['token' => []]]);
    }

    public function testItRequiresTheInstrument()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('The instrument token or subtoken is required');

        $this->getService()->invalidateToken([]);
    }

    public function testItThrowsExceptionWhenBadArgumentProvided()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('Wrong instrument request');

        $this->getService()->instrumentInformation('token');
    }
}
