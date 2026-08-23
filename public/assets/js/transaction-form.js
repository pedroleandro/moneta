document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-lancamento');
    if (!form) return;

    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category_id');
    const pmAccount = document.getElementById('pm_account');
    const pmCard = document.getElementById('pm_card');
    const wrapperAccount = document.getElementById('wrapper-account');
    const wrapperCard = document.getElementById('wrapper-card');
    const bankAccountSelect = document.getElementById('bank_account_id');
    const creditCardSelect = document.getElementById('credit_card_id');
    const splitsSection = document.getElementById('splits-section');

    function filterCategories() {
        if (!typeSelect || !categorySelect) return;

        const selectedType = typeSelect.value;

        Array.from(categorySelect.options).forEach(function (option) {
            if (!option.dataset.type) return;

            const visible = option.dataset.type === selectedType;
            option.style.display = visible ? '' : 'none';

            if (!visible && option.selected) {
                option.selected = false;
            }
        });
    }

    function togglePaymentMethod() {
        if (!pmCard || !pmAccount || !wrapperAccount || !wrapperCard) return;

        if (pmCard.checked) {
            wrapperAccount.style.display = 'none';
            wrapperCard.style.display = '';
            if (bankAccountSelect) bankAccountSelect.value = '';
            if (splitsSection) splitsSection.style.display = '';
        } else {
            wrapperAccount.style.display = '';
            wrapperCard.style.display = 'none';
            if (creditCardSelect) creditCardSelect.value = '';
            if (splitsSection) splitsSection.style.display = 'none';
        }

        toggleIncomeOption();
    }

    function toggleIncomeOption() {
        if (!typeSelect || !pmCard) return;

        const incomeOption = Array.from(typeSelect.options).find(function (option) {
            return option.value === 'receita';
        });

        if (!incomeOption) return;

        if (pmCard.checked) {
            incomeOption.disabled = true;
            incomeOption.style.display = 'none';

            if (typeSelect.value === 'receita') {
                typeSelect.value = 'despesa';
                filterCategories();
            }
        } else {
            incomeOption.disabled = false;
            incomeOption.style.display = '';
        }
    }

    function toggleCardPaymentOption() {
        if (!typeSelect || !pmCard) return;

        const cardOptionWrapper = pmCard.closest('.form-check-inline') || pmCard.parentElement;

        if (typeSelect.value === 'receita') {
            if (cardOptionWrapper) cardOptionWrapper.style.display = 'none';
            pmCard.disabled = true;

            if (pmCard.checked) {
                pmCard.checked = false;
                if (pmAccount) pmAccount.checked = true;
                togglePaymentMethod();
            }
        } else {
            if (cardOptionWrapper) cardOptionWrapper.style.display = '';
            pmCard.disabled = false;
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            filterCategories();
            toggleCardPaymentOption();
        });
    }

    if (pmAccount) pmAccount.addEventListener('change', togglePaymentMethod);
    if (pmCard) pmCard.addEventListener('change', togglePaymentMethod);

    filterCategories();
    toggleCardPaymentOption();
    togglePaymentMethod();
});