import { Toast } from 'bootstrap';

const TOAST_DURATION_MS = 10000;
const POLL_INTERVAL_MS = 500;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function getToastContainer() {
    let container = document.getElementById('admin-toast-container');

    if (!container) {
        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.className = 'admin-toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(container);
    }

    return container;
}

async function markNotificationRead(notificationId, config) {
    if (!notificationId || !config.markReadUrl) {
        return null;
    }

    const url = config.markReadUrl.replace('__ID__', encodeURIComponent(notificationId));

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });

        if (!response.ok) {
            throw new Error(`Mark notification read failed: ${response.status}`);
        }

        const data = await response.json();

        return Number(data.unread_count ?? NaN);
    } catch (error) {
        console.error('Mark notification read failed', error);

        return null;
    }
}

function showNotificationToast(notification, labels, config, dropdown) {
    const container = getToastContainer();
    const toastEl = document.createElement('div');

    toastEl.className = 'toast admin-notification-toast align-items-center border-0 shadow';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    const title = escapeHtml(notification.title || labels.notification);
    const message = escapeHtml(notification.message || labels.newNotification);

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-bell-fill text-primary mt-1"></i>
                    <div class="min-w-0">
                        <div class="fw-semibold small">${title}</div>
                        <div class="small text-muted text-truncate">${message}</div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="${escapeHtml(labels.close)}"></button>
        </div>
    `;

    if (notification.url) {
        toastEl.classList.add('admin-notification-toast--clickable');
        toastEl.addEventListener('click', async (event) => {
            if (event.target.closest('.btn-close')) {
                return;
            }

            event.preventDefault();

            const unreadCount = await markNotificationRead(notification.id, config);

            if (unreadCount !== null && !Number.isNaN(unreadCount)) {
                updateBadge(dropdown, unreadCount);
                updateMarkAllButton(dropdown, unreadCount);
            }

            window.location.href = notification.url;
        });
    }

    container.appendChild(toastEl);

    const bsToast = new Toast(toastEl, {
        autohide: true,
        delay: TOAST_DURATION_MS,
    });

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    bsToast.show();
}

function renderNotificationItem(notification, labels) {
    const li = document.createElement('li');
    const link = document.createElement('a');

    link.href = notification.url;
    link.className = `dropdown-item py-2${notification.read_at ? '' : ' fw-semibold'}`;
    link.setAttribute('data-notification-link', '');
    link.setAttribute('data-notification-id', notification.id);
    link.innerHTML = `
        <div class="small text-muted">${escapeHtml(notification.created_at_human)}</div>
        <div class="text-truncate">${escapeHtml(notification.message || labels.newNotification)}</div>
    `;

    li.appendChild(link);

    return li;
}

function updateBadge(dropdown, unreadCount) {
    const badge = dropdown.querySelector('[data-notifications-badge]');

    if (!badge) {
        return;
    }

    if (unreadCount > 0) {
        badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function updateNotificationList(dropdown, notifications, labels) {
    const menu = dropdown.querySelector('[data-notifications-menu]');
    const divider = dropdown.querySelector('[data-notifications-divider]');

    if (!menu || !divider) {
        return;
    }

    menu.querySelectorAll('[data-notification-item], [data-notifications-empty]').forEach((item) => item.remove());

    if (!notifications.length) {
        const emptyItem = document.createElement('li');
        const emptyText = document.createElement('span');

        emptyItem.setAttribute('data-notifications-empty', '');
        emptyText.className = 'dropdown-item text-muted';
        emptyText.textContent = labels.noNotifications;
        emptyItem.appendChild(emptyText);
        menu.insertBefore(emptyItem, divider);

        return;
    }

    notifications.forEach((notification) => {
        const item = renderNotificationItem(notification, labels);

        item.setAttribute('data-notification-item', '');
        item.setAttribute('data-notification-id', notification.id);
        menu.insertBefore(item, divider);
    });
}

function updateMarkAllButton(dropdown, unreadCount) {
    const markAllItem = dropdown.querySelector('[data-notifications-mark-all]');

    if (!markAllItem) {
        return;
    }

    markAllItem.classList.toggle('d-none', unreadCount === 0);
}

export function initLiveNotifications(config = {}) {
    const dropdown = document.querySelector('[data-live-notifications]');

    if (!dropdown || !config.feedUrl) {
        return;
    }

    const labels = {
        notification: config.labels?.notification ?? 'Notification',
        newNotification: config.labels?.newNotification ?? 'New notification',
        noNotifications: config.labels?.noNotifications ?? 'No notifications',
        close: config.labels?.close ?? 'Close',
    };

    const toastedIds = new Set(
        [...dropdown.querySelectorAll('[data-notification-item]')].map((el) => el.dataset.notificationId),
    );

    let pollTimer = null;
    let inFlight = false;
    let dropdownOpen = false;

    const toggleButton = dropdown.querySelector('[data-bs-toggle="dropdown"]');
    const menu = dropdown.querySelector('[data-notifications-menu]');

    if (toggleButton) {
        toggleButton.addEventListener('show.bs.dropdown', () => {
            dropdownOpen = true;
        });

        toggleButton.addEventListener('hidden.bs.dropdown', () => {
            dropdownOpen = false;
            syncFeed();
        });
    }

    if (menu) {
        menu.addEventListener('click', async (event) => {
            const link = event.target.closest('[data-notification-link]');

            if (!link) {
                return;
            }

            event.preventDefault();

            const notificationId = link.dataset.notificationId;
            const targetUrl = link.getAttribute('href');

            if (!targetUrl) {
                return;
            }

            const unreadCount = await markNotificationRead(notificationId, config);

            if (unreadCount !== null && !Number.isNaN(unreadCount)) {
                updateBadge(dropdown, unreadCount);
                updateMarkAllButton(dropdown, unreadCount);
                link.classList.remove('fw-semibold');
            }

            window.location.href = targetUrl;
        });
    }

    function discoverNotifications(notifications) {
        notifications.forEach((notification) => {
            if (!notification?.id || toastedIds.has(notification.id)) {
                return;
            }

            toastedIds.add(notification.id);
            showNotificationToast(notification, labels, config, dropdown);
        });
    }

    async function syncFeed() {
        if (inFlight || document.hidden) {
            return;
        }

        inFlight = true;

        try {
            const response = await fetch(config.feedUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`Notification feed failed: ${response.status}`);
            }

            const data = await response.json();
            const notifications = Array.isArray(data.notifications) ? data.notifications : [];
            const unreadCount = Number(data.unread_count ?? 0);

            discoverNotifications(notifications);
            updateBadge(dropdown, unreadCount);

            if (!dropdownOpen) {
                updateNotificationList(dropdown, notifications, labels);
            }

            updateMarkAllButton(dropdown, unreadCount);
        } catch (error) {
            console.error('Notification feed sync failed', error);
        } finally {
            inFlight = false;
        }
    }

    function startPolling() {
        stopPolling();
        pollTimer = window.setInterval(syncFeed, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        if (!pollTimer) {
            return;
        }

        clearInterval(pollTimer);
        pollTimer = null;
    }

    const markAllForm = dropdown.querySelector('[data-notifications-mark-all-form]');

    if (markAllForm) {
        markAllForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                const response = await fetch(markAllForm.action, {
                    method: 'POST',
                    body: new FormData(markAllForm),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Mark all read failed: ${response.status}`);
                }

                await syncFeed();
            } catch (error) {
                console.error('Mark all notifications read failed', error);
            }
        });
    }

    syncFeed();
    startPolling();

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
            return;
        }

        syncFeed();
        startPolling();
    });
}
