@php
    $product = $product ?? null;
    $isEdit = $product !== null;
    $allProducts = $allProducts ?? collect();
    $catalogVariants = $catalogVariants ?? collect();
    $existingProductVariants = $existingProductVariants ?? [];
    $selectedCategoryIds = array_map(
        'intval',
        old('category_ids', $isEdit ? $product->categories->pluck('id')->all() : []),
    );

    $selectedRelatedIds = array_map(
        'intval',
        old('related_products', $isEdit ? $product->relatedProducts?->pluck('related_product_id')->all() ?? [] : []),
    );

    $thumbnailPath = $isEdit ? $product->getRawOriginal('thumbnail') : null;
    $thumbnailPreview = $thumbnailPath ? asset('storage/'.$thumbnailPath) : null;

    $existingImages = $isEdit
        ? $product->images->map(fn ($image) => [
            'id' => $image->id,
            'url' => $image->image_path,
            'keep' => in_array($image->id, array_map('intval', old('existing_images', $product->images->pluck('id')->all())), true),
        ])->values()->all()
        : [];

    $wizardConfig = [
        'mode' => $isEdit ? 'edit' : 'create',
        'thumbnailPreview' => old('_thumbnail_preview') ?: $thumbnailPreview,
        'existingImages' => $existingImages,
        'catalogVariants' => $catalogVariants->values()->all(),
        'quickCatalogVariantUrl' => route('v1.admin.products.quick-catalog-variant'),
        'quickVariantOptionUrl' => route('v1.admin.products.quick-variant-option'),
        'nextSkuUrl' => route('v1.admin.products.next-sku'),
        'existingProductVariants' => old('product_variants')
            ? array_values(old('product_variants'))
            : $existingProductVariants,
        'labels' => [
            'basic' => __('messages.Basic info'),
            'pricing' => __('messages.Pricing and stock'),
            'variants' => __('messages.Product variants'),
            'variantRow' => __('messages.Variant row'),
            'categories' => __('messages.Categories'),
            'images' => __('messages.Media'),
            'related' => __('messages.Related products'),
        ],
        'formData' => [
            'type' => old('type', $product?->type ?? 'simple'),
            'brand_id' => (string) old('brand_id', $product?->brand_id ?? ''),
            'name_en' => old('name.en', $isEdit ? $product->getTranslation('name', 'en', false) : ''),
            'name_ar' => old('name.ar', $isEdit ? $product->getTranslation('name', 'ar', false) : ''),
            'slug' => old('slug', $product?->slug ?? ''),
            'sku' => old('sku', $product?->sku ?? ''),
            'description_en' => old('description.en', $isEdit ? $product->getTranslation('description', 'en', false) : ''),
            'description_ar' => old('description.ar', $isEdit ? $product->getTranslation('description', 'ar', false) : ''),
            'price' => old('price', $product?->price ?? '0'),
            'stock' => old('stock', $product?->stock !== null ? (string) $product?->stock : ''),
            'is_in_stock' => (bool) old('is_in_stock', $product?->is_in_stock ?? true),
            'sort_order' => old('sort_order', $product?->sort_order ?? ''),
            'discount' => old('discount', $product?->discount ?? '0'),
            'discount_type' => old('discount_type', $product?->discount_type ?? 'percentage'),
            'category_ids' => array_map(fn ($id) => (string) $id, $selectedCategoryIds),
            'related_products' => array_map(fn ($id) => (string) $id, $selectedRelatedIds),
            'is_active' => (bool) old('is_active', $product?->is_active ?? true),
            'is_featured' => (bool) old('is_featured', $product?->is_featured ?? false),
            'is_new' => (bool) old('is_new', $product?->is_new ?? false),
            'is_approved' => (bool) old('is_approved', $product?->is_approved ?? true),
            'is_bookable' => (bool) old('is_bookable', $product?->is_bookable ?? false),
        ],
    ];
@endphp

