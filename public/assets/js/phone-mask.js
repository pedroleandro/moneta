function formatPhoneNumber(value) {
    let digits = (value || '').replace(/\D/g, '').slice(0, 11);

    if (digits.length > 10) {
        return digits.replace(/(\d{2})(\d{5})(\d{0,4})/, function (_, ddd, first, rest) {
            return '(' + ddd + ') ' + first + (rest ? '-' + rest : '');
        });
    }

    if (digits.length > 6) {
        return digits.replace(/(\d{2})(\d{4})(\d{0,4})/, function (_, ddd, first, rest) {
            return '(' + ddd + ') ' + first + (rest ? '-' + rest : '');
        });
    }

    if (digits.length > 2) {
        return digits.replace(/(\d{2})(\d{0,4})/, '($1) $2');
    }

    if (digits.length > 0) {
        return '(' + digits;
    }

    return '';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.phone-mask').forEach(function (input) {
        function apply() {
            input.value = formatPhoneNumber(input.value);
        }

        apply(); // formata o valor inicial (útil na edição)
        input.addEventListener('input', apply);
    });
});