(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const popupSelector = '.gp-advset--root';
    const overlaySelector = '.gp-sidebar--overlay';
    const openBtnSelector = '.gp-top-bar--advanced-settings-btn';
    const closeBtnSelector = '.gp-advset--close';
    const openClass = 'is-open';
    const bodyOpenClass = 'gp-advset--open';
    const visibleClass = 'is-visible';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        const btn = e.target.closest(openBtnSelector);
        if (!btn) return;

        e.preventDefault();
        openPopup();
    });

    document.addEventListener('click', function (e) {
        const closeBtn = e.target.closest(closeBtnSelector);
        if (!closeBtn) return;

        closePopup();
    });

    document.addEventListener('click', function (e) {
        const overlay = e.target.closest(overlaySelector);
        if (!overlay) return;

        const popup = document.querySelector(popupSelector);
        if (popup && popup.classList.contains(openClass)) {
            closePopup();
        }
    });

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
        }
        document.body.classList.add(bodyOpenClass);
        if (overlay) {
            overlay.classList.add(visibleClass);
        }
    }

    function closePopup() {
        const popup = document.querySelector(popupSelector);
        const overlay = document.querySelector(overlaySelector);

        if (popup) {
            popup.classList.remove(openClass);
        }
        document.body.classList.remove(bodyOpenClass);
        if (overlay) {
            overlay.classList.remove(visibleClass);
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
