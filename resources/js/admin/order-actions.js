import { confirmWithModal } from './confirm-modal';

function resolveConfirmForm(element) {
    if (element instanceof HTMLFormElement) {
        return element;
    }

    const formId = element.getAttribute('form');

    if (formId) {
        const linkedForm = document.getElementById(formId);

        if (linkedForm instanceof HTMLFormElement) {
            return linkedForm;
        }
    }

    return element.closest('form');
}

function resolveConfirmMessage(element, form) {
    const buttonMessage = element.dataset?.confirmMessage;

    if (buttonMessage) {
        return buttonMessage;
    }

    return form?.dataset?.confirmMessage ?? '';
}

/**
 * Bootstrap modal confirmation for destructive actions, status changes, and deletes.
 */
export function initOrderConfirmActions() {
    if (document.body.dataset.orderConfirmInitialized === 'true') {
        return;
    }
    document.body.dataset.orderConfirmInitialized = 'true';

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-order-confirm-submit]');

        if (! button) {
            return;
        }

        event.preventDefault();

        const form = resolveConfirmForm(button);
        const message = resolveConfirmMessage(button, form);

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
