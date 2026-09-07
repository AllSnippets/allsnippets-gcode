(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const accordionSelector = '.gp-sidebar--accordion';
    const toggleSelector = '.gp-sidebar--accordion--toggle';
    const expandedAttr = 'aria-expanded';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    document.addEventListener('DOMContentLoaded', function () {
        initAccordions();
    });
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Uporabljamo delegiran listener za boljše delovanje z dinamično vsebino
    document.addEventListener('click', function (e) {
        const toggle = e.target.closest(toggleSelector);
        if (!toggle) return;

        const accordion = toggle.closest(accordionSelector);
        if (!accordion) return;

        e.preventDefault();
        e.stopPropagation();

        toggleAccordion(accordion, toggle);
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function initAccordions() {
        const accordions = document.querySelectorAll(accordionSelector);
        
        accordions.forEach(function (accordion) {
            // Zagotovi začetno stanje
            if (!accordion.hasAttribute(expandedAttr)) {
                accordion.setAttribute(expandedAttr, 'false');
            }
            
            const toggle = accordion.querySelector(toggleSelector);
            if (toggle && !toggle.hasAttribute(expandedAttr)) {
                toggle.setAttribute(expandedAttr, 'false');
            }
        });
    }

    function toggleAccordion(accordion, toggle) {
        const isExpanded = accordion.getAttribute(expandedAttr) === 'true';
        const newState = isExpanded ? 'false' : 'true';

        accordion.setAttribute(expandedAttr, newState);
        toggle.setAttribute(expandedAttr, newState);
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
