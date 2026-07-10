<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

final class DocumentValidator
{
    private function __construct()
    {
    }

    /** Document types that identify a business rather than a natural person. */
    private const BUSINESS_TYPES = [
        DocumentType::NIT,
        DocumentType::RUT,
        DocumentType::RUC,
        DocumentType::CLRUT,
        DocumentType::CEDULAJURIDICA,
        DocumentType::CPJ,
    ];

    /** Document types that identify a natural person. */
    private const PERSON_TYPES = [
        DocumentType::CC,
        DocumentType::CE,
        DocumentType::TI,
        DocumentType::RC,
        DocumentType::PPN,
        DocumentType::TAX,
        DocumentType::LIC,
        DocumentType::SSN,
        DocumentType::CIP,
        DocumentType::CPF,
        DocumentType::CI,
        DocumentType::DNI,
        DocumentType::CRCPF,
        DocumentType::DIMEX,
        DocumentType::DIDI,
        DocumentType::PP,
        DocumentType::PAS,
        DocumentType::TE,
    ];

    /** Regex patterns for validation (ported from Java / PHP reference). */
    private const PATTERNS = [
        'CC' => '/^[1-9][0-9]{3,9}$/',
        'CE' => '/^([a-zA-Z]{1,5})?[1-9][0-9]{3,7}$/',
        'TI' => '/^[1-9][0-9]{4,11}$/',
        'NIT' => '/^[1-9]\d{6,9}$/',
        'RUT' => '/^[1-9]\d{6,9}$/',
        'PPN' => '/^[a-zA-Z0-9_]{4,16}$/',
        'TAX' => '/^[a-zA-Z0-9_]{4,16}$/',
        'LIC' => '/^[a-zA-Z0-9_]{4,16}$/',
        'SSN' => '/^\d{3}\d{2,3}\d{4}$/',
        'CIP' => '/^(PE|E|N|[23456789](?:AV|PI)?|1[0123]?(?:AV|PI)?)-?(\d{1,4})-?(\d{1,6})$/',
        'CPF' => '/^\d{10,11}$/',
        'CI' => '/^\d{10}$/',
        'RUC' => '/^\d{13}$/',
        'DNI' => '/^\d{8}$/',
        'CRCPF' => '/^[1-9][0-9]{8}$/',
        'CPJ' => '/^[1-9][0-9]{9}$/',
        'DIMEX' => '/^[1-9][0-9]{10,11}$/',
        'DIDI' => '/^[1-9][0-9]{10,11}$/',
        'CLRUT' => '/^(\d{1,2}(?:\.?\d{1,3}){2}-[\dkK])$/',
    ];

    public static function isBusinessDocument(DocumentType $type): bool
    {
        return in_array($type, self::BUSINESS_TYPES, true);
    }

    public static function isPersonDocument(DocumentType $type): bool
    {
        return in_array($type, self::PERSON_TYPES, true);
    }

    public static function isValid(DocumentType $type, string $document): bool
    {
        $pattern = self::PATTERNS[$type->value] ?? null;
        if ($pattern === null) {
            // Known type with no pattern — pass through
            return true;
        }
        return (bool) preg_match($pattern, $document);
    }
}
