@extends('layouts.app')

@section('title', __('messages.Profile'))
@section('subtitle', __('messages.Manage your account details'))

@section('content')
    <form action="{{ route('v1.admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center pt-5 pb-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $user->image }}" alt="{{ $user->name }}" id="profile-photo-preview"
                                class="rounded-circle border border-2 border-light shadow-sm"
                                width="128" height="128" style="object-fit: cover;">
                            @if ($user->hasRole('super-admin'))
                                <span class="position-absolute bottom-0 end-0 badge bg-danger rounded-pill px-2 py-1">
                                    <i class="bi bi-shield-fill-check"></i>
                                </span>
                            @elseif ($user->hasRole('admin'))
                                <span class="position-absolute bottom-0 end-0 badge bg-primary rounded-pill px-2 py-1">
                                    <i class="bi bi-person-gear"></i>
                                </span>
                            @endif
                        </div>
                        <h5 class="mb-1">{{ $user->name }}</h5>
                        <p class="text-muted small mb-2">{{ $user->email ?? $user->phone }}</p>
                        @if ($user->hasRole('super-admin'))
                            <span class="badge bg-danger bg-opacity-10 text-danger">{{ __('messages.Super Admin') }}</span>
                        @elseif ($user->hasRole('admin'))
                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ __('messages.Admin') }}</span>
                        @endif
                    </div>
                    <div class="card-body border-top pt-4">
                        <label for="image" class="form-label small fw-semibold">{{ __('messages.Profile photo') }}</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="form-control form-control-sm @error('image') is-invalid @enderror">
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($user->getRawOriginal('image'))
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="remove_image" id="remove_image" value="1"
                                    {{ old('remove_image') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remove_image">{{ __('messages.Remove photo') }}</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>{{ __('messages.Permissions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($user->hasRole('super-admin'))
                            <div class="alert alert-info mb-0 py-2 small">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ __('messages.Super Admin has all permissions automatically.') }}
                            </div>
                        @else
                            @php $userPermissions = $user->getPermissionNames()->toArray(); @endphp
                            @foreach ($permissionsGrouped as $group => $permissions)
                                <h6 class="text-muted text-uppercase small fw-bold mt-3 mb-2">{{ $group }}</h6>
                                <div class="d-flex flex-wrap gap-1 mb-1">
                                    @foreach ($permissions as $permName => $permLabel)
                                        @if (in_array($permName, $userPermissions))
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                <i class="bi bi-check-circle me-1"></i>{{ $permLabel }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                                <i class="bi bi-x-circle me-1"></i>{{ $permLabel }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">{{ __('messages.Basic Information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('messages.Name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">{{ __('messages.Email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="form-control @error('email') is-invalid @enderror" autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                    class="form-control @error('phone') is-invalid @enderror" autocomplete="tel">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <p class="form-text mb-3">{{ __('messages.Provide email or phone') }}</p>

                        <div class="mb-0">
                            <label for="birthdate" class="form-label">{{ __('messages.Birthdate') }}</label>
                            <input type="date" name="birthdate" id="birthdate"
                                value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
                                class="form-control @error('birthdate') is-invalid @enderror"
                                max="{{ now()->subDay()->format('Y-m-d') }}">
                            @error('birthdate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">{{ __('messages.Change password') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">{{ __('messages.Leave blank to keep current password') }}</p>
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="password" class="form-label">{{ __('messages.New password') }}</label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">{{ __('messages.Confirm password') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1"></i>{{ __('messages.Save changes') }}
                    </button>
                    <a href="{{ route('v1.admin.dashboard') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('image');
            const preview = document.getElementById('profile-photo-preview');

            imageInput?.addEventListener('change', function () {
                const file = this.files?.[0];
                if (!file || !preview) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target?.result ?? preview.src;
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endpush
