@extends('layouts.app')

@section('title', __('messages.Profile'))
@section('subtitle', __('messages.Manage your account details'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                        <img src="{{ $user->image }}" alt="{{ $user->name }}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                        <div>
                            <h2 class="h5 mb-1">{{ $user->name }}</h2>
                            <p class="text-muted mb-0 small">{{ $user->email ?? $user->phone }}</p>
                            @if ($user->hasRole('admin'))
                                <span class="badge bg-primary mt-1">{{ __('messages.Admin') }}</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('v1.admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('messages.Name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('messages.Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror" autocomplete="email">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                class="form-control @error('phone') is-invalid @enderror" autocomplete="tel">
                            <small class="text-muted d-block">{{ __('messages.Provide email or phone') }}</small>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="birthdate" class="form-label">{{ __('messages.Birthdate') }}</label>
                            <input type="date" name="birthdate" id="birthdate"
                                value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
                                class="form-control @error('birthdate') is-invalid @enderror"
                                max="{{ now()->subDay()->format('Y-m-d') }}">
                            <small class="text-muted d-block">{{ __('messages.Birthdate hint') }}</small>
                            @error('birthdate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">{{ __('messages.Profile photo') }}</label>
                            <input type="file" name="image" id="image" accept="image/*"
                                class="form-control @error('image') is-invalid @enderror">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($user->getRawOriginal('image'))
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="remove_image" id="remove_image" value="1"
                                        {{ old('remove_image') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remove_image">{{ __('messages.Remove photo') }}</label>
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <h3 class="h6 mb-3">{{ __('messages.Change password') }}</h3>
                        <p class="text-muted small">{{ __('messages.Leave blank to keep current password') }}</p>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('messages.New password') }}</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">{{ __('messages.Confirm password') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" autocomplete="new-password">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('messages.Save changes') }}</button>
                            <a href="{{ route('v1.admin.dashboard') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
