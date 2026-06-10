<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\ProductReport;
use App\V1\Services\ProductReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductReportController extends AdminController
{
    public function __construct(
        protected ProductReportService $reports,
    ) {}

    public function index(Request $request): View|Response
    {
        $reports = $this->reports->getPaginated(15, $this->listFilters($request, [
            'search', 'status', 'product_id',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.product-reports.index',
            'v1.admin.product-reports.partials.results',
            ['reports' => $reports],
            ['reports' => $reports],
        );
    }

    public function updateStatus(ProductReport $productReport, string $status): RedirectResponse
    {
        $allowed = ['pending', 'reviewed', 'ignored'];

        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', __('messages.Invalid status.'));
        }

        $data = ['status' => $status];

        if ($status !== 'pending') {
            $data['handled_by'] = Auth::id();
            $data['handled_at'] = now();
        } else {
            $data['handled_by'] = null;
            $data['handled_at'] = null;
        }

        $this->reports->update($productReport, $data);

        return $this->redirectWithSuccess('v1.admin.product-reports.index', 'Report status updated successfully.');
    }
}
