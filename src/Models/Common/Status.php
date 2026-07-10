<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class Status
{
    public function __construct(
        public StatusCode $status,
        public ?string $reason = null,
        public ?string $message = null,
        public ?string $date = null,
    ) {
    }

    public function isApproved(): bool
    {
        return $this->status === StatusCode::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === StatusCode::REJECTED;
    }

    public function isError(): bool
    {
        return $this->status === StatusCode::ERROR;
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            StatusCode::OK,
            StatusCode::APPROVED,
            StatusCode::APPROVED_PARTIAL,
        ], true);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            StatusCode::PENDING,
            StatusCode::PENDING_VALIDATION,
        ], true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'status' => $this->status->value,
        ];

        if ($this->reason !== null) {
            $data['reason'] = $this->reason;
        }
        if ($this->message !== null) {
            $data['message'] = $this->message;
        }
        if ($this->date !== null) {
            $data['date'] = $this->date;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: StatusCode::fromString($data['status'] ?? null),
            reason: self::toNullableString($data['reason'] ?? null),
            message: self::toNullableString($data['message'] ?? null),
            date: self::toNullableString($data['date'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }
}
