document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('splits-container');
    const addBtn = document.getElementById('add-split-btn');
    const template = document.getElementById('split-row-template');

    if (!container || !addBtn || !template) return;

    function initSplitRow(row) {
        const amountDisplay = row.querySelector('.split-amount-display');
        const amountHidden = row.querySelector('.split-amount-hidden');
        if (amountDisplay && amountHidden && window.applyCurrencyMask) {
            window.applyCurrencyMask(amountDisplay, amountHidden);
        }
    }

    function refreshRowOptions(row) {
        const creditCardSelect = document.getElementById('credit_card_id');
        const selectedCard = creditCardSelect ? creditCardSelect.value : '';
        const select = row.querySelector('select');

        Array.from(select.options).forEach(function (option) {
            if (!option.dataset.cards) return;
            const visible = option.dataset.cards.split(',').includes(selectedCard);
            option.style.display = visible ? '' : 'none';
        });
    }

    function addRow() {
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);

        const row = container.lastElementChild;
        refreshRowOptions(row);
        initSplitRow(row);

        if (typeof initModernSelects === 'function') {
            initModernSelects(row);
        }
    }

    addBtn.addEventListener('click', addRow);

    container.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.remove-split-row');
        if (removeBtn) {
            removeBtn.closest('.split-row').remove();
        }
    });

    Array.from(container.querySelectorAll('.split-row')).forEach(function (row) {
        refreshRowOptions(row);
        initSplitRow(row);
    });

    const creditCardSelect = document.getElementById('credit_card_id');
    if (creditCardSelect) {
        creditCardSelect.addEventListener('change', function () {
            Array.from(container.querySelectorAll('.split-row')).forEach(refreshRowOptions);
        });
    }
});