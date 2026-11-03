<?php

namespace PlacetoPay\PlacetoPaySdk\Contracts;

use PlacetoPay\PlacetoPaySdk\Entities\AmountBase;
use PlacetoPay\PlacetoPaySdk\Helpers\Settings;
use PlacetoPay\PlacetoPaySdk\Message\CancelSessionResponse;
use PlacetoPay\PlacetoPaySdk\Message\CollectRequest;
use PlacetoPay\PlacetoPaySdk\Message\InstrumentInformation;
use PlacetoPay\PlacetoPaySdk\Message\InstrumentRequest;
use PlacetoPay\PlacetoPaySdk\Message\RedirectInformation;
use PlacetoPay\PlacetoPaySdk\Message\RedirectRequest;
use PlacetoPay\PlacetoPaySdk\Message\RedirectResponse;
use PlacetoPay\PlacetoPaySdk\Message\ReverseResponse;
use PlacetoPay\PlacetoPaySdk\Message\StatusResponse;
use PlacetoPay\PlacetoPaySdk\Message\TokenInformation;
use PlacetoPay\PlacetoPaySdk\Message\TransactionRequest;
use PlacetoPay\PlacetoPaySdk\Message\TransactionResponse;

abstract class Carrier
{
    public function __construct(protected readonly Settings $settings)
    {
    }

    abstract public function request(RedirectRequest $redirectRequest): RedirectResponse;

    abstract public function query(int $requestId): RedirectInformation;

    abstract public function cancelSession(int $requestId): CancelSessionResponse;

    abstract public function collect(CollectRequest $collectRequest): RedirectInformation;

    abstract public function reverse(int $internalReference, ?AmountBase $amount = null): ReverseResponse;

    abstract public function transaction(TransactionRequest $transactionRequest): TransactionResponse;

    abstract public function invalidateToken(InstrumentRequest $instrumentRequest): StatusResponse;

    abstract public function instrumentInformation(InstrumentRequest $instrumentRequest): InstrumentInformation;

    abstract public function tokenInformation(InstrumentRequest $instrumentRequest): TokenInformation;
}
