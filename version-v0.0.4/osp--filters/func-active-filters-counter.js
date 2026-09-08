(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const filterSelectors = {
        active: '.gp-filter-radio--input[name="filter_active"]',
        global: '.gp-filter-radio--input[name="filter_global"]',
        toggle: '.gp-filter-toggle--input',
        codeType: '.gp-filter-toggle--input[name^="filter_code_type"]',
        tag: '.gp-filter-dropdown--input[name^="filter_tag"]',
        category: '.gp-filter-dropdown--input[name^="filter_category"]',
        folder: '.gp-filter-dropdown--input[name^="filter_folder"]'
    };
    const counterSelector = '.gp-filter-reset--count-bubble';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Inicializiraj števec ob nalaganju strani
    updateActiveFiltersCount();
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Delegiran event listener za spremembe filtrov
    document.addEventListener('change', function (e) {
        const target = e.target;
        
        // Preveri, če je sprememba na kateremkoli filter inputu
        if (target.matches(filterSelectors.active) ||
            target.matches(filterSelectors.global) ||
            target.matches(filterSelectors.toggle) ||
            target.matches(filterSelectors.codeType) ||
            target.matches(filterSelectors.tag) ||
            target.matches(filterSelectors.category) ||
            target.matches(filterSelectors.folder)) {
            updateActiveFiltersCount();
        }
    });

    // Poslušaj za custom refresh dogodek (univerzalno ime)
    document.addEventListener('gpMainTableRefreshed', function () {
        setTimeout(updateActiveFiltersCount, 100);
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function updateActiveFiltersCount() {
        let activeCount = 0;

        // Helper za preverjanje radio buttonov (kjer 'all' ne šteje)
        function checkRadio(selector) {
            const el = document.querySelector(selector + ':checked');
            if (el && el.value !== 'all') {
                activeCount++;
            }
        }

        // Helper za preverjanje checkboxov/dropdownov (šteje samo 'exclude')
        function checkExclude(selector) {
            const els = document.querySelectorAll(selector + '[value="exclude"]:checked');
            activeCount += els.length;
        }

        checkRadio(filterSelectors.active);
        checkRadio(filterSelectors.global);
        
        // Za toggle in dropdown filtre preverjamo exclude
        checkExclude('.gp-filter-toggle--input'); // Splošni toggle
        checkExclude('.gp-filter-dropdown--input[name^="filter_tag"]');
        checkExclude('.gp-filter-dropdown--input[name^="filter_category"]');
        checkExclude('.gp-filter-dropdown--input[name^="filter_folder"]');
        
        // Posodobi števec
        const counterElement = document.querySelector(counterSelector);
        if (counterElement) {
            counterElement.textContent = activeCount;
            // Odstranjeno skrivanje elementa - vedno prikaži številko, tudi če je 0
            counterElement.style.display = ''; 
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Izpostavi funkcijo globalno
    window.updateActiveFiltersCount = updateActiveFiltersCount;
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
