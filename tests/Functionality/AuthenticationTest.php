<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Functionality;

use PlacetoPay\PlacetoPaySdk\Carrier\Authentication;
use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;
use PlacetoPay\PlacetoPaySdk\Tests\Mocks\RestCarrierMock;

class AuthenticationTest extends BaseTestCase
{
    private const TOKEN = 'faketoken12f233bd8f5d42138d681bf07ea8295429df07a4af287703e30c891';

    public function testItHandlesNoLoginProvided()
    {
        $this->expectException(PlacetoPayException::class);
        new Authentication([]);
    }

    public function testItGeneratesNewAuthentications()
    {
        $auth = new Authentication([
            'login' => 'login_here',
            'tranKey' => 'ABCD1234',
        ]);

        $generated = $auth->asArray();
        $this->assertEquals('login_here', $generated['login']);
        $this->assertNotEquals('ABCD1234', $generated['tranKey']);
        $this->assertNotEmpty($generated['seed']);
        $this->assertNotEmpty($generated['nonce']);
    }

    public function testItOverridesTheAuthenticationSeedAndNonce()
    {
        $auth = new Authentication([
            'login' => 'login',
            'tranKey' => 'ABCD1234',
            'auth' => [
                'seed' => '2016-10-26T21:37:00+00:00',
                'nonce' => 'ifYEPnAcJbpDVR1t',
            ],
        ]);

        $data = $auth->asArray();

        $this->assertEquals('login', $data['login'], 'Login matches');
        $this->assertEquals('2016-10-26T21:37:00+00:00', $data['seed'], 'Seed matches');
        $this->assertEquals('aWZZRVBuQWNKYnBEVlIxdA==', $data['nonce'], 'Nonce matches');
        $this->assertEquals('NDlpEEAIe8sbKZ6mGWQrZwbyBB62J1LcyP/a/o7LRqY=', $data['tranKey'], 'Trankey matches');

        $sha1 = new Authentication([
            'login' => 'login',
            'tranKey' => 'ABCD1234',
            'algorithm' => 'sha1',
            'auth' => ['seed' => '2016-10-26T21:37:00+00:00', 'nonce' => 'ifYEPnAcJbpDVR1t'],
        ]);
        $this->assertEquals('Xi5xrRwrqPU21WE2JI4hyMaCvQ8=', $sha1->asArray()['tranKey'], 'Trankey with sha1 matches');

        $this->assertNotEmpty($auth->digest(false));
        $this->assertInstanceOf(\SoapHeader::class, $auth->asSoapHeader());
    }

    public function testItHandlesCorrectlyABadRequest()
    {
        $response = $this->getService([
            'login' => 'failed_login',
        ])->request($this->baseRequest());

        $this->assertSame(StatusType::FAILED, $response->status()->type());
        $this->assertFalse($response->isSuccessful());
    }

    public function testItHandlesAuthenticationAdditional()
    {
        $response = $this->getService([
            'authAdditional' => [
                'testing-auth' => 'ERROR-200',
            ],
        ])->request($this->baseRequest());

        $this->assertSame(StatusType::FAILED, $response->status()->type());
        $this->assertEquals(401, $response->status()->reason());
        $this->assertEquals('Autenticación fallida 200', $response->status()->message());
    }

    public function testItDigestsTheTranKeyWithSha256ByDefault()
    {
        $this->getService()->query(10012);

        $this->assertSame(self::digest('sha256'), RestCarrierMock::instance()->parameters()['auth']['tranKey']);
        $this->assertSame('sha256', $this->getSettings()->algorithm());
    }

    public function testItDigestsTheCheckoutRequestsWithTheConfiguredAlgorithm()
    {
        $service = $this->getService(['algorithm' => 'sha1']);

        $service->query(10012);
        $this->assertSame(self::digest('sha1'), RestCarrierMock::instance()->parameters()['auth']['tranKey']);

        $service->invalidateToken(['instrument' => ['token' => ['token' => self::TOKEN]]]);
        $this->assertSame(self::digest('sha1'), RestCarrierMock::instance()->parameters()['auth']['tranKey']);
    }

    public function testItAlwaysDigestsTheGatewayRequestsWithSha256()
    {
        $service = $this->getService(['algorithm' => 'sha1']);

        $this->assertTrue($service->tokenInformation(['instrument' => ['token' => ['token' => self::TOKEN]]])->isSuccessful());
        $this->assertSame(self::digest('sha256'), RestCarrierMock::instance()->parameters()['auth']['tranKey']);

        $this->assertTrue($service->instrumentInformation(['instrument' => ['token' => ['token' => self::TOKEN]]])->isSuccessful());
        $this->assertSame(self::digest('sha256'), RestCarrierMock::instance()->parameters()['auth']['tranKey']);
    }

    public function testItRejectsAnUnknownAlgorithm()
    {
        $this->expectException(PlacetoPayException::class);
        $this->expectExceptionMessage('The algorithm must be one of: sha256, sha1');

        $this->getSettings(['algorithm' => 'md5']);
    }

    private static function digest(string $algorithm): string
    {
        $auth = RestCarrierMock::instance()->parameters()['auth'];

        return base64_encode(hash($algorithm, base64_decode($auth['nonce']) . $auth['seed'] . RestCarrierMock::TRAN_KEY, true));
    }
}
