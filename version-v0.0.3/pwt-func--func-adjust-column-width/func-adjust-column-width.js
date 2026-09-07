(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    // Nastavitve za prilagajanje širine stolpcev
    const minWidth = 300;
    const transitionStyle = 'width 0.3s ease';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    document.addEventListener('DOMContentLoaded', function () {
        adjustAllTables();
    });
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    document.addEventListener('DOMContentLoaded', function () {
        // Poslušaj za spremembe v izbiri stolpcev
        document.addEventListener('change', function (e) {
            if (e.target.matches('.gp-column-selection--checkbox')) {
                setTimeout(adjustAllTables, 500);
            }
        });

        // Poslušaj za resize dogodke
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(adjustAllTables, 250);
        });

        // Poslušaj za custom event osvežitve tabele
        document.addEventListener('gpMainTableRefreshed', function () {
            setTimeout(adjustAllTables, 100);
        });
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function adjustTable(table) {
        var column = table.getAttribute('data-main-column');
        if (!column) return;

        var headerCells = table.querySelectorAll('thead th[data-column="' + column + '"]');
        if (!headerCells.length) return;

        headerCells.forEach(function (cell) {
            cell.style.transition = transitionStyle;

            var oldWidth = cell.style.width;
            cell.style.width = '';
            var naturalWidth = cell.offsetWidth;

            var targetWidth = 'calc(100% - 15px)';
            if (naturalWidth < minWidth) {
                targetWidth = minWidth + 'px';
            }

            cell.style.width = targetWidth;
        });
    }

    function adjustAllTables() {
        var tables = document.querySelectorAll('.gp-main-table--root[data-main-column]');
        tables.forEach(adjustTable);
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Izpostavi funkcijo globalno, da jo lahko kličejo drugi skripti
    window.gpAdjustMainTablesColumnWidth = adjustAllTables;
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
