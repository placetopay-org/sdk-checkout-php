<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

enum DocumentType: string
{
    // Colombia
    case CC = 'CC';
    case CE = 'CE';
    case TI = 'TI';
    case RC = 'RC';
    case NIT = 'NIT';
    case RUT = 'RUT';

    // Generic
    case PPN = 'PPN';
    case TAX = 'TAX';
    case LIC = 'LIC';
    case CD = 'CD';

    // Country-specific
    case SSN = 'SSN';
    case CIP = 'CIP';
    case CPF = 'CPF';
    case CI = 'CI';
    case RUC = 'RUC';
    case DNI = 'DNI';
    case CRCPF = 'CRCPF';
    case CPJ = 'CPJ';
    case DIMEX = 'DIMEX';
    case DIDI = 'DIDI';
    case CLRUT = 'CLRUT';

    // Legacy aliases
    case TE = 'TE';
    case PP = 'PP';
    case PAS = 'PAS';
    case CEDULAJURIDICA = 'CEDULAJURIDICA';

    public static function fromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }
        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0) {
                return $case;
            }
        }
        return null;
    }
}
