<?php

namespace PlacetoPay\PlacetoPaySdk\Tests\Messages;

use PlacetoPay\PlacetoPaySdk\Enums\StatusType;
use PlacetoPay\PlacetoPaySdk\Message\Notification;
use PlacetoPay\PlacetoPaySdk\Tests\BaseTestCase;

class NotificationTest extends BaseTestCase
{
    public function testItParsesCorrectlyTheNotification()
    {
        $data = unserialize('a:4:{s:6:"status";a:4:{s:6:"status";s:8:"APPROVED";s:6:"reason";s:2:"00";s:7:"message";s:82:"Se ha aprobado su pago, puede imprimir el recibo o volver a la pagina del comercio";s:4:"date";s:25:"2016-10-10T16:39:57-05:00";}s:9:"requestId";i:83;s:9:"reference";s:20:"TEST_20161010_213937";s:9:"signature";s:40:"8fb4beea130ab3e75a1de956bd0213892e0f6839";}');
        $notification = new Notification($data, '024h1IlD');

        $this->assertTrue($notification->isValidNotification(), 'Valid notification');
        $this->assertTrue($notification->isApproved(), $notification->status()->status());
        $this->assertFalse($notification->isRejected(), $notification->status()->status());
        $this->assertEquals($notification->requestId(), 83, 'Same request identifier');
        $this->assertEquals($notification->reference(), 'TEST_20161010_213937', 'Same reference');

        $this->assertEquals($data, $notification->toArray());
    }

    public function testItParsesANotificationPost()
    {
        $data = unserialize('a:4:{s:6:"status";a:4:{s:6:"status";s:8:"REJECTED";s:6:"reason";s:2:"?C";s:7:"message";s:51:"El proceso de pago ha sido cancelado por el usuario";s:4:"date";s:25:"2016-10-12T01:44:37-05:00";}s:9:"requestId";i:126;s:9:"reference";s:9:"100000071";s:9:"signature";s:40:"554fa6c36bd5d1376b192b8bc3a1e3dd9a01e448";}');

        $gateway = $this->getService([
            'url' => 'https://testing.com',
            'tranKey' => '024h1IlD',
        ]);
        $notification = $gateway->readNotification($data);

        $this->assertTrue($notification->isValidNotification(), 'Its a valid notification');
        $this->assertSame(StatusType::REJECTED, $notification->status()->type());
        $this->assertFalse($notification->isApproved());
    }

    public function testItValidatesASha256Signature()
    {
        $data = [
            'status' => ['status' => 'APPROVED', 'reason' => '00', 'message' => 'Aprobada', 'date' => '2026-11-03T14:44:03-05:00'],
            'requestId' => 898,
            'reference' => 'TEST-1793735042',
            'signature' => 'sha256:' . hash('sha256', '898APPROVED2026-11-03T14:44:03-05:00024h1IlD'),
        ];

        $this->assertTrue((new Notification($data, '024h1IlD'))->isValidNotification());
        $this->assertFalse((new Notification($data, 'other-tran-key'))->isValidNotification());
    }

    public function testItRejectsASha256SignatureWithoutItsPrefix()
    {
        $data = [
            'status' => ['status' => 'APPROVED', 'reason' => '00', 'message' => 'Aprobada', 'date' => '2026-11-03T14:44:03-05:00'],
            'requestId' => 898,
            'reference' => 'TEST-1793735042',
            'signature' => hash('sha256', '898APPROVED2026-11-03T14:44:03-05:00024h1IlD'),
        ];

        $this->assertFalse((new Notification($data, '024h1IlD'))->isValidNotification());
    }
}
