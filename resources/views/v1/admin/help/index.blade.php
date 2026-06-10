@extends('layouts.app')

@php
    $page = 'help';
@endphp

@section('title', __('messages.Help'))
@section('subtitle', __('messages.Help and documentation'))

@section('content')
    <p class="text-muted mb-4">{{ __('messages.Help intro') }}</p>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-box-seam fs-4 text-primary d-block mb-2"></i>
                    <span class="small fw-semibold">{{ __('messages.Catalog') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-cart-check fs-4 text-primary d-block mb-2"></i>
                    <span class="small fw-semibold">{{ __('messages.Orders') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-truck fs-4 text-primary d-block mb-2"></i>
                    <span class="small fw-semibold">{{ __('messages.Deliveries') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-gift fs-4 text-primary d-block mb-2"></i>
                    <span class="small fw-semibold">{{ __('messages.Marketing') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-chat-dots fs-4 text-primary d-block mb-2"></i>
                    <span class="small fw-semibold">{{ __('messages.Tickets') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <i class="bi bi-star fs-4 text-primary d-block mb-2"></i>
                    <span class="small fw-semibold">{{ __('messages.Rating & Support') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion" id="helpAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#helpCatalog">
                    <i class="bi bi-box-seam me-2"></i>{{ __('messages.Help catalog title') }}
                </button>
            </h2>
            <div id="helpCatalog" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-2">{{ __('messages.Help catalog') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('messages.Help catalog tip categories') }}</li>
                        <li>{{ __('messages.Help catalog tip products') }}</li>
                        <li>{{ __('messages.Help catalog tip brands') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpOrders">
                    <i class="bi bi-cart-check me-2"></i>{{ __('messages.Help orders title') }}
                </button>
            </h2>
            <div id="helpOrders" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-2">{{ __('messages.Help orders') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('messages.Help orders tip status') }}</li>
                        <li>{{ __('messages.Help orders tip payment') }}</li>
                        <li>{{ __('messages.Help orders tip create') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpPayments">
                    <i class="bi bi-credit-card me-2"></i>{{ __('messages.Help payments title') }}
                </button>
            </h2>
            <div id="helpPayments" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-2">{{ __('messages.Help payments client') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('messages.Help payments tip cod') }}</li>
                        <li>{{ __('messages.Help payments tip online') }}</li>
                        <li>{{ __('messages.Help payments tip wallet') }}</li>
                        <li>{{ __('messages.Help payments tip pending') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpDeliveries">
                    <i class="bi bi-truck me-2"></i>{{ __('messages.Help deliveries title') }}
                </button>
            </h2>
            <div id="helpDeliveries" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-2">{{ __('messages.Help deliveries body') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('messages.Help deliveries tip zones') }}</li>
                        <li>{{ __('messages.Help deliveries tip drivers') }}</li>
                        <li>{{ __('messages.Help deliveries tip shifts') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpMarketing">
                    <i class="bi bi-gift me-2"></i>{{ __('messages.Help marketing title') }}
                </button>
            </h2>
            <div id="helpMarketing" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-2">{{ __('messages.Help marketing body') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('messages.Help marketing tip offers') }}</li>
                        <li>{{ __('messages.Help marketing tip coupons') }}</li>
                        <li>{{ __('messages.Help marketing tip sliders') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpSupport">
                    <i class="bi bi-chat-dots me-2"></i>{{ __('messages.Help support title') }}
                </button>
            </h2>
            <div id="helpSupport" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-2">{{ __('messages.Help support body') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('messages.Help support tip tickets') }}</li>
                        <li>{{ __('messages.Help ratings') }}</li>
                        <li>{{ __('messages.Help support tip reports') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpRefunds">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>{{ __('messages.Help refunds title') }}
                </button>
            </h2>
            <div id="helpRefunds" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-0">{{ __('messages.Help refunds body') }}</p>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#helpNotifications">
                    <i class="bi bi-bell me-2"></i>{{ __('messages.Help notifications title') }}
                </button>
            </h2>
            <div id="helpNotifications" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                <div class="accordion-body text-muted">
                    <p class="mb-0">{{ __('messages.Help notifications body') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h6 mb-2"><i class="bi bi-envelope me-2"></i>{{ __('messages.Help contact title') }}</h2>
            <p class="text-muted small mb-0">{{ __('messages.Help contact') }}</p>
        </div>
    </div>
@endsection
