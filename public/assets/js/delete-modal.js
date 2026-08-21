document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal[data-delete-modal]').forEach(function (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const name = button.getAttribute('data-name');

            const form = modal.querySelector('form');
            const nameEl = modal.querySelector('[data-delete-name]');

            if (form && action) form.setAttribute('action', action);
            if (nameEl && name) nameEl.textContent = name;

            const extraField = modal.getAttribute('data-extra-field');
            const extraValue = button.getAttribute('data-extra-id');
            const extraInput = modal.querySelector('[data-extra-value]');

            if (extraField && extraInput && extraValue !== null) {
                extraInput.value = extraValue;
            }
        });
    });
});