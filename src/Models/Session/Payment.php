<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\Amount;
use PlaceToPay\Checkout\Models\Common\AmountDetail;
use PlaceToPay\Checkout\Models\Common\Item;
use PlaceToPay\Checkout\Models\Common\NameValuePair;
use PlaceToPay\Checkout\Models\Common\Person;
use PlaceToPay\Checkout\Models\Instrument\Instrument;
use PlaceToPay\Checkout\Models\Transaction\Recurring;

readonly class Payment
{
    /**
     * @param Item[] $items
     * @param NameValuePair[] $fields
     * @param DispersionItem[] $dispersion
     * @param PaymentModifier[] $modifiers
     */
    public function __construct(
        public string $reference,
        public string $description,
        public Amount $amount,
        public ?bool $allowPartial = null,
        public ?Person $shipping = null,
        public array $items = [],
        public array $fields = [],
        public ?Recurring $recurring = null,
        public ?bool $subscribe = null,
        public array $dispersion = [],
        public array $modifiers = [],
        public ?string $agreement = null,
        public ?string $agreementType = null,
        public ?AmountDetail $discount = null,
        public ?Instrument $instrument = null,
    ) {
    }

    public static function of(string $reference, string $description, Amount $amount): self
    {
        return new self(
            reference: $reference,
            description: $description,
            amount: $amount,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'reference' => $this->reference,
            'description' => $this->description,
            'amount' => $this->amount->toArray(),
        ];

        if ($this->allowPartial !== null) {
            $data['allowPartial'] = $this->allowPartial;
        }
        if ($this->shipping !== null) {
            $data['shipping'] = $this->shipping->toArray();
        }
        if ($this->items !== []) {
            $data['items'] = array_map(fn (Item $i) => $i->toArray(), $this->items);
        }
        if ($this->fields !== []) {
            $data['fields'] = array_map(fn (NameValuePair $f) => $f->toArray(), $this->fields);
        }
        if ($this->recurring !== null) {
            $data['recurring'] = $this->recurring->toArray();
        }
        if ($this->subscribe !== null) {
            $data['subscribe'] = $this->subscribe;
        }
        if ($this->dispersion !== []) {
            $data['dispersion'] = array_map(fn (DispersionItem $d) => $d->toArray(), $this->dispersion);
        }
        if ($this->modifiers !== []) {
            $data['modifiers'] = array_map(fn (PaymentModifier $m) => $m->toArray(), $this->modifiers);
        }
        if ($this->agreement !== null) {
            $data['agreement'] = $this->agreement;
        }
        if ($this->agreementType !== null) {
            $data['agreementType'] = $this->agreementType;
        }
        if ($this->discount !== null) {
            $data['discount'] = $this->discount->toArray();
        }
        if ($this->instrument !== null) {
            $data['instrument'] = $this->instrument->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $items = [];
        foreach ($data['items'] ?? [] as $item) {
            $items[] = Item::fromArray($item);
        }

        $fields = [];
        foreach ($data['fields'] ?? [] as $field) {
            $fields[] = NameValuePair::fromArray($field);
        }

        $dispersion = [];
        foreach ($data['dispersion'] ?? [] as $d) {
            $dispersion[] = DispersionItem::fromArray($d);
        }

        $modifiers = [];
        foreach ($data['modifiers'] ?? [] as $m) {
            $modifiers[] = PaymentModifier::fromArray($m);
        }

        return new self(
            reference: $data['reference'],
            description: $data['description'],
            amount: Amount::fromArray($data['amount']),
            allowPartial: $data['allowPartial'] ?? null,
            shipping: isset($data['shipping']) ? Person::fromArray($data['shipping']) : null,
            items: $items,
            fields: $fields,
            recurring: isset($data['recurring']) ? Recurring::fromArray($data['recurring']) : null,
            subscribe: $data['subscribe'] ?? null,
            dispersion: $dispersion,
            modifiers: $modifiers,
            agreement: $data['agreement'] ?? null,
            agreementType: $data['agreementType'] ?? null,
            discount: isset($data['discount']) ? AmountDetail::fromArray($data['discount']) : null,
            instrument: isset($data['instrument']) ? Instrument::fromArray($data['instrument']) : null,
        );
    }
}
