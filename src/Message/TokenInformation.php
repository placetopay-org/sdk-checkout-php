<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Entities\Token;
use PlacetoPay\PlacetoPaySdk\Traits\StatusTrait;

class TokenInformation extends Entity
{
    use StatusTrait;

    protected ?int $id = null;
    protected string $type = '';
    protected ?Token $token = null;

    public function __construct(array $data = [])
    {
        $this->loadEntity($data['status'] ?? null, 'status', Status::class);
        $this->load($data['data'] ?? null, ['id', 'type']);
        $this->loadEntity($data['data'] ?? null, 'token', Token::class);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function token(): ?Token
    {
        return $this->token;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'status' => $this->status()->toArray(),
            'data' => $this->token() ? $this->arrayFilter([
                'id' => $this->id(),
                'type' => $this->type(),
                ...$this->token()->toArray(),
            ]) : null,
        ]);
    }
}
