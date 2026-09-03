(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    // Main accordion selectors
    const mainAccordionSelector = '.gp-advset--accordion';
    const mainTitleSelector = '.gp-advset--accordion-title';
    const mainContentSelector = '.gp-advset--accordion-content';

    // Info accordion selectors
    const infoRootSelector = '.gp-advset--info-acc--root';
    const infoTitleSelector = '.gp-advset--info-acc--title';
    const infoContentSelector = '.gp-advset--info-acc--content';

    const openClass = 'open';
    const stickyTitleClass = 'sticky-title';
    const stickySubTitleClass = 'sticky-sub-title';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // MAIN ACCORDION - delegiran listener
    document.addEventListener('click', function (e) {
        const title = e.target.closest(mainTitleSelector);
        if (!title) return;

        const parent = title.closest(mainAccordionSelector);
        if (!parent) return;

        handleMainAccordionClick(parent, title);
    });

    // INFO ACCORDION - delegiran listener
    document.addEventListener('click', function (e) {
        const title = e.target.closest(infoTitleSelector);
        if (!title) return;

        const parent = title.closest(infoRootSelector);
        if (!parent) return;

        handleInfoAccordionClick(parent, title);
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function handleMainAccordionClick(parent, title) {
        // Če je že odprt, ga zapremo
        if (parent.classList.contains(openClass)) {
            parent.classList.remove(openClass);
            title.classList.remove(stickyTitleClass);
            // Zapremo tudi VSE info accordione globalno
            closeAllInfoAccordions();
        } else {
            // Najprej zapremo vse main accordion elemente in odstranimo sticky
            document.querySelectorAll(mainAccordionSelector).forEach(el => el.classList.remove(openClass));
            document.querySelectorAll(mainTitleSelector).forEach(el => el.classList.remove(stickyTitleClass));

            // Zapremo tudi VSE info accordione globalno
            closeAllInfoAccordions();

            // Nato odpremo samo kliknjenega in dodamo sticky
            parent.classList.add(openClass);
            title.classList.add(stickyTitleClass);

            // Scroll na content element
            scrollToContent(parent, mainContentSelector);
        }
    }

    function handleInfoAccordionClick(parent, title) {
        // Če je že odprt, ga zapremo
        if (parent.classList.contains(openClass)) {
            parent.classList.remove(openClass);
            title.classList.remove(stickySubTitleClass);
        } else {
            // Zapremo VSE info accordione globalno (ne samo znotraj parent accordion-a)
            closeAllInfoAccordions();

            // Nato odpremo samo kliknjenega in dodamo sticky-sub-title
            parent.classList.add(openClass);
            title.classList.add(stickySubTitleClass);

            // Scroll na content element
            scrollToContent(parent, infoContentSelector);
        }
    }

    function closeAllInfoAccordions() {
        document.querySelectorAll(infoRootSelector).forEach(el => {
            el.classList.remove(openClass);
        });
        document.querySelectorAll(infoTitleSelector).forEach(el => {
            el.classList.remove(stickySubTitleClass);
        });
    }

    function scrollToContent(parent, contentSelector) {
        setTimeout(function () {
            const content = parent.querySelector(contentSelector);
            if (content) {
                content.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Trenutno ni dodatnih helper funkcij.
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
