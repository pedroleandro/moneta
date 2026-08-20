function onTurnstileVerified(token) {
    document.querySelectorAll('.turnstile-token-field').forEach(function (field) {
        field.value = token;
    });
}

function onTurnstileExpired() {
    document.querySelectorAll('.turnstile-token-field').forEach(function (field) {
        field.value = '';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const tokenField = form.querySelector('.turnstile-token-field');
            if (tokenField && !tokenField.value) {
                event.preventDefault();
                alert('Aguarde a verificação de segurança terminar antes de continuar.');
            }
        });
    });
});