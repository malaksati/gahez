<?php

namespace Tests\Unit;

use App\V1\Http\Requests\Rules\PhoneValidation;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    public function test_normalize_local_eleven_digit_phone_to_plus_twenty(): void
    {
        $this->assertSame('+201012345678', PhoneValidation::normalize('01012345678'));
    }

    public function test_normalize_international_plus_twenty_format(): void
    {
        $this->assertSame('+201012345678', PhoneValidation::normalize('+201012345678'));
        $this->assertSame('+201012345678', PhoneValidation::normalize('201012345678'));
    }

    public function test_normalize_ten_digit_mobile_without_leading_zero(): void
    {
        $this->assertSame('+201012345678', PhoneValidation::normalize('1012345678'));
    }

    public function test_normalize_rejects_invalid_lengths_and_prefixes(): void
    {
        $this->assertNull(PhoneValidation::normalize('50001111'));
        $this->assertNull(PhoneValidation::normalize('0101234567'));
        $this->assertNull(PhoneValidation::normalize('02012345678'));
        $this->assertNull(PhoneValidation::normalize('+201312345678'));
    }

    public function test_normalize_returns_null_for_empty_values(): void
    {
        $this->assertNull(PhoneValidation::normalize(null));
        $this->assertNull(PhoneValidation::normalize(''));
        $this->assertNull(PhoneValidation::normalize('   '));
    }
}
