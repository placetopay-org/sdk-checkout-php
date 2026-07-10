<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Internal;

/**
 * @internal
 */
final class JsonHelper
{
    private function __construct()
    {
    }

    /**
     * Recursively remove null values from an array.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function filterNulls(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_array($value)) {
                $filtered = self::filterNulls($value);
                if ($filtered !== [] || array_key_exists($key, $data)) {
                    $result[$key] = $filtered;
                }
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * JSON-encode a body array.
     *
     * @param array<string, mixed> $body
     */
    public static function encodeBody(array $body): string
    {
        $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json;
    }

    /**
     * Decode a JSON string into an array.
     *
     * @return array<string, mixed>
     */
    public static function decode(string $json): array
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \JsonException('Expected a JSON object, got: ' . gettype($data));
        }
        return $data;
    }
}
