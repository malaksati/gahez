<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\OrderRefundRequest;
use App\V1\Http\Requests\Web\Admin\UpdateOrderRefundRequestRequest;
use App\V1\Services\OrderRefundRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderRefundRequestController extends AdminController
{
    public function __construct(
        protected OrderRefundRequestService $refundRequests,
    ) {}

    public function index(Request $request): View|Response
    {
        $refundRequests = $this->refundRequests->getPaginated(15, $this->listFilters($request, [
            'search', 'status', 'from_date', 'to_date', 'sort',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.order-refund-requests.index',
            'v1.admin.order-refund-requests.partials.results',
            ['refundRequests' => $refundRequests],
            ['refundRequests' => $refundRequests],
        );
    }

    public function show(OrderRefundRequest $orderRefundRequest): View
    {
        return view('v1.admin.order-refund-requests.show', [
            'refundRequest' => $this->refundRequests->getById($orderRefundRequest->id),
        ]);
    }

    public function edit(OrderRefundRequest $orderRefundRequest): View
    {
        return view('v1.admin.order-refund-requests.edit', [
            'refundRequest' => $this->refundRequests->getById($orderRefundRequest->id),
        ]);
    }

    public function update(UpdateOrderRefundRequestRequest $request, OrderRefundRequest $orderRefundRequest): RedirectResponse
    {
        $this->refundRequests->update($orderRefundRequest, $request->validated());

        return $this->redirectWithSuccess(
            'v1.admin.order-refund-requests.index',
            __('messages.Refund request updated successfully.'),
        );
    }

    public function approve(OrderRefundRequest $orderRefundRequest): RedirectResponse
    {
        try {
            $this->refundRequests->approve($orderRefundRequest);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.Refund request approved successfully.'));
    }

    public function reject(OrderRefundRequest $orderRefundRequest): RedirectResponse
    {
        try {
            $this->refundRequests->reject($orderRefundRequest);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.Refund request rejected successfully.'));
    }
}
