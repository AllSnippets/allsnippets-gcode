(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const menuItemSelector = '#adminmenu li.wp-has-submenu';
    const submenuSelector = '.wp-submenu';
    const fixedSubmenuSelector = '.wp-submenu[style*="position: fixed"]';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Inicializiraj ob DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        initFixedSubmenus();
    });
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    function initFixedSubmenus() {
        const menuItems = document.querySelectorAll(menuItemSelector);

        menuItems.forEach(menuItem => {
            const submenu = menuItem.querySelector(submenuSelector);
            if (!submenu) return;

            // Ko je hover nad menijem
            menuItem.addEventListener('mouseenter', function () {
                handleMouseEnter(this, submenu);
            });

            // Ko miška zapusti
            menuItem.addEventListener('mouseleave', function () {
                submenu.style.display = 'none';
            });
        });

        // Ob scrollu popravi pozicijo vseh prikazanih submenijev
        window.addEventListener('scroll', handleScroll);
    }
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function handleMouseEnter(menuItem, submenu) {
        const rect = menuItem.getBoundingClientRect();

        // Odstrani WordPressove skrivalne stile
        submenu.style.cssText = '';

        // Nastavi fixed pozicijo
        submenu.style.cssText = `
            position: fixed !important;
            left: ${rect.left + rect.width}px !important;
            top: ${rect.top}px !important;
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            z-index: 99999 !important;
        `;
    }

    function handleScroll() {
        document.querySelectorAll(fixedSubmenuSelector).forEach(submenu => {
            const menuItem = submenu.closest('li.wp-has-submenu');
            if (menuItem && menuItem.matches(':hover')) {
                const rect = menuItem.getBoundingClientRect();
                submenu.style.left = (rect.left + rect.width) + 'px !important';
                submenu.style.top = rect.top + 'px !important';
            }
        });
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
