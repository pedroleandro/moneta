document.addEventListener('DOMContentLoaded', function () {

    const defaultMessages = {
        valueMissing: 'Este campo é obrigatório.',
        typeMismatchEmail: 'Informe um e-mail válido.',
        typeMismatchDefault: 'Valor inválido.',
        patternMismatch: 'Formato inválido.',
        badInput: 'Valor inválido.',
        tooShort: function (field) {
            return 'Deve ter pelo menos ' + field.minLength + ' caracteres.';
        },
        tooLong: function (field) {
            return 'Deve ter no máximo ' + field.maxLength + ' caracteres.';
        },
        rangeUnderflow: function (field) {
            return 'O valor mínimo é ' + field.min + '.';
        },
        rangeOverflow: function (field) {
            return 'O valor máximo é ' + field.max + '.';
        },
    };

    function getMessage(field) {
        const v = field.validity;
        const custom = field.dataset;

        if (v.valueMissing) return custom.errorRequired || defaultMessages.valueMissing;
        if (v.typeMismatch) {
            if (field.type === 'email') return custom.errorType || defaultMessages.typeMismatchEmail;
            return custom.errorType || defaultMessages.typeMismatchDefault;
        }
        if (v.tooShort) return custom.errorMinlength || defaultMessages.tooShort(field);
        if (v.tooLong) return custom.errorMaxlength || defaultMessages.tooLong(field);
        if (v.rangeUnderflow) return custom.errorMin || defaultMessages.rangeUnderflow(field);
        if (v.rangeOverflow) return custom.errorMax || defaultMessages.rangeOverflow(field);
        if (v.patternMismatch) return custom.errorPattern || defaultMessages.patternMismatch;
        if (v.badInput) return defaultMessages.badInput;

        return field.validationMessage || 'Valor inválido.';
    }

    function ensureFeedbackEl(field) {
        if (field.dataset.feedbackId) {
            const explicit = document.getElementById(field.dataset.feedbackId);
            if (explicit) return explicit;
        }

        let feedback = field.nextElementSibling;

        if (feedback && feedback.classList && feedback.classList.contains('invalid-feedback')) {
            return feedback;
        }

        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.insertAdjacentElement('afterend', feedback);

        return feedback;
    }

    function validateField(field) {
        if (field.disabled || field.type === 'hidden' || field.type === 'submit' || field.type === 'button') {
            return true;
        }

        const feedback = ensureFeedbackEl(field);
        const valid = field.checkValidity();

        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid', valid && field.value !== '');

        feedback.textContent = valid ? '' : getMessage(field);

        return valid;
    }

    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.setAttribute('novalidate', 'novalidate');

        const fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(function (field) {
            field.addEventListener('blur', function (event) {
                const target = event.relatedTarget;

                if (!target) {
                    validateField(field);
                    return;
                }

                const isLink = target.tagName === 'A';
                const isOutsideForm = !form.contains(target);

                if (isLink || isOutsideForm) return;

                validateField(field);
            });

            field.addEventListener('input', function () {
                if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) {
                    validateField(field);
                }
            });

            field.addEventListener('change', function () {
                if (field.tagName === 'SELECT') {
                    validateField(field);
                } else if ((field.type === 'checkbox' || field.type === 'radio') && field.required) {
                    validateField(field);
                }
            });
        });

        form.addEventListener('submit', function (event) {
            let formValid = true;

            fields.forEach(function (field) {
                if (!validateField(field)) {
                    formValid = false;
                }
            });

            if (!formValid) {
                event.preventDefault();
                event.stopPropagation();

                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }

            form.classList.add('was-validated');
        });
    });
});