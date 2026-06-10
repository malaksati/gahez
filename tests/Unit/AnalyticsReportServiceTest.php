<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Models\User;
use App\V1\Http\Requests\Web\Admin\Reports\AnalyticsReportRequest;
use App\V1\Services\AnalyticsReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsReportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AnalyticsReportService::class);
    }

    public function test_customers_report_lists_customers(): void
    {
        User::factory()->create(['role' => 'user', 'name' => 'Customer A']);
        User::factory()->create(['role' => 'admin', 'name' => 'Admin']);

        $report = $this->service->build('customers');

        $this->assertSame(__('messages.Customers report'), $report['title']);
        $this->assertCount(4, $report['headings']);
        $this->assertCount(1, $report['rows']);
        $this->assertSame('Customer A', $report['rows'][0][0]);
        $this->assertSame(1, $report['summary']['total']);
    }

    public function test_customer_segments_report_uses_threshold_settings(): void
    {
        Setting::query()->updateOrCreate(['key' => 'report_hero_order_amount'], ['value' => '150', 'type' => 'number']);
        Setting::query()->updateOrCreate(['key' => 'report_lower_value_order_amount'], ['value' => '15', 'type' => 'number']);

        User::factory()->create(['role' => 'user']);

        $report = $this->service->build('customer-segments', [
            'hero_amount' => 150,
            'lower_value_amount' => 15,
        ]);

        $this->assertSame(150.0, $report['summary']['hero_threshold']);
        $this->assertSame(15.0, $report['summary']['lower_value_threshold']);
        $this->assertCount(1, $report['rows']);
    }

    public function test_available_reports_returns_six_entries(): void
    {
        $this->assertCount(6, $this->service->availableReports());
    }

    public function test_chart_overview_returns_thirty_day_series(): void
    {
        $charts = $this->service->chartOverview();

        $this->assertCount(30, $charts['revenue_trend']['labels']);
        $this->assertCount(30, $charts['revenue_trend']['values']);
        $this->assertCount(30, $charts['orders_trend']['values']);
        $this->assertArrayHasKey('payment_methods', $charts);
        $this->assertArrayHasKey('top_products', $charts);
    }

    public function test_daily_period_uses_current_day_only(): void
    {
        $report = $this->service->build('sales-payment-methods', ['period_type' => 'daily']);
        $today = now()->toDateString();

        $this->assertSame($today, $report['meta']['from']);
        $this->assertSame($today, $report['meta']['to']);
    }

    public function test_weekly_period_uses_last_seven_days(): void
    {
        $report = $this->service->build('sales-payment-methods', ['period_type' => 'weekly']);

        $this->assertSame(now()->subDays(6)->toDateString(), $report['meta']['from']);
        $this->assertSame(now()->toDateString(), $report['meta']['to']);
    }

    public function test_monthly_period_uses_last_thirty_days(): void
    {
        $report = $this->service->build('sales-payment-methods', ['period_type' => 'monthly']);

        $this->assertSame(now()->subMonth()->toDateString(), $report['meta']['from']);
        $this->assertSame(now()->toDateString(), $report['meta']['to']);
    }

    public function test_resolved_filters_include_active_period_dates(): void
    {
        $request = AnalyticsReportRequest::create('/admin/reports/sales-period', 'GET', [
            'period_type' => 'monthly',
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        $filters = $request->resolvedFilters();

        $this->assertSame('monthly', $filters['period_type']);
        $this->assertSame(now()->subMonth()->toDateString(), $filters['resolved_from']);
        $this->assertSame(now()->toDateString(), $filters['resolved_to']);
        $this->assertNull($filters['from_date']);
    }
}
