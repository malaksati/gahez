<?php

namespace Tests\Unit;

use App\V1\DataTransfer\Support\ImportBooleanParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportBooleanParserTest extends TestCase
{
    #[DataProvider('trueValuesProvider')]
    public function test_parses_truthy_values(mixed $value): void
    {
        $this->assertTrue(ImportBooleanParser::parse($value, false));
    }

    #[DataProvider('falseValuesProvider')]
    public function test_parses_falsy_values(mixed $value): void
    {
        $this->assertFalse(ImportBooleanParser::parse($value, true));
    }

    public static function trueValuesProvider(): array
    {
        return [
            [1],
            [1.0],
            ['1'],
            ['TRUE'],
            ['true'],
            ['Yes'],
            ['active'],
            ['Active'],
            ['featured'],
            ['approved'],
            ['required'],
        ];
    }

    public static function falseValuesProvider(): array
    {
        return [
            [0],
            ['0'],
            ['FALSE'],
            ['false'],
            ['No'],
            ['inactive'],
            ['not active'],
            ['Not Active'],
            ['not featured'],
            ['pending'],
            ['not required'],
        ];
    }

    public function test_empty_string_uses_default(): void
    {
        $this->assertTrue(ImportBooleanParser::parse('', true));
        $this->assertFalse(ImportBooleanParser::parse('', false));
    }
}
