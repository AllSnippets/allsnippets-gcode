(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    if (!window.sharedPageConfig) return;
    const { pluginSlug, adminObjName } = window.sharedPageConfig;
    const adminObj = window[adminObjName];
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        const detectButton = e.target.closest('.gp-detect-plugins-btn');
        if (!detectButton) return;

        e.preventDefault();

        showLoadingIndicator();
        detectPlugins();
    });
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    const slugUnderscored = pluginSlug.replace(/-/g, '_');
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherDetectPluginsFormData() {
        const formData = new FormData();
        
        formData.append('action', `${slugUnderscored}__ajax__detect_plugins`);
        formData.append('nonce', adminObj.nonce);
        
        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function detectPlugins() {
        const formData = gatherDetectPluginsFormData();

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(resp => {
                handleDetectResponse(resp);
            })
            .catch(error => {
                handleAjaxError(error);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleDetectResponse(resp) {
        if (resp && resp.success) {
            hideLoadingIndicator();
            
            // Osveži tabelo
            if (typeof window.allsnippetsAjaxRefreshPage === 'function') {
                window.allsnippetsAjaxRefreshPage();
            } else {
                console.error('[DETECT PLUGINS] window.allsnippetsAjaxRefreshPage function does not exist');
            }
        } else {
            hideLoadingIndicator();
            console.error('Detect error:', resp.data || 'Unknown error');
        }
    }
    // --- 6. KONEC: HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //



    // --- 7. OTHER HELPER FUNCTIONS --- //
    function showLoadingIndicator() {
        const overlay = document.querySelector('.gp-loading--overlay');
        if (overlay) overlay.classList.remove('gp-display-none');
    }

    function hideLoadingIndicator() {
        const overlay = document.querySelector('.gp-loading--overlay');
        if (overlay) overlay.classList.add('gp-display-none');
    }

    function handleAjaxError(error) {
        console.error('AJAX error:', error);
        hideLoadingIndicator();
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
