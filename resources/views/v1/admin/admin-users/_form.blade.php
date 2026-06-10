@csrf

@php
    $isEdit = isset($admin);
    $isSuperAdmin = $isEdit && $admin->hasRole('super-admin');
    $adminPermissions = $isEdit ? $admin->getPermissionNames()->toArray() : [];
@endphp

{{-- Basic Information --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="bi bi-person me-2"></i>{{ __('messages.Basic information') }}</h6>
    </div>
    <div class="card-body">
        @if (!$isEdit)
            <div class="mb-4">
                <label class="form-label">{{ __('messages.Admin login account') }}</label>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_type" id="user_type_new" value="new"
                            {{ old('user_type', 'new') === 'new' ? 'checked' : '' }}>
                        <label class="form-check-label" for="user_type_new">{{ __('messages.Create new user') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_type" id="user_type_existing" value="existing"
                            {{ old('user_type') === 'existing' ? 'checked' : '' }}>
                        <label class="form-check-label" for="user_type_existing">{{ __('messages.Link to existing user') }}</label>
                    </div>
                </div>

                <div id="existing-user-field" class="d-none">
                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                        <option value="">{{ __('messages.Select user') }}</option>
                        @foreach ($users ?? [] as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})@if($user->phone) — {{ $user->phone }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('messages.Link existing user admin hint') }}</div>
                </div>
            </div>
        @endif

        <div id="new-user-fields">
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $admin->name ?? '') }}" {{ $isEdit ? 'required' : '' }}>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('messages.Email') }} <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $admin->email ?? '') }}" {{ $isEdit ? 'required' : '' }}>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">
                        {{ __('messages.Password') }}
                        @if (!$isEdit) <span class="text-danger">*</span> @endif
                    </label>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           {{ $isEdit ? '' : '' }}
                           placeholder="{{ $isEdit ? __('messages.Leave blank to keep current') : '' }}">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">
                        {{ __('messages.Confirm password') }}
                        @if (!$isEdit) <span class="text-danger">*</span> @endif
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control">
                </div>
            </div>

            @if (!$isEdit)
                <p class="text-muted small mb-0">{{ __('messages.Create new user admin hint') }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Permissions --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>{{ __('messages.Permissions') }}</h6>
        @if (!$isSuperAdmin)
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                <label class="form-check-label small" for="selectAllPermissions">{{ __('messages.Select all') }}</label>
            </div>
        @endif
    </div>
    <div class="card-body">
        @if ($isSuperAdmin)
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                {{ __('messages.Super Admin has all permissions automatically.') }}
            </div>
        @else
            <div class="row">
                @foreach ($permissionsGrouped as $group => $permissions)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-uppercase small fw-bold text-muted mb-3 border-bottom pb-2">
                                {{ $group }}
                            </h6>
                            @foreach ($permissions as $permName => $permLabel)
                                <div class="form-check mb-2">
                                    <input class="form-check-input permission-checkbox" type="checkbox"
                                           name="permissions[]" value="{{ $permName }}"
                                           id="perm_{{ Str::slug($permName) }}"
                                           @checked(in_array($permName, old('permissions', $adminPermissions)))>
                                    <label class="form-check-label" for="perm_{{ Str::slug($permName) }}">
                                        {{ $permLabel }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Actions --}}
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>{{ __('messages.Save') }}
    </button>
    <a href="{{ route('v1.admin.admin-users.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllPermissions');
    const checkboxes = document.querySelectorAll('.permission-checkbox');

    if (selectAll) {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                selectAll.checked = Array.from(checkboxes).every(c => c.checked);
            });
        });
    }

    const userTypeNew = document.getElementById('user_type_new');
    const userTypeExisting = document.getElementById('user_type_existing');
    const newUserFields = document.getElementById('new-user-fields');
    const existingUserField = document.getElementById('existing-user-field');

    if (!userTypeNew || !userTypeExisting || !newUserFields || !existingUserField) {
        return;
    }

    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const userSelect = existingUserField.querySelector('select');

    function toggleUserFields() {
        if (userTypeNew.checked) {
            newUserFields.classList.remove('d-none');
            existingUserField.classList.add('d-none');
            if (userSelect) userSelect.removeAttribute('required');
            nameInput.setAttribute('required', 'required');
            emailInput.setAttribute('required', 'required');
            passwordInput.setAttribute('required', 'required');
            passwordConfirmInput.setAttribute('required', 'required');
        } else {
            newUserFields.classList.add('d-none');
            existingUserField.classList.remove('d-none');
            nameInput.removeAttribute('required');
            emailInput.removeAttribute('required');
            passwordInput.removeAttribute('required');
            passwordConfirmInput.removeAttribute('required');
            if (userSelect) userSelect.setAttribute('required', 'required');
        }
    }

    userTypeNew.addEventListener('change', toggleUserFields);
    userTypeExisting.addEventListener('change', toggleUserFields);
    toggleUserFields();
});
</script>
@endpush
