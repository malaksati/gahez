@csrf

@include('v1.admin.partials.translatable-inputs', ['model' => $goal ?? null])

@include('v1.admin.partials.translatable-inputs', [
    'field' => 'description',
    'label' => __('messages.Description'),
    'model' => $goal ?? null,
    'type' => 'textarea',
    'rows' => 3,
])

<div class="mb-3">
    <label for="period_type" class="form-label">{{ __('messages.Period') }} *</label>
    <select name="period_type" id="period_type" class="form-select @error('period_type') is-invalid @enderror" required>
        @foreach (['daily', 'weekly', 'monthly'] as $period)
            <option value="{{ $period }}" @selected(old('period_type', $goal->period_type ?? '') === $period)>
                {{ __('messages.Goal period '.$period) }}
            </option>
        @endforeach
    </select>
    @error('period_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <p class="form-text mb-0">{{ __('messages.Goal period hint') }}</p>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="min_order_total" class="form-label">{{ __('messages.Minimum order subtotal') }} *</label>
        <input type="number" step="0.01" min="0.01" name="min_order_total" id="min_order_total"
            value="{{ old('min_order_total', $goal->min_order_total ?? '') }}"
            class="form-control @error('min_order_total') is-invalid @enderror" required>
        @error('min_order_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="reward_amount" class="form-label">{{ __('messages.Reward amount') }} *</label>
        <input type="number" step="0.01" min="0.01" name="reward_amount" id="reward_amount"
            value="{{ old('reward_amount', $goal->reward_amount ?? '') }}"
            class="form-control @error('reward_amount') is-invalid @enderror" required>
        @error('reward_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Goal reward hint') }}</p>
    </div>
</div>

<div class="mb-2">
    <label class="form-label">{{ __('messages.Validity') }}</label>
    <p class="form-text mb-2">{{ __('messages.Goal validity hint') }}</p>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">{{ __('messages.Start date') }}</label>
        <input type="date" name="start_date" id="start_date"
            value="{{ old('start_date', isset($goal) && $goal->start_date ? $goal->start_date->format('Y-m-d') : '') }}"
            class="form-control @error('start_date') is-invalid @enderror">
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Leave blank to start immediately') }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">{{ __('messages.End date') }}</label>
        <input type="date" name="end_date" id="end_date"
            value="{{ old('end_date', isset($goal) && $goal->end_date ? $goal->end_date->format('Y-m-d') : '') }}"
            class="form-control @error('end_date') is-invalid @enderror">
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Leave blank for no end date') }}</p>
    </div>
</div>

<div class="mb-3">
    <label for="sort_order" class="form-label">{{ __('messages.Sort order') }}</label>
    <input type="number" min="0" name="sort_order" id="sort_order"
        value="{{ old('sort_order', $goal->sort_order ?? 0) }}"
        class="form-control @error('sort_order') is-invalid @enderror">
    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-check form-switch mb-4">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1"
        @checked(old('is_active', $goal->is_active ?? true))>
    <label class="form-check-label" for="is_active">{{ __('messages.Active') }}</label>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.goals.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
