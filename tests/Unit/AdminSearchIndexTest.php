<?php

namespace Tests\Unit;

use App\V1\Support\AdminSearchIndex;
use Tests\TestCase;

class AdminSearchIndexTest extends TestCase
{
    public function test_filter_pages_finds_help_entries(): void
    {
        $help = AdminSearchIndex::filterPages('help guide');
        $this->assertNotEmpty($help);
        $this->assertStringContainsString('help', mb_strtolower($help[0]['url']));
    }

    public function test_filter_pages_finds_import_export_entries(): void
    {
        $imports = AdminSearchIndex::filterPages('import products');
        $this->assertNotEmpty($imports);

        $exports = AdminSearchIndex::filterPages('export categories');
        $this->assertNotEmpty($exports);
    }

    public function test_pages_index_includes_variant_options(): void
    {
        $urls = collect(AdminSearchIndex::pages())->pluck('url')->all();

        $this->assertTrue(collect($urls)->contains(fn (string $url) => str_contains($url, 'variant-options')));
    }
}
