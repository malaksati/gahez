function formatFileSize(bytes) {
    if (bytes === 0) {
        return '0 Bytes';
    }

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${Math.round((bytes / k ** i) * 100) / 100} ${sizes[i]}`;
}

function handleFileSelect(input, fieldId) {
    const file = input.files[0];

    if (! file) {
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        window.alert('File is too large. Maximum size is 10MB.');
        input.value = '';

        return;
    }

    const isImage =
        (file.type && file.type.startsWith('image/')) ||
        file.type === 'application/svg+xml' ||
        /\.(svg|png|jpe?g|gif|webp)$/i.test(file.name);

    if (! isImage) {
        window.alert('Please select an image file.');
        input.value = '';

        return;
    }

    const reader = new FileReader();

    reader.onload = (event) => {
        const previewDiv = document.getElementById(`${fieldId}_preview`);
        const previewImg = document.getElementById(`${fieldId}_preview_img`);
        const contentDiv = document.getElementById(`${fieldId}_content`);
        const infoDiv = document.getElementById(`${fieldId}_info`);
        const nameSpan = document.getElementById(`${fieldId}_name`);
        const sizeSpan = document.getElementById(`${fieldId}_size`);
        const zone = document.getElementById(`${fieldId}_zone`);

        if (previewDiv && previewImg && contentDiv) {
            previewImg.src = event.target.result;
            previewDiv.style.display = 'flex';
            contentDiv.style.display = 'none';
            zone?.classList.add('has-image');
        }

        if (infoDiv && nameSpan && sizeSpan) {
            nameSpan.textContent = file.name;
            sizeSpan.textContent = formatFileSize(file.size);
            infoDiv.style.display = 'block';
        }
    };

    reader.readAsDataURL(file);
}

function removeImage(fieldId) {
    const input = document.getElementById(fieldId);
    const previewDiv = document.getElementById(`${fieldId}_preview`);
    const contentDiv = document.getElementById(`${fieldId}_content`);
    const infoDiv = document.getElementById(`${fieldId}_info`);
    const zone = document.getElementById(`${fieldId}_zone`);

    if (input) {
        input.value = '';
    }

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

function initDragAndDrop(fieldId) {
    const zone = document.getElementById(`${fieldId}_zone`);
    const input = document.getElementById(fieldId);

    if (! zone || ! input) {
        return;
    }

    zone.addEventListener('click', (event) => {
        if (event.target.tagName !== 'BUTTON' && event.target.closest('.preview-overlay') === null) {
            input.click();
        }
    });

    zone.addEventListener('dragover', (event) => {
        event.preventDefault();
        event.stopPropagation();
        zone.classList.add('dragover');
    });

    zone.addEventListener('dragleave', (event) => {
        event.preventDefault();
        event.stopPropagation();
        zone.classList.remove('dragover');
    });

    zone.addEventListener('drop', (event) => {
        event.preventDefault();
        event.stopPropagation();
        zone.classList.remove('dragover');

        const files = event.dataTransfer.files;

        if (files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            input.files = dataTransfer.files;
            handleFileSelect(input, fieldId);
        }
    });
}

function loadExistingImage(fieldId) {
    const zone = document.getElementById(`${fieldId}_zone`);

    if (! zone) {
        return;
    }

    const existingImageUrl = zone.getAttribute('data-existing-image');

    if (! existingImageUrl?.trim()) {
        return;
    }

    const previewDiv = document.getElementById(`${fieldId}_preview`);
    const previewImg = document.getElementById(`${fieldId}_preview_img`);
    const contentDiv = document.getElementById(`${fieldId}_content`);
    const infoDiv = document.getElementById(`${fieldId}_info`);
    const nameSpan = document.getElementById(`${fieldId}_name`);
    const sizeSpan = document.getElementById(`${fieldId}_size`);

    if (previewDiv && previewImg && contentDiv) {
        previewImg.src = existingImageUrl;
        previewDiv.style.display = 'flex';
        contentDiv.style.display = 'none';
        zone.classList.add('has-image');

        if (infoDiv && nameSpan) {
            nameSpan.textContent = existingImageUrl.split('/').pop() || 'Existing image';

            if (sizeSpan) {
                sizeSpan.textContent = 'Existing image';
            }

            infoDiv.style.display = 'block';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (! document.querySelector('[data-settings-upload]')) {
        return;
    }

    ['app_logo'].forEach((fieldId) => {
        initDragAndDrop(fieldId);
        loadExistingImage(fieldId);

        const input = document.getElementById(fieldId);

        input?.addEventListener('change', () => handleFileSelect(input, fieldId));
    });

    document.querySelectorAll('[data-settings-remove-image]').forEach((button) => {
        button.addEventListener('click', () => {
            removeImage(button.getAttribute('data-settings-remove-image'));
        });
    });
});
