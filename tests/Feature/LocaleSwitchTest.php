<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_switch_redirects_to_requested_path_with_query(): void
    {
        $response = $this->get('/locale/ar?redirect=/admin/reports?period_type=weekly');

        $response->assertRedirect('/admin/reports?period_type=weekly');
        $response->assertCookie('locale', 'ar');
        $this->assertSame('ar', session('locale'));
    }

    public function test_locale_switch_rejects_external_redirect(): void
    {
        $response = $this->get('/locale/en?redirect=//evil.test/phish');

        $response->assertRedirect(route('home'));
    }
}
