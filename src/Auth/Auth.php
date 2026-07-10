<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Auth;

readonly class Auth
{
    /**
     * @param array<string, string>|null $additional
     */
    public function __construct(
        public string $login,
        public string $tranKey,
        public string $nonce,
        public string $seed,
        public ?array $additional = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'login' => $this->login,
            'tranKey' => $this->tranKey,
            'nonce' => $this->nonce,
            'seed' => $this->seed,
        ];

        if ($this->additional !== null) {
            $data['additional'] = $this->additional;
        }

        return $data;
    }
}
