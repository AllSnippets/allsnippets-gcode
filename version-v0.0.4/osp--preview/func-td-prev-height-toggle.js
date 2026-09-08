(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const wrapperSelector = '.gp-td-prev-height--root';
    const containerSelector = '.gp-td-prev-height--max-height';
    const buttonSelector = '.gp-td-prev-height--view-more-btn';
    const expandedClass = 'gp-max-height-none';
    const hiddenClass = 'gp-display-none';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    document.addEventListener('DOMContentLoaded', function () {
        checkHeightsAndToggleButtons();
    });
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        // Preveri ali je target ali njegov starš gumb
        const button = e.target.closest(buttonSelector);

        // Preveri, če je gumb in če je znotraj našega root wrapperja
        if (!button || !button.closest(wrapperSelector)) {
            return;
        }

        handleToggleClick(button);
    });

    // Poslušaj za resize dogodke, saj se višina lahko spremeni
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(checkHeightsAndToggleButtons, 250);
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function checkHeightsAndToggleButtons() {
        const wrappers = document.querySelectorAll(wrapperSelector);

        wrappers.forEach(function (wrapper) {
            const container = wrapper.querySelector(containerSelector);
            const button = wrapper.querySelector(buttonSelector);

            if (container && button) {
                // Preveri, če vsebina presega max-height
                // Uporabljamo scrollHeight vs clientHeight
                if (container.scrollHeight > container.clientHeight) {
                    button.classList.remove(hiddenClass);
                } else {
                    // Če je vsebina manjša, skrij gumb, razen če je že razširjen
                    if (!container.classList.contains(expandedClass)) {
                        button.classList.add(hiddenClass);
                    }
                }
            }
        });
    }

    function handleToggleClick(button) {
        const wrapper = button.closest(wrapperSelector);
        const container = wrapper.querySelector(containerSelector);

        if (!container) return;

        const isExpanded = container.classList.contains(expandedClass);

        if (!isExpanded) {
            // Razširi
            container.classList.add(expandedClass);
            button.textContent = 'View Less';
        } else {
            // Skrči
            container.classList.remove(expandedClass);
            button.textContent = 'View More';
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Izpostavi funkcijo globalno, da jo lahko pokličemo po AJAX reloadu ali drugih spremembah
    window.allsnippetsCheckViewMoreHeights = checkHeightsAndToggleButtons;
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
