<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Traits\StatusTrait;

class Notification extends Entity
{
    use StatusTrait;
    private const SHA256_PREFIX = 'sha256:';

    protected int $requestId;
    protected string $reference;
    protected string $signature;

    public function __construct(array $data, private readonly string $tranKey)
    {
        $this->load($data, ['requestId', 'reference', 'signature']);
        $this->loadEntity($data['status'], 'status', Status::class);
    }

    public function requestId(): int
    {
        return $this->requestId;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function signature(): string
    {
        return $this->signature;
    }

    public function makeSignature(): string
    {
        $data = $this->requestId() . $this->status()->status() . $this->status()->date() . $this->tranKey;

        return str_starts_with($this->signature(), self::SHA256_PREFIX)
            ? self::SHA256_PREFIX . hash('sha256', $data)
            : sha1($data);
    }

    public function isValidNotification(): bool
    {
        return hash_equals($this->makeSignature(), $this->signature());
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status()->toArray(),
            'requestId' => $this->requestId(),
            'reference' => $this->reference(),
            'signature' => $this->signature(),
        ];
    }
}
