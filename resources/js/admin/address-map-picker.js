import { Modal } from 'bootstrap';

const LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

let leafletPromise = null;
let activePicker = null;
let mapInstance = null;
let markerInstance = null;
let pendingLat = null;
let pendingLng = null;

function loadLeaflet() {
    if (window.L) {
        return Promise.resolve(window.L);
    }

    if (!leafletPromise) {
        leafletPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = LEAFLET_JS;
            script.async = true;
            script.onload = () => resolve(window.L);
            script.onerror = () => reject(new Error('Failed to load map library'));
            document.head.appendChild(script);
        });
    }

    return leafletPromise;
}

function parseCoord(value, fallback) {
    const parsed = Number.parseFloat(value);

    return Number.isFinite(parsed) ? parsed : fallback;
}

function formatCoord(value) {
    return Number.parseFloat(value).toFixed(6);
}

function summaryText(lat, lng, labels) {
    return labels.selected
        .replace(':lat', formatCoord(lat))
        .replace(':lng', formatCoord(lng));
}

function updatePickerSummary(picker, lat, lng, labels) {
    const summary = picker.querySelector('[data-map-picker-summary]');
    const preview = picker.querySelector('[data-map-picker-preview]');

    if (!summary) {
        return;
    }

    if (lat !== null && lng !== null && Number.isFinite(lat) && Number.isFinite(lng)) {
        summary.textContent = summaryText(lat, lng, labels);
        summary.classList.remove('text-muted');
        summary.classList.add('text-body');

        if (preview) {
            preview.href = `https://www.google.com/maps?q=${lat},${lng}`;
            preview.classList.remove('d-none');
        }
    } else {
        summary.textContent = labels.empty;
        summary.classList.add('text-muted');
        summary.classList.remove('text-body');

        if (preview) {
            preview.classList.add('d-none');
            preview.removeAttribute('href');
        }
    }
}

function syncModalSummary(labels) {
    const modalSummary = document.querySelector('[data-map-picker-modal-summary]');

    if (!modalSummary) {
        return;
    }

    if (pendingLat === null || pendingLng === null) {
        modalSummary.textContent = labels.modalHint;

        return;
    }

    modalSummary.textContent = summaryText(pendingLat, pendingLng, labels);
}

function destroyMap() {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        markerInstance = null;
    }
}

function initMap(labels) {
    const mapEl = document.getElementById('adminMapPickerMap');

    if (!mapEl || !window.L) {
        return;
    }

    destroyMap();

    const defaultLat = parseCoord(activePicker?.dataset.defaultLat, 29.3759);
    const defaultLng = parseCoord(activePicker?.dataset.defaultLng, 47.9774);
    const latInput = document.getElementById(activePicker?.dataset.latInput ?? '');
    const lngInput = document.getElementById(activePicker?.dataset.lngInput ?? '');

    const startLat = parseCoord(latInput?.value, defaultLat);
    const startLng = parseCoord(lngInput?.value, defaultLng);

    pendingLat = startLat;
    pendingLng = startLng;

    mapInstance = window.L.map(mapEl, {
        scrollWheelZoom: true,
    }).setView([startLat, startLng], 13);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(mapInstance);

    markerInstance = window.L.marker([startLat, startLng], { draggable: true }).addTo(mapInstance);

    markerInstance.on('dragend', () => {
        const { lat, lng } = markerInstance.getLatLng();
        pendingLat = lat;
        pendingLng = lng;
        syncModalSummary(labels);
    });

    mapInstance.on('click', (event) => {
        pendingLat = event.latlng.lat;
        pendingLng = event.latlng.lng;
        markerInstance.setLatLng(event.latlng);
        syncModalSummary(labels);
    });

    syncModalSummary(labels);

    setTimeout(() => mapInstance.invalidateSize(), 200);
}

export function initAddressMapPickers(labels = {}) {
    const mergedLabels = {
        empty: labels.empty ?? 'No location selected',
        selected: labels.selected ?? 'Location: :lat, :lng',
        modalHint: labels.modalHint ?? 'Click the map or drag the marker to set the location.',
    };

    const modalEl = document.getElementById('adminMapPickerModal');

    if (!modalEl) {
        return;
    }

    const modal = Modal.getOrCreateInstance(modalEl);

    document.querySelectorAll('[data-map-picker]').forEach((picker) => {
        const latInput = document.getElementById(picker.dataset.latInput ?? '');
        const lngInput = document.getElementById(picker.dataset.lngInput ?? '');

        if (!latInput || !lngInput) {
            return;
        }

        const lat = latInput.value ? parseCoord(latInput.value, null) : null;
        const lng = lngInput.value ? parseCoord(lngInput.value, null) : null;
        updatePickerSummary(picker, lat, lng, mergedLabels);

        picker.querySelector('[data-map-picker-open]')?.addEventListener('click', async () => {
            activePicker = picker;

            try {
                await loadLeaflet();
                modal.show();
            } catch (error) {
                console.error(error);
            }
        });
    });

    modalEl.addEventListener('shown.bs.modal', async () => {
        try {
            await loadLeaflet();
            initMap(mergedLabels);
        } catch (error) {
            console.error(error);
        }
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        activePicker = null;
        pendingLat = null;
        pendingLng = null;
        destroyMap();
    });

    modalEl.querySelector('[data-map-picker-confirm]')?.addEventListener('click', () => {
        if (!activePicker || pendingLat === null || pendingLng === null) {
            modal.hide();

            return;
        }

        const latInput = document.getElementById(activePicker.dataset.latInput ?? '');
        const lngInput = document.getElementById(activePicker.dataset.lngInput ?? '');

        if (latInput && lngInput) {
            latInput.value = formatCoord(pendingLat);
            lngInput.value = formatCoord(pendingLng);
            updatePickerSummary(activePicker, pendingLat, pendingLng, mergedLabels);
        }

        modal.hide();
    });
}
