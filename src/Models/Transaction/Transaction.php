<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Transaction;

use PlaceToPay\Checkout\Models\Common\AmountConversion;
use PlaceToPay\Checkout\Models\Common\Discount;
use PlaceToPay\Checkout\Models\Common\NameValuePair;
use PlaceToPay\Checkout\Models\Common\Status;

readonly class Transaction
{
    /**
     * @param NameValuePair[] $processorFields
     * @param array<string, mixed>|null $dispersion
     */
    public function __construct(
        public ?Status $status = null,
        public ?string $reference = null,
        public int|string|null $internalReference = null,
        public ?string $paymentMethod = null,
        public ?string $paymentMethodName = null,
        public ?string $issuerName = null,
        public ?AmountConversion $amount = null,
        public ?string $authorization = null,
        public ?string $receipt = null,
        public ?string $franchise = null,
        public ?bool $refunded = null,
        public array $processorFields = [],
        public ?string $agreement = null,
        public ?string $agreementType = null,
        public ?array $dispersion = null,
        public ?Discount $discount = null,
        public ?SubscriptionInformation $subscription = null,
    ) {
    }

    public function isApproved(): bool
    {
        return $this->status !== null && $this->status->isApproved();
    }

    public function isRejected(): bool
    {
        return $this->status !== null && $this->status->isRejected();
    }

    public function isSuccessful(): bool
    {
        return $this->status !== null && $this->status->isSuccessful();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }
        if ($this->reference !== null) {
            $data['reference'] = $this->reference;
        }
        if ($this->internalReference !== null) {
            $data['internalReference'] = $this->internalReference;
        }
        if ($this->paymentMethod !== null) {
            $data['paymentMethod'] = $this->paymentMethod;
        }
        if ($this->paymentMethodName !== null) {
            $data['paymentMethodName'] = $this->paymentMethodName;
        }
        if ($this->issuerName !== null) {
            $data['issuerName'] = $this->issuerName;
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount->toArray();
        }
        if ($this->authorization !== null) {
            $data['authorization'] = $this->authorization;
        }
        if ($this->receipt !== null) {
            $data['receipt'] = $this->receipt;
        }
        if ($this->franchise !== null) {
            $data['franchise'] = $this->franchise;
        }
        if ($this->refunded !== null) {
            $data['refunded'] = $this->refunded;
        }
        if ($this->processorFields !== []) {
            $data['processorFields'] = array_map(fn (NameValuePair $p) => $p->toArray(), $this->processorFields);
        }
        if ($this->agreement !== null) {
            $data['agreement'] = $this->agreement;
        }
        if ($this->agreementType !== null) {
            $data['agreementType'] = $this->agreementType;
        }
        if ($this->dispersion !== null) {
            $data['dispersion'] = $this->dispersion;
        }
        if ($this->discount !== null) {
            $data['discount'] = $this->discount->toArray();
        }
        if ($this->subscription !== null) {
            $data['subscription'] = $this->subscription->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $processorFields = [];
        foreach ($data['processorFields'] ?? [] as $pf) {
            $processorFields[] = NameValuePair::fromArray($pf);
        }

        return new self(
            status: isset($data['status']) ? Status::fromArray($data['status']) : null,
            reference: $data['reference'] ?? null,
            internalReference: $data['internalReference'] ?? null,
            paymentMethod: $data['paymentMethod'] ?? null,
            paymentMethodName: $data['paymentMethodName'] ?? null,
            issuerName: $data['issuerName'] ?? null,
            amount: isset($data['amount']) ? AmountConversion::fromArray($data['amount']) : null,
            authorization: $data['authorization'] ?? null,
            receipt: $data['receipt'] ?? null,
            franchise: $data['franchise'] ?? null,
            refunded: $data['refunded'] ?? null,
            processorFields: $processorFields,
            agreement: $data['agreement'] ?? null,
            agreementType: $data['agreementType'] ?? null,
            dispersion: $data['dispersion'] ?? null,
            discount: isset($data['discount']) ? Discount::fromArray($data['discount']) : null,
            subscription: isset($data['subscription']) ? SubscriptionInformation::fromArray($data['subscription']) : null,
        );
    }
}
