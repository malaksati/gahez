<header class="admin-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2 flex-shrink-0 me-2">
                <button class="hamburger-menu" type="button" data-sidebar-toggle aria-label="{{ __('messages.Menu') }}">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand d-flex align-items-center p-0 m-0" href="{{ route('v1.admin.dashboard') }}">
                    @include('layouts.partials.brand-logo', ['height' => 48])
                </a>
            </div>

            <div class="search-container flex-grow-1 mx-2" x-data="searchComponent" @click.stop>
                <div class="position-relative">
                    <input
                        type="search"
                        class="form-control"
                        :placeholder="labels.placeholder"
                        x-model="query"
                        @input="onInput()"
                        @focus="isOpen = query.trim().length > 0"
                        data-search-input
                        aria-label="{{ __('messages.Search') }}"
                        autocomplete="off"
                    >
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3 d-flex align-items-center gap-2">
                        <span
                            x-show="isLoading"
                            class="spinner-border spinner-border-sm text-muted"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        <kbd class="d-none d-md-inline badge bg-light text-muted border fw-normal small">Ctrl+K</kbd>
                    </span>

                    <div
                        x-show="isOpen"
                        x-transition
                        @click.stop
                        class="admin-search-dropdown position-absolute top-100 start-0 w-100 bg-white border rounded-2 shadow-lg mt-1 z-3 overflow-hidden"
                    >
                        <template x-if="query.trim().length > 0 && query.trim().length < 2">
                            <div class="px-3 py-3 text-muted small" x-text="labels.minChars"></div>
                        </template>

                        <template x-if="query.trim().length >= 2 && ! isLoading && results.length === 0">
                            <div class="px-3 py-3 text-muted small" x-text="labels.noResults"></div>
                        </template>

                        <template x-for="(result, index) in results" :key="result.url + '-' + index">
                            <a
                                :href="result.url"
                                class="admin-search-result d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-dark border-bottom"
                                @click="close()"
                            >
                                <i class="bi flex-shrink-0 text-muted" :class="'bi-' + (result.icon || 'link-45deg')"></i>
                                <span class="flex-grow-1 min-w-0">
                                    <span class="d-block text-truncate fw-medium" x-text="result.title"></span>
                                    <small
                                        class="d-block text-muted text-truncate"
                                        x-show="result.subtitle"
                                        x-text="result.subtitle"
                                    ></small>
                                </span>
                                <small class="text-muted text-nowrap ms-1" x-text="result.group"></small>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <div class="navbar-nav flex-row">
                @include('layouts.partials.theme-switch-button', ['variant' => 'admin'])
                @include('layouts.partials.locale-switch-button', ['variant' => 'admin'])

                @auth
                    @include('layouts.partials.notifications-dropdown')
                @endauth

                <div class="dropdown">
                    <button class="btn btn-outline-secondary d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ auth()->user()->image }}" alt="" width="24" height="24" class="rounded-circle me-2" style="object-fit: cover;">
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('v1.admin.profile.edit') }}">
                                <i class="bi bi-person me-2"></i>{{ __('messages.Profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('messages.Sign out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>
