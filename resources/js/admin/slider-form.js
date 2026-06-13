const FIELD_ID = 'slider_image';
const MAX_BYTES = 4 * 1024 * 1024;

function formatFileSize(bytes) {
    if (bytes === 0) {
        return '0 Bytes';
    }

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${Math.round((bytes / k ** i) * 100) / 100} ${sizes[i]}`;
}

function getElements() {
    return {
        zone: document.getElementById(`${FIELD_ID}_zone`),
        input: document.getElementById(FIELD_ID),
        previewDiv: document.getElementById(`${FIELD_ID}_preview`),
        previewImg: document.getElementById(`${FIELD_ID}_preview_img`),
        contentDiv: document.getElementById(`${FIELD_ID}_content`),
        infoDiv: document.getElementById(`${FIELD_ID}_info`),
        nameSpan: document.getElementById(`${FIELD_ID}_name`),
        sizeSpan: document.getElementById(`${FIELD_ID}_size`),
        removeFlag: document.querySelector('[data-slider-remove-image-flag]'),
    };
}

function showPreview(src, name, sizeLabel) {
    const { previewDiv, previewImg, contentDiv, infoDiv, nameSpan, sizeSpan, zone } = getElements();

    if (! previewDiv || ! previewImg || ! contentDiv) {
        return;
    }

    previewImg.src = src;
    previewDiv.style.display = 'block';
    contentDiv.style.display = 'none';
    zone?.classList.add('has-image');

    if (infoDiv && nameSpan && sizeSpan) {
        nameSpan.textContent = name;
        sizeSpan.textContent = sizeLabel;
        infoDiv.style.display = 'block';
    }
}

function hidePreview() {
    const { previewDiv, contentDiv, infoDiv, zone } = getElements();

    if (previewDiv) {
        previewDiv.style.display = 'none';
    }

    if (contentDiv) {
        contentDiv.style.display = 'flex';
    }

    if (infoDiv) {
        infoDiv.style.display = 'none';
    }

    zone?.classList.remove('has-image');
}

function setRemoveFlag(active) {
    const { removeFlag } = getElements();

    if (removeFlag) {
        removeFlag.value = active ? '1' : '0';
    }
}

function handleFileSelect(input) {
    const file = input.files[0];

    if (! file) {
        return;
    }

    if (file.size > MAX_BYTES) {
        window.alert('File is too large. Maximum size is 4 MB.');
        input.value = '';

        return;
    }

    if (! file.type.startsWith('image/')) {
        window.alert('Please select an image file.');
        input.value = '';

        return;
    }

    setRemoveFlag(false);

    const reader = new FileReader();

    reader.onload = (event) => {
        showPreview(event.target.result, file.name, formatFileSize(file.size));
    };

    reader.readAsDataURL(file);
}

function removeImage() {
    const { input } = getElements();

    if (input) {
        input.value = '';

        if (getElements().removeFlag) {
            input.removeAttribute('required');
        }
    }

    const hadExisting = getElements().zone?.getAttribute('data-existing-image')?.trim();

    if (hadExisting) {
        getElements().zone?.setAttribute('data-existing-image', '');
        setRemoveFlag(true);
    }

    hidePreview();
}

function loadExistingImage() {
    const { zone } = getElements();

    if (! zone) {
        return;
    }

    const existingImageUrl = zone.getAttribute('data-existing-image');

    if (! existingImageUrl?.trim()) {
        return;
    }

    showPreview(existingImageUrl, existingImageUrl.split('/').pop() || 'image', 'Current image');
}

function initDragAndDrop() {
    const { zone, input } = getElements();

    if (! zone || ! input) {
        return;
    }

    zone.addEventListener('click', (event) => {
        if (event.target.closest('[data-slider-image-remove]')) {
            return;
        }

        input.click();
    });

    zone.addEventListener('dragover', (event) => {
        event.preventDefault();
        zone.classList.add('dragover');
    });

    zone.addEventListener('dragleave', (event) => {
        event.preventDefault();
        zone.classList.remove('dragover');
    });

    zone.addEventListener('drop', (event) => {
        event.preventDefault();
        zone.classList.remove('dragover');

        const files = event.dataTransfer.files;

        if (files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            input.files = dataTransfer.files;
            handleFileSelect(input);
        }
    });
}

export function initSliderForm() {
    const root = document.querySelector('[data-slider-image-upload]');

    if (! root) {
        return;
    }

    const { input } = getElements();

    initDragAndDrop();
    loadExistingImage();

    input?.addEventListener('change', () => handleFileSelect(input));

    root.querySelector('[data-slider-image-remove]')?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        removeImage();
    });
}
