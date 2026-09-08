(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    if (!window.sharedPageConfig) return;
    const { pluginSlug, adminObjName, pageUrl, pageSlug } = window.sharedPageConfig;
    const adminObj = window[adminObjName];

    if (!adminObj || !pageSlug) {
        console.error('[AJAX REFRESH] Required config missing');
        return;
    }
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    window.allsnippetsAjaxRefreshPage = function () {
        performPageRefresh();
    };
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    function getCurrentPage() {
        const table = document.querySelector('.gp-main-table--root');
        return table && table.dataset.paged ? parseInt(table.dataset.paged) : 1;
    }
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherRefreshFormData(paged) {
        const formData = new FormData();

        formData.append('action', 'all_snippets__ajax__refresh_page__vsh0_0_4');
        formData.append('nonce', adminObj.nonce);
        formData.append('plugin_slug', pluginSlug);
        formData.append('page_slug', pageSlug);
        formData.append('paged', paged);

        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function performPageRefresh(pagedOverride = null, isRetry = false) {
        // Prekliči prejšnjo zahtevo, če obstaja
        if (window.sharedPageConfig.currentAbortController) {
            window.sharedPageConfig.currentAbortController.abort();
        }
        window.sharedPageConfig.currentAbortController = new AbortController();

        const paged = pagedOverride !== null ? pagedOverride : getCurrentPage();

        toggleLoadingIndicator(true);

        const formData = gatherRefreshFormData(paged);

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData,
            signal: window.sharedPageConfig.currentAbortController.signal
        })
            .then(response => response.text())
            .then(html => handleRefreshSuccess(html, isRetry))
            .catch(error => handleRefreshError(error));
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleRefreshSuccess(htmlResponse, isRetry) {
        const pageViewZone = document.querySelector('.gp-main-view--root');
        if (!pageViewZone) {
            toggleLoadingIndicator(false);
            return;
        }

        // Razčleni HTML in zamenjaj vsebino
        const parser = new DOMParser();
        const parsedDocument = parser.parseFromString(htmlResponse, 'text/html');
        const newPage = parsedDocument.querySelector('.gp-main-view--root');

        if (newPage) {
            pageViewZone.replaceWith(newPage);
        } else {
            pageViewZone.innerHTML = htmlResponse;
        }

        // Sproži dogodek za posodobitev tabele
        const eventName = `${pluginSlug.replace(/-/g, '')}TableRefreshed`;
        document.dispatchEvent(new CustomEvent(eventName));

        // Preveri paginacijo in ponovi, če je potrebno (če smo na neobstoječi strani)
        checkPaginationAndRetry(isRetry);

        // Posodobi URL v brskalniku
        updateBrowserUrl();

        toggleLoadingIndicator(false);
        window.sharedPageConfig.currentAbortController = null;
    }

    function handleRefreshError(error) {
        if (error.name !== 'AbortError') {
            console.error('[AJAX REFRESH] Error:', error);
        }
        toggleLoadingIndicator(false);
        window.sharedPageConfig.currentAbortController = null;
    }

    function checkPaginationAndRetry(isRetry) {
        if (isRetry) return; // Prepreči neskončno zanko

        const pagerSpan = document.querySelector('.gp-pagination--root span');
        if (!pagerSpan) return;

        const maxPageMatch = pagerSpan.textContent.match(/of (\d+)/);
        const maxPage = maxPageMatch ? parseInt(maxPageMatch[1]) : 1;
        const currentTable = document.querySelector('.gp-main-table--root');
        const currentPage = currentTable && currentTable.dataset.paged ? parseInt(currentTable.dataset.paged) : 1;

        if (currentPage > maxPage) {
            // Posodobi dataset tabele, da se izognemo težavam v logiki
            if (currentTable) {
                currentTable.dataset.paged = maxPage;
            }
            performPageRefresh(maxPage, true);
        }
    }

    function updateBrowserUrl() {
        const adminUrl = window.location.origin + '/wp-admin/admin.php';
        const queryParams = new URLSearchParams();
        queryParams.append('page', pageUrl);

        const tableFinal = document.querySelector('.gp-main-table');
        const paged = tableFinal && tableFinal.dataset.paged ? tableFinal.dataset.paged : '1';
        queryParams.append('paged', paged);

        window.history.pushState({}, '', adminUrl + '?' + queryParams.toString());
    }
    // --- 6. KONEC: HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //



    // --- 7. OTHER HELPER FUNCTIONS --- //
    function toggleLoadingIndicator(show) {
        const overlay = document.querySelector('.gp-loading--overlay');
        if (overlay) {
            overlay.classList.toggle('gp-display-none', !show);
        }
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();