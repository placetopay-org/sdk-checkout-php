<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Entities\Transaction;
use PlacetoPay\PlacetoPaySdk\Traits\StatusTrait;

class TransactionResponse extends Entity
{
    use StatusTrait;

    protected ?Transaction $payment = null;

    public function payment(): ?Transaction
    {
        return $this->payment;
    }

    public function __construct(array $data = [])
    {
        $this->loadEntity($data['status'] ?? null, 'status', Status::class);
        $this->loadEntity($data['payment'] ?? null, 'payment', Transaction::class);
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'status' => $this->status()->toArray(),
            'payment' => $this->payment()?->toArray(),
        ]);
    }
}
