<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreBrandRequest;
use App\V1\Http\Requests\Api\UpdateBrandRequest;
use App\V1\Http\Resources\Api\BrandResource;
use App\V1\Services\BrandService;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $brandService,
    ) {}

    public function index()
    {
        return BrandResource::collection(
            $this->brandService->getAllBrands()
        );
    }

    public function show(int $id)
    {
        return new BrandResource(
            $this->brandService->getBrandById($id)
        );
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->create($request->validated());

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBrandRequest $request, int $id)
    {
        $brand = $this->brandService->getBrandById($id);

        $this->brandService->update($brand, $request->validated());

        return new BrandResource($brand->fresh());
    }

    public function destroy(int $id)
    {
        $brand = $this->brandService->getBrandById($id);

        $this->brandService->delete($brand);

        return response()->noContent();
    }
}
