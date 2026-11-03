<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Entities;

use PlacetoPay\PlacetoPaySdk\Entities\Credit;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;

class CreditTest extends BaseTestCase
{
    public function testItHandlesACreditWithoutAllItsData()
    {
        $credit = new Credit(['installment' => 3]);

        $this->assertSame('', $credit->code());
        $this->assertSame(['installment' => 3], $credit->toArray());
    }

    public function testItChargesASingleInstallmentByDefault()
    {
        $this->assertSame(1, (new Credit(['code' => '1', 'type' => '02', 'groupCode' => 'C']))->installment());
    }
}
