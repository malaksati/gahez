<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\V1\Exports\Reports\ReportArrayExport;
use App\V1\Http\Requests\Web\Admin\Reports\AnalyticsReportRequest;
use App\V1\Services\AnalyticsReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends AdminController
{
    private const REPORT_TYPES = [
        'customers',
        'customer-segments',
        'sales-period',
        'sales-payment-methods',
        'top-products-categories',
        'stock',
    ];

    public function __construct(
        protected AnalyticsReportService $analytics,
    ) {}

    public function index(): View
    {
        return view('v1.admin.reports.index', [
            'reports' => $this->analytics->availableReports(),
            'charts' => $this->analytics->chartOverview(),
        ]);
    }

    public function show(string $type, AnalyticsReportRequest $request): View
    {
        $this->assertValidReportType($type);

        $filters = $request->resolvedFilters();

        return view('v1.admin.reports.show', [
            'type' => $type,
            'report' => $this->analytics->build($type, $filters),
            'filters' => $filters,
            'reports' => $this->analytics->availableReports(),
        ]);
    }

    public function export(string $type, AnalyticsReportRequest $request): BinaryFileResponse
    {
        $this->assertValidReportType($type);

        $report = $this->analytics->build($type, $request->resolvedFilters());
        $filename = Str::slug($type).'-report-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new ReportArrayExport($report['headings'], $report['rows'], $report['title']),
            $filename,
        );
    }

    public function exportPdf(string $type, AnalyticsReportRequest $request): Response
    {
        $this->assertValidReportType($type);

        $filters = $request->resolvedFilters();
        $report = $this->analytics->build($type, $filters);
        $filename = Str::slug($type).'-report-'.now()->format('Y-m-d').'.pdf';

        $pdf = PDF::loadView('v1.admin.reports.pdf', [
            'report' => $report,
            'filters' => $filters,
            'type' => $type,
            'asPdf' => true,
        ], [], $this->pdfConfig());

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function earnings(AnalyticsReportRequest $request): RedirectResponse
    {
        return redirect()->route('v1.admin.reports.show', array_merge(
            ['type' => 'sales-period'],
            $request->query(),
        ));
    }

    public function productPerformance(AnalyticsReportRequest $request): RedirectResponse
    {
        return redirect()->route('v1.admin.reports.show', array_merge(
            ['type' => 'top-products-categories'],
            $request->query(),
        ));
    }

    protected function assertValidReportType(string $type): void
    {
        if (! in_array($type, self::REPORT_TYPES, true)) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function pdfConfig(): array
    {
        $logo = setting('app_logo');
        $logoPath = is_string($logo) && $logo !== ''
            ? public_path('storage/'.$logo)
            : null;
        $hasWatermarkLogo = $logoPath !== null && is_file($logoPath);

        $pdfConfig = array_merge(config('pdf'), [
            'margin_bottom' => 22,
        ]);

        $pdfConfig['instanceConfigurator'] = function ($mpdf) use ($hasWatermarkLogo, $logoPath): void {
            if ($hasWatermarkLogo && $logoPath !== null) {
                $mpdf->SetWatermarkImage($logoPath, 0.07, 'D', 'P');
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true;
            }

            $mpdf->setAutoBottomMargin = 'pad';
            $mpdf->margin_footer = 12;
        };

        return $pdfConfig;
    }
}
