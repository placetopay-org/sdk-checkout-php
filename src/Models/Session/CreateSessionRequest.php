<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\NameValuePair;
use PlaceToPay\Checkout\Models\Common\Person;
use PlaceToPay\Checkout\Models\Transaction\AutopayRequest;

readonly class CreateSessionRequest
{
    /**
     * @param NameValuePair[] $fields
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        public ?string $locale = null,
        public ?Person $buyer = null,
        public ?Person $payer = null,
        public ?Payment $payment = null,
        public ?SubscriptionRequest $subscription = null,
        public ?string $returnUrl = null,
        public ?string $cancelUrl = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?string $expiration = null,
        public ?string $paymentMethod = null,
        public ?bool $skipResult = null,
        public ?bool $noBuyerFill = null,
        public ?bool $captureAddress = null,
        public array $fields = [],
        public ?string $type = null,
        public ?AutopayRequest $autopay = null,
        public ?array $metadata = null,
        public ?string $initiatorIndicator = null,
    ) {
    }

    public function withLocale(string $locale): self
    {
        return new self(
            locale: $locale,
            buyer: $this->buyer,
            payer: $this->payer,
            payment: $this->payment,
            subscription: $this->subscription,
            returnUrl: $this->returnUrl,
            cancelUrl: $this->cancelUrl,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            expiration: $this->expiration,
            paymentMethod: $this->paymentMethod,
            skipResult: $this->skipResult,
            noBuyerFill: $this->noBuyerFill,
            captureAddress: $this->captureAddress,
            fields: $this->fields,
            type: $this->type,
            autopay: $this->autopay,
            metadata: $this->metadata,
            initiatorIndicator: $this->initiatorIndicator,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->locale !== null) {
            $data['locale'] = $this->locale;
        }
        if ($this->buyer !== null) {
            $data['buyer'] = $this->buyer->toArray();
        }
        if ($this->payer !== null) {
            $data['payer'] = $this->payer->toArray();
        }
        if ($this->payment !== null) {
            $data['payment'] = $this->payment->toArray();
        }
        if ($this->subscription !== null) {
            $data['subscription'] = $this->subscription->toArray();
        }
        if ($this->returnUrl !== null) {
            $data['returnUrl'] = $this->returnUrl;
        }
        if ($this->cancelUrl !== null) {
            $data['cancelUrl'] = $this->cancelUrl;
        }
        if ($this->ipAddress !== null) {
            $data['ipAddress'] = $this->ipAddress;
        }
        if ($this->userAgent !== null) {
            $data['userAgent'] = $this->userAgent;
        }
        if ($this->expiration !== null) {
            $data['expiration'] = $this->expiration;
        }
        if ($this->paymentMethod !== null) {
            $data['paymentMethod'] = $this->paymentMethod;
        }
        if ($this->skipResult !== null) {
            $data['skipResult'] = $this->skipResult;
        }
        if ($this->noBuyerFill !== null) {
            $data['noBuyerFill'] = $this->noBuyerFill;
        }
        if ($this->captureAddress !== null) {
            $data['captureAddress'] = $this->captureAddress;
        }
        if ($this->fields !== []) {
            $data['fields'] = array_map(fn (NameValuePair $f) => $f->toArray(), $this->fields);
        }
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->autopay !== null) {
            $data['autopay'] = $this->autopay->toArray();
        }
        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }
        if ($this->initiatorIndicator !== null) {
            $data['initiatorIndicator'] = $this->initiatorIndicator;
        }
        return $data;
    }

    public static function builder(): CreateSessionRequestBuilder
    {
        return new CreateSessionRequestBuilder();
    }
}

class CreateSessionRequestBuilder
{
    private ?string $locale = null;
    private ?Person $buyer = null;
    private ?Person $payer = null;
    private ?Payment $payment = null;
    private ?SubscriptionRequest $subscription = null;
    private ?string $returnUrl = null;
    private ?string $cancelUrl = null;
    private ?string $ipAddress = null;
    private ?string $userAgent = null;
    private ?string $expiration = null;
    private ?string $paymentMethod = null;
    private ?bool $skipResult = null;
    private ?bool $noBuyerFill = null;
    private ?bool $captureAddress = null;
    /** @var NameValuePair[] */
    private array $fields = [];
    private ?string $type = null;
    private ?AutopayRequest $autopay = null;
    /** @var array<string, mixed>|null */
    private ?array $metadata = null;
    private ?string $initiatorIndicator = null;

    public function locale(?string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
    public function buyer(?Person $buyer): static
    {
        $this->buyer = $buyer;
        return $this;
    }
    public function payer(?Person $payer): static
    {
        $this->payer = $payer;
        return $this;
    }
    public function payment(?Payment $payment): static
    {
        $this->payment = $payment;
        return $this;
    }
    public function subscription(?SubscriptionRequest $subscription): static
    {
        $this->subscription = $subscription;
        return $this;
    }
    public function returnUrl(?string $returnUrl): static
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }
    public function cancelUrl(?string $cancelUrl): static
    {
        $this->cancelUrl = $cancelUrl;
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
    public function expiration(?string $expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }
    public function paymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }
    public function skipResult(?bool $skipResult): static
    {
        $this->skipResult = $skipResult;
        return $this;
    }
    public function noBuyerFill(?bool $noBuyerFill): static
    {
        $this->noBuyerFill = $noBuyerFill;
        return $this;
    }
    public function captureAddress(?bool $captureAddress): static
    {
        $this->captureAddress = $captureAddress;
        return $this;
    }
    /** @param NameValuePair[] $fields */
    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }
    public function type(?string $type): static
    {
        $this->type = $type;
        return $this;
    }
    public function autopay(?AutopayRequest $autopay): static
    {
        $this->autopay = $autopay;
        return $this;
    }
    /** @param array<string, mixed>|null $metadata */
    public function metadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }
    public function initiatorIndicator(?string $initiatorIndicator): static
    {
        $this->initiatorIndicator = $initiatorIndicator;
        return $this;
    }

    public function build(): CreateSessionRequest
    {
        return new CreateSessionRequest(
            locale: $this->locale,
            buyer: $this->buyer,
            payer: $this->payer,
            payment: $this->payment,
            subscription: $this->subscription,
            returnUrl: $this->returnUrl,
            cancelUrl: $this->cancelUrl,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            expiration: $this->expiration,
            paymentMethod: $this->paymentMethod,
            skipResult: $this->skipResult,
            noBuyerFill: $this->noBuyerFill,
            captureAddress: $this->captureAddress,
            fields: $this->fields,
            type: $this->type,
            autopay: $this->autopay,
            metadata: $this->metadata,
            initiatorIndicator: $this->initiatorIndicator,
        );
    }
}
