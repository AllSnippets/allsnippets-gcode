(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    let currentLimitValue = '';
    let saveTimeout = null;
    let currentSaveController = null;

    if (!window.sharedPageConfig) return;
    const { pluginSlug, pageSlug, adminObjName } = window.sharedPageConfig;
    const adminObj = window[adminObjName];

    // Inicializacija currentLimitValue
    const limitInputSelector = '.gp-pagination--limit-input';
    const limitInput = document.querySelector(limitInputSelector);
    if (limitInput) {
        currentLimitValue = limitInput.value;
    }
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    // Delegiran handler za spremembo limita
    document.addEventListener('input', function (e) {
        if (e.target.matches(limitInputSelector)) {
            currentLimitValue = e.target.value;
        }
    });

    // Delegiran handler za klik na Apply button za limit
    document.addEventListener('click', function (e) {
        const limitButtonSelector = '.gp-pagination--limit-btn';
        const limitButton = e.target.closest(limitButtonSelector);
        if (!limitButton) return;

        e.preventDefault();
        e.stopPropagation();
        const limitWrapSelector = '.gp-pagination--limit-wrap';
        const limitForm = limitButton.closest(limitWrapSelector);
        const limitInput = limitForm ? limitForm.querySelector(limitInputSelector) : null;
        if (limitInput) {
            currentLimitValue = limitInput.value;
        }
        showLoadingIndicator();
        saveUserPreferences();
    });

    // Delegiran handler za Enter key na limit input
    document.addEventListener('keydown', function (e) {
        if (e.target.matches(limitInputSelector) && e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            currentLimitValue = e.target.value;
            showLoadingIndicator();
            saveUserPreferences();
        }
    });

    // Delegiran handler za spremembo filter-radio
    document.addEventListener('change', function (e) {
        const filterRadioSelector = '.gp-filter-radio--input';
        if (e.target.matches(filterRadioSelector)) {
            e.preventDefault();
            showLoadingIndicator();
            saveUserPreferences();
        }
    });

    // Delegiran handler za spremembo filter-toggle
    document.addEventListener('change', function (e) {
        const filterToggleSelector = '.gp-filter-toggle--input';
        if (e.target.matches(filterToggleSelector)) {
            e.preventDefault();
            
            // Debounced shranjevanje
            if (saveTimeout) clearTimeout(saveTimeout);
            abortPreviousRequest();

            saveTimeout = setTimeout(function () {
                showLoadingIndicator();
                saveUserPreferences();
            }, 300);
        }
    });

    // Delegiran handler za spremembo filter-dropdown
    document.addEventListener('change', function (e) {
        const filterDropdownSelector = '.gp-filter-dropdown--input';
        if (e.target.matches(filterDropdownSelector)) {
            e.preventDefault();
            showLoadingIndicator();
            saveUserPreferences();
        }
    });

    // Delegiran handler za spremembo stolpcev (checkboxi)
    document.addEventListener('change', function (e) {
        const columnCheckboxSelector = '.gp-column-selection--checkbox';
        if (e.target.matches(columnCheckboxSelector)) {
            const checkbox = e.target;
            const column = checkbox.dataset.column;
            if (column) {
                const colSelector = 'th[data-column="' + column + '"], td[data-column="' + column + '"]';
                const columnElements = document.querySelectorAll(colSelector);

                if (checkbox.checked) {
                    columnElements.forEach(el => el.style.display = '');
                } else {
                    columnElements.forEach(el => el.style.display = 'none');
                }
            }

            // Debounced shranjevanje
            if (saveTimeout) clearTimeout(saveTimeout);
            abortPreviousRequest();

            saveTimeout = setTimeout(function () {
                saveUserPreferences(null, null, false);
            }, 300);
        }
    });

    // Delegiran handler za spremembo table visibility (checkboxi)
    document.addEventListener('change', function (e) {
        const tableCheckboxSelector = '.gp-table-selection--checkbox';
        if (e.target.matches(tableCheckboxSelector)) {
            const checkbox = e.target;
            const table = checkbox.dataset.table;
            if (table) {
                const tableSelector = '[data-table="' + table + '"]';
                const tableElement = document.querySelector(tableSelector);

                if (checkbox.checked) {
                    if (tableElement) tableElement.style.display = '';
                } else {
                    if (tableElement) tableElement.style.display = 'none';
                }
            }

            // Debounced shranjevanje
            if (saveTimeout) clearTimeout(saveTimeout);
            abortPreviousRequest();

            saveTimeout = setTimeout(function () {
                saveUserPreferences(null, null, false);
            }, 300);
        }
    });

    // Delegiran handler za klik na sorting linke
    document.addEventListener('click', function (e) {
        const sortingLinkSelector = '.gp-main-table--sorting-link';
        const sortingLink = e.target.closest(sortingLinkSelector);
        if (!sortingLink) return;

        e.preventDefault();
        showLoadingIndicator();

        const href = sortingLink.getAttribute('href');
        if (!href) {
            hideLoadingIndicator();
            return;
        }

        // Parsi URL parametre
        const urlParams = new URLSearchParams(href.split('?')[1]);
        const orderby = urlParams.get('orderby');
        const order = urlParams.get('order');

        if (orderby && order) {
            // Normaliziraj order vrednost (asc/desc -> ASC/DESC)
            const normalizedOrder = order.toUpperCase() === 'ASC' ? 'ASC' : 'DESC';
            // Shrani v user preferences
            saveUserPreferences(orderby, normalizedOrder);
        } else {
            hideLoadingIndicator();
        }
    });
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    function getLimitValue() {
        const limitInput = document.querySelector(limitInputSelector);
        return currentLimitValue || (limitInput ? limitInput.value : '');
    }

    function getSortingValue(orderby, order) {
        if (orderby && order) {
            return { orderby: orderby, order: order };
        }
        const table = document.querySelector('.wp-list-table');
        if (!table) return { orderby: '', order: 'DESC' };
        return {
            orderby: table.getAttribute('data-current-orderby') || '',
            order: table.getAttribute('data-current-order') || 'DESC'
        };
    }

    function appendAllFilters(formData) {
        // Radio filters
        document.querySelectorAll('.gp-filter-radio--input:checked').forEach(function (input) {
            const filterKey = input.dataset.filterKey;
            if (filterKey) {
                formData.append('user_preferences[' + filterKey + ']', input.value);
            }
        });

        // Toggle filters
        document.querySelectorAll('.gp-filter-toggle--input:checked').forEach(function (input) {
            const filterKey = input.dataset.filterKey;
            const filterPath = input.dataset.filterPath;
            if (filterKey && filterPath) {
                formData.append('user_preferences[' + filterKey + '][' + filterPath + ']', input.value);
            }
        });

        // Dropdown filters
        document.querySelectorAll('.gp-filter-dropdown--input:checked').forEach(function (input) {
            const filterKey = input.dataset.filterKey;
            const filterPath = input.dataset.filterPath;
            if (filterKey && filterPath) {
                formData.append('user_preferences[' + filterKey + '][' + filterPath + ']', input.value);
            }
        });
    }

    function appendColumns(formData) {
        const selector = '.gp-column-selection--checkbox';
        // Checked columns
        document.querySelectorAll(selector + ':checked').forEach((cb, index) => {
            if (cb.dataset.column) {
                formData.append('user_preferences[columns][' + index + ']', cb.dataset.column);
            }
        });
        // Unchecked columns (označeni kot :off, če je potrebno, sicer se lahko izpusti ali prilagodi logiko)
        let offIndex = 1000; // Da se ne prekrivajo indeksi
        document.querySelectorAll(selector + ':not(:checked)').forEach(cb => {
            if (cb.dataset.column) {
                formData.append('user_preferences[columns][' + offIndex++ + ']', cb.dataset.column + ':off');
            }
        });
    }

    function appendTables(formData) {
        document.querySelectorAll('.gp-table-selection--checkbox').forEach((cb, index) => {
            const table = cb.dataset.table;
            if (table) {
                const value = table + (cb.checked ? '' : ':off');
                formData.append('user_preferences[tables][' + index + ']', value);
            }
        });
    }
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherUserPrefFormData(orderby, order) {
        const limitToSave = getLimitValue();
        const sorting = getSortingValue(orderby, order);

        const formData = new FormData();
        formData.append('action', 'all_snippets__ajax__save_userpref_admin_page__vsh0_0_2');
        formData.append('user_id', adminObj.user_id);
        formData.append('nonce', adminObj.nonce);
        formData.append('plugin_slug', pluginSlug);
        formData.append('page_slug', pageSlug);
        formData.append('user_preferences[limit]', limitToSave);
        formData.append('user_preferences[orderby]', sorting.orderby);
        formData.append('user_preferences[order]', sorting.order);

        appendAllFilters(formData);
        appendColumns(formData);
        appendTables(formData);

        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function saveUserPreferences(orderby, order, shouldRefresh = true) {
        abortPreviousRequest();

        const formData = gatherUserPrefFormData(orderby, order);
        
        const abortController = startNewRequest();

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData,
            signal: abortController.signal
        })
            .then(response => response.json())
            .then(resp => {
                if (currentSaveController === abortController) {
                    handleSaveResponse(resp, shouldRefresh);
                    currentSaveController = null;
                }
            })
            .catch(error => {
                handleAjaxError(error, abortController);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleSaveResponse(resp, shouldRefresh) {
        if (resp && resp.success) {
            if (shouldRefresh) {
                window.allsnippetsAjaxRefreshPage();
            }
        } else {
            console.error('[SAVE USERPREF] Error response:', resp);
            hideLoadingIndicator();
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

    function abortPreviousRequest() {
        if (currentSaveController) {
            currentSaveController.abort();
            currentSaveController = null;
        }
    }

    function startNewRequest() {
        const abortController = new AbortController();
        currentSaveController = abortController;
        return abortController;
    }

    function handleAjaxError(error, abortController) {
        if (error.name !== 'AbortError') {
            console.error('[SAVE USERPREF] Error:', error);
            hideLoadingIndicator();
        }
        if (currentSaveController === abortController) {
            currentSaveController = null;
        }
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
