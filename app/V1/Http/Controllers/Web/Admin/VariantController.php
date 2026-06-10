<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Variant;
use App\V1\Http\Requests\Web\Admin\StoreVariantRequest;
use App\V1\Http\Requests\Web\Admin\UpdateVariantRequest;
use App\V1\Services\VariantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VariantController extends AdminController
{
    public function __construct(
        protected VariantService $variants,
    ) {}

    public function index(Request $request): View|Response
    {
        $variants = $this->variants->getPaginatedVariants(15, $this->listFilters($request, [
            'search', 'status', 'required', 'sort',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.variants.index',
            'v1.admin.variants.partials.results',
            ['variants' => $variants],
            ['variants' => $variants],
        );
    }

    public function create(): View
    {
        return view('v1.admin.variants.create');
    }

    public function store(StoreVariantRequest $request): RedirectResponse
    {
        $this->variants->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.variants.index', 'Variant created successfully.');
    }

    public function show(Variant $variant): View
    {
        $variant->load('options')->loadCount('options');

        return view('v1.admin.variants.show', [
            'variant' => $variant,
        ]);
    }

    public function edit(Variant $variant): View
    {
        $variant->load('options');

        return view('v1.admin.variants.edit', [
            'variant' => $variant,
        ]);
    }

    public function update(UpdateVariantRequest $request, Variant $variant): RedirectResponse
    {
        $this->variants->update($variant, $request->validated());

        return $this->redirectWithSuccess('v1.admin.variants.index', 'Variant updated successfully.');
    }

    public function destroy(Variant $variant): RedirectResponse
    {
        $this->variants->delete($variant);

        return $this->redirectWithSuccess('v1.admin.variants.index', 'Variant deleted successfully.');
    }
}
