function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
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

export function initGoalsIndexTable() {
    if (document.body.dataset.page !== 'goals') {
        return;
    }

    document.addEventListener('change', (event) => {
        if (!event.target.classList.contains('goal-toggle-active-btn')) {
            return;
        }

        const input = event.target;
        const url = input.getAttribute('data-toggle-url');
        const previous = !input.checked;

        if (!url) {
            input.checked = previous;
            window.alert('Toggle URL is missing.');

            return;
        }

        input.disabled = true;

        postToggle(url)
            .then(() => window.location.reload())
            .catch((error) => {
                input.checked = previous;
                input.disabled = false;
                window.alert(error.message ?? 'Failed to update.');
            });
    });
}
