<?php

namespace App\V1\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Product;
use App\Models\ProductRelation;
use App\Models\User;
use App\Models\WalletTransaction;
use App\V1\Repositories\ProductRepository;
use App\V1\Support\UploadStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        protected ProductRepository $products,
        protected ProductVariantService $productVariants,
    ) {}

    public function getAllProducts(): Collection
    {
        return $this->products->getAllProducts();
    }

    public function getPaginatedProducts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->products->getPaginatedProducts($perPage, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAdminIndexCategories(int $perPage, array $filters): LengthAwarePaginator
    {
        $query = Category::query();

        $productConstraint = function ($productQuery) use ($filters) {
            $productQuery->with(['brand', 'variants'])
                ->orderBy('sort_order')
                ->orderBy('id');
            $this->products->applyAdminListFilters($productQuery, $filters);
        };

        $query->with(['products' => $productConstraint]);

        if ($filters !== []) {
            $query->whereHas('products', function ($productQuery) use ($filters) {
                $this->products->applyAdminListFilters($productQuery, $filters);
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('id', $filters['category_id']);
        }

        return $query->orderBy('sort_order')->orderBy('id')->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAdminIndexUncategorizedProducts(int $perPage, array $filters): LengthAwarePaginator
    {
        return $this->uncategorizedProductsQuery($filters)->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listAdminIndexUncategorizedProducts(array $filters): Collection
    {
        return $this->uncategorizedProductsQuery($filters)->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    protected function uncategorizedProductsQuery(array $filters)
    {
        $query = Product::query()
            ->doesntHave('categories')
            ->with(['brand', 'variants']);

        $this->products->applyAdminListFilters($query, $filters);
        $this->products->applyAdminListSort($query, (string) ($filters['sort'] ?? 'latest'));

        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function countAdminIndexUncategorizedProducts(array $filters): int
    {
        $query = Product::query()->doesntHave('categories');

        $this->products->applyAdminListFilters($query, $filters);

        return $query->count();
    }

    public function generateNextSimpleSku(): string
    {
        $maxNumber = Product::query()
            ->where('sku', 'like', 'PRD-%')
            ->pluck('sku')
            ->map(static function (string $sku): ?int {
                return preg_match('/^PRD-(\d{4})$/', $sku, $matches) ? (int) $matches[1] : null;
            })
            ->filter()
            ->max();

        return sprintf('PRD-%04d', ((int) $maxNumber) + 1);
    }

    public function getProductById(int $id): Product
    {
        return $this->products->getProductById($id);
    }

    public function getProductBySlug(string $slug): Product
    {
        return $this->products->getProductBySlug($slug);
    }

    public function getActiveProducts(): Collection
    {
        return $this->products->getActiveProducts();
    }

    public function getFeaturedProducts(): Collection
    {
        return $this->products->getFeaturedProducts();
    }

    public function getNewProducts(int $limit = 10): Collection
    {
        return $this->products->getNewProducts($limit);
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->products->getProductsByCategory($categoryId);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateNextSimpleSku();
            }

            $sortOrder = isset($data['sort_order']) ? max(1, (int) $data['sort_order']) : null;
            $data['sort_order'] = $this->resolveSortOrderForCreate($sortOrder);

            return $this->products->create($data);
        });
    }

    public function update(Product $product, array $data): bool
    {
        return DB::transaction(function () use ($product, $data) {
            if (array_key_exists('sort_order', $data)) {
                $data['sort_order'] = $this->reassignSortOrder($product, (int) $data['sort_order']);
            }

            return $this->products->update($product, $data);
        });
    }

    protected function resolveSortOrderForCreate(?int $sortOrder): int
    {
        if ($sortOrder === null || $sortOrder < 1) {
            return ((int) Product::query()->max('sort_order')) + 1;
        }

        Product::query()
            ->where('sort_order', '>=', $sortOrder)
            ->increment('sort_order');

        return $sortOrder;
    }

    protected function reassignSortOrder(Product $product, int $newOrder): int
    {
        $newOrder = max(1, $newOrder);
        $oldOrder = (int) ($product->sort_order ?? 0);

        if ($oldOrder < 1) {
            Product::query()
                ->where('id', '!=', $product->id)
                ->where('sort_order', '>=', $newOrder)
                ->increment('sort_order');

            return $newOrder;
        }

        if ($oldOrder === $newOrder) {
            return $newOrder;
        }

        if ($newOrder < $oldOrder) {
            Product::query()
                ->where('id', '!=', $product->id)
                ->where('sort_order', '>=', $newOrder)
                ->where('sort_order', '<', $oldOrder)
                ->increment('sort_order');
        } else {
            Product::query()
                ->where('id', '!=', $product->id)
                ->where('sort_order', '>', $oldOrder)
                ->where('sort_order', '<=', $newOrder)
                ->decrement('sort_order');
        }

        return $newOrder;
    }

    public function toggleActive(Product $product): Product
    {
        $product->update(['is_active' => ! $product->is_active]);

        return $product->fresh();
    }

    public function toggleFeatured(Product $product): Product
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return $product->fresh();
    }

    public function toggleApproved(Product $product): Product
    {
        $product->update(['is_approved' => ! $product->is_approved]);

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        DB::transaction(function () use ($product): void {
            $this->retireOrdersForProduct($product);
            $product->update(['is_active' => false]);
        });

        return $this->products->delete($product);
    }

    public function forceDelete(Product $product): bool
    {
        $hasOrderItems = OrderItem::query()->where('product_id', $product->id)->exists();

        if ($hasOrderItems) {
            throw ValidationException::withMessages([
                'product' => ['Cannot permanently delete a product that exists in orders.'],
            ]);
        }

        return $this->products->forceDelete($product);
    }

    public function restore(Product $product): bool
    {
        return $this->products->restore($product);
    }

    public function search(string $search): Collection
    {
        return $this->products->search($search);
    }

    public function syncCategories(Product $product, array $categoryIds): void
    {
        $this->products->syncCategories($product, $categoryIds);
        $product->load('categories');
        $product->syncCategorySnapshot();
        $product->save();
    }

    public function attachCategory(Product $product, int $categoryId): void
    {
        $this->products->attachCategory($product, $categoryId);
        $product->load('categories');
        $product->syncCategorySnapshot();
        $product->save();
    }

    public function detachCategory(Product $product, int $categoryId): void
    {
        $this->products->detachCategory($product, $categoryId);
        $product->load('categories');
        $product->syncCategorySnapshot();
        $product->save();
    }

    /**
     * @param  list<int|string>  $relatedProductIds
     */
    public function syncRelatedProducts(Product $product, array $relatedProductIds): void
    {
        $ids = collect($relatedProductIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0 && $id !== $product->id)
            ->unique()
            ->values();

        ProductRelation::query()->where('product_id', $product->id)->delete();

        foreach ($ids as $relatedId) {
            ProductRelation::query()->create([
                'product_id' => $product->id,
                'related_product_id' => $relatedId,
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    public function storeImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $path = UploadStorage::store($image, 'products');
            $product->images()->create(['path' => $path]);
        }
    }

    public function syncImages(Product $product, Request $request): void
    {
        $keepIds = array_map('intval', $request->input('existing_images', []));

        $product->images()
            ->when($keepIds !== [], fn ($query) => $query->whereNotIn('id', $keepIds))
            ->when($keepIds === [], fn ($query) => $query)
            ->each(function ($image) {
                if ($image->path && Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            });

        if ($request->hasFile('images')) {
            $this->storeImages($product, $request->file('images'));
        }
    }

    public function applyThumbnailUpload(?Product $product, Request $request, array &$data): void
    {
        $file = $request->file('thumbnail');

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return;
        }

        if ($product?->exists) {
            $this->deleteStoredFile($product->getRawOriginal('thumbnail'));
        }

        $data['thumbnail'] = UploadStorage::store($file, 'products');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function syncProductVariants(Product $product, array $rows, string $productType, ?Request $request = null): void
    {
        $this->productVariants->syncForProduct($product, $rows, $productType, $request);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serializeProductVariantsForWizard(Product $product): array
    {
        return $this->productVariants->serializeForWizard($product);
    }

    public function extractPersistableData(array $validated): array
    {
        unset(
            $validated['category_ids'],
            $validated['related_products'],
            $validated['existing_images'],
            $validated['images'],
            $validated['thumbnail'],
            $validated['product_variants'],
        );

        if (array_key_exists('sort_order', $validated) && ($validated['sort_order'] === '' || $validated['sort_order'] === null)) {
            unset($validated['sort_order']);
        }

        if (array_key_exists('stock', $validated) && ($validated['stock'] === '' || $validated['stock'] === null)) {
            $validated['stock'] = null;
        }

        return $validated;
    }

    protected function deleteStoredFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function retireOrdersForProduct(Product $product): void
    {
        Order::query()
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->with('items')
            ->chunkById(100, function (Collection $orders): void {
                foreach ($orders as $order) {
                    $lockedOrder = Order::query()->lockForUpdate()->find($order->id);

                    if (! $lockedOrder) {
                        continue;
                    }

                    if ($lockedOrder->payment_status === 'paid') {
                        $this->cancelAndRefundOrderToWallet($lockedOrder);
                        continue;
                    }

                    $this->cancelUnpaidOrder($lockedOrder);
                }
            });
    }

    protected function cancelUnpaidOrder(Order $order): void
    {
        if ($order->status === 'cancelled') {
            return;
        }

        $fromStatus = $order->status;
        $order->update(['status' => 'cancelled']);

        OrderLog::query()->create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => 'cancelled',
            'from_status' => $fromStatus,
            'to_status' => 'cancelled',
            'payload' => ['trigger' => 'product_retired'],
        ]);
    }

    protected function cancelAndRefundOrderToWallet(Order $order): void
    {
        if ($order->payment_status === 'refunded' || $order->status === 'refunded') {
            return;
        }

        $user = User::query()->lockForUpdate()->find($order->user_id);

        if (! $user) {
            return;
        }

        $refundAmount = round((float) $order->total + (float) $order->wallet_used, 2);
        $fromStatus = $order->status;
        $fromPaymentStatus = $order->payment_status;

        if ($refundAmount > 0) {
            $user->wallet = round((float) $user->wallet + $refundAmount, 2);
            $user->save();

            WalletTransaction::query()->create([
                'user_id' => $user->id,
                'type' => 'addition',
                'amount' => $refundAmount,
                'balance_after' => $user->wallet,
                'notes' => 'Refund for retired product order #'.$order->id,
            ]);
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'refunded',
            'refund_status' => 'refunded',
            'refunded_total' => $refundAmount,
        ]);

        OrderLog::query()->create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => 'cancelled',
            'from_status' => $fromStatus,
            'to_status' => 'cancelled',
            'payload' => [
                'trigger' => 'product_retired',
                'refund_amount' => $refundAmount,
            ],
        ]);

        OrderLog::query()->create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => 'payment_change',
            'from_status' => $fromPaymentStatus,
            'to_status' => 'refunded',
            'payload' => [
                'trigger' => 'product_retired',
                'refund_amount' => $refundAmount,
            ],
        ]);
    }
}
