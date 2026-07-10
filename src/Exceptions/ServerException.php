<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Exceptions;

use PlaceToPay\Checkout\Models\Common\Status;

class ServerException extends PlaceToPayException
{
    public function __construct(
        string $message,
        private readonly int $httpStatusCode,
        ?Status $status = null,
        ?string $reason = null,
        ?int $requestId = null,
        ?string $rawBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $reason, $requestId, $rawBody, $previous);
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
