(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    if (!window.sharedPageConfig) return;
    const { pluginSlug, adminObjName } = window.sharedPageConfig;
    const adminObj = window[adminObjName];
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.gp-delete-all-plugin-associations-btn');
        if (!button) return;

        e.preventDefault();

        if (!confirm('Are you sure you want to delete all plugin_association values? This action cannot be undone!')) {
            return;
        }

        showLoadingIndicator();
        deletePluginAssociations();
    });
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    const slugUnderscored = pluginSlug.replace(/-/g, '_');
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherDeleteAssociationsFormData() {
        const formData = new FormData();
        
        formData.append('action', `${slugUnderscored}__ajax__delete_all_plugin_associations`);
        formData.append('nonce', adminObj.nonce);
        
        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function deletePluginAssociations() {
        const formData = gatherDeleteAssociationsFormData();

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(resp => {
                handleDeleteResponse(resp);
            })
            .catch(error => {
                handleAjaxError(error);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleDeleteResponse(resp) {
        if (resp && resp.success) {
            // Osveži stran
            if (typeof window.allsnippetsAjaxRefreshPage === 'function') {
                window.allsnippetsAjaxRefreshPage();
            } else {
                // Rezervna možnost, če funkcija ne obstaja (čeprav bi morala)
                location.reload();
            }
        } else {
            console.error('[REMOVE PLUGIN ASSOCIATION] Error:', resp);
            hideLoadingIndicator();
            alert('Error deleting plugin_association values: ' + (resp.data && resp.data.message ? resp.data.message : 'Unknown error'));
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
        console.error('[REMOVE PLUGIN ASSOCIATION] AJAX error:', error);
        hideLoadingIndicator();
        alert('Error connecting to server.');
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
