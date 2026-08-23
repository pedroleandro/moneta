document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        submitBtn.dataset.originalHtml = submitBtn.innerHTML;

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

        if (requiredFields.length > 0) {
            // Adia a checagem inicial pro fim da fila de eventos — dá
            // tempo de outros scripts (ex: currency-mask.js) que também
            // escutam DOMContentLoaded terminarem de preencher campos
            // via JS antes da gente conferir se estão vazios.
            setTimeout(function () {
                submitBtn.disabled = !allFieldsFilled();
            }, 0);

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

window.addEventListener('pageshow', function (event) {
    if (!event.persisted) return;

    document.querySelectorAll('button[type="submit"]').forEach(function (submitBtn) {
        if (submitBtn.dataset.originalHtml) {
            submitBtn.innerHTML = submitBtn.dataset.originalHtml;
        }

        const form = submitBtn.closest('form');
        const requiredFields = form ? form.querySelectorAll('[required]') : [];

        if (requiredFields.length > 0) {
            let filled = true;
            requiredFields.forEach(function (field) {
                if (field.type === 'checkbox') {
                    if (!field.checked) filled = false;
                } else if (!field.value.trim()) {
                    filled = false;
                }
            });
            submitBtn.disabled = !filled;
        } else {
            submitBtn.disabled = false;
        }
    });

    if (window.turnstile) {
        document.querySelectorAll('.cf-turnstile').forEach(function (widget) {
            turnstile.reset(widget);
        });
    } else {
        document.querySelectorAll('.turnstile-token-field').forEach(function (field) {
            field.value = '';
        });
    }
});