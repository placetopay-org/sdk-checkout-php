<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Traits\StatusTrait;

class StatusResponse extends Entity
{
    use StatusTrait;

    public function __construct(array $data = [])
    {
        $this->loadEntity($data['status'] ?? null, 'status', Status::class);
    }

    public function toArray(): array
    {
        return ['status' => $this->status()->toArray()];
    }
}
