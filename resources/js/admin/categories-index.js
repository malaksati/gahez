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

        if (! response.ok) {
            throw new Error(data.message ?? 'Request failed');
        }

        return data;
    });
}

function buildDeleteMessage(button) {
    const name = button.dataset.categoryName ?? '';
    const hasChildren = button.dataset.hasChildren === 'true';
    const childrenCount = parseInt(button.dataset.childrenCount ?? '0', 10);

    let extra = '';

    if (hasChildren && childrenCount > 0) {
        const template = window.__categoriesIndexLabels?.confirmDeleteWithChildren
            ?? 'This category has :count subcategories. Deleting it will also delete all subcategories.';

        extra = template.replace(':count', String(childrenCount));
    }
    return {
        name,
        extra,
    };
}

async function handleDeleteClick(event) {
    const button = event.target.closest('.delete-category-btn');

    if (! button) {
        return;
    }

    event.preventDefault();

    const { name, extra } = buildDeleteMessage(button);
    const message = buildDeleteConfirmMessage({
        name,
        confirmText: window.__categoriesIndexLabels?.confirmDelete ?? 'Delete this category?',
        cannotUndoText: window.__categoriesIndexLabels?.cannotUndo ?? 'This action cannot be undone.',
        extraText: extra,
    });

    const approved = await confirmWithModal(message);

    if (! approved) {
        return;
    }

    const row = button.closest('tr');

    postJson(button.dataset.deleteUrl, { _method: 'DELETE' })
        .then(() => {
            if (row) {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
        })
        .catch((error) => {
            window.alert(error.message ?? window.__categoriesIndexLabels?.deleteFailed ?? 'Failed to delete category.');
        });
}

export function initCategoriesIndexTable() {
    if (document.body.dataset.page !== 'categories') {
        return;
    }

    document.addEventListener('click', handleDeleteClick);
}
