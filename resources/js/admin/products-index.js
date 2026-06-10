import { buildDeleteConfirmMessage } from './delete-confirm';
import { confirmWithModal } from './confirm-modal';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function postJson(url, body = {}) {
    const token = csrfToken();

    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ ...body, _token: token }),
    }).then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message ?? `Request failed (${response.status})`);
        }

        return data;
    });
}

function postToggle(url) {
    const token = csrfToken();

    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        credentials: 'same-origin',
        body: new URLSearchParams({ _token: token }),
    }).then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message ?? `Request failed (${response.status})`);
        }

        return data;
    });
}

function handleToggleChange(event) {
    const input = event.target;
    const url = input.getAttribute('data-toggle-url');

    if (!url) {
        window.alert('Toggle URL is missing.');
        return;
    }

    const previous = !input.checked;
    input.disabled = true;

    postToggle(url)
        .then(() => {
            window.location.reload();
        })
        .catch((error) => {
            input.checked = previous;
            input.disabled = false;
            window.alert(error.message ?? input.dataset.errorMessage ?? 'Failed to update.');
        });
}

async function handleDeleteClick(event) {
    const button = event.target.closest('.delete-product-btn');

    if (!button) {
        return;
    }

    event.preventDefault();

    const name = button.dataset.productName ?? '';
    const message = button.dataset.confirmMessage || buildDeleteConfirmMessage({
        name,
        confirmText: window.__productsIndexLabels?.confirmDelete ?? 'Delete this product?',
        cannotUndoText: window.__productsIndexLabels?.cannotUndo ?? 'This action cannot be undone.',
    });

    const approved = await confirmWithModal(message);
    if (!approved) {
        return;
    }

    const row = button.closest('tr');

    postJson(button.dataset.deleteUrl, { _method: 'DELETE' })
        .then(() => {
            if (row) {
                row.remove();
            }
        })
        .catch((error) => {
            window.alert(error.message ?? window.__productsIndexLabels?.deleteFailed ?? 'Failed to delete product.');
        });
}

export function initProductsIndexTable() {
    if (document.body.dataset.page !== 'products') {
        return;
    }

    document.addEventListener('change', (event) => {
        if (
            event.target.classList.contains('toggle-active-btn')
            || event.target.classList.contains('toggle-featured-btn')
            || event.target.classList.contains('toggle-approved-btn')
        ) {
            handleToggleChange(event);
        }
    });

    document.addEventListener('click', handleDeleteClick);
}
