{{-- Sellable units (pricing step, simple products only) --}}
<div class="mt-3" x-show="!isVariable" x-cloak data-product-units-scope="simple">
    <h6 class="mb-2">{{ __('messages.Product units') }}</h6>
    <p class="text-muted small mb-3">{{ __('messages.Product units wizard hint') }}</p>

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
                <template x-for="(row, index) in productUnits" :key="`unit_row_${index}`">
                    <tr>
                        <td>
                            <input type="hidden" :name="`product_units[${index}][id]`" x-bind:value="row.id || ''">
                            <select class="form-select form-select-sm"
                                :name="`product_units[${index}][unit_id]`"
                                data-unit-select
                                x-model="row.unit_id"
                                @change="refreshProductUnitRowSku(index)"
                                required>
                                <option value="">{{ __('messages.Select unit') }}</option>
                                @foreach ($catalogUnits as $catalogUnit)
                                    <option value="{{ (string) $catalogUnit->id }}">
                                        {{ $catalogUnit->getTranslation('name', app()->getLocale())
                                            ?: $catalogUnit->getTranslation('name', 'en') }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                :name="`product_units[${index}][sku]`"
                                x-model="row.sku" placeholder="{{ __('messages.Optional') }}">
                        </td>
                        <td>
                            <input type="number" step="0.5" min="0" class="form-control form-control-sm"
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
                                @click="removeProductUnitRow(index)"
                                :disabled="productUnits.length <= 1">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary" @click="addProductUnitRow()">
        <i class="bi bi-plus-lg me-1"></i>{{ __('messages.Add unit row') }}
    </button>
</div>
