function formatCurrencyFromDigits(digits) {
    digits = (digits || '').replace(/\D/g, '');
    digits = digits.replace(/^0+(?=\d)/, '');

    while (digits.length < 3) {
        digits = '0' + digits;
    }

    const cents = digits.slice(-2);
    const intPart = digits.slice(0, -2);
    const intFormatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return {
        display: 'R$ ' + intFormatted + ',' + cents,
        raw: intPart + '.' + cents,
    };
}

window.applyCurrencyMask = function (input, hiddenOverride) {
    const targetSelector = input.dataset.target;
    const hidden = hiddenOverride || (targetSelector ? document.querySelector(targetSelector) : null);

    function sync(digits) {
        const result = formatCurrencyFromDigits(digits);
        input.value = result.display;
        if (hidden) hidden.value = result.raw;
    }

    const initialRaw = hidden && hidden.value ? parseFloat(hidden.value) : 0;
    const initialDigits = Math.round((initialRaw || 0) * 100).toString();
    sync(initialDigits);

    input.addEventListener('input', function () {
        sync(input.value);
    });

    input.addEventListener('focus', function () {
        setTimeout(function () {
            input.setSelectionRange(input.value.length, input.value.length);
        }, 0);
    });
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.currency-mask:not(.split-amount-display)').forEach(function (input) {
        window.applyCurrencyMask(input);
    });
});