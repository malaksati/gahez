<?php

namespace Tests\Unit;

use App\V1\DataTransfer\Support\ImportSlugNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportSlugNormalizerTest extends TestCase
{
    #[DataProvider('normalizeProvider')]
    public function test_normalizes_malformed_slugs(string $input, string $expected): void
    {
        $this->assertSame($expected, ImportSlugNormalizer::normalize($input, 'fallback'));
    }

    public static function normalizeProvider(): array
    {
        return [
            ['my-product', 'my-product'],
            ['My Product', 'my-product'],
            ['my product', 'my-product'],
            ['SIZE', 'size'],
        ];
    }

    public function test_generates_from_name_when_slug_empty(): void
    {
        $this->assertSame('sweet-treats', ImportSlugNormalizer::fromNameSource(null, 'Sweet Treats'));
    }

    public function test_keeps_valid_explicit_slug(): void
    {
        $this->assertSame('custom-slug', ImportSlugNormalizer::fromNameSource('custom-slug', 'Other Name'));
    }
}
