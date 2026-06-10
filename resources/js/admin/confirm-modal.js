import { Modal } from 'bootstrap';

let modalInstance = null;
let messageElement = null;
let confirmButton = null;
let initialized = false;
let pendingResolver = null;

function ensureInitialized() {
    if (initialized) {
        return Boolean(modalInstance && messageElement && confirmButton);
    }

    initialized = true;
    const modalEl = document.getElementById('orderActionConfirmModal');

    if (! modalEl) {
        return false;
    }

    modalInstance = Modal.getOrCreateInstance(modalEl);
    messageElement = modalEl.querySelector('[data-order-confirm-message]');
    confirmButton = modalEl.querySelector('[data-order-confirm-accept]');

    confirmButton?.addEventListener('click', () => {
        const resolve = pendingResolver;
        pendingResolver = null;
        modalInstance.hide();
        resolve?.(true);
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (pendingResolver) {
            const resolve = pendingResolver;
            pendingResolver = null;
            resolve(false);
        }
    });

    return Boolean(modalInstance && messageElement && confirmButton);
}

export function confirmWithModal(message) {
    if (! ensureInitialized() || ! messageElement) {
        return Promise.resolve(window.confirm(message));
    }

    messageElement.textContent = message;

    return new Promise((resolve) => {
        pendingResolver = resolve;
        modalInstance.show();
    });
}
