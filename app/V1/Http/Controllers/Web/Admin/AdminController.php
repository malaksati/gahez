<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\V1\Services\DataTransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class AdminController extends Controller
{
    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    protected function listFilters(Request $request, array $keys): array
    {
        return array_filter(
            $request->only($keys),
            static fn ($value) => $value !== null && $value !== '',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    /**
     * @param  array<string, mixed>  $viewData
     * @param  array<string, mixed>  $resultsData
     */
    protected function adminListResponse(
        Request $request,
        string $indexView,
        string $resultsView,
        array $viewData,
        array $resultsData = [],
    ): View|Response {
        if ($request->header('X-Admin-List-Filter')) {
            return response()->view($resultsView, $resultsData !== [] ? $resultsData : $viewData);
        }

        return view($indexView, $viewData);
    }

    protected function redirectWithSuccess(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)->with('success', $message);
    }

    protected function redirectBackWithSuccess(string $message): RedirectResponse
    {
        return back()->with('success', $message);
    }

    /**
     * @return array{importBatches: \Illuminate\Support\Collection, exportBatches: \Illuminate\Support\Collection, showRoutePrefix: string, downloadRoutePrefix: string}
     */
    protected function transferSidebarData(string $entity, string $routePrefix): array
    {
        $grouped = app(DataTransferService::class)->recentBatchesGrouped($entity);

        return [
            'importBatches' => $grouped['import'],
            'exportBatches' => $grouped['export'],
            'showRoutePrefix' => $routePrefix,
            'downloadRoutePrefix' => $routePrefix,
        ];
    }
}
