<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Product;
use App\V1\Http\Controllers\Web\Admin\Concerns\PreparesProductFormData;
use App\V1\Http\Requests\Web\Admin\StoreProductRequest;
use App\V1\Http\Requests\Web\Admin\UpdateProductRequest;
use App\V1\Services\BrandService;
use App\V1\Services\CategoryService;
use App\V1\Services\ProductService;
use App\V1\Services\VariantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends AdminController
{
    use PreparesProductFormData;

    public function __construct(
        protected ProductService $products,
        protected BrandService $brands,
        protected CategoryService $categories,
        protected VariantService $variants,
    ) {}

    public function index(Request $request): View|Response
    {
        $filters = $this->listFilters($request, [
            'search', 'status', 'type', 'category_id', 'featured', 'sort', 'view',
        ]);

        $showAllProducts = ($filters['view'] ?? null) === 'all';
        $perPage = max(1, min(100, (int) $request->input('per_page', 20)));

        if ($showAllProducts) {
            $allProducts = $this->products->getPaginatedProducts($perPage, $filters);
            $categories = null;
            $uncategorizedCount = $this->products->countAdminIndexUncategorizedProducts($filters);
        } else {
            $allProducts = null;
            $categories = $this->products->getAdminIndexCategories(15, $filters);
            $uncategorizedProducts = $this->products->listAdminIndexUncategorizedProducts($filters);
            $uncategorizedCount = $uncategorizedProducts->count();
        }

        $viewData = [
            'categories' => $categories,
            'allProducts' => $allProducts,
            'uncategorizedProducts' => $uncategorizedProducts ?? collect(),
            'uncategorizedCount' => $uncategorizedCount,
            'showAllProducts' => $showAllProducts,
            'filterCategories' => $this->categories->getAllCategories(),
        ];

        return $this->adminListResponse(
            $request,
            'v1.admin.products.index',
            'v1.admin.products.partials.results',
            $viewData,
            $viewData,
        );
    }

    public function nextSku(): JsonResponse
    {
        return response()->json([
            'sku' => $this->products->generateNextSimpleSku(),
        ]);
    }

    public function create(): View
    {
        return view('v1.admin.products.create', array_merge([
            'brands' => $this->brands->getAllBrands(),
            'categories' => $this->categories->getActiveCategoriesTreeFlat(),
            'allProducts' => $this->products->getAllProducts(),
        ], $this->productFormViewData(null, $this->products, $this->variants)));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $this->products->extractPersistableData($request->validated());
        $categoryIds = $request->input('category_ids', []);
        $relatedProductIds = $request->input('related_products', []);

        $this->products->applyThumbnailUpload(null, $request, $data);
        $product = $this->products->create($data);

        if ($categoryIds !== []) {
            $this->products->syncCategories($product, $categoryIds);
        }

        $this->products->syncRelatedProducts($product, $relatedProductIds);

        if ($request->hasFile('images')) {
            $this->products->storeImages($product, $request->file('images'));
        }

        $this->products->syncProductVariants(
            $product,
            $request->input('product_variants', []),
            $data['type'] ?? 'simple',
            $request,
        );

        return $this->redirectWithSuccess('v1.admin.products.index', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load([
            'brand',
            'categories',
            'images',
            'variants.values.variantOption.variant',
            'relatedProducts.relatedProduct.brand',
        ]);

        return view('v1.admin.products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load(['categories', 'images', 'relatedProducts', 'variants.values.variantOption']);

        return view('v1.admin.products.edit', array_merge([
            'product' => $product,
            'brands' => $this->brands->getAllBrands(),
            'categories' => $this->categories->getActiveCategoriesTreeFlat(),
            'allProducts' => $this->products->getAllProducts()->where('id', '!=', $product->id),
        ], $this->productFormViewData($product, $this->products, $this->variants)));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->products->extractPersistableData($request->validated());
        $categoryIds = $request->input('category_ids');
        $relatedProductIds = $request->input('related_products', []);

        $this->products->applyThumbnailUpload($product, $request, $data);
        $this->products->update($product, $data);

        if ($categoryIds !== null) {
            $this->products->syncCategories($product, $categoryIds);
        }

        $this->products->syncRelatedProducts($product, $relatedProductIds);
        $this->products->syncImages($product, $request);
        $this->products->syncProductVariants(
            $product,
            $request->input('product_variants', []),
            $data['type'] ?? $product->type,
            $request,
        );

        return $this->redirectWithSuccess('v1.admin.products.index', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        $this->products->delete($product);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.Product deleted successfully.'),
            ]);
        }

        return $this->redirectWithSuccess('v1.admin.products.index', 'Product deleted successfully.');
    }

    public function toggleActive(Product $product): JsonResponse
    {
        $this->products->toggleActive($product);

        return response()->json([
            'success' => true,
            'message' => __('messages.Product status updated successfully.'),
        ]);
    }

    public function toggleFeatured(Product $product): JsonResponse
    {
        $this->products->toggleFeatured($product);

        return response()->json([
            'success' => true,
            'message' => __('messages.Product featured status updated successfully.'),
        ]);
    }

    public function toggleApproved(Product $product): JsonResponse
    {
        $this->products->toggleApproved($product);

        return response()->json([
            'success' => true,
            'message' => __('messages.Product approval status updated successfully.'),
        ]);
    }

    public function quickStoreCatalogVariant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
        ]);

        $variant = $this->variants->create([
            'name' => [
                'en' => trim($validated['name']['en']),
                'ar' => trim($validated['name']['ar']),
            ],
            'is_active' => true,
            'is_required' => false,
        ]);

        return response()->json([
            'variant' => $this->serializeCatalogVariant($variant->load('options')),
        ]);
    }

    public function quickStoreVariantOption(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
        ]);

        $variant = $this->variants->getVariantById((int) $validated['variant_id']);
        $nameEn = trim($validated['name']['en']);
        $nameAr = trim($validated['name']['ar']);

        $option = $variant->options()->create([
            'name' => [
                'en' => $nameEn,
                'ar' => $nameAr,
            ],
            'code' => \Illuminate\Support\Str::slug($nameEn !== '' ? $nameEn : $nameAr) ?: 'option-'.uniqid(),
        ]);

        return response()->json([
            'option' => $this->serializeCatalogOption($option),
            'variant_id' => $variant->id,
        ]);
    }
}
