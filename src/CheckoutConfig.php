<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

final class CheckoutConfig
{
    public readonly string $baseUrl;

    /**
     * @param array<string, string> $additionalHeaders
     * @param array<string, string>|null $authAdditional
     */
    public function __construct(
        public readonly string $login,
        public readonly string $secretKey,
        public readonly Environment $environment,
        public readonly ?Country $country,
        public readonly int $connectTimeout,
        public readonly int $readTimeout,
        public readonly int $retryMaxAttempts,
        public readonly int $retryInitialDelayMs,
        public readonly int $retryMaxDelayMs,
        public readonly ?string $userAgentSuffix,
        public readonly array $additionalHeaders,
        public readonly ?array $authAdditional,
        public readonly ?LoggerInterface $logger,
        public readonly ?ClientInterface $httpClient,
        ?string $baseUrlOverride,
    ) {
        $resolved = $this->resolveBaseUrl($baseUrlOverride);
        $this->validateBaseUrl($resolved);
        $this->baseUrl = rtrim($resolved, '/');
    }

    private function resolveBaseUrl(?string $override): string
    {
        if ($override !== null) {
            return $override;
        }
        if ($this->country !== null) {
            $countryOverride = $this->country->baseUrlOverride($this->environment);
            if ($countryOverride !== null) {
                return $countryOverride;
            }
        }
        return $this->environment->defaultBaseUrl();
    }

    private function validateBaseUrl(string $url): void
    {
        if (str_starts_with($url, 'http://')) {
            $host = parse_url($url, PHP_URL_HOST) ?? '';
            if (!in_array($host, ['localhost', '127.0.0.1'], true)) {
                throw new \InvalidArgumentException(
                    "Insecure HTTP URL is only allowed for localhost/127.0.0.1, got: {$url}"
                );
            }
        }
    }

    public function defaultCurrency(): ?string
    {
        return $this->country?->defaultCurrency();
    }

    public function defaultLocale(): ?string
    {
        return $this->country?->defaultLocale();
    }

    public static function builder(): CheckoutConfigBuilder
    {
        return new CheckoutConfigBuilder();
    }
}

final class CheckoutConfigBuilder
{
    private ?string $login = null;
    private ?string $secretKey = null;
    private Environment $environment = Environment::SANDBOX;
    private ?Country $country = null;
    private ?string $baseUrl = null;
    private int $connectTimeout = 10;
    private int $readTimeout = 30;
    private int $retryMaxAttempts = 3;
    private int $retryInitialDelayMs = 200;
    private int $retryMaxDelayMs = 5000;
    private ?string $userAgentSuffix = null;
    /** @var array<string, string> */
    private array $additionalHeaders = [];
    /** @var array<string, string>|null */
    private ?array $authAdditional = null;
    private ?LoggerInterface $logger = null;
    private ?ClientInterface $httpClient = null;

    public function login(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function secretKey(string $secretKey): static
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function environment(Environment $environment): static
    {
        $this->environment = $environment;
        return $this;
    }

    public function country(?Country $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function baseUrl(?string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function connectTimeout(int $connectTimeout): static
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function readTimeout(int $readTimeout): static
    {
        $this->readTimeout = $readTimeout;
        return $this;
    }

    public function retryMaxAttempts(int $retryMaxAttempts): static
    {
        $this->retryMaxAttempts = $retryMaxAttempts;
        return $this;
    }

    public function retryInitialDelayMs(int $retryInitialDelayMs): static
    {
        $this->retryInitialDelayMs = $retryInitialDelayMs;
        return $this;
    }

    public function retryMaxDelayMs(int $retryMaxDelayMs): static
    {
        $this->retryMaxDelayMs = $retryMaxDelayMs;
        return $this;
    }

    public function userAgentSuffix(?string $userAgentSuffix): static
    {
        $this->userAgentSuffix = $userAgentSuffix;
        return $this;
    }

    /** @param array<string, string> $additionalHeaders */
    public function additionalHeaders(array $additionalHeaders): static
    {
        $this->additionalHeaders = $additionalHeaders;
        return $this;
    }

    /** @param array<string, string>|null $authAdditional */
    public function authAdditional(?array $authAdditional): static
    {
        $this->authAdditional = $authAdditional;
        return $this;
    }

    public function logger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function httpClient(?ClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function build(): CheckoutConfig
    {
        if ($this->login === null || $this->login === '') {
            throw new \InvalidArgumentException('login is required');
        }
        if ($this->secretKey === null || $this->secretKey === '') {
            throw new \InvalidArgumentException('secretKey is required');
        }
        if ($this->retryMaxAttempts < 1) {
            throw new \InvalidArgumentException('retryMaxAttempts must be >= 1');
        }

        return new CheckoutConfig(
            login: $this->login,
            secretKey: $this->secretKey,
            environment: $this->environment,
            country: $this->country,
            connectTimeout: $this->connectTimeout,
            readTimeout: $this->readTimeout,
            retryMaxAttempts: $this->retryMaxAttempts,
            retryInitialDelayMs: $this->retryInitialDelayMs,
            retryMaxDelayMs: $this->retryMaxDelayMs,
            userAgentSuffix: $this->userAgentSuffix,
            additionalHeaders: $this->additionalHeaders,
            authAdditional: $this->authAdditional,
            logger: $this->logger,
            httpClient: $this->httpClient,
            baseUrlOverride: $this->baseUrl,
        );
    }
}
