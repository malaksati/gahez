{{-- Step: product variants (variable products only) --}}
<div x-show="currentStepKey === 'variants'" x-transition.opacity class="wizard-content">
    <h5 class="mb-3">
        <i class="bi bi-tags me-2"></i>{{ __('messages.Product variants') }}
    </h5>
    <p class="text-muted mb-4">{{ __('messages.Define sellable combinations by selecting variant options') }}</p>

    <div class="card border mb-4">
        <div class="card-body">
            <h6 class="mb-2">{{ __('messages.Add variant attribute') }}</h6>
            <p class="text-muted small mb-3">{{ __('messages.Add variant attribute hint') }}</p>
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1">{{ __('messages.Name') }} ({{ __('messages.English') }})</label>
                    <input type="text" class="form-control form-control-sm" x-model="newCatalogVariant.name_en"
                        placeholder="{{ __('messages.e.g. Color') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-1">{{ __('messages.Name') }} ({{ __('messages.Arabic') }})</label>
                    <input type="text" class="form-control form-control-sm" dir="rtl" x-model="newCatalogVariant.name_ar"
                        placeholder="{{ __('messages.e.g. اللون') }}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" @click="createCatalogVariant()"
                        :disabled="catalogVariantSaving">
                        <span x-show="!catalogVariantSaving">{{ __('messages.Add') }}</span>
                        <span x-show="catalogVariantSaving" x-cloak>...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="catalogVariants.length === 0" class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        {{ __('messages.No variant attributes configured yet.') }}
        {{ __('messages.Create one above to start building combinations.') }}
    </div>

    <div x-show="catalogVariants.length > 0">
        <div class="mb-4">
            <h6 class="mb-2">{{ __('messages.Select variant options') }}</h6>
            <p class="text-muted small mb-3">
                {{ __('messages.Search and select options for each attribute. Create missing options without leaving this page.') }}
            </p>
            <div class="row g-4">
                <template x-for="catalogVariant in catalogVariants" :key="catalogVariant.id">
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body">
                                <label class="form-label fw-bold text-primary mb-2">
                                    <span x-text="catalogVariant.name"></span>
                                    <span x-show="catalogVariant.is_required" class="text-danger">*</span>
                                </label>

                                <input type="search" class="form-control form-control-sm mb-2"
                                    :placeholder="@js(__('messages.Search options'))"
                                    x-model="optionSearchQueries[String(catalogVariant.id)]">

                                <div class="border rounded p-2 mb-2" style="max-height: 140px; overflow-y: auto;">
                                    <template x-for="option in filteredCatalogOptions(catalogVariant)" :key="option.id">
                                        <button type="button"
                                            class="btn btn-sm w-100 text-start mb-1"
                                            :class="isOptionSelected(catalogVariant.id, option.id) ? 'btn-primary' : 'btn-outline-secondary'"
                                            @click="toggleVariantOption(catalogVariant.id, option.id, !isOptionSelected(catalogVariant.id, option.id))">
                                            <span x-text="option.name"></span>
                                            <span class="text-muted ms-1" x-show="option.code" x-text="`(${option.code})`"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredCatalogOptions(catalogVariant).length === 0" class="text-muted small">
                                        {{ __('messages.No matching options') }}
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <template x-for="optionId in (formData.variantOptions[String(catalogVariant.id)] || [])" :key="`${catalogVariant.id}_sel_${optionId}`">
                                        <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                                            <span x-text="optionLabel(optionId)"></span>
                                            <button type="button" class="btn-close btn-close-white"
                                                style="font-size: 0.55rem;"
                                                @click="toggleVariantOption(catalogVariant.id, optionId, false)"></button>
                                        </span>
                                    </template>
                                </div>

                                <div class="border-top pt-2">
                                    <p class="small text-muted mb-2">{{ __('messages.Create new option') }}</p>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="text" class="form-control form-control-sm"
                                                :placeholder="@js(__('messages.English'))"
                                                x-model="newOptionForms[String(catalogVariant.id)].name_en">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control form-control-sm" dir="rtl"
                                                :placeholder="@js(__('messages.Arabic'))"
                                                x-model="newOptionForms[String(catalogVariant.id)].name_ar">
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-sm btn-outline-success w-100"
                                                @click="createCatalogOption(catalogVariant.id)"
                                                :disabled="optionSavingId === String(catalogVariant.id)">
                                                <span x-show="optionSavingId !== String(catalogVariant.id)">{{ __('messages.Add option') }}</span>
                                                <span x-show="optionSavingId === String(catalogVariant.id)" x-cloak>...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="formData.type === 'variable' && productVariants.length > 0" x-transition class="mb-4">
            <h6 class="mb-3">{{ __('messages.Product variations') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">{{ __('messages.Variation') }}</th>
                            <th style="width: 12%;">{{ __('messages.Name') }} ({{ __('messages.English') }})</th>
                            <th style="width: 12%;">{{ __('messages.Name') }} ({{ __('messages.Arabic') }})</th>
                            <th style="width: 12%;">{{ __('messages.SKU') }}</th>
                            <th style="width: 12%;">{{ __('messages.Thumbnail') }}</th>
                            <th style="width: 10%;">{{ __('messages.Availability') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in productVariants" :key="row._key">
                            <tr>
                                <td>
                                    <span class="small" x-text="row.name"></span>
                                    <input type="hidden" :name="`product_variants[${index}][id]`" :value="row.id">
                                    <input type="hidden" :name="`product_variants[${index}][discount]`"
                                        :value="row.discount ?? 0">
                                    <input type="hidden" :name="`product_variants[${index}][discount_type]`"
                                        :value="row.discount_type ?? 'percentage'">
                                    <input type="hidden" :name="`product_variants[${index}][is_active]`"
                                        :value="row.is_active ? 1 : 0">
                                    <input type="hidden" :name="`product_variants[${index}][is_in_stock]`"
                                        :value="row.is_in_stock ? 1 : 0">
                                    <template x-for="optionId in row.option_ids"
                                        :key="`${row._key}_opt_${optionId}`">
                                        <input type="hidden" :name="`product_variants[${index}][option_ids][]`"
                                            :value="optionId">
                                    </template>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        :name="`product_variants[${index}][name][en]`" x-model="row.name_en"
                                        :placeholder="@js(__('messages.English name'))">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" dir="rtl"
                                        :name="`product_variants[${index}][name][ar]`" x-model="row.name_ar"
                                        :placeholder="@js(__('messages.Arabic name'))">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        :name="`product_variants[${index}][sku]`" x-model="row.sku"
                                        :placeholder="@js(__('messages.Auto'))">
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary mb-2"
                                            @click="document.getElementById(`product_variant_thumbnail_${index}`)?.click()">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                        <input type="file" class="d-none" :id="`product_variant_thumbnail_${index}`"
                                            accept="image/*" @change="handleVariationThumbnailChange(index, $event)">
                                        <div x-show="row.thumbnailPreview" class="position-relative">
                                            <img :src="row.thumbnailPreview" alt="" class="img-thumbnail"
                                                style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                            <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0"
                                                style="width: 18px; height: 18px; font-size: 10px; line-height: 1;"
                                                @click="removeVariationThumbnail(index)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input"
                                            :id="`variant_in_stock_${index}`" x-model="row.is_in_stock">
                                        <label class="form-check-label small" :for="`variant_in_stock_${index}`">
                                            {{ __('messages.Available') }}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="formData.type === 'variable' && hasAnyVariantOptionSelected() && productVariants.length === 0"
            class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ __('messages.Select at least one option for each variant attribute to generate combinations.') }}
        </div>

        <div x-show="formData.type === 'variable' && !hasAnyVariantOptionSelected()" class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            {{ __('messages.Toggle variant options above to generate the variations table.') }}
        </div>

        @include('v1.admin.products.partials.wizard-variant-units')
    </div>

    @error('product_variants')
        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
    @enderror
    @error('product_variants.*')
        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
    @enderror
</div>
