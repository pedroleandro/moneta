document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        const originalHTML = submitBtn.innerHTML;
        const requiredFields = form.querySelectorAll('[required]');

        function allFieldsFilled() {
            let filled = true;

            requiredFields.forEach(function (field) {
                if (field.type === 'checkbox') {
                    if (!field.checked) filled = false;
                } else if (!field.value.trim()) {
                    filled = false;
                }
            });

            return filled;
        }

        // Desabilita o botão até todos os campos obrigatórios estarem preenchidos.
        if (requiredFields.length > 0) {
            submitBtn.disabled = !allFieldsFilled();

            requiredFields.forEach(function (field) {
                field.addEventListener('input', function () {
                    submitBtn.disabled = !allFieldsFilled();
                });
                field.addEventListener('change', function () {
                    submitBtn.disabled = !allFieldsFilled();
                });
            });
        }

        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>';
        });
    });
});