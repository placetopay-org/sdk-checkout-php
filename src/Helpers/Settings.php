<?php

namespace PlacetoPay\PlacetoPaySdk\Helpers;

use GuzzleHttp\Client;
use PlacetoPay\PlacetoPaySdk\Carrier\Authentication;
use PlacetoPay\PlacetoPaySdk\Carrier\RestCarrier;
use PlacetoPay\PlacetoPaySdk\Contracts\Carrier;
use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;

class Settings extends Entity
{
    public const DEFAULT_ALGORITHM = 'sha256';
    public const ALGORITHMS = ['sha256', 'sha1'];

    protected readonly string $baseUrl;
    protected readonly int $timeout;
    protected readonly bool $verifySsl;
    protected readonly string $login;
    protected readonly string $tranKey;
    protected readonly array $headers;
    protected readonly array $authAdditional;
    protected readonly string $algorithm;
    protected readonly Logger $logger;

    protected ?Client $client;
    protected ?Carrier $carrier = null;

    /**
     * @throws PlacetoPayException
     */
    public function __construct(array $data)
    {
        if (!isset($data['login'], $data['tranKey'])) {
            throw PlacetoPayException::forDataNotProvided('No login or tranKey provided on gateway');
        }

        if (!isset($data['baseUrl']) || !filter_var($data['baseUrl'], FILTER_VALIDATE_URL)) {
            throw PlacetoPayException::forDataNotProvided('No service URL provided to use');
        }

        $algorithm = $data['algorithm'] ?? self::DEFAULT_ALGORITHM;
        if (!in_array($algorithm, self::ALGORITHMS, true)) {
            throw PlacetoPayException::forDataNotProvided('The algorithm must be one of: ' . implode(', ', self::ALGORITHMS));
        }

        $this->baseUrl = str_ends_with($data['baseUrl'], '/') ? $data['baseUrl'] : $data['baseUrl'] . '/';
        $this->timeout = $data['timeout'] ?? 15;
        $this->verifySsl = $data['verifySsl'] ?? true;
        $this->login = $data['login'];
        $this->tranKey = $data['tranKey'];
        $this->headers = $data['headers'] ?? [];
        $this->authAdditional = $data['authAdditional'] ?? [];
        $this->algorithm = $algorithm;
        $this->client = $data['client'] ?? null;
        $this->logger = new Logger($data['logger'] ?? null);
    }

    public function baseUrl(string $endpoint = ''): string
    {
        return $this->baseUrl . $endpoint;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function verifySsl(): bool
    {
        return $this->verifySsl;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function tranKey(): string
    {
        return $this->tranKey;
    }

    public function algorithm(): string
    {
        return $this->algorithm;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function client(): Client
    {
        return $this->client ??= new Client([
            'timeout' => $this->timeout(),
            'connect_timeout' => $this->timeout(),
            'verify' => $this->verifySsl(),
        ]);
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    public function toArray(): array
    {
        return [];
    }

    public function carrier(): Carrier
    {
        return $this->carrier ??= new RestCarrier($this);
    }

    public function authentication(?string $algorithm = null): Authentication
    {
        return new Authentication([
            'login' => $this->login(),
            'tranKey' => $this->tranKey(),
            'authAdditional' => $this->authAdditional,
            'algorithm' => $algorithm ?? $this->algorithm(),
        ]);
    }
}
