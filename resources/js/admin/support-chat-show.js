import { createEcho } from '../echo';

const POLL_INTERVAL_MS = 4000;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatMessageDate(isoString) {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const locale = document.documentElement.lang?.replace('_', '-') || 'en';

    return new Intl.DateTimeFormat(locale, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

function buildMessageHtml(message, labels) {
    const senderName = escapeHtml(message.sender?.name ?? labels.system);
    const isAdmin = message.sender_type === 'admin';
    const badgeClass = isAdmin ? 'danger' : 'info';
    const badgeLabel = escapeHtml(isAdmin ? labels.admin : labels.customer);
    const body = escapeHtml(message.message);
    const createdAt = formatMessageDate(message.created_at);

    let attachmentsHtml = '';

    if (Array.isArray(message.attachments) && message.attachments.length > 0) {
        const links = message.attachments.map((url) => {
            const safeUrl = escapeHtml(url);
            const name = escapeHtml(url.split('/').pop() ?? 'file');

            return `<a href="${safeUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-paperclip me-1"></i>${name}
            </a>`;
        }).join('');

        attachmentsHtml = `<div class="d-flex flex-wrap gap-2">${links}</div>`;
    }

    return `
        <div class="message-item mb-4 pb-4 border-bottom" data-message-id="${message.id}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <strong>${senderName}</strong>
                    <span class="badge bg-${badgeClass} ms-2">${badgeLabel}</span>
                </div>
                <small class="text-muted">${createdAt}</small>
            </div>
            <div class="bg-light rounded p-3 mb-2">${body}</div>
            ${attachmentsHtml}
        </div>
    `;
}

function scrollMessagesToBottom(container) {
    if (!container) {
        return;
    }

    container.scrollTop = container.scrollHeight;
}

export function initSupportChatShow(config = {}) {
    const root = document.querySelector('[data-support-chat-show]');

    if (!root) {
        return;
    }

    const messagesList = root.querySelector('[data-support-chat-messages]');
    const emptyState = root.querySelector('[data-support-chat-empty]');
    const countEl = root.querySelector('[data-support-chat-count]');
    const replyForm = root.querySelector('[data-support-chat-reply-form]');
    const scrollContainer = messagesList?.closest('.card-body') ?? messagesList;

    if (!messagesList) {
        return;
    }

    const labels = {
        admin: config.labels?.admin ?? 'Admin',
        customer: config.labels?.customer ?? 'Customer',
        system: config.labels?.system ?? 'System',
    };

    const seenMessageIds = new Set(
        [...messagesList.querySelectorAll('[data-message-id]')].map((el) => el.dataset.messageId),
    );

    let pollTimer = null;
    let pollInFlight = false;

    function updateCount() {
        if (!countEl) {
            return;
        }

        const total = seenMessageIds.size;
        const base = countEl.dataset.countLabel ?? 'Messages';
        countEl.textContent = `${base} (${total})`;
    }

    function appendMessage(message, { scroll = true } = {}) {
        if (!message?.id || seenMessageIds.has(String(message.id))) {
            return false;
        }

        seenMessageIds.add(String(message.id));

        if (emptyState?.isConnected) {
            emptyState.remove();
        }

        messagesList.insertAdjacentHTML('beforeend', buildMessageHtml(message, labels));
        updateCount();

        if (scroll) {
            scrollMessagesToBottom(scrollContainer);
        }

        return true;
    }

    function mergeMessages(messages) {
        let added = false;

        messages.forEach((message) => {
            if (appendMessage(message, { scroll: false })) {
                added = true;
            }
        });

        if (added) {
            scrollMessagesToBottom(scrollContainer);
        }
    }

    async function syncMessages() {
        const pollUrl = config.pollUrl;

        if (!pollUrl || pollInFlight || document.hidden) {
            return;
        }

        pollInFlight = true;

        try {
            const response = await fetch(pollUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`Message sync failed: ${response.status}`);
            }

            const payload = await response.json();
            const messages = Array.isArray(payload.data) ? payload.data : [];

            mergeMessages(messages);
        } catch (error) {
            console.error('Support chat message sync failed', error);
        } finally {
            pollInFlight = false;
        }
    }

    function startPolling() {
        if (!config.pollUrl) {
            return;
        }

        stopPolling();
        pollTimer = window.setInterval(syncMessages, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        if (!pollTimer) {
            return;
        }

        clearInterval(pollTimer);
        pollTimer = null;
    }

    if (replyForm) {
        replyForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitBtn = replyForm.querySelector('[type="submit"]');
            let originalHtml = '';

            if (submitBtn) {
                submitBtn.disabled = true;
                originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            }

            try {
                const response = await fetch(replyForm.action, {
                    method: 'POST',
                    body: new FormData(replyForm),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Support-Chat-Ajax': '1',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    const message = data?.message ?? data?.errors?.message?.[0] ?? 'Failed to send message';
                    throw new Error(message);
                }

                appendMessage(data.data ?? data);
                replyForm.reset();
            } catch (error) {
                console.error('Support chat reply failed', error);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            }
        });
    }

    syncMessages();
    startPolling();

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
            return;
        }

        syncMessages();
        startPolling();
    });

    if (config.websocketEnabled) {
        const echo = createEcho({ authEndpoint: config.authEndpoint ?? '/admin/broadcasting/auth' });

        if (!echo) {
            console.warn('Support chat realtime: Reverb client is not configured. Run npm run build after setting VITE_REVERB_* in .env.');
        } else {
            const supportId = config.supportId ?? root.dataset.supportId;

            if (supportId) {
                echo.private(`support.${supportId}`)
                    .listen('.support.message.sent', (payload) => {
                        if (payload?.message) {
                            appendMessage(payload.message);
                        }
                    })
                    .error((error) => {
                        console.error('Support chat channel subscription failed', error);
                    });
            }
        }
    }

    scrollMessagesToBottom(scrollContainer);
}
