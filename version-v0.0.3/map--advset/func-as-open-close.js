(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const popupSelector = '.gp-advset--root';
    const overlaySelector = '.gp-sidebar--overlay';
    const openBtnSelector = '.gp-top-bar--advanced-settings-btn';
    const closeBtnSelector = '.gp-advset--close';
    const openClass = 'is-open';
    const visibleClass = 'is-visible';
    const closingClass = 'closing';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Odpri popup - delegiran listener
    document.addEventListener('click', function (e) {
        const btn = e.target.closest(openBtnSelector);
        if (!btn) return;

        e.preventDefault();
        openPopup();
    });

    // Zapri popup (gumb) - delegiran listener
    document.addEventListener('click', function (e) {
        const closeBtn = e.target.closest(closeBtnSelector);
        if (!closeBtn) return;

        closePopup();
    });

    // Zapri popup (overlay) - delegiran listener
    document.addEventListener('click', function (e) {
        const overlay = e.target.closest(overlaySelector);
        if (!overlay) return;

        // Če je popup odprt in kliknemo na overlay
        const popup = document.querySelector(popupSelector);
        if (popup && popup.classList.contains(openClass)) {
            closePopup();
        }
    });

    // Zapri popup (ESC)
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        const popup = document.querySelector(popupSelector);
        if (popup && popup.classList.contains(openClass)) {
            closePopup();
        }
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function openPopup() {
        const popup = document.querySelector(popupSelector);
        const overlay = document.querySelector(overlaySelector);

        if (popup) {
            popup.classList.add(openClass);
            popup.classList.remove(closingClass);
        }
        if (overlay) {
            overlay.classList.add(visibleClass);
        }
    }

    function closePopup() {
        const popup = document.querySelector(popupSelector);
        const overlay = document.querySelector(overlaySelector);

        if (popup) {
            popup.classList.add(closingClass);
            popup.classList.remove(openClass);
        }
        if (overlay) {
            overlay.classList.remove(visibleClass);
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
