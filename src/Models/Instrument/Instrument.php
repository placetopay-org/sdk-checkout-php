<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Instrument;

readonly class Instrument
{
    public function __construct(
        public ?Token $token = null,
        public ?Card $card = null,
        public ?Credit $credit = null,
        public ?Bank $bank = null,
    ) {
    }

    public static function ofToken(Token $token): self
    {
        return new self(token: $token);
    }

    public static function ofCard(Card $card): self
    {
        return new self(card: $card);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->token !== null) {
            $data['token'] = $this->token->toArray();
        }
        if ($this->card !== null) {
            $data['card'] = $this->card->toArray();
        }
        if ($this->credit !== null) {
            $data['credit'] = $this->credit->toArray();
        }
        if ($this->bank !== null) {
            $data['bank'] = $this->bank->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            token: isset($data['token']) ? Token::fromArray($data['token']) : null,
            card: isset($data['card']) ? Card::fromArray($data['card']) : null,
            credit: isset($data['credit']) ? Credit::fromArray($data['credit']) : null,
            bank: isset($data['bank']) ? Bank::fromArray($data['bank']) : null,
        );
    }
}
