import './bootstrap';

import {
    Collapse,
    Dropdown,
    Modal,
    Popover,
    Tab,
    Toast,
    Tooltip,
} from 'bootstrap';

import Alpine from 'alpinejs';
import { productWizard } from './admin/product-wizard';
import { initOrderConfirmActions } from './admin/order-actions';
import { initCategoriesIndexTable } from './admin/categories-index';
import { initProductsIndexTable } from './admin/products-index';
import { initGoalsIndexTable } from './admin/goals-index';
import { initVariantFormOptions } from './admin/variant-form-options';
import { initOrderCreate } from './admin/order-create';
import { initLiveNotifications } from './admin/live-notifications';
import { initAddressMapPickers } from './admin/address-map-picker';
import { initSupportChatShow } from './admin/support-chat-show';
import { initSliderForm } from './admin/slider-form';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('searchComponent', () => ({
        query: '',
        results: [],
        isLoading: false,
        isOpen: false,
        debounceTimer: null,
        searchUrl: window.__adminSearchUrl ?? '/admin/search',
        labels: window.__adminSearchLabels ?? {
            placeholder: 'Search…',
            noResults: 'No results found',
            pages: 'Pages',
            minChars: 'Type at least 2 characters',
        },

        init() {
            document.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    this.focusInput();
                }

                if (event.key === 'Escape') {
                    this.close();
                }
            });

            document.addEventListener('click', (event) => {
                if (!this.$el.contains(event.target)) {
                    this.close();
                }
            });
        },

        focusInput() {
            const input = this.$el.querySelector('[data-search-input]');

            if (input) {
                input.focus();
                this.isOpen = true;
            }
        },

        close() {
            this.isOpen = false;
        },

        onInput() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.search(), 250);
        },

        async search() {
            const term = this.query.trim();

            if (term.length < 2) {
                this.results = [];
                this.isLoading = false;
                this.isOpen = term.length > 0;

                return;
            }

            this.isLoading = true;
            this.isOpen = true;

            try {
                const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Search failed: ${response.status}`);
                }

                const data = await response.json();
                this.results = Array.isArray(data.results) ? data.results : [];
            } catch (error) {
                console.error('Admin search failed', error);
                this.results = [];
            } finally {
                this.isLoading = false;
            }
        },
    }));

    Alpine.data('themeSwitch', () => ({
        currentTheme: 'light',

        init() {
            this.currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', this.currentTheme);
        },

        toggle() {
            this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', this.currentTheme);
            localStorage.setItem('theme', this.currentTheme);
            window.dispatchEvent(
                new CustomEvent('gahez-theme-changed', { detail: { theme: this.currentTheme } }),
            );
        },
    }));

    Alpine.data('localeSwitch', (config = {}) => ({
        current: config.current || 'en',
        enUrl: config.enUrl || '/locale/en',
        arUrl: config.arUrl || '/locale/ar',

        get isArabic() {
            return String(this.current).startsWith('ar');
        },

        switchTo(locale) {
            if (locale === 'ar' && !this.isArabic) {
                window.location.href = this.arUrl;
            } else if (locale === 'en' && this.isArabic) {
                window.location.href = this.enUrl;
            }
        },

        toggle() {
            if (this._navigating) {
                return;
            }

            this._navigating = true;
            const nextLocale = this.isArabic ? 'en' : 'ar';
            this.current = nextLocale;
            const url = nextLocale === 'ar' ? this.arUrl : this.enUrl;

            window.location.assign(url);
        },
    }));

    Alpine.data('productWizard', (config = {}) => productWizard(config));

    Alpine.data('offerablePicker', (config = {}) => ({
        products: config.products ?? [],
        categories: config.categories ?? [],
        typeKey: config.initialTypeKey ?? 'product',
        selectedId: config.initialId ? String(config.initialId) : '',

        currentOptions() {
            return this.typeKey === 'category' ? this.categories : this.products;
        },

        onTypeChange() {
            const ids = this.currentOptions().map((item) => String(item.id));

            if (!ids.includes(String(this.selectedId))) {
                this.selectedId = '';
            }
        },

        init() {
            this.$nextTick(() => {
                const options = this.currentOptions();

                if (
                    this.selectedId
                    && !options.some((item) => String(item.id) === String(this.selectedId))
                ) {
                    this.selectedId = '';
                }
            });
        },
    }));
});

Alpine.start();

const ADMIN_LIST_FILTER_HEADER = 'X-Admin-List-Filter';

function initTooltipsIn(root = document) {
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        const existing = Tooltip.getInstance(el);

        if (existing) {
            existing.dispose();
        }

        new Tooltip(el);
    });
}

function getAdminListResultsContainer(form) {
    return form.closest('.admin-main')?.querySelector('[data-admin-list-results]')
        ?? document.querySelector('[data-admin-list-results]');
}

function buildUrlFromForm(form) {
    const url = new URL(form.action, window.location.origin);
    const params = new URLSearchParams(new FormData(form));

    url.search = params.toString();

    return url.toString();
}

async function fetchAdminListResults(url, form = null) {
    const container = form
        ? getAdminListResultsContainer(form)
        : document.querySelector('[data-admin-list-results]');

    if (!container) {
        return;
    }

    container.classList.add('is-loading');

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [ADMIN_LIST_FILTER_HEADER]: '1',
                Accept: 'text/html',
            },
        });

        if (!response.ok) {
            throw new Error(`Request failed: ${response.status}`);
        }

        container.innerHTML = await response.text();
        window.history.pushState({ adminListFilter: true }, '', url);
        initTooltipsIn(container);
    } catch (error) {
        console.error('Admin list filter failed', error);
    } finally {
        container.classList.remove('is-loading');
    }
}

function initAdminListFilters() {
    document.querySelectorAll('form[data-admin-list-filters]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            fetchAdminListResults(buildUrlFromForm(form), form);
        });

        const searchInput = form.querySelector('[data-auto-search]');

        if (!searchInput) {
            return;
        }

        let debounceTimer;

        const runSearch = () => {
            fetchAdminListResults(buildUrlFromForm(form), form);
        };

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runSearch, 400);
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                clearTimeout(debounceTimer);
                runSearch();
            }
        });
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-admin-list-results] .pagination a');

        if (!link?.href) {
            return;
        }

        event.preventDefault();
        fetchAdminListResults(link.href);
    });

    window.addEventListener('popstate', () => {
        if (!document.querySelector('[data-admin-list-results]')) {
            return;
        }

        fetchAdminListResults(window.location.href);
    });
}

function initAjaxForms() {
    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.classList.contains('ajax-form')) return;
        
        if (event.defaultPrevented) return;

        event.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        let originalText = '';
        if (submitBtn) {
            submitBtn.disabled = true;
            originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (response.redirected) {
                // Fetch followed a redirect, response is the new page HTML
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('.admin-main');

            if (newContent) {
                document.querySelector('.admin-main').innerHTML = newContent.innerHTML;
                initTooltipsIn(document.querySelector('.admin-main'));
            } else {
                window.location.reload();
            }
        } catch (error) {
            console.error('Ajax form submission failed', error);
            window.location.reload();
        } finally {
            if (submitBtn && document.body.contains(submitBtn)) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLiveNotifications(window.__adminNotifications ?? {});

    initOrderConfirmActions();
    initProductsIndexTable();
    initGoalsIndexTable();
    initCategoriesIndexTable();
    initAdminListFilters();
    initAjaxForms();

    if (document.getElementById('variantOptionsContainer')) {
        initVariantFormOptions(window.__variantFormOptions ?? {});
    }

    if (document.getElementById('createOrderForm')) {
        initOrderCreate(window.__orderCreate ?? {});
    }

    if (document.querySelector('[data-map-picker]')) {
        initAddressMapPickers(window.__addressMapPickerLabels ?? {});
    }

    if (document.querySelector('[data-support-chat-show]')) {
        initSupportChatShow(window.__supportChatRealtime ?? {});
    }

    initSliderForm();

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new Tooltip(el));
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => new Popover(el));

    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        loadingScreen.classList.add('fade-out');
        setTimeout(() => loadingScreen.remove(), 300);
    }
});
