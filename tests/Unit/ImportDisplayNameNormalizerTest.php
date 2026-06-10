<?php

namespace Tests\Unit;

use App\V1\DataTransfer\Support\ImportDisplayNameNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportDisplayNameNormalizerTest extends TestCase
{
    #[DataProvider('englishNameProvider')]
    public function test_normalizes_english_names(string $input, string $expected): void
    {
        $this->assertSame($expected, ImportDisplayNameNormalizer::name($input));
    }

    public static function englishNameProvider(): array
    {
        return [
            ['size', 'Size'],
            ['Size', 'Size'],
            ['SIZE', 'Size'],
            ['  color  ', 'Color'],
        ];
    }

    public function test_leaves_arabic_names_unchanged(): void
    {
        $arabic = 'القياس';

        $this->assertSame($arabic, ImportDisplayNameNormalizer::name($arabic, 'ar'));
    }
}
