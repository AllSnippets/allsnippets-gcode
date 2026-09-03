(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    if (!window.sharedPageConfig) return;
    const { pluginSlug, pageSlug, adminObjName, buildSelector } = window.sharedPageConfig;
    const adminObj = window[adminObjName];
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    // Delegiran event handler za "Resetiraj vse" gumb
    document.addEventListener('click', function (e) {
        const resetButtonSelector = '.gp-filter-reset--reset-button-small';
        const resetButton = e.target.closest(resetButtonSelector);
        if (!resetButton) return;

        // Prepreči propagacijo eventa (da se ne odpre accordion)
        e.stopPropagation();
        e.stopImmediatePropagation();
        e.preventDefault();

        if (confirm('Are you sure you want to reset all filters and settings?')) {
            showLoadingIndicator(resetButton);
            resetFilters(resetButton);
        }
    }, true); // capture: true - izvede se preden se event propagira
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    // V tem primeru ne potrebujemo posebnih funkcij za branje iz DOM-a
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherResetFormData() {
        const formData = new FormData();
        formData.append('action', 'all_snippets__ajax__reset_filters__vsh0_0_2');
        formData.append('nonce', adminObj.nonce);
        formData.append('user_id', adminObj.user_id);
        formData.append('plugin_slug', pluginSlug);
        formData.append('page_slug', pageSlug);
        
        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function resetFilters(resetButton) {
        const formData = gatherResetFormData();

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(res => res.json())
            .then(response => {
                handleResetResponse(response, resetButton);
            })
            .catch(error => {
                handleAjaxError(error, resetButton);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleResetResponse(response, resetButton) {
        if (response.success) {
            // Osveži stran za popoln reset
            window.location.reload();
        } else {
            alert('Error resetting filters: ' + (response.data || 'Unknown error'));
            resetUI(resetButton);
        }
    }

    function handleAjaxError(error, resetButton) {
        console.error('[RESET FILTERS] Error:', error);
        alert('Error resetting filters.');
        resetUI(resetButton);
    }
    // --- 6. KONEC: HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //



    // --- 7. OTHER HELPER FUNCTIONS --- //
    function showLoadingIndicator(button) {
        // Shrani originalno vsebino
        if (!button.dataset.originalContent) {
            button.dataset.originalContent = button.innerHTML;
        }
        
        button.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span>';
        button.style.pointerEvents = 'none';
        button.style.opacity = '0.6';
    }

    function resetUI(button) {
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
        }
        button.style.pointerEvents = '';
        button.style.opacity = '';
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
