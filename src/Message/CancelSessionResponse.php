<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Traits\StatusTrait;

class CancelSessionResponse extends Entity
{
    use StatusTrait;

    protected ?RedirectInformation $session = null;

    public function __construct(array $data = [])
    {
        $this->loadEntity($data['status'] ?? null, 'status', Status::class);
        $this->loadEntity($data['session'] ?? null, 'session', RedirectInformation::class);
    }

    public function session(): ?RedirectInformation
    {
        return $this->session;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'status' => $this->status()->toArray(),
            'session' => $this->session()?->toArray(),
        ]);
    }
}
