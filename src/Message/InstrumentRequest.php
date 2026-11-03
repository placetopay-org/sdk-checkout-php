<?php

namespace PlacetoPay\PlacetoPaySdk\Message;

use PlacetoPay\PlacetoPaySdk\Contracts\Entity;
use PlacetoPay\PlacetoPaySdk\Entities\Instrument;
use PlacetoPay\PlacetoPaySdk\Exceptions\PlacetoPayException;
use PlacetoPay\PlacetoPaySdk\Helpers\ArrayHelper;

class InstrumentRequest extends Entity
{
    protected Instrument $instrument;
    protected string $locale = '';

    /**
     * @throws PlacetoPayException
     */
    public function __construct(array $data = [])
    {
        $this->loadEntity($data['instrument'] ?? null, 'instrument', Instrument::class);

        $token = isset($this->instrument) ? $this->instrument->token() : null;
        if (!$token?->token() && !$token?->subtoken()) {
            throw PlacetoPayException::forDataNotProvided('The instrument token or subtoken is required');
        }

        $this->load($data, ['locale']);
    }

    public function instrument(): Instrument
    {
        return $this->instrument;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'instrument' => ['token' => ArrayHelper::only($this->instrument()->token()->toArray(), ['token', 'subtoken'])],
            'locale' => $this->locale(),
        ]);
    }
}
