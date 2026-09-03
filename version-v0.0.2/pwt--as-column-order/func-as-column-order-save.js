(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const dragInitialized = {};
    let currentAbortController = null;

    if (!window.sharedPageConfig) return;
    const { pluginSlug, adminObjName } = window.sharedPageConfig;
    const adminObj = window[adminObjName];
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    document.addEventListener('change', function (e) {
        const widthInput = e.target.closest('.gp-advset--column-order--width-input');
        if (widthInput) {
            // Poišči page slug iz data-page-slug atributa na reset buttonu ali iz najbližjega accordion-a
            const resetBtn = widthInput.closest('.gp-advset--column-order--wrapper').querySelector('.gp-advset--column-order--reset-btn');
            const pageSlug = resetBtn ? resetBtn.getAttribute('data-page-slug') : null;
            if (pageSlug) {
                saveColumnOrderSilent(pageSlug);
            }
        }
    });

    document.addEventListener('click', function (e) {
        const title = e.target.closest('.gp-advset--accordion-title');
        if (!title) return;

        if (title.textContent.includes('Column Order')) {
            setTimeout(function () {
                // Poišči vse column order liste (generični selector)
                const columnOrderLists = document.querySelectorAll('.gp-advset--column-order--list');
                columnOrderLists.forEach(function (list) {
                    // Poišči page slug iz reset buttona v istem wrapper-ju
                    const wrapper = list.closest('.gp-advset--column-order--wrapper');
                    const resetBtn = wrapper ? wrapper.querySelector('.gp-advset--column-order--reset-btn') : null;
                    const pageSlug = resetBtn ? resetBtn.getAttribute('data-page-slug') : null;
                    if (pageSlug) {
                        initDragAndDrop(pageSlug);
                    }
                });
            }, 300);
        }
    });
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    function getColumnOrderList(pageSlug) {
        const resetBtn = document.querySelector('.gp-advset--column-order--reset-btn[data-page-slug="' + pageSlug + '"]');
        if (!resetBtn) return null;

        const wrapper = resetBtn.closest('.gp-advset--column-order--wrapper');
        if (!wrapper) return null;

        return wrapper.querySelector('.gp-advset--column-order--list');
    }

    function appendColumnOrder(formData, list) {
        list.querySelectorAll('.gp-advset--column-order--item').forEach(function (item) {
            if (item.dataset.column) {
                formData.append('column_order[]', item.dataset.column);
            }
        });
    }

    function appendColumnWidths(formData, list) {
        list.querySelectorAll('.gp-advset--column-order--item').forEach(function (item) {
            const col = item.dataset.column;
            if (!col) return;
            const valueInput = item.querySelector('.gp-advset--column-order--width-input');
            if (valueInput && valueInput.value) {
                formData.append(`column_widths[${col}]`, parseInt(valueInput.value) || '');
            }
        });
    }
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherColumnSaveData(pageSlug) {
        const list = getColumnOrderList(pageSlug);
        if (!list) return null;

        const formData = new FormData();
        formData.append('action', 'all_snippets__ajax__save_column_order__vsh0_0_2');
        formData.append('plugin_slug', pluginSlug);
        formData.append('nonce', adminObj.nonce);
        formData.append('page_slug', pageSlug);

        appendColumnOrder(formData, list);
        appendColumnWidths(formData, list);

        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function saveColumnOrderSilent(pageSlug) {
        // Prekliči prejšnji AJAX klic, če obstaja
        abortPreviousRequest();

        const formData = gatherColumnSaveData(pageSlug);
        if (!formData) return;

        // Ustvari nov AbortController za ta klic
        const abortController = startNewRequest();

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            signal: abortController.signal
        })
            .then(response => response.json())
            .then(resp => {
                // Preveri, ali je še vedno aktiven (ni bil preklican)
                if (currentAbortController === abortController) {
                    handleSaveResponse(resp);
                    currentAbortController = null;
                }
            })
            .catch(error => {
                handleAjaxError(error, abortController);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handleSaveResponse(resp) {
        if (resp && resp.success) {
            // Osveži stran, da se prikaže privzeti vrstni red
            if (typeof window.allsnippetsAjaxRefreshPage === 'function') {
                window.allsnippetsAjaxRefreshPage();
            }
        } else {
            console.error('Error saving column order:', resp);
        }
    }
    // --- 6. KONEC: HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //



    // --- 7. OTHER HELPER FUNCTIONS --- //
    function abortPreviousRequest() {
        if (currentAbortController) {
            currentAbortController.abort();
            currentAbortController = null;
        }
    }

    function startNewRequest() {
        const abortController = new AbortController();
        currentAbortController = abortController;
        return abortController;
    }

    function handleAjaxError(error, abortController) {
        // Ignoriraj napake zaradi preklica
        if (error.name !== 'AbortError') {
            console.error('Error saving column order:', error);
        }
        // Preveri, ali je še vedno aktiven (ni bil preklican)
        if (currentAbortController === abortController) {
            currentAbortController = null;
        }
    }

    function initDragAndDrop(pageSlug) {
        if (dragInitialized[pageSlug]) return;

        const list = getColumnOrderList(pageSlug);
        if (!list) return;

        // Omogoči draggable na vseh itemih
        const items = list.querySelectorAll('.gp-advset--column-order--item');
        items.forEach(item => item.setAttribute('draggable', 'true'));

        let draggedItem = null;

        list.addEventListener('dragstart', function (e) {
            const item = e.target.closest('.gp-advset--column-order--item');
            if (!item) return;
            draggedItem = item;
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
        });

        list.addEventListener('dragover', function (e) {
            if (!draggedItem) return;
            e.preventDefault(); // Nujno za drop
            const item = e.target.closest('.gp-advset--column-order--item');
            if (!item || item === draggedItem) return;

            const rect = item.getBoundingClientRect();
            const isAfter = (e.clientY - rect.top) > rect.height / 2;
            if (isAfter) {
                item.after(draggedItem);
            } else {
                item.before(draggedItem);
            }
        });

        list.addEventListener('drop', function (e) {
            e.preventDefault();
        });

        list.addEventListener('dragend', function () {
            if (draggedItem) {
                draggedItem.classList.remove('dragging');
                draggedItem = null;
                saveColumnOrderSilent(pageSlug);
            }
        });

        dragInitialized[pageSlug] = true;
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
