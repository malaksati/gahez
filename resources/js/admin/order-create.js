function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function buildProductOptions(products, selectLabel) {
    let html = `<option value="">${escapeHtml(selectLabel)}</option>`;

    products.forEach((product) => {
        html += `<option value="${product.id}" data-type="${escapeHtml(product.type)}" data-price="${product.price}" data-variants="${escapeHtml(JSON.stringify(product.variants))}">${escapeHtml(product.name)}</option>`;
    });

    return html;
}

function handleProductChange(select) {
    const row = select.closest('tr');
    if (!row) {
        return;
    }

    const option = select.selectedOptions[0];
    const type = option?.dataset.type;
    const price = option?.dataset.price;
    let variants = [];

    try {
        variants = JSON.parse(option?.dataset.variants ?? '[]');
    } catch {
        variants = [];
    }

    const variantSelect = row.querySelector('.variant-select');
    const priceInput = row.querySelector('.item-price');

    variantSelect.innerHTML = '<option value="">—</option>';

    if (type === 'variable' && variants.length > 0) {
        variantSelect.disabled = false;
        variants.forEach((variant) => {
            const variantOption = document.createElement('option');
            variantOption.value = variant.id;
            variantOption.dataset.price = variant.price;
            variantOption.textContent = variant.name;
            variantSelect.appendChild(variantOption);
        });
        priceInput.value = '';
    } else {
        variantSelect.disabled = true;
        if (price !== undefined && price !== '') {
            priceInput.value = price;
        }
    }
}

function handleVariantChange(select) {
    const row = select.closest('tr');
    if (!row) {
        return;
    }

    const price = select.selectedOptions[0]?.dataset.price;
    const priceInput = row.querySelector('.item-price');

    if (price !== undefined && price !== '') {
        priceInput.value = price;
    }
}

function bindRowEvents(row) {
    const productSelect = row.querySelector('.product-select');
    const variantSelect = row.querySelector('.variant-select');
    const removeBtn = row.querySelector('.remove-item-btn');

    productSelect?.addEventListener('change', () => handleProductChange(productSelect));
    variantSelect?.addEventListener('change', () => handleVariantChange(variantSelect));
    removeBtn?.addEventListener('click', () => row.remove());
}

export function initOrderCreate(config = {}) {
    const products = config.products ?? [];
    const itemsBody = document.getElementById('itemsBody');
    const template = document.getElementById('itemRowTemplate');
    const addBtn = document.getElementById('addItemBtn');
    const selectLabel = config.labels?.selectProduct ?? 'Select product';

    if (!itemsBody || !template || !addBtn) {
        return;
    }

    let itemIndex = 0;

    function addItemRow() {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('tr');

        row.querySelectorAll('[name]').forEach((input) => {
            input.name = input.name.replace(/__INDEX__/g, String(itemIndex));
        });

        const productSelect = row.querySelector('.product-select');
        productSelect.innerHTML = buildProductOptions(products, selectLabel);

        itemsBody.appendChild(clone);
        bindRowEvents(itemsBody.lastElementChild);
        itemIndex += 1;
    }

    addBtn.addEventListener('click', addItemRow);
    addItemRow();
}
