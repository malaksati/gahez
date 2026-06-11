<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocalizeDigitsTest extends TestCase
{
    public function test_localize_digits_converts_when_arabic_locale(): void
    {
        App::setLocale('ar');

        $this->assertSame('١٬٢٣٤٫٥٠', format_local_number(1234.5, 2));
        $this->assertSame('عدد ٥ عناصر', localize_digits('عدد 5 عناصر'));
    }

    public function test_format_local_number_keeps_western_digits_for_english(): void
    {
        App::setLocale('en');

        $this->assertSame('1,234.50', format_local_number(1234.5, 2));
        $this->assertSame('items 5', localize_digits('items 5'));
    }

    public function test_local_num_matches_format_local_number(): void
    {
        App::setLocale('ar');

        $this->assertSame(local_num(42), format_local_number(42));
    }

    public function test_display_currency_shows_egp_symbol_in_arabic(): void
    {
        App::setLocale('ar');

        $this->assertSame('ج.م', display_currency('EGP'));
        $this->assertSame('USD', display_currency('USD'));
    }

    public function test_display_currency_shows_le_for_english_egp(): void
    {
        App::setLocale('en');

        $this->assertSame('L.E.', display_currency('EGP'));
        $this->assertSame('USD', display_currency('USD'));
    }
}
