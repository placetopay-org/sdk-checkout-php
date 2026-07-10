<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\Status;
use PlaceToPay\Checkout\Models\Transaction\SubscriptionInformation;
use PlaceToPay\Checkout\Models\Transaction\Transaction;

readonly class SessionInformation
{
    /**
     * @param array<string, mixed>|null $request
     * @param Transaction[] $payment
     * @param array<string, mixed>|null $autopay
     */
    public function __construct(
        public ?int $requestId = null,
        public ?Status $status = null,
        public ?array $request = null,
        public array $payment = [],
        public ?SubscriptionInformation $subscription = null,
        public ?array $autopay = null,
    ) {
    }

    /**
     * Most-recent transaction. When approvedOnly=true, returns first APPROVED transaction.
     * When false, returns last element of payment array.
     */
    public function lastTransaction(bool $approvedOnly = false): ?Transaction
    {
        if ($this->payment === []) {
            return null;
        }
        if (!$approvedOnly) {
            return $this->payment[count($this->payment) - 1];
        }
        foreach ($this->payment as $transaction) {
            if ($transaction->isApproved()) {
                return $transaction;
            }
        }
        return null;
    }

    public function lastApprovedTransaction(): ?Transaction
    {
        return $this->lastTransaction(true);
    }

    /**
     * Authorization code of the last approved transaction, or "" when none exist.
     */
    public function lastAuthorization(): string
    {
        $transaction = $this->lastApprovedTransaction();
        if ($transaction === null || $transaction->authorization === null) {
            return '';
        }
        return $transaction->authorization;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->requestId !== null) {
            $data['requestId'] = $this->requestId;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }
        if ($this->request !== null) {
            $data['request'] = $this->request;
        }
        if ($this->payment !== []) {
            $data['payment'] = array_map(fn (Transaction $t) => $t->toArray(), $this->payment);
        }
        if ($this->subscription !== null) {
            $data['subscription'] = $this->subscription->toArray();
        }
        if ($this->autopay !== null) {
            $data['autopay'] = $this->autopay;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $transactions = [];
        foreach ($data['payment'] ?? [] as $t) {
            $transactions[] = Transaction::fromArray($t);
        }

        return new self(
            requestId: isset($data['requestId']) ? (int) $data['requestId'] : null,
            status: isset($data['status']) ? Status::fromArray($data['status']) : null,
            request: $data['request'] ?? null,
            payment: $transactions,
            subscription: isset($data['subscription']) ? SubscriptionInformation::fromArray($data['subscription']) : null,
            autopay: $data['autopay'] ?? null,
        );
    }
}
