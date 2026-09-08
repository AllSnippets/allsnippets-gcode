(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const toggleBtnSelector = '.gp-sidebar--toggle-btn';
    const panelRootSelector = '.gp-admin-panel--root';
    const adaptiveMarginSelector = '.gp-sidebar--adaptive-margin';
    const iconSelector = '.dashicons';
    const hiddenClass = 'is-hidden';
    const sidebarHiddenClass = 'sidebar-hidden';
    const leftArrowClass = 'dashicons-arrow-left-alt2';
    const rightArrowClass = 'dashicons-arrow-right-alt2';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest(toggleBtnSelector);
        if (!toggleBtn) return;

        handleSidebarToggle(toggleBtn);
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function handleSidebarToggle(toggleBtn) {
        const panel = toggleBtn.closest(panelRootSelector);
        if (!panel) return;

        const adaptiveMargins = document.querySelectorAll(adaptiveMarginSelector);
        const icon = toggleBtn.querySelector(iconSelector);

        if (adaptiveMargins.length === 0 || !icon) return;

        panel.classList.toggle(hiddenClass);

        adaptiveMargins.forEach(function (adaptiveMargin) {
            adaptiveMargin.classList.toggle(sidebarHiddenClass);
        });

        updateToggleIcon(panel, icon);
    }

    function updateToggleIcon(panel, icon) {
        if (panel.classList.contains(hiddenClass)) {
            icon.classList.remove(leftArrowClass);
            icon.classList.add(rightArrowClass);
        } else {
            icon.classList.remove(rightArrowClass);
            icon.classList.add(leftArrowClass);
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
