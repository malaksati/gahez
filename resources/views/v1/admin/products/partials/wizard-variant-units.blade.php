{{-- Variant + unit pricing (variable products, variants step) --}}
<div class="mt-4" x-show="productVariants.length > 0" x-cloak>
    <h6 class="mb-2">{{ __('messages.Variant units') }}</h6>
    <p class="text-muted small mb-3">{{ __('messages.Variant units wizard hint') }}</p>

    <div class="card border mb-3">
        <div class="card-body">
            <h6 class="mb-2">{{ __('messages.Add sellable unit') }}</h6>
            <p class="text-muted small mb-3">{{ __('messages.Add sellable unit hint') }}</p>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">{{ __('messages.Name') }} ({{ __('messages.English') }})</label>
                    <input type="text" class="form-control form-control-sm" x-model="newCatalogUnit.name_en"
                        placeholder="{{ __('messages.e.g. Box') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">{{ __('messages.Name') }} ({{ __('messages.Arabic') }})</label>
                    <input type="text" class="form-control form-control-sm" dir="rtl" x-model="newCatalogUnit.name_ar"
                        placeholder="{{ __('messages.e.g. صندوق') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('messages.Code') }}</label>
                    <input type="text" class="form-control form-control-sm" x-model="newCatalogUnit.code"
                        placeholder="{{ __('messages.Optional') }}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" @click="createCatalogUnit()"
                        :disabled="catalogUnitSaving">
                        <span x-show="!catalogUnitSaving">{{ __('messages.Add') }}</span>
                        <span x-show="catalogUnitSaving" x-cloak>...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Variation') }}</th>
                    <th>{{ __('messages.Unit') }}</th>
                    <th>{{ __('messages.SKU') }}</th>
                    <th>{{ __('messages.Price') }}</th>
                    <th>{{ __('messages.Discount value') }}</th>
                    <th>{{ __('messages.Discount type') }}</th>
                    <th>{{ __('messages.Stock quantity') }}</th>
                    <th>{{ __('messages.Unit factor') }}</th>
                    <th>{{ __('messages.Default') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in productUnits" :key="`variant_unit_row_${index}`">
                    <tr>
                        <td>
                            <select class="form-select form-select-sm"
                                x-model="row.variant_key"
                                @change="syncVariantUnitRow(index)"
                                required>
                                <option value="">{{ __('messages.Select variation') }}</option>
                                <template x-for="variant in productVariants" :key="variant._key">
                                    <option :value="variant._key.replace(/^pv_/, '')" x-text="variant.name"></option>
                                </template>
                            </select>
                            <input type="hidden" :name="`product_units[${index}][id]`" x-bind:value="row.id || ''">
                            <input type="hidden" :name="`product_units[${index}][product_variant_id]`"
                                x-bind:value="row.product_variant_id || ''">
                            <template x-for="optionId in (row.variant_option_ids || [])"
                                :key="`variant_unit_opt_${index}_${optionId}`">
                                <input type="hidden" :name="`product_units[${index}][variant_option_ids][]`"
                                    :value="optionId">
                            </template>
                        </td>
                        <td>
                            <select class="form-select form-select-sm"
                                :name="`product_units[${index}][unit_id]`"
                                x-model="row.unit_id"
                                @change="refreshVariantUnitRowSku(index)"
                                required>
                                <option value="">{{ __('messages.Select unit') }}</option>
                                <template x-for="unit in catalogUnits" :key="unit.id">
                                    <option :value="String(unit.id)" x-text="unit.name"></option>
                                </template>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                :name="`product_units[${index}][sku]`"
                                x-model="row.sku" placeholder="{{ __('messages.Optional') }}">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                :name="`product_units[${index}][price]`"
                                x-model="row.price" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                :name="`product_units[${index}][discount]`"
                                x-model="row.discount">
                        </td>
                        <td>
                            <select class="form-select form-select-sm"
                                :name="`product_units[${index}][discount_type]`"
                                x-model="row.discount_type">
                                <option value="percentage">%</option>
                                <option value="fixed">{{ __('messages.Fixed') }}</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" min="0" class="form-control form-control-sm"
                                :name="`product_units[${index}][stock]`"
                                x-model="row.stock"
                                placeholder="{{ __('messages.Leave blank if unknown') }}">
                            <input type="hidden" :name="`product_units[${index}][is_in_stock]`"
                                x-bind:value="row.is_in_stock ? 1 : 0">
                        </td>
                        <td>
                            <input type="number" min="1" class="form-control form-control-sm"
                                :name="`product_units[${index}][factor]`"
                                x-model="row.factor">
                        </td>
                        <td class="text-center">
                            <input type="hidden" :name="`product_units[${index}][is_default]`"
                                x-bind:value="row.is_default ? 1 : 0">
                            <input type="radio" class="form-check-input"
                                :checked="row.is_default"
                                @change="setDefaultProductUnit(index)">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                @click="removeProductUnitRow(index)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary" @click="addVariantUnitRow()">
        <i class="bi bi-plus-lg me-1"></i>{{ __('messages.Add variant unit row') }}
    </button>
</div>
