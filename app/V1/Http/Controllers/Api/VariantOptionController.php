<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreVariantOptionRequest;
use App\V1\Http\Requests\Api\UpdateVariantOptionRequest;
use App\V1\Http\Resources\Api\VariantOptionResource;
use App\V1\Services\VariantOptionService;

class VariantOptionController extends Controller
{
    public function __construct(
        protected VariantOptionService $variantOptionService,
    ) {}

    public function store(StoreVariantOptionRequest $request)
    {
        $variantOption = $this->variantOptionService->create($request->validated());

        return (new VariantOptionResource($variantOption->load('variant')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateVariantOptionRequest $request, int $id)
    {
        $variantOption = $this->variantOptionService->getVariantOptionById($id);

        $this->variantOptionService->update($variantOption, $request->validated());

        return new VariantOptionResource($variantOption->fresh(['variant']));
    }

    public function destroy(int $id)
    {
        $variantOption = $this->variantOptionService->getVariantOptionById($id);

        $this->variantOptionService->delete($variantOption);

        return response()->noContent();
    }
}
