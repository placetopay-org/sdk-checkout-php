<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Internal;

use PlaceToPay\Checkout\Models\Common\Status;

/**
 * @internal
 * Wraps the {"status": {...}} envelope returned by instrument/invalidate.
 */
final class ErrorResponse
{
    public function __construct(
        public readonly ?Status $status = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: isset($data['status']) && is_array($data['status'])
                ? Status::fromArray($data['status'])
                : (isset($data['status']) ? Status::fromArray($data) : null),
        );
    }
}
