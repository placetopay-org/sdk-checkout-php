<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Instrument;

readonly class InvalidateTokenRequest
{
    public function __construct(
        public ?string $locale = null,
        public ?Instrument $instrument = null,
    ) {
    }

    public static function ofToken(string $token): self
    {
        return new self(
            instrument: Instrument::ofToken(new Token(token: $token)),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->locale !== null) {
            $data['locale'] = $this->locale;
        }
        if ($this->instrument !== null) {
            $data['instrument'] = $this->instrument->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            locale: $data['locale'] ?? null,
            instrument: isset($data['instrument']) ? Instrument::fromArray($data['instrument']) : null,
        );
    }
}
