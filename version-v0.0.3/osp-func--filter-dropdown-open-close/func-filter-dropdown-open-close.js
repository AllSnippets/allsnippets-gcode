(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const dropdownButtonSelector = '.gp-filter-dropdown--button';
    const dropdownRootSelector = '.gp-filter-dropdown--root';
    const dropdownContentSelector = '.gp-filter-dropdown--content';
    const openClass = 'gp-filter-dropdown--content-open';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Delegiran handler za klik na gumb dropdowna
    document.addEventListener('click', function (e) {
        const button = e.target.closest(dropdownButtonSelector);
        
        if (button) {
            handleDropdownClick(e, button);
        } else {
            // Zapri dropdown ko klikneš zunaj
            handleOutsideClick(e);
        }
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function handleDropdownClick(e, button) {
        e.preventDefault();
        e.stopPropagation();

        const root = button.closest(dropdownRootSelector);
        if (!root) return;

        const content = root.querySelector(dropdownContentSelector);
        if (!content) return;

        // Preveri stanje in izvedi akcijo
        const wasOpen = content.classList.contains(openClass);
        
        closeAllDropdowns();

        if (!wasOpen) {
            content.classList.add(openClass);
        }
    }

    function handleOutsideClick(e) {
        if (!e.target.closest(dropdownRootSelector)) {
            closeAllDropdowns();
        }
    }

    function closeAllDropdowns() {
        const allContents = document.querySelectorAll(dropdownContentSelector);
        allContents.forEach(function (content) {
            content.classList.remove(openClass);
        });
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
