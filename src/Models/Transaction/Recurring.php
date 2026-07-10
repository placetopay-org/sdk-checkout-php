<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Transaction;

readonly class Recurring
{
    public function __construct(
        public ?string $periodicity = null,
        public ?int $interval = null,
        public ?string $nextPayment = null,
        public ?int $maxPeriods = null,
        public ?string $dueDate = null,
        public ?string $notificationUrl = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        $periodicity = $this->periodicity;
        if ($periodicity === null && $this->notificationUrl !== null) {
            // API expects periodicity when recurring webhook is provided.
            $periodicity = 'D';
        }

        if ($periodicity !== null) {
            $data['periodicity'] = $periodicity;
        }
        if ($this->interval !== null) {
            $data['interval'] = $this->interval;
        }
        if ($this->nextPayment !== null) {
            $data['nextPayment'] = $this->nextPayment;
        }
        if ($this->maxPeriods !== null) {
            $data['maxPeriods'] = $this->maxPeriods;
        }
        if ($this->dueDate !== null) {
            $data['dueDate'] = $this->dueDate;
        }
        if ($this->notificationUrl !== null) {
            $data['notificationUrl'] = $this->notificationUrl;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            periodicity: $data['periodicity'] ?? null,
            interval: $data['interval'] ?? null,
            nextPayment: $data['nextPayment'] ?? null,
            maxPeriods: $data['maxPeriods'] ?? null,
            dueDate: $data['dueDate'] ?? null,
            notificationUrl: $data['notificationUrl'] ?? null,
        );
    }
}
