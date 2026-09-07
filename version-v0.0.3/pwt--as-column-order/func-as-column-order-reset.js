(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    if (!window.sharedPageConfig) return;
    const { pluginSlug, adminObjName } = window.sharedPageConfig;
    const adminObj = window[adminObjName];
    // --- 1. KONEC: CONFIGURATION --- //


    // --- 2. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        const resetButton = e.target.closest('.gp-advset--column-order--reset-btn');
        if (resetButton) {
            e.preventDefault();
            const pageSlug = resetButton.getAttribute('data-page-slug');
            if (pageSlug && confirm('Are you sure you want to reset the column order to default?')) {
                resetColumnOrder(pageSlug);
            }
        }
    });
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    // (Tu ni kompleksnih iskanj elementov, zato ni dodatnih funkcij)
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function getResetFormData(pageSlug) {
        const formData = new FormData();
        formData.append('action', 'all_snippets__ajax__reset_column_order__vsh0_0_3');
        formData.append('plugin_slug', pluginSlug);
        formData.append('nonce', adminObj.nonce);
        formData.append('page_slug', pageSlug);
        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function resetColumnOrder(pageSlug) {
        const formData = getResetFormData(pageSlug);

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(resp => {
                handleResetResponse(resp);
            })
            .catch(error => {
                console.error('Error resetting column order:', error);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //


    
    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleResetResponse(resp) {
        if (resp && resp.success) {
            // Osveži stran, da se prikaže privzeti vrstni red
            window.allsnippetsAjaxRefreshPage();
        } else if (resp && resp.data) {
            alert('Napaka pri ponastavitvi: ' + resp.data);
        }
    }
    // --- 6. KONEC: HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //



    // --- 7. OTHER HELPER FUNCTIONS --- //
    // (Prazno, ker ta skripta nima posebnih helper funkcij, a pustimo placeholder za konsistenco)
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
