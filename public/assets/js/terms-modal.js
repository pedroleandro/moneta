document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modal-termos');

    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }

    const scrollArea = document.getElementById('termos-scroll-area');
    const acceptBtn = document.getElementById('btn-aceitar-termos');
    const aviso = document.getElementById('termos-aviso');
    const checkbox = document.getElementById('terms-conditions');

    if (!scrollArea || !acceptBtn || !checkbox || !modalEl) return;

    function checkScrollEnd() {
        const reachedEnd = scrollArea.scrollTop + scrollArea.clientHeight >= scrollArea.scrollHeight - 2;

        if (reachedEnd) {
            acceptBtn.disabled = false;
            if (aviso) aviso.textContent = 'Muito obrigado por ler! Você já pode aceitar.';
        }
    }

    scrollArea.addEventListener('scroll', checkScrollEnd);

    modalEl.addEventListener('shown.bs.modal', function () {
        scrollArea.scrollTop = 0;
        acceptBtn.disabled = true;
        if (aviso) aviso.textContent = 'Leia de forma completa os termos';
        checkScrollEnd();
    });

    acceptBtn.addEventListener('click', function () {
        checkbox.checked = true;
        checkbox.dispatchEvent(new Event('change'));

        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    });
});