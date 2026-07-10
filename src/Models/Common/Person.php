<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class Person
{
    public function __construct(
        public ?string $document = null,
        public ?DocumentType $documentType = null,
        public ?string $name = null,
        public ?string $surname = null,
        public ?string $company = null,
        public ?string $email = null,
        public ?string $mobile = null,
        public ?Address $address = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->document !== null) {
            $data['document'] = $this->document;
        }
        if ($this->documentType !== null) {
            $data['documentType'] = $this->documentType->value;
        }
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->surname !== null) {
            $data['surname'] = $this->surname;
        }
        if ($this->company !== null) {
            $data['company'] = $this->company;
        }
        if ($this->email !== null) {
            $data['email'] = $this->email;
        }
        if ($this->mobile !== null) {
            $data['mobile'] = $this->mobile;
        }
        if ($this->address !== null) {
            $data['address'] = $this->address->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            document: $data['document'] ?? null,
            documentType: isset($data['documentType']) ? DocumentType::fromString($data['documentType']) : null,
            name: $data['name'] ?? null,
            surname: $data['surname'] ?? null,
            company: $data['company'] ?? null,
            email: $data['email'] ?? null,
            mobile: $data['mobile'] ?? null,
            address: isset($data['address']) ? Address::fromArray($data['address']) : null,
        );
    }
}
