<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\AmountBase;
use PlacetoPay\PlacetoPaySdk\Enums\TransactionAction;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;

class TransactionRequest extends Entity
{
    protected int $internalReference;
    protected TransactionAction $action;
    protected ?AmountBase $amount = null;

    /**
     * @throws PlacetoPayException
     */
    public function __construct(array $data = [])
    {
        if (!isset($data['internalReference'], $data['action'])) {
            throw PlacetoPayException::forDataNotProvided('The internalReference and the action are required');
        }

        $this->internalReference = $data['internalReference'];
        $this->action = $data['action'] instanceof TransactionAction
            ? $data['action']
            : TransactionAction::tryFrom($data['action']) ?? throw PlacetoPayException::forDataNotProvided('Unknown transaction action ' . $data['action']);
        $this->loadEntity($data['amount'] ?? null, 'amount', AmountBase::class);
    }

    public function internalReference(): int
    {
        return $this->internalReference;
    }

    public function action(): TransactionAction
    {
        return $this->action;
    }

    public function amount(): ?AmountBase
    {
        return $this->amount;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'internalReference' => $this->internalReference(),
            'action' => $this->action()->value,
            'amount' => $this->amount()?->toArray(),
        ]);
    }
}
