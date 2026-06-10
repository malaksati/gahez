<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Brand;
use App\V1\Http\Requests\Web\Admin\StoreBrandRequest;
use App\V1\Http\Requests\Web\Admin\UpdateBrandRequest;
use App\V1\Services\BrandService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BrandController extends AdminController
{
    public function __construct(
        protected BrandService $brands,
    ) {}

    public function index(Request $request): View|Response
    {
        $brands = $this->brands->getPaginatedBrands(15, $this->listFilters($request, [
            'search', 'sort',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.brands.index',
            'v1.admin.brands.partials.results',
            ['brands' => $brands],
            ['brands' => $brands],
        );
    }

    public function create(): View
    {
        return view('v1.admin.brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $this->brands->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.brands.index', 'Brand created successfully.');
    }

    public function show(Brand $brand): View
    {
        return view('v1.admin.brands.show', [
            'brand' => $brand,
        ]);
    }

    public function edit(Brand $brand): View
    {
        return view('v1.admin.brands.edit', [
            'brand' => $brand,
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brands->update($brand, $request->validated());

        return $this->redirectWithSuccess('v1.admin.brands.index', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->brands->delete($brand);

        return $this->redirectWithSuccess('v1.admin.brands.index', 'Brand deleted successfully.');
    }
}
