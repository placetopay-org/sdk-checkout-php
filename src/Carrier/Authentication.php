<?php

namespace PlacetoPay\PlacetoPaySdk\Carrier;

use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Helpers\Settings;
use SoapHeader;
use SoapVar;
use stdClass;

/**
 * Class Authentication
 * Generates the needed authentication elements.
 */
class Authentication
{
    public const WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    public const WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    private readonly string $login;
    private readonly string $tranKey;
    private readonly array $additional;
    private readonly bool $overridden;
    private readonly string $algorithm;

    private array $auth = [];

    /**
     * @throws PlacetoPayException
     */
    public function __construct(array $config)
    {
        if (!isset($config['login'], $config['tranKey'])) {
            throw PlacetoPayException::forDataNotProvided('No login or tranKey provided on authentication');
        }

        $this->login = $config['login'];
        $this->tranKey = $config['tranKey'];
        $this->overridden = isset($config['auth']);
        $this->auth = $config['auth'] ?? [];
        $this->algorithm = $config['algorithm'] ?? Settings::DEFAULT_ALGORITHM;
        $this->additional = $config['authAdditional'] ?? [];

        $this->generate();
    }

    public function getNonce(bool $encoded = true): string
    {
        $nonce = $this->auth ? $this->auth['nonce'] : bin2hex(random_bytes(16));

        return $encoded ? base64_encode($nonce) : $nonce;
    }

    public function getSeed(): string
    {
        return $this->auth ? $this->auth['seed'] : date('c');
    }

    public function digest(bool $encoded = true): string
    {
        $digest = hash($this->algorithm, $this->getNonce(false) . $this->getSeed() . $this->tranKey(), true);

        return $encoded ? base64_encode($digest) : $digest;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function tranKey(): string
    {
        return $this->tranKey;
    }

    public function additional(): array
    {
        return $this->additional;
    }

    public function generate(): static
    {
        if (!$this->overridden) {
            $this->auth = [
                'seed' => $this->getSeed(),
                'nonce' => $this->getNonce(),
            ];
        }

        return $this;
    }

    /**
     * Parses the entity as a SOAP Header, requires ext-soap.
     */
    public function asSoapHeader(): SoapHeader
    {
        $UsernameToken = new stdClass();
        $UsernameToken->Username = new SoapVar($this->login(), XSD_STRING, null, self::WSSE, null, self::WSSE);
        $UsernameToken->Password = new SoapVar($this->digest(), XSD_STRING, 'PasswordDigest', null, 'Password', self::WSSE);
        $UsernameToken->Nonce = new SoapVar($this->getNonce(), XSD_STRING, null, self::WSSE, null, self::WSSE);
        $UsernameToken->Created = new SoapVar($this->getSeed(), XSD_STRING, null, self::WSU, null, self::WSU);

        $security = new stdClass();
        $security->UsernameToken = new SoapVar($UsernameToken, SOAP_ENC_OBJECT, null, self::WSSE, 'UsernameToken', self::WSSE);

        return new SoapHeader(self::WSSE, 'Security', $security, true);
    }

    public function asArray(): array
    {
        return [
            'login' => $this->login(),
            'tranKey' => $this->digest(),
            'nonce' => $this->getNonce(),
            'seed' => $this->getSeed(),
            'additional' => $this->additional(),
        ];
    }
}
