<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PlaceToPay\Checkout\Models\Common\DocumentType;
use PlaceToPay\Checkout\Models\Common\DocumentValidator;

class DocumentValidatorTest extends TestCase
{
    public function testBusinessDocumentTypes(): void
    {
        $businessTypes = [
            DocumentType::NIT,
            DocumentType::RUT,
            DocumentType::RUC,
            DocumentType::CLRUT,
            DocumentType::CEDULAJURIDICA,
            DocumentType::CPJ,
        ];

        foreach ($businessTypes as $type) {
            $this->assertTrue(
                DocumentValidator::isBusinessDocument($type),
                "{$type->value} should be a business document"
            );
            $this->assertFalse(
                DocumentValidator::isPersonDocument($type),
                "{$type->value} should not be a person document"
            );
        }
    }

    public function testPersonDocumentTypes(): void
    {
        $personTypes = [
            DocumentType::CC,
            DocumentType::CE,
            DocumentType::TI,
            DocumentType::PPN,
            DocumentType::DNI,
            DocumentType::CPF,
            DocumentType::CI,
        ];

        foreach ($personTypes as $type) {
            $this->assertTrue(
                DocumentValidator::isPersonDocument($type),
                "{$type->value} should be a person document"
            );
            $this->assertFalse(
                DocumentValidator::isBusinessDocument($type),
                "{$type->value} should not be a business document"
            );
        }
    }

    public function testValidColombianCC(): void
    {
        $this->assertTrue(DocumentValidator::isValid(DocumentType::CC, '12345678'));
        $this->assertTrue(DocumentValidator::isValid(DocumentType::CC, '1234567'));
        $this->assertTrue(DocumentValidator::isValid(DocumentType::CC, '1234'));
    }

    public function testInvalidColombianCC(): void
    {
        $this->assertFalse(DocumentValidator::isValid(DocumentType::CC, '123')); // too short
        $this->assertFalse(DocumentValidator::isValid(DocumentType::CC, '0123456')); // starts with 0
    }

    public function testValidNIT(): void
    {
        $this->assertTrue(DocumentValidator::isValid(DocumentType::NIT, '9000001234'));
    }

    public function testValidCPF(): void
    {
        $this->assertTrue(DocumentValidator::isValid(DocumentType::CPF, '12345678901')); // 11 digits
        $this->assertTrue(DocumentValidator::isValid(DocumentType::CPF, '1234567890')); // 10 digits
    }

    public function testValidEcuadorCI(): void
    {
        $this->assertTrue(DocumentValidator::isValid(DocumentType::CI, '1234567890')); // exactly 10 digits
        $this->assertFalse(DocumentValidator::isValid(DocumentType::CI, '12345678')); // 8 digits
        $this->assertFalse(DocumentValidator::isValid(DocumentType::CI, '12345678901')); // 11 digits
    }

    public function testDocumentTypeFromString(): void
    {
        $this->assertSame(DocumentType::CC, DocumentType::fromString('CC'));
        $this->assertSame(DocumentType::CC, DocumentType::fromString('cc'));
        $this->assertNull(DocumentType::fromString('UNKNOWN_TYPE'));
        $this->assertNull(DocumentType::fromString(null));
    }
}
