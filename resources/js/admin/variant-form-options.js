/**
 * Dynamic variant option rows on admin variant create/edit.
 */
export function initVariantFormOptions(config = {}) {
    const container = document.getElementById('variantOptionsContainer');
    const addButton = document.getElementById('addVariantOptionBtn');

    if (!container || !addButton) {
        return;
    }

    let optionIndex = config.initialCount ?? container.querySelectorAll('.option-row').length;

    const labels = {
        option: config.optionLabel ?? 'Option',
        nameEn: config.nameEnLabel ?? 'Name (English)',
        nameAr: config.nameArLabel ?? 'Name (Arabic)',
        code: config.codeLabel ?? 'Code',
        auto: config.autoLabel ?? 'Auto',
        optional: config.optionalLabel ?? 'Optional',
    };

    addButton.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'option-row card border mb-3';
        row.innerHTML = `
            <motion.div class="card-body">
                <motion.div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0 option-row-title">${labels.option} #${optionIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn" aria-label="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </motion.div>
                <motion.div class="row g-3">
                    <motion.div class="col-md-5">
                        <label class="form-label">${labels.nameEn} *</label>
                        <input type="text" class="form-control" name="options[${optionIndex}][name][en]" required>
                    </motion.div>
                    <motion.div class="col-md-5">
                        <label class="form-label">${labels.nameAr} *</label>
                        <input type="text" class="form-control" name="options[${optionIndex}][name][ar]" dir="rtl" required>
                    </motion.div>
                    <motion.div class="col-md-2">
                        <label class="form-label">${labels.code}</label>
                        <input type="text" class="form-control" name="options[${optionIndex}][code]" placeholder="${labels.auto}">
                        <small class="text-muted">${labels.optional}</small>
                    </motion.div>
                </motion.div>
            </motion.div>
        `.replace(/motion\.div/g, 'div');

        container.appendChild(row);
        optionIndex += 1;
    });

    container.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-option-btn');
        if (!button) {
            return;
        }

        button.closest('.option-row')?.remove();
        updateOptionNumbers(container, labels.option);
    });

    updateOptionNumbers(container, labels.option);
}

function updateOptionNumbers(container, optionLabel) {
    container.querySelectorAll('.option-row').forEach((row, index) => {
        const title = row.querySelector('.option-row-title');
        if (title) {
            title.textContent = `${optionLabel} #${index + 1}`;
        }
    });
}
