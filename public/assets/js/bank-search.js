document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bank-search-input').forEach(function (input) {
        const dataElId = input.dataset.banksSource;
        const dataEl = dataElId ? document.getElementById(dataElId) : null;
        const resultsBox = document.getElementById(input.id + '_results');

        if (!dataEl || !resultsBox) return;

        let banks = [];
        try {
            banks = JSON.parse(dataEl.textContent);
        } catch (e) {
            return;
        }

        function render(filtered) {
            resultsBox.innerHTML = '';

            if (filtered.length === 0) {
                resultsBox.style.display = 'none';
                return;
            }

            filtered.slice(0, 8).forEach(function (bank) {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action py-2 d-flex align-items-center';
                item.title = bank.code + ' - ' + bank.name;
                item.innerHTML =
                    '<span class="badge bg-label-secondary me-2">' + bank.code + '</span>' +
                    '<span class="text-truncate">' + bank.name + '</span>';

                item.addEventListener('click', function () {
                    input.value = bank.code + ' - ' + bank.name;
                    resultsBox.style.display = 'none';
                });

                resultsBox.appendChild(item);
            });

            resultsBox.style.display = 'block';
        }

        input.addEventListener('input', function () {
            const term = input.value.trim().toLowerCase();

            if (!term) {
                resultsBox.style.display = 'none';
                return;
            }

            const filtered = banks.filter(function (bank) {
                return bank.code.includes(term) || bank.name.toLowerCase().includes(term);
            });

            render(filtered);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim()) {
                input.dispatchEvent(new Event('input'));
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                resultsBox.style.display = 'none';
            }
        });

        document.addEventListener('click', function (event) {
            if (!resultsBox.contains(event.target) && event.target !== input) {
                resultsBox.style.display = 'none';
            }
        });
    });
});