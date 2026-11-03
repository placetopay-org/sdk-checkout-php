<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\CreditOption;
use PlacetoPay\PlacetoPaySdk\Entities\Status;
use PlacetoPay\PlacetoPaySdk\Traits\StatusTrait;

class InstrumentInformation extends Entity
{
    use StatusTrait;

    protected string $provider = '';
    protected string $serviceCode = '';
    protected string $cardType = '';
    /**
     * @var string[]
     */
    protected array $cardTypes = [];
    protected string $threeDS = '';
    /**
     * @var CreditOption[]
     */
    protected array $credits = [];
    /**
     * @var array<array{code: string, description: string}>
     */
    protected array $pockets = [];
    /**
     * @var array<array{code: string, description: string}>
     */
    protected array $bankList = [];
    protected string $zipCodeFormat = '';
    /**
     * @var array<string, bool>
     */
    protected array $requirements = [];

    private const REQUIREMENTS = [
        'displayInterest', 'requireOtp', 'requireCvv2', 'requirePockets', 'requireAvs',
        'accountVerification', 'requirePin', 'requireRedirection',
    ];

    public function __construct(array $data = [])
    {
        $this->loadEntity($data['status'] ?? null, 'status', Status::class);
        $this->load($data, ['provider', 'serviceCode', 'cardType', 'cardTypes', 'threeDS', 'pockets', 'bankList', 'zipCodeFormat']);
        $this->credits = array_map(fn (array $credit) => new CreditOption($credit), $data['credits'] ?? []);

        foreach (self::REQUIREMENTS as $requirement) {
            $value = $data[$requirement] ?? $data[str_replace('require', 'requiere', $requirement)] ?? null;
            if ($value !== null) {
                $this->requirements[$requirement] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function serviceCode(): string
    {
        return $this->serviceCode;
    }

    public function cardType(): string
    {
        return $this->cardType;
    }

    /**
     * @return string[]
     */
    public function cardTypes(): array
    {
        return $this->cardTypes;
    }

    public function threeDS(): string
    {
        return $this->threeDS;
    }

    /**
     * @return CreditOption[]
     */
    public function credits(): array
    {
        return $this->credits;
    }

    /**
     * @return array<array{code: string, description: string}>
     */
    public function pockets(): array
    {
        return $this->pockets;
    }

    /**
     * @return array<array{code: string, description: string}>
     */
    public function bankList(): array
    {
        return $this->bankList;
    }

    public function zipCodeFormat(): string
    {
        return $this->zipCodeFormat;
    }

    public function displayInterest(): bool
    {
        return $this->requirements['displayInterest'] ?? false;
    }

    public function requireOtp(): bool
    {
        return $this->requirements['requireOtp'] ?? false;
    }

    public function requireCvv2(): bool
    {
        return $this->requirements['requireCvv2'] ?? false;
    }

    public function requirePockets(): bool
    {
        return $this->requirements['requirePockets'] ?? false;
    }

    public function requireAvs(): bool
    {
        return $this->requirements['requireAvs'] ?? false;
    }

    public function accountVerification(): bool
    {
        return $this->requirements['accountVerification'] ?? false;
    }

    public function requirePin(): bool
    {
        return $this->requirements['requirePin'] ?? false;
    }

    public function requireRedirection(): bool
    {
        return $this->requirements['requireRedirection'] ?? false;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'status' => $this->status()->toArray(),
            'provider' => $this->provider(),
            'serviceCode' => $this->serviceCode(),
            'cardType' => $this->cardType(),
            'cardTypes' => $this->cardTypes(),
            'threeDS' => $this->threeDS(),
            'credits' => array_map(fn (CreditOption $credit) => $credit->toArray(), $this->credits()),
            'pockets' => $this->pockets(),
            'bankList' => $this->bankList(),
            'zipCodeFormat' => $this->zipCodeFormat(),
            ...$this->requirements,
        ]);
    }
}
