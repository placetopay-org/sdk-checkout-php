<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Entities;

use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;

class SettingsTest extends BaseTestCase
{
    public function testItFailsIfNoLoginProvided()
    {
        $this->expectException(PlacetoPayException::class);
        $this->getSettings([
            'login' => null,
        ]);
    }

    public function testItFailsIfNoBaseURLProvided()
    {
        $this->expectException(PlacetoPayException::class);
        $this->getSettings([
            'baseUrl' => null,
        ]);
    }
}