<div class="product-wizard" x-data="productWizard(@js($wizardConfig))" x-init="init()">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.Close') }}"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">
                {{ $isEdit ? __('messages.Edit product step by step') : __('messages.Add a new product step by step') }}
            </p>
        </div>
        <a href="{{ route('v1.admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>{{ __('messages.Back') }}
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">{{ __('messages.Progress') }}</span>
                <span class="text-muted" x-text="`${currentStep} / ${totalSteps}`"></span>
            </div>

            <div class="wizard-steps mb-4">
                @include('v1.admin.products.partials.wizard-step-indicators')
            </div>

            <form action="{{ $formAction }}" method="POST" id="productWizardForm"
                enctype="multipart/form-data"
                @submit.prevent="submitWizard($event)" novalidate>
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                @include('v1.admin.products.partials.wizard-persistent-file-inputs')

                @if (! $isEdit)
                    <input type="hidden" name="slug" x-bind:value="formData.slug">
                @endif

                {{-- Step 1: Basic information --}}
                <div x-show="currentStepKey === 'basic'" x-transition.opacity class="wizard-content">
                    <h5 class="mb-3">
                        <i class="bi bi-info-circle me-2"></i>{{ __('messages.Product basic information') }}
                    </h5>
                    <p class="text-muted mb-4">{{ __('messages.Enter the basic product details') }}</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">{{ __('messages.Type') }} *</label>
                            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror"
                                x-model="formData.type" @change="onTypeChange()" required>
                                <option value="simple">{{ __('messages.Simple') }}</option>
                                <option value="variable">{{ __('messages.Variable') }}</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="brand_id" class="form-label">{{ __('messages.Brand') }} *</label>
                            <select id="brand_id" name="brand_id"
                                class="form-select @error('brand_id') is-invalid @enderror"
                                x-model="formData.brand_id" required>
                                <option value="">{{ __('messages.Select brand') }}</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">
                                        {{ $brand->getTranslation('name', app()->getLocale()) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">{{ __('messages.Name') }} ({{ __('messages.English') }}) *</label>
                        <input type="text" id="name_en" name="name[en]"
                            class="form-control @error('name.en') is-invalid @enderror"
                            x-model="formData.name_en" @input="onNameEnInput()" required>
                        @error('name.en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name_ar" class="form-label">{{ __('messages.Name') }} ({{ __('messages.Arabic') }}) *</label>
                        <input type="text" id="name_ar" name="name[ar]" dir="rtl"
                            class="form-control @error('name.ar') is-invalid @enderror"
                            x-model="formData.name_ar" required>
                        @error('name.ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug_preview" class="form-label">{{ __('messages.Slug') }}</label>
                        <input type="text" id="slug_preview" class="form-control bg-light"
                            :value="formData.slug" disabled
                            placeholder="{{ __('messages.Auto-generated from English name') }}">
                        <small class="text-muted">{{ __('messages.Auto-generated from English name') }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="sku" class="form-label">{{ __('messages.SKU') }} *</label>
                        <input type="text" id="sku" name="sku"
                            class="form-control @error('sku') is-invalid @enderror"
                            x-model="formData.sku" @input="onSkuInput()" required
                            :readonly="!isEdit && skuAutoGenerated"
                            :class="{ 'bg-light': !isEdit && skuAutoGenerated }">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <p class="form-text mb-0" x-show="!isEdit">{{ __('messages.Product sku auto hint') }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="description_en" class="form-label">{{ __('messages.Description') }} ({{ __('messages.English') }}) *</label>
                        <textarea id="description_en" name="description[en]" rows="4"
                            class="form-control @error('description.en') is-invalid @enderror"
                            x-model="formData.description_en" required></textarea>
                        @error('description.en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description_ar" class="form-label">{{ __('messages.Description') }} ({{ __('messages.Arabic') }}) *</label>
                        <textarea id="description_ar" name="description[ar]" rows="4" dir="rtl"
                            class="form-control @error('description.ar') is-invalid @enderror"
                            x-model="formData.description_ar" required></textarea>
                        @error('description.ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Step 2: Pricing & stock --}}
                <div x-show="currentStepKey === 'pricing'" x-transition.opacity class="wizard-content">
                    <h5 class="mb-3">
                        <i class="bi bi-currency-dollar me-2"></i>{{ __('messages.Pricing and stock') }}
                    </h5>
                    <p class="text-muted mb-4">{{ __('messages.Set product pricing and inventory') }}</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">{{ __('messages.Price') }} *</label>
                            <input type="number" step="0.01" min="0" id="price" name="price"
                                class="form-control @error('price') is-invalid @enderror"
                                x-model="formData.price" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" x-show="!isVariable" x-cloak>
                            <label for="stock" class="form-label">{{ __('messages.Stock quantity') }}</label>
                            <input type="number" min="0" id="stock" name="stock"
                                class="form-control @error('stock') is-invalid @enderror"
                                x-model="formData.stock"
                                placeholder="{{ __('messages.Leave blank if unknown') }}">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <p class="form-text mb-0">{{ __('messages.Product stock quantity hint') }}</p>
                        </div>

                        <div class="col-md-6 mb-3" x-show="!isVariable" x-cloak>
                            <input type="hidden" name="is_in_stock" :value="formData.is_in_stock ? 1 : 0">
                            <div class="form-check form-switch mt-4 pt-2">
                                <input type="checkbox" class="form-check-input" id="is_in_stock"
                                    x-model="formData.is_in_stock">
                                <label class="form-check-label" for="is_in_stock">{{ __('messages.Available for sale') }}</label>
                            </div>
                            <p class="form-text mb-0">{{ __('messages.Product available for sale hint') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">{{ __('messages.Sort order') }}</label>
                            <input type="number" min="1" id="sort_order" name="sort_order"
                                class="form-control @error('sort_order') is-invalid @enderror"
                                x-model="formData.sort_order" placeholder="{{ __('messages.Leave blank for last position') }}">
                            <p class="form-text mb-0">{{ __('messages.Sort order shift hint') }}</p>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="discount" class="form-label">{{ __('messages.Discount value') }}</label>
                            <input type="number" step="0.01" min="0" id="discount" name="discount"
                                class="form-control @error('discount') is-invalid @enderror"
                                x-model="formData.discount">
                            @error('discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="discount_type" class="form-label">{{ __('messages.Discount type') }}</label>
                            <select id="discount_type" name="discount_type"
                                class="form-select @error('discount_type') is-invalid @enderror"
                                x-model="formData.discount_type">
                                <option value="percentage">%</option>
                                <option value="fixed">{{ __('messages.Fixed') }}</option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-2">
                        @foreach ([
                            'is_active' => __('messages.Active'),
                            'is_featured' => __('messages.Featured'),
                            'is_new' => __('messages.New product'),
                            'is_approved' => __('messages.Approved'),
                            'is_bookable' => __('messages.Bookable'),
                        ] as $flag => $label)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <input type="hidden" name="{{ $flag }}" :value="formData.{{ $flag }} ? 1 : 0">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="{{ $flag }}"
                                        x-model="formData.{{ $flag }}">
                                    <label class="form-check-label" for="{{ $flag }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @include('v1.admin.products.partials.wizard-steps-variants')

                {{-- Categories --}}
                <div x-show="currentStepKey === 'categories'" x-transition.opacity class="wizard-content">
                    <h5 class="mb-3">
                        <i class="bi bi-grid me-2"></i>{{ __('messages.Categories') }}
                    </h5>
                    <p class="text-muted mb-4">{{ __('messages.Select product categories') }}</p>

                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                        @forelse ($categories as $category)
                            @php($depth = (int) ($category->tree_depth ?? 0))
                            <div class="form-check mb-2" @if ($depth > 0) style="margin-inline-start: {{ $depth }}rem" @endif>
                                <input class="form-check-input" type="checkbox" name="category_ids[]"
                                    id="category_{{ $category->id }}" value="{{ $category->id }}"
                                    x-model="formData.category_ids">
                                <label class="form-check-label" for="category_{{ $category->id }}">
                                    {{ $category->getTranslation('name', app()->getLocale()) }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('messages.No categories yet') }}</p>
                        @endforelse
                    </div>
                    @error('category_ids')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                    @error('category_ids.*')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                </div>

                @include('v1.admin.products.partials.wizard-steps-media')

                <div class="d-flex justify-content-between mt-4 pt-4 border-top">
                    <button type="button" class="btn btn-secondary" @click="previousStep()"
                        :disabled="currentStep === 1">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('messages.Previous') }}
                    </button>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" @click="nextStep()"
                            x-show="currentStep < totalSteps" :disabled="!canProceed()">
                            {{ __('messages.Next') }}
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn btn-success"
                            x-show="currentStep === totalSteps" :disabled="!canProceed()">
                            <i class="bi bi-check-lg me-2"></i>
                            {{ $isEdit ? __('messages.Save changes') : __('messages.Create product') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
