(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const ajaxAction = 'all_snippets__ajax__refresh_page__vsh0_0_2';
    const mainViewSelector = '.gp-main-view--root';
    const overlaySelector = '.gp-loading--overlay';
    const arrowLinkSelector = '.gp-pagination--arrow-link, .gp-pagination--page-number-link';
    const gotoBtnSelector = '.gp-pagination--goto-btn';
    const gotoInputSelector = '.gp-pagination--goto-input';
    const validationNoticeSelector = '.gp-pagination--goto-validation-notice';
    const hiddenClass = 'gp-display-none';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Zaženi inicializacijo (funkcija že počaka na window.sharedPageConfig in window[adminObjName])
    initAjaxReloadGenerateUrl();
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    function addEventListeners(config) {
        // Listener za puščice (delegiran)
        document.addEventListener('click', function (e) {
            const ajaxLink = e.target.closest(arrowLinkSelector);
            if (!ajaxLink) return;

            const href = ajaxLink.getAttribute('href');
            if (!href || href === '#' || href.indexOf('page=' + config.pageUrl) === -1) return;
            e.preventDefault();
            handlePagination(href, config);
        });

        // Listener za spremembo vrednosti inputa
        document.addEventListener('input', function (e) {
            if (e.target.matches(gotoInputSelector)) {
                hideNotice();
            }
        });

        // Listener za ročni vnos (klik na Go button)
        document.addEventListener('click', function (e) {
            const goButton = e.target.closest(gotoBtnSelector);
            if (!goButton) return;

            e.preventDefault();
            const pagedInput = document.querySelector(gotoInputSelector);
            if (!pagedInput) return;

            if (!pagedInput.checkValidity()) {
                showNotice();
                return;
            }

            hideNotice();
            const pagedValue = pagedInput.value || '1';
            const href = window.location.origin + '/wp-admin/admin.php?page=' + config.pageUrl + '&paged=' + pagedValue;
            handlePagination(href, config);
        });
    }
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    // Funkcija za inicializacijo - počaka, da se objekt lokalizira
    function initAjaxReloadGenerateUrl() {
        // Počakaj, da se sharedPageConfig naloži (iz func-get-page-config.js)
        if (!window.sharedPageConfig) {
            setTimeout(initAjaxReloadGenerateUrl, 50);
            return;
        }

        const { pluginSlug, adminObjName, pageUrl, pageSlug } = window.sharedPageConfig;

        // Preveri, če objekt obstaja - če ne, počakaj malo in poskusi znova
        if (!window[adminObjName]) {
            // Počakaj 50ms in poskusi znova (maksimalno 20 poskusov = 1 sekunda)
            waitForAdminObject(adminObjName, function () {
                continueInit({ pluginSlug, adminObjName, pageUrl, pageSlug });
            });
            return;
        }

        continueInit({ pluginSlug, adminObjName, pageUrl, pageSlug });
    }

    function waitForAdminObject(adminObjName, callback) {
        let attempts = 0;
        const maxAttempts = 20;
        const checkInterval = setInterval(() => {
            attempts++;
            if (window[adminObjName]) {
                clearInterval(checkInterval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('[AJAX RELOAD] Object', adminObjName, 'does not exist after', maxAttempts * 50, 'ms');
            }
        }, 50);
    }

    function continueInit(config) {
        if (!config.pageSlug) {
            console.error('[AJAX RELOAD] page slug is not defined');
            return;
        }

        // Skrij loading indikator (za vsak slučaj)
        hideLoadingIndicator();

        // Dodaj listenerje
        addEventListeners(config);
    }

    // Vse v eni funkciji: AJAX, update URL, loading inline
    function handlePagination(source, config) {
        const data = {};
        const { pluginSlug, adminObjName, pageSlug, pageUrl } = config;

        // Pripravi parametre iz source (href)
        if (typeof source === 'string') {
            const urlParams = new URLSearchParams(source.split('?')[1]);
            urlParams.forEach(function (value, key) {
                if (key !== 'action' && key !== 'nonce') data[key] = value;
            });
        }

        // Dopolni manjkajoče parametre iz data atributa tabele
        if (!data.paged) {
            const table = document.querySelector('.wp-list-table.widefat');
            if (table && table.dataset.paged) {
                data.paged = table.dataset.paged;
            }
        }

        // Doda action, nonce, plugin_slug in page_slug
        data.action = ajaxAction;
        data.nonce = window[adminObjName].nonce;
        data.plugin_slug = pluginSlug;
        data.page_slug = pageSlug;

        // Prikaže loading
        showLoadingIndicator();

        // Pošlje AJAX zahtevek
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }

        fetch(window[adminObjName].ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(htmlResponse => {
                // Parse HTML iz odgovora
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlResponse, 'text/html');
                const newWrap = doc.querySelector(mainViewSelector);

                // Poišči element z class gp-main-view--root (vedno isti class, ne rabimo pluginSlug)
                const pageViewZone = document.querySelector(mainViewSelector);
                if (newWrap && pageViewZone) {
                    pageViewZone.replaceWith(newWrap);
                }

                // Sproži custom event za posodobitev tabele
                // Dinamično ime eventa na podlagi plugin slug-a
                const eventName = `${pluginSlug.replace(/-/g, '')}TableRefreshed`;
                setTimeout(() => document.dispatchEvent(new CustomEvent(eventName)), 50);

                // Posodobi URL
                const adminUrl = window.location.origin + '/wp-admin/admin.php';
                const queryParams = new URLSearchParams();
                queryParams.append('page', pageUrl);
                if (data.paged) queryParams.append('paged', data.paged);
                window.history.pushState({}, '', adminUrl + '?' + queryParams.toString());

                hideLoadingIndicator();
            })
            .catch(error => {
                console.error('[AJAX RELOAD] Error:', error);
                hideLoadingIndicator();
            });
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    function setOverlayHidden(hidden) {
        const overlay = document.querySelector(overlaySelector);
        if (overlay) {
            overlay.classList.toggle(hiddenClass, hidden);
        }
    }

    function showLoadingIndicator() {
        setOverlayHidden(false);
    }

    function hideLoadingIndicator() {
        setOverlayHidden(true);
    }

    function showNotice() {
        const n = document.querySelector(validationNoticeSelector);
        if (n) n.classList.remove(hiddenClass);
    }

    function hideNotice() {
        const n = document.querySelector(validationNoticeSelector);
        if (n) n.classList.add(hiddenClass);
    }
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
