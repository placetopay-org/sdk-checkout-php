<?php

namespace PlacetoPay\PlacetoPaySdk\Entities;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;

class Instrument extends Entity
{
    protected ?Account $bank = null;
    protected ?Token $token = null;
    protected ?Credit $credit = null;
    protected string $pin;
    protected string $password;
    protected string $pocket;

    public function __construct(array $data = [])
    {
        $this->loadEntity($data['bank'] ?? null, 'bank', Account::class);
        $this->loadEntity($data['credit'] ?? null, 'credit', Credit::class);
        $this->loadEntity($data['token'] ?? null, 'token', Token::class);

        $this->pin = $data['pin'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->pocket = (string)($data['pocket'] ?? '');
    }

    public function bank(): ?Account
    {
        return $this->bank;
    }

    public function credit(): ?Credit
    {
        return $this->credit;
    }

    public function token(): ?Token
    {
        return $this->token;
    }

    public function pin(): string
    {
        return $this->pin;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function pocket(): string
    {
        return $this->pocket;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'bank' => $this->bank()?->toArray(),
            'credit' => $this->credit()?->toArray(),
            'token' => $this->token()?->toArray(),
            'pin' => $this->pin(),
            'password' => $this->password(),
            'pocket' => $this->pocket(),
        ]);
    }
}
