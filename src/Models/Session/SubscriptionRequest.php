<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\NameValuePair;

readonly class SubscriptionRequest
{
    /**
     * @param NameValuePair[] $fields
     */
    public function __construct(
        public ?string $reference = null,
        public ?string $description = null,
        public array $fields = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->reference !== null) {
            $data['reference'] = $this->reference;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        if ($this->fields !== []) {
            $data['fields'] = array_map(fn (NameValuePair $f) => $f->toArray(), $this->fields);
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $fields = [];
        foreach ($data['fields'] ?? [] as $field) {
            $fields[] = NameValuePair::fromArray($field);
        }
        return new self(
            reference: $data['reference'] ?? null,
            description: $data['description'] ?? null,
            fields: $fields,
        );
    }
}
