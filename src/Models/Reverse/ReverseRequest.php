<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Reverse;

readonly class ReverseRequest
{
    public function __construct(
        public string $internalReference,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['internalReference' => $this->internalReference];
    }
}
