<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Exceptions;

use PlaceToPay\Checkout\Models\Common\Status;

class PlaceToPayException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?Status $status = null,
        private readonly ?string $reason = null,
        private readonly ?int $requestId = null,
        private readonly ?string $rawBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getRequestId(): ?int
    {
        return $this->requestId;
    }

    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }
}
