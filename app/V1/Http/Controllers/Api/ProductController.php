<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreProductRequest;
use App\V1\Http\Requests\Api\UpdateProductRequest;
use App\V1\Http\Resources\Api\ProductResource;
use App\V1\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        return ProductResource::collection(
            $this->productService->getPaginatedProducts(
                $perPage,
                $this->productFilters($request)
            )
        );
    }

    public function show(int $id)
    {
        return new ProductResource(
            $this->productService->getProductById($id)
        );
    }

    public function showBySlug(string $slug)
    {
        return new ProductResource(
            $this->productService->getProductBySlug($slug)
        );
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $product = $this->productService->create($data);

        if ($categoryIds !== null) {
            $this->productService->syncCategories($product, $categoryIds);
        }

        return (new ProductResource($product->load(['categories', 'images', 'variants'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = $this->productService->getProductById($id);
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $this->productService->update($product, $data);

        if ($categoryIds !== null) {
            $this->productService->syncCategories($product, $categoryIds);
        }

        return new ProductResource(
            $product->fresh(['categories', 'images', 'variants'])
        );
    }

    public function destroy(int $id)
    {
        $product = $this->productService->getProductById($id);

        $this->productService->delete($product);

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    protected function productFilters(Request $request): array
    {
        return $request->only([
            'search',
            'status',
            'featured',
            'approved',
            'type',
            'category_id',
            'min_price',
            'max_price',
            'stock',
            'is_new',
            'is_bookable',
            'sort',
        ]);
    }
}
