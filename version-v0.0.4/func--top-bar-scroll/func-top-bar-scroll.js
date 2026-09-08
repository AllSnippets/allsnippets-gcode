(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const initialTop = 50; // Začetna pozicija (top: 50px)
    const topBarSelector = '.gp-top-bar--root';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Inicializacija se zgodi ob prvem scrollu.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    let ticking = false; // requestAnimationFrame throttle

    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                updateTopBarPosition();
                ticking = false;
            });
            ticking = true;
        }
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function updateTopBarPosition() {
        const topBar = document.querySelector(topBarSelector);
        if (topBar) {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const newTop = initialTop - scrollTop;
            topBar.style.top = newTop + 'px';
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
