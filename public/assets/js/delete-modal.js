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
        });
    });
});