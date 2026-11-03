<?php

namespace PlacetoPay\PlacetoPaySdk\Entities;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;

class CreditOption extends Entity
{
    protected string $code = '';
    protected string $type = '';
    protected string $groupCode = '';
    protected string $description = '';
    /**
     * @var int[]
     */
    protected array $installments = [];

    public function __construct(array $data = [])
    {
        $this->load($data, ['code', 'type', 'groupCode', 'description']);
        $this->installments = array_map('intval', $data['installments'] ?? []);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function groupCode(): string
    {
        return $this->groupCode;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return int[]
     */
    public function installments(): array
    {
        return $this->installments;
    }

    public function toCredit(int $installment): Credit
    {
        return new Credit([
            'code' => $this->code(),
            'type' => $this->type(),
            'groupCode' => $this->groupCode(),
            'installment' => $installment,
        ]);
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'code' => $this->code(),
            'type' => $this->type(),
            'groupCode' => $this->groupCode(),
            'description' => $this->description(),
            'installments' => $this->installments(),
        ]);
    }
}
