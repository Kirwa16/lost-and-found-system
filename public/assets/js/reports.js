// assets/js/reports.js

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */
    const resetButton = document.querySelector('.reset-filters');

    if (resetButton) {
        resetButton.addEventListener('click', function (e) {
            e.preventDefault();

            const form = document.querySelector('.filters');

            if (!form) return;

            form.querySelectorAll('input').forEach(function (input) {

                switch (input.type) {
                    case 'text':
                    case 'search':
                    case 'date':
                    case 'number':
                        input.value = '';
                        break;
                }

            });

            form.querySelectorAll('select').forEach(function (select) {
                select.selectedIndex = 0;
            });

            form.submit();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Auto Submit Filters
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.filters select').forEach(function (select) {

        select.addEventListener('change', function () {

            const form = this.closest('form');

            if (form) {
                form.submit();
            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Search on Enter
    |--------------------------------------------------------------------------
    */
    const searchInput = document.querySelector('.filters input[type="text"]');

    if (searchInput) {

        searchInput.addEventListener('keydown', function (e) {

            if (e.key === 'Enter') {

                e.preventDefault();

                this.closest('form').submit();

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Export Confirmation
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.export-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            return confirm('Generate and download this report?');

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Print Report
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.print-report').forEach(function (button) {

        button.addEventListener('click', function (e) {

            e.preventDefault();

            window.print();

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Clickable Table Rows
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('tr[data-href]').forEach(function (row) {

        row.style.cursor = 'pointer';

        row.addEventListener('click', function (e) {

            if (e.target.closest('a') || e.target.closest('button')) {
                return;
            }

            const url = this.dataset.href;

            if (url) {
                window.location.href = url;
            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Highlight Active Filters
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.filters select, .filters input').forEach(function (field) {

        if (field.value !== '' && field.value !== 'all') {
            field.classList.add('active-filter');
        }

        field.addEventListener('change', function () {

            if (this.value !== '' && this.value !== 'all') {
                this.classList.add('active-filter');
            } else {
                this.classList.remove('active-filter');
            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Table Hover Effect
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.table tbody tr').forEach(function (row) {

        row.addEventListener('mouseenter', function () {
            this.classList.add('table-row-hover');
        });

        row.addEventListener('mouseleave', function () {
            this.classList.remove('table-row-hover');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Tooltips
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('[title]').forEach(function (element) {

        element.setAttribute('data-tooltip', element.getAttribute('title'));

    });

});