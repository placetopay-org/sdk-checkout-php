<?php

namespace PlacetoPay\PlacetoPaySdk;

use PlacetoPay\PlacetoPaySdk\Entities\AmountBase;
use PlacetoPay\PlacetoPaySdk\Enums\TransactionAction;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Helpers\Settings;
use PlacetoPay\PlacetoPaySdk\Message\CancelSessionResponse;
use PlacetoPay\PlacetoPaySdk\Message\CollectRequest;
use PlacetoPay\PlacetoPaySdk\Message\InstrumentInformation;
use PlacetoPay\PlacetoPaySdk\Message\InstrumentRequest;
use PlacetoPay\PlacetoPaySdk\Message\Notification;
use PlacetoPay\PlacetoPaySdk\Message\RedirectInformation;
use PlacetoPay\PlacetoPaySdk\Message\RedirectRequest;
use PlacetoPay\PlacetoPaySdk\Message\RedirectResponse;
use PlacetoPay\PlacetoPaySdk\Message\ReverseResponse;
use PlacetoPay\PlacetoPaySdk\Message\StatusResponse;
use PlacetoPay\PlacetoPaySdk\Message\TokenInformation;
use PlacetoPay\PlacetoPaySdk\Message\TransactionRequest;
use PlacetoPay\PlacetoPaySdk\Message\TransactionResponse;

class PlacetoPay
{
    protected readonly Settings $settings;

    /**
     * @throws PlacetoPayException
     */
    public function __construct(array $data)
    {
        $this->settings = new Settings($data);
    }

    /**
     * Accepts mixed so anything other than an array or RedirectRequest raises a PlacetoPayException.
     * @param RedirectRequest|array $redirectRequest
     * @throws PlacetoPayException
     */
    public function request(mixed $redirectRequest): RedirectResponse
    {
        $redirectRequest = match (true) {
            is_array($redirectRequest) => new RedirectRequest($redirectRequest),
            $redirectRequest instanceof RedirectRequest => $redirectRequest,
            default => throw PlacetoPayException::forDataNotProvided('Wrong class request'),
        };

        return $this->settings->carrier()->request($redirectRequest);
    }

    public function query(int $requestId): RedirectInformation
    {
        return $this->settings->carrier()->query($requestId);
    }

    public function cancelSession(int $requestId): CancelSessionResponse
    {
        return $this->settings->carrier()->cancelSession($requestId);
    }

    /**
     * Accepts mixed so anything other than an array or CollectRequest raises a PlacetoPayException.
     * @param CollectRequest|array $collectRequest
     * @throws PlacetoPayException
     */
    public function collect(mixed $collectRequest): RedirectInformation
    {
        $collectRequest = match (true) {
            is_array($collectRequest) => new CollectRequest($collectRequest),
            $collectRequest instanceof CollectRequest => $collectRequest,
            default => throw new PlacetoPayException('Wrong collect request'),
        };

        return $this->settings->carrier()->collect($collectRequest);
    }

    public function reverse(int $internalReference, AmountBase|array|null $amount = null): ReverseResponse
    {
        return $this->settings->carrier()->reverse($internalReference, is_array($amount) ? new AmountBase($amount) : $amount);
    }

    /**
     * @param TransactionRequest|array $transactionRequest
     * @throws PlacetoPayException
     */
    public function transaction(mixed $transactionRequest): TransactionResponse
    {
        $transactionRequest = match (true) {
            is_array($transactionRequest) => new TransactionRequest($transactionRequest),
            $transactionRequest instanceof TransactionRequest => $transactionRequest,
            default => throw new PlacetoPayException('Wrong transaction request'),
        };

        return $this->settings->carrier()->transaction($transactionRequest);
    }

    /**
     * @throws PlacetoPayException
     */
    public function checkout(int $internalReference, AmountBase|array|null $amount = null): TransactionResponse
    {
        return $this->transaction([
            'internalReference' => $internalReference,
            'action' => TransactionAction::CHECKOUT,
            'amount' => $amount,
        ]);
    }

    /**
     * @throws PlacetoPayException
     */
    public function reauthorize(int $internalReference, AmountBase|array $amount): TransactionResponse
    {
        return $this->transaction([
            'internalReference' => $internalReference,
            'action' => TransactionAction::REAUTHORIZATION,
            'amount' => $amount,
        ]);
    }

    /**
     * @throws PlacetoPayException
     */
    public function confirm(int $internalReference): TransactionResponse
    {
        return $this->transaction(['internalReference' => $internalReference, 'action' => TransactionAction::CONFIRM]);
    }

    /**
     * @throws PlacetoPayException
     */
    public function cancelTransaction(int $internalReference): TransactionResponse
    {
        return $this->transaction(['internalReference' => $internalReference, 'action' => TransactionAction::CANCEL]);
    }

    /**
     * @param InstrumentRequest|array $instrumentRequest
     * @throws PlacetoPayException
     */
    public function invalidateToken(mixed $instrumentRequest): StatusResponse
    {
        return $this->settings->carrier()->invalidateToken($this->instrumentRequest($instrumentRequest));
    }

    /**
     * @param InstrumentRequest|array $instrumentRequest
     * @throws PlacetoPayException
     */
    public function instrumentInformation(mixed $instrumentRequest): InstrumentInformation
    {
        return $this->settings->carrier()->instrumentInformation($this->instrumentRequest($instrumentRequest));
    }

    /**
     * @param InstrumentRequest|array $instrumentRequest
     * @throws PlacetoPayException
     */
    public function tokenInformation(mixed $instrumentRequest): TokenInformation
    {
        return $this->settings->carrier()->tokenInformation($this->instrumentRequest($instrumentRequest));
    }

    public function readNotification(array $data): Notification
    {
        return new Notification($data, $this->settings->tranKey());
    }

    /**
     * @throws PlacetoPayException
     */
    private function instrumentRequest(mixed $instrumentRequest): InstrumentRequest
    {
        return match (true) {
            is_array($instrumentRequest) => new InstrumentRequest($instrumentRequest),
            $instrumentRequest instanceof InstrumentRequest => $instrumentRequest,
            default => throw new PlacetoPayException('Wrong instrument request'),
        };
    }
}
