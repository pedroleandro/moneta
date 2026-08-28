document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') return;

    function removeAccents(str) {
        if (!str) return '';
        return str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    jQuery.fn.dataTable.ext.type.search.string = function (data) {
        return removeAccents(data);
    };
    jQuery.fn.dataTable.ext.type.search.html = function (data) {
        return removeAccents(typeof data === 'string' ? data.replace(/<.*?>/g, '') : data);
    };

    jQuery('table.table-datatable').each(function () {
        if (jQuery(this).find('tbody tr.table-empty-row').length > 0) return;

        jQuery(this).DataTable({
            order: [],
            pageLength: 10,
            lengthMenu: [
                [10, 20, 30, -1],
                [10, 20, 30, 'Todos']
            ],
            dom:
                "<'dt-toolbar-top d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4'f l>" +
                "t" +
                "<'dt-toolbar-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4'i p>",
            language: {
                search: '',
                searchPlaceholder: 'Pesquisar...',
                lengthMenu: '_MENU_ por página',
                info: 'Mostrando _START_–_END_ de _TOTAL_',
                infoEmpty: 'Nenhum registro encontrado',
                infoFiltered: '(filtrado de _MAX_)',
                zeroRecords: 'Nenhum registro encontrado para essa busca',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹',
                },
            },
            columnDefs: [
                {orderable: false, targets: -1},
            ],
            initComplete: function () {
                const api = this.api();
                const searchInput = jQuery('div.dataTables_filter input', api.table().container());

                searchInput.off('keyup.moneta input.moneta').on('keyup.moneta input.moneta', function () {
                    api.search(removeAccents(this.value)).draw();
                });
            },
        });
    });
});