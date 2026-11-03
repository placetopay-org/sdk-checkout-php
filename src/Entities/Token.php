<?php

namespace PlacetoPay\PlacetoPaySdk\Entities;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;

class Token extends Entity
{
    protected string $token = '';
    protected string $subtoken = '';
    protected string $franchise = '';
    protected string $franchiseName = '';
    protected string $issuerName = '';
    protected string $lastDigits = '';
    protected string $validUntil = '';
    protected string $bin = '';
    protected string $owner = '';
    // Just in case the token will be utilized
    protected string $cvv = '';
    protected ?int $installments = null;

    public function __construct(array $data = [])
    {
        $this->load($data, ['token', 'subtoken', 'franchise', 'franchiseName', 'issuerName', 'lastDigits', 'validUntil', 'bin', 'owner', 'cvv']);
        $this->installments = is_numeric($data['installments'] ?? null) ? (int)$data['installments'] : null;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function subtoken(): string
    {
        return $this->subtoken;
    }

    public function franchise(): string
    {
        return $this->franchise;
    }

    public function franchiseName(): string
    {
        return $this->franchiseName;
    }

    public function issuerName(): string
    {
        return $this->issuerName;
    }

    public function lastDigits(): string
    {
        return $this->lastDigits;
    }

    public function validUntil(): string
    {
        return $this->validUntil;
    }

    public function bin(): string
    {
        return $this->bin;
    }

    public function owner(): string
    {
        return $this->owner;
    }

    public function cvv(): string
    {
        return $this->cvv;
    }

    public function installments(): ?int
    {
        return $this->installments;
    }

    public function expiration(): string
    {
        return date('m/y', strtotime($this->validUntil()));
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'token' => $this->token(),
            'subtoken' => $this->subtoken(),
            'franchise' => $this->franchise(),
            'franchiseName' => $this->franchiseName(),
            'issuerName' => $this->issuerName(),
            'lastDigits' => $this->lastDigits(),
            'validUntil' => $this->validUntil(),
            'bin' => $this->bin(),
            'owner' => $this->owner(),
            'cvv' => $this->cvv(),
            'installments' => $this->installments(),
        ]);
    }
}
