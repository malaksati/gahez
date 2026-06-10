<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreVariantRequest;
use App\V1\Http\Requests\Api\UpdateVariantRequest;
use App\V1\Http\Resources\Api\VariantResource;
use App\V1\Services\VariantService;

class VariantController extends Controller
{
    public function __construct(
        protected VariantService $variantService,
    ) {}

    public function store(StoreVariantRequest $request)
    {
        $variant = $this->variantService->create($request->validated());

        return (new VariantResource($variant))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateVariantRequest $request, int $id)
    {
        $variant = $this->variantService->getVariantById($id);

        $this->variantService->update($variant, $request->validated());

        return new VariantResource($variant->fresh());
    }

    public function destroy(int $id)
    {
        $variant = $this->variantService->getVariantById($id);

        $this->variantService->delete($variant);

        return response()->noContent();
    }
}
