<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\DataTransferBatch;
use App\Models\VariantOption;
use App\V1\Http\Requests\Web\Admin\StoreVariantOptionRequest;
use App\V1\Http\Requests\Web\Admin\UpdateVariantOptionRequest;
use App\V1\Services\VariantOptionService;
use App\V1\Services\VariantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VariantOptionController extends AdminController
{
    public function __construct(
        protected VariantOptionService $variantOptions,
        protected VariantService $variants,
    ) {}

    public function index(): View
    {
        return view('v1.admin.variant-options.index', array_merge([
            'variantOptions' => $this->variantOptions->getAllVariantOptions(),
        ], $this->transferSidebarData(
            DataTransferBatch::ENTITY_VARIANT_OPTIONS,
            'v1.admin.variant-options.import-export',
        )));
    }

    public function create(): View
    {
        return view('v1.admin.variant-options.create', [
            'variants' => $this->variants->getAllVariants(),
        ]);
    }

    public function store(StoreVariantOptionRequest $request): RedirectResponse
    {
        $this->variantOptions->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.variant-options.index', 'Variant option created successfully.');
    }

    public function show(VariantOption $variant_option): View
    {
        $variant_option->load('variant');

        return view('v1.admin.variant-options.show', [
            'variantOption' => $variant_option,
        ]);
    }

    public function edit(VariantOption $variant_option): View
    {
        return view('v1.admin.variant-options.edit', [
            'variantOption' => $variant_option,
            'variants' => $this->variants->getAllVariants(),
        ]);
    }

    public function update(UpdateVariantOptionRequest $request, VariantOption $variant_option): RedirectResponse
    {
        $this->variantOptions->update($variant_option, $request->validated());

        return $this->redirectWithSuccess('v1.admin.variant-options.index', 'Variant option updated successfully.');
    }

    public function destroy(VariantOption $variant_option): RedirectResponse
    {
        $this->variantOptions->delete($variant_option);

        return $this->redirectWithSuccess('v1.admin.variant-options.index', 'Variant option deleted successfully.');
    }
}
