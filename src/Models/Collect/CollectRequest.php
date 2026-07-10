<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Collect;

use PlaceToPay\Checkout\Models\Common\NameValuePair;
use PlaceToPay\Checkout\Models\Common\Person;
use PlaceToPay\Checkout\Models\Instrument\Instrument;
use PlaceToPay\Checkout\Models\Session\Payment;

readonly class CollectRequest
{
    /**
     * @param NameValuePair[] $fields
     */
    public function __construct(
        public ?string $locale = null,
        public ?Person $payer = null,
        public ?Person $buyer = null,
        public ?Payment $payment = null,
        public ?Instrument $instrument = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public array $fields = [],
        public ?bool $captureAddress = null,
        public ?string $provider = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->locale !== null) {
            $data['locale'] = $this->locale;
        }
        if ($this->payer !== null) {
            $data['payer'] = $this->payer->toArray();
        }
        if ($this->buyer !== null) {
            $data['buyer'] = $this->buyer->toArray();
        }
        if ($this->payment !== null) {
            $data['payment'] = $this->payment->toArray();
        }
        if ($this->instrument !== null) {
            $data['instrument'] = $this->instrument->toArray();
        }
        if ($this->ipAddress !== null) {
            $data['ipAddress'] = $this->ipAddress;
        }
        if ($this->userAgent !== null) {
            $data['userAgent'] = $this->userAgent;
        }
        if ($this->fields !== []) {
            $data['fields'] = array_map(fn (NameValuePair $f) => $f->toArray(), $this->fields);
        }
        if ($this->captureAddress !== null) {
            $data['captureAddress'] = $this->captureAddress;
        }
        if ($this->provider !== null) {
            $data['provider'] = $this->provider;
        }
        return $data;
    }

    public static function builder(): CollectRequestBuilder
    {
        return new CollectRequestBuilder();
    }
}

class CollectRequestBuilder
{
    private ?string $locale = null;
    private ?Person $payer = null;
    private ?Person $buyer = null;
    private ?Payment $payment = null;
    private ?Instrument $instrument = null;
    private ?string $ipAddress = null;
    private ?string $userAgent = null;
    /** @var NameValuePair[] */
    private array $fields = [];
    private ?bool $captureAddress = null;
    private ?string $provider = null;

    public function locale(?string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
    public function payer(?Person $payer): static
    {
        $this->payer = $payer;
        return $this;
    }
    public function buyer(?Person $buyer): static
    {
        $this->buyer = $buyer;
        return $this;
    }
    public function payment(?Payment $payment): static
    {
        $this->payment = $payment;
        return $this;
    }
    public function instrument(?Instrument $instrument): static
    {
        $this->instrument = $instrument;
        return $this;
    }
    public function ipAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }
    public function userAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }
    /** @param NameValuePair[] $fields */
    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }
    public function captureAddress(?bool $captureAddress): static
    {
        $this->captureAddress = $captureAddress;
        return $this;
    }
    public function provider(?string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function build(): CollectRequest
    {
        return new CollectRequest(
            locale: $this->locale,
            payer: $this->payer,
            buyer: $this->buyer,
            payment: $this->payment,
            instrument: $this->instrument,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            fields: $this->fields,
            captureAddress: $this->captureAddress,
            provider: $this->provider,
        );
    }
}
