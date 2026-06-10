import { buildDeleteConfirmMessage } from './delete-confirm';
import { confirmWithModal } from './confirm-modal';

function postJson(url, body = {}) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        body: JSON.stringify(body),
    }).then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message ?? 'Request failed');
        }

        return data;
    });
}

function handleToggleChange(event, field) {
    const input = event.target;

    if (!input.matches(`.toggle-${field}-btn`)) {
        return;
    }

    const previous = !input.checked;

    postJson(input.dataset.toggleUrl)
        .catch(() => {
            input.checked = previous;
            window.alert(input.dataset.errorMessage ?? 'Failed to update.');
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
        if (event.target.classList.contains('toggle-active-btn')) {
            handleToggleChange(event, 'is_active');
        }

        if (event.target.classList.contains('toggle-featured-btn')) {
            handleToggleChange(event, 'is_featured');
        }

        if (event.target.classList.contains('toggle-approved-btn')) {
            handleToggleChange(event, 'is_approved');
        }
    });

    document.addEventListener('click', handleDeleteClick);
}
