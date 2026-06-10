<?php

namespace App\V1\Http\Resources\Api;

use App\Models\Product;
use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    public bool $excludeNestedRelated = false;

    public function withoutNestedRelated(): static
    {
        $this->excludeNestedRelated = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $categorySnapshots = collect($this->category_snapshot ?? [])
            ->map(fn ($row) => [
                'id' => $row['id'] ?? null,
                'name' => $request->getLocale() === 'ar'
                    ? ($row['name_ar'] ?? ($row['name'] ?? null))
                    : ($row['name'] ?? ($row['name_ar'] ?? null)),
                'name_ar' => $row['name_ar'] ?? ($row['name'] ?? null),
                'is_snapshot' => true,
            ])
            ->values();
        $brandSnapshot = is_array($this->brand_snapshot)
            ? [
                'id' => $this->brand_snapshot['id'] ?? null,
                'name' => $request->getLocale() === 'ar'
                    ? (($this->brand_snapshot['name_ar'] ?? null) ?: ($this->brand_snapshot['name'] ?? null))
                    : (($this->brand_snapshot['name'] ?? null) ?: ($this->brand_snapshot['name_ar'] ?? null)),
                'name_ar' => ($this->brand_snapshot['name_ar'] ?? null) ?: ($this->brand_snapshot['name'] ?? null),
                'is_snapshot' => true,
            ]
            : null;

        return [
            'id' => $this->id,
            'name' => $this->localized('name', null, $request),
            'slug' => $this->slug,
            'thumb_image' => $this->main_image,
            'description' => $this->localized('description', null, $request),
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'stock' => $this->stock,
            'units' => ProductUnitResource::collection(
                $this->whenLoaded('productUnits', fn () => $this->productUnits->where('is_active', true)->values()),
            ),
            'is_active' => $this->is_active,
            'is_wishlisted' => $this->when($user = $request->user(), function () use ($user) {
                return $this->wishlistedBy()->where('user_id', $user->id)->exists();
            }, false),
            'is_featured' => $this->is_featured,
            'is_approved' => $this->is_approved,
            'is_bookable' => $this->is_bookable,
            'is_new' => $this->is_new,
            'brand' => $this->brand
                ? new BrandResource($this->brand)
                : $brandSnapshot,
            'brand_snapshot' => $brandSnapshot,
            'categories' => $this->relationLoaded('categories')
                ? CategoryResource::collection($this->categories)
                : $categorySnapshots,
            'categories_snapshot' => $categorySnapshots,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'related_products' => $this->when(
                ! $this->excludeNestedRelated,
                fn () => ProductResource::collection(
                    $this->getEffectiveRelatedProducts()->map(
                        fn (Product $product) => (new ProductResource($product))->withoutNestedRelated(),
                    ),
                ),
            ),
            'type' => $this->type ?? 'simple',
            'rating' => $this->when($this->relationLoaded('ratings'), function () {
                $visibleRatings = $this->ratings->where('is_visible', true);

                return [
                    'average' => (float) ($visibleRatings->avg('rating') ?? 0),
                    'count' => $visibleRatings->count(),
                ];
            }, function () {
                return [
                    'average' => (float) ($this->ratings()->where('is_visible', true)->avg('rating') ?? 0),
                    'count' => (int) $this->ratings()->where('is_visible', true)->count(),
                ];
            }),
            'ratings' => ProductRatingResource::collection(
                $this->whenLoaded('ratings', function () {
                    return $this->ratings->where('is_visible', true)->values();
                })
            ),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'variant_options' => $this->when($this->type === 'variable', function () use ($request) {
                return $this->getVariantOptions($request);
            }),
            'variants_summary' => $this->when($this->type === 'variable', function () {
                $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();

                if ($variants->isEmpty()) {
                    return [
                        'total_variants' => 0,
                        'active_variants' => 0,
                        'price_range' => [
                            'min' => null,
                            'max' => null,
                        ],
                    ];
                }

                return [
                    'total_variants' => $variants->count(),
                    'active_variants' => $variants->where('is_active', true)->count(),
                    'price_range' => [
                        'min' => $variants->min('price'),
                        'max' => $variants->max('price'),
                    ],
                ];
            }),
        ];
    }

    /**
     * Get variant options grouped by variant type
     */
    protected function getVariantOptions(Request $request): array
    {
        if (! $this->relationLoaded('variants')) {
            $this->load('variants.values.variantOption.variant');
        }

        $groupedOptions = [];

        foreach ($this->variants as $productVariant) {
            foreach ($productVariant->values as $value) {
                if ($value->variantOption && $value->variantOption->variant) {
                    $variantId = $value->variantOption->variant->id;
                    $variantName = $this->localizedValue(
                        $value->variantOption->variant->getTranslations('name'),
                        null,
                        $request
                    );
                    $optionId = $value->variantOption->id;
                    $optionName = $this->localizedValue(
                        $value->variantOption->getTranslations('name'),
                        null,
                        $request
                    );
                    $optionCode = $value->variantOption->code;

                    // Initialize variant group if not exists
                    if (! isset($groupedOptions[$variantId])) {
                        $groupedOptions[$variantId] = [
                            'variant_id' => $variantId,
                            'variant_name' => $variantName,
                            'options' => [],
                        ];
                    }

                    // Add option if not already added (avoid duplicates)
                    $optionExists = false;
                    foreach ($groupedOptions[$variantId]['options'] as $existingOption) {
                        if ($existingOption['id'] === $optionId) {
                            $optionExists = true;
                            break;
                        }
                    }

                    if (! $optionExists) {
                        $groupedOptions[$variantId]['options'][] = [
                            'id' => $optionId,
                            'name' => $optionName,
                            'code' => $optionCode,
                        ];
                    }
                }
            }
        }

        // Convert to indexed array
        return array_values($groupedOptions);
    }

    /**
     * Get manual related products if defined, otherwise generate automatic related products.
     */
    protected function getEffectiveRelatedProducts()
    {
        if ($this->relationLoaded('relatedProducts') && $this->relatedProducts->isNotEmpty()) {
            return $this->relatedProducts
                ->map->relatedProduct
                ->filter()
                ->take(4)
                ->values();
        }

        return $this->getAutoRelatedProducts();
    }

    /**
     * Generate automatic related products for this product.
     */
    protected function getAutoRelatedProducts()
    {
        // Try to use already loaded categories; fall back to querying if not loaded
        $categoryIds = $this->relationLoaded('categories')
            ? $this->categories->pluck('id')
            : $this->categories()->pluck('categories.id');

        $query = Product::active()
            ->approved()
            ->with(['brand', 'categories', 'images'])
            ->where('id', '!=', $this->id);

        if ($categoryIds->isNotEmpty()) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        return $query->inRandomOrder()->limit(4)->get();
    }
}
