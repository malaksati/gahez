import { confirmWithModal } from './confirm-modal';

/**
 * Bootstrap modal confirmation for order / payment status actions.
 */
export function initOrderConfirmActions() {
    if (document.body.dataset.orderConfirmInitialized === 'true') {
        return;
    }
    document.body.dataset.orderConfirmInitialized = 'true';

    document.querySelectorAll('[data-order-confirm-submit]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const form = button.closest('form');
            const message = button.dataset.confirmMessage ?? '';

            if (! form || ! message) {
                return;
            }

            const ok = await confirmWithModal(message);
            if (! ok) {
                return;
            }

            form.dataset.confirmApproved = 'true';
            form.requestSubmit();
        });
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.confirmApproved === 'true') {
            delete form.dataset.confirmApproved;

            return;
        }

        const message = form.dataset.confirmMessage;

        if (! message) {
            return;
        }

        event.preventDefault();
        const ok = await confirmWithModal(message);
        if (! ok) {
            return;
        }

        form.dataset.confirmApproved = 'true';
        form.requestSubmit();
    }, true);
}
