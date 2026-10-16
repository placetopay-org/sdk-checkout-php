<?php

namespace PlacetoPay\PlacetoPaySdk\Contracts;

use PlacetoPay\PlacetoPaySdk\Helpers\ArrayHelper;
use PlacetoPay\PlacetoPaySdk\Traits\LoaderTrait;

abstract class Entity
{
    use LoaderTrait;

    /**
     * Extracts the information for the entity.
     * @return array
     */
    abstract public function toArray(): array;

    protected function loadEntity($data, string $attribute, string $class): self
    {
        if ($data) {
            if (is_array($data)) {
                $data = new $class($data);
            }
            if (!($data instanceof $class)) {
                $data = null;
            }
            $this->{$attribute} = $data;
        }

        return $this;
    }

    protected function arrayFilter(array $array): array
    {
        return ArrayHelper::filter($array);
    }
}
