document.addEventListener('DOMContentLoaded', function () {
    const cepInput = document.getElementById('zip_code');
    if (!cepInput) return;

    const addressInput = document.getElementById('address');
    const neighborhoodInput = document.getElementById('neighborhood');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const numberInput = document.getElementById('address_number');

    function formatCep(value) {
        const digits = (value || '').replace(/\D/g, '').slice(0, 8);
        return digits.replace(/(\d{5})(\d)/, '$1-$2');
    }

    cepInput.addEventListener('input', function () {
        cepInput.value = formatCep(cepInput.value);
    });

    cepInput.addEventListener('blur', function () {
        const digits = cepInput.value.replace(/\D/g, '');

        if (digits.length !== 8) return;

        cepInput.disabled = true;

        fetch('https://viacep.com.br/ws/' + digits + '/json/')
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.erro) {
                    return;
                }

                if (addressInput) addressInput.value = data.logradouro || '';
                if (neighborhoodInput) neighborhoodInput.value = data.bairro || '';
                if (cityInput) cityInput.value = data.localidade || '';
                if (stateInput) stateInput.value = data.uf || '';

                if (numberInput) numberInput.focus();
            })
            .catch(function () {
                // Falha silenciosa: usuário ainda pode preencher manualmente.
            })
            .finally(function () {
                cepInput.disabled = false;
            });
    });
});