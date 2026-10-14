<?php

namespace PlacetoPay\PlacetoPaySdk\Contracts;

use PlacetoPay\PlacetoPaySdk\Helpers\Settings;
use PlacetoPay\PlacetoPaySdk\Message\CollectRequest;
use PlacetoPay\PlacetoPaySdk\Message\RedirectInformation;
use PlacetoPay\PlacetoPaySdk\Message\RedirectRequest;
use PlacetoPay\PlacetoPaySdk\Message\RedirectResponse;
use PlacetoPay\PlacetoPaySdk\Message\ReverseResponse;

abstract class Carrier
{
    protected Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    abstract public function request(RedirectRequest $redirectRequest): RedirectResponse;

    abstract public function query(string $requestId): RedirectInformation;

    abstract public function collect(CollectRequest $collectRequest): RedirectInformation;

    abstract public function reverse(string $transactionId): ReverseResponse;
}
