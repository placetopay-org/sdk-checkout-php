<?php

namespace PlacetoPay\PlacetoPaySdk\Carrier;

use GuzzleHttp\Exception\BadResponseException;
use PlacetoPay\PlacetoPaySdk\Contracts\Carrier;
use PlacetoPay\PlacetoPaySdk\Entities\AmountBase;
use PlacetoPay\PlacetoPaySdk\Enums\TransactionAction;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayServiceException;
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
use Throwable;

class RestCarrier extends Carrier
{
    private const GATEWAY_ALGORITHM = 'sha256';

    private function makeRequest(string $url, array $arguments, ?string $algorithm = null): array
    {
        try {
            $data = [...$arguments, 'auth' => $this->settings->authentication($algorithm)->asArray()];

            $this->settings->logger()->debug('REQUEST', $data);

            $response = $this->settings->client()->post($url, [
                'json' => $data,
                'headers' => $this->settings->headers(),
            ]);
            $statusCode = $response->getStatusCode();
            $result = $response->getBody()->getContents();

            $this->settings->logger()->debug('RESPONSE', [
                'result' => $result,
            ]);
        } catch (BadResponseException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            $result = $exception->getResponse()->getBody()->getContents();
            $this->settings->logger()->warning('BAD_RESPONSE', [
                'class' => $exception::class,
                'result' => $result,
            ]);
        } catch (Throwable $exception) {
            $this->settings->logger()->warning('EXCEPTION_RESPONSE', [
                'exception' => PlacetoPayException::readException($exception),
            ]);
            throw PlacetoPayServiceException::fromServiceException($exception);
        }

        $decoded = json_decode($result, true);

        if (!is_array($decoded)) {
            throw PlacetoPayServiceException::forInvalidResponse($statusCode, json_last_error_msg(), $result);
        }

        return $decoded;
    }

    public function request(RedirectRequest $redirectRequest): RedirectResponse
    {
        return new RedirectResponse(
            $this->makeRequest($this->settings->baseUrl('api/session'), $redirectRequest->toArray())
        );
    }

    public function query(int $requestId): RedirectInformation
    {
        return new RedirectInformation(
            $this->makeRequest($this->settings->baseUrl('api/session/' . $requestId), [])
        );
    }

    public function cancelSession(int $requestId): CancelSessionResponse
    {
        return new CancelSessionResponse(
            $this->makeRequest($this->settings->baseUrl('api/session/' . $requestId . '/cancel'), [])
        );
    }

    public function collect(CollectRequest $collectRequest): RedirectInformation
    {
        return new RedirectInformation(
            $this->makeRequest($this->settings->baseUrl('api/collect'), $collectRequest->toArray())
        );
    }

    public function reverse(int $internalReference, ?AmountBase $amount = null): ReverseResponse
    {
        $transactionRequest = new TransactionRequest([
            'internalReference' => $internalReference,
            'action' => TransactionAction::REVERSE,
            'amount' => $amount,
        ]);

        return new ReverseResponse(
            $this->makeRequest($this->settings->baseUrl('api/transaction'), $transactionRequest->toArray())
        );
    }

    public function transaction(TransactionRequest $transactionRequest): TransactionResponse
    {
        return new TransactionResponse(
            $this->makeRequest($this->settings->baseUrl('api/transaction'), $transactionRequest->toArray())
        );
    }

    public function invalidateToken(InstrumentRequest $instrumentRequest): StatusResponse
    {
        return new StatusResponse(
            $this->makeRequest($this->settings->baseUrl('api/instrument/invalidate'), $instrumentRequest->toArray())
        );
    }

    public function instrumentInformation(InstrumentRequest $instrumentRequest): InstrumentInformation
    {
        return new InstrumentInformation(
            $this->makeRequest($this->settings->baseUrl('api/gateway/information'), $instrumentRequest->toArray(), self::GATEWAY_ALGORITHM)
        );
    }

    public function tokenInformation(InstrumentRequest $instrumentRequest): TokenInformation
    {
        return new TokenInformation(
            $this->makeRequest($this->settings->baseUrl('api/gateway/token'), $instrumentRequest->toArray(), self::GATEWAY_ALGORITHM)
        );
    }
}
