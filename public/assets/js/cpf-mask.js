function formatCpf(value) {
    const digits = (value || '').replace(/\D/g, '').slice(0, 11);

    return digits
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cpf-mask').forEach(function (input) {
        function apply() {
            input.value = formatCpf(input.value);
        }

        apply();
        input.addEventListener('input', apply);
    });
});