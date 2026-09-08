(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    if (!window.sharedPageConfig) return;
    const { adminObjName } = window.sharedPageConfig;
    const adminObj = window[adminObjName];

    let currentBtn = null;
    let currentPage = 1;
    let isLoading = false;
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. EVENT LISTENERS --- //
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.gp-generic-popup--trigger-btn');
        if (btn) {
            e.preventDefault();
            handleOpenPopup(btn);
        }

        const closeBtn = e.target.closest('.gp-generic-popup--close-btn');
        if (closeBtn) {
            handleClosePopup();
        }

        const loadMoreBtn = e.target.closest('.gp-generic-popup--load-more-btn');
        if (loadMoreBtn) {
            e.preventDefault();
            handleLoadMore();
        }

        if (e.target.classList.contains('gp-generic-popup--root')) {
            handleClosePopup();
        }
    });
    // --- 2. KONEC: EVENT LISTENERS --- //



    // --- 3. PRIDOBIVANJE PODATKOV ZA AJAX --- //
    function getAttachmentId() {
        return currentBtn ? currentBtn.getAttribute('data-popup-attachment_id') : '';
    }

    function getPopupTitle() {
        return currentBtn ? (currentBtn.getAttribute('data-popup-title') || 'Details') : 'Details';
    }
    // --- 3. KONEC: PRIDOBIVANJE PODATKOV ZA AJAX --- //



    // --- 4. HELPER FUNCTIONS (DATA GATHERING) --- //
    function gatherPopupFormData() {
        const formData = new FormData();

        const actionName = currentBtn ? currentBtn.getAttribute('data-popup-action') : '';
        formData.append('action', actionName);
        formData.append('nonce', adminObj.nonce);
        formData.append('paged', currentPage);

        const attachmentId = getAttachmentId();
        if (attachmentId) {
            formData.append('attachment_id', attachmentId);
        }

        return formData;
    }
    // --- 4. KONEC: HELPER FUNCTIONS (DATA GATHERING) --- //



    // --- 5. AJAX FETCH --- //
    function loadPopupData() {
        if (!currentBtn) return;
        isLoading = true;
        updatePopupUIForLoading();

        const formData = gatherPopupFormData();

        fetch(adminObj.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(resp => {
                isLoading = false;
                handlePopupResponse(resp);
            })
            .catch(error => {
                isLoading = false;
                handleAjaxError(error);
            });
    }
    // --- 5. KONEC: AJAX FETCH --- //



    // --- 6. HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //
    function handlePopupResponse(response) {
        updatePopupUIAfterLoading();

        if (response.success) {
            const res = response.data;
            const popup = document.querySelector('.gp-generic-popup--root');
            const container = popup.querySelector('.gp-generic-popup--data-container');

            if (currentPage === 1) {
                if (container) container.innerHTML = res.html;
            } else {
                if (container) {
                    const tbody = container.querySelector('tbody');
                    if (tbody) {
                        tbody.insertAdjacentHTML('beforeend', res.html);
                    } else {
                        container.insertAdjacentHTML('beforeend', res.html);
                    }
                }
            }

            updateFooterUI(res.has_more);
        } else {
            alert('Error loading data');
        }
    }
    // --- 6. KONEC: HELPER FUNCTIONS (SUCCESS/ERROR HANDLING) --- //



    // --- 7. OTHER HELPER FUNCTIONS --- //
    function handleOpenPopup(btn) {
        currentBtn = btn;
        currentPage = 1;
        isLoading = false;

        const popup = document.querySelector('.gp-generic-popup--root');
        if (!popup) return;

        const titleEl = popup.querySelector('.gp-generic-popup--title');
        if (titleEl) titleEl.textContent = getPopupTitle();

        const container = popup.querySelector('.gp-generic-popup--data-container');
        if (container) container.innerHTML = '';

        const loadingBody = popup.querySelector('.gp-generic-popup--loading-body');
        if (loadingBody) loadingBody.classList.add('gp-display-none');

        const footer = popup.querySelector('.gp-generic-popup--footer');
        if (footer) footer.classList.add('gp-display-none');

        popup.classList.remove('gp-display-none');

        loadPopupData();
    }

    function handleClosePopup() {
        const popup = document.querySelector('.gp-generic-popup--root');
        if (popup) popup.classList.add('gp-display-none');
    }

    function handleLoadMore() {
        if (!isLoading) {
            currentPage++;
            loadPopupData();
        }
    }

    function updatePopupUIForLoading() {
        const popup = document.querySelector('.gp-generic-popup--root');
        const loadingBody = popup.querySelector('.gp-generic-popup--loading-body');
        const container = popup.querySelector('.gp-generic-popup--data-container');
        const loadMoreBtn = popup.querySelector('.gp-generic-popup--load-more-btn');
        const status = popup.querySelector('.gp-generic-popup--status');

        if (currentPage === 1) {
            if (loadingBody) loadingBody.classList.remove('gp-display-none');
            if (container) container.classList.add('gp-display-none');
        } else {
            if (loadMoreBtn) loadMoreBtn.classList.add('gp-display-none');
            if (status) status.classList.remove('gp-display-none');
        }
    }

    function updatePopupUIAfterLoading() {
        const popup = document.querySelector('.gp-generic-popup--root');
        const loadingBody = popup.querySelector('.gp-generic-popup--loading-body');
        const container = popup.querySelector('.gp-generic-popup--data-container');

        if (loadingBody) loadingBody.classList.add('gp-display-none');
        if (container) container.classList.remove('gp-display-none');
    }

    function updateFooterUI(hasMore) {
        const popup = document.querySelector('.gp-generic-popup--root');
        const footer = popup.querySelector('.gp-generic-popup--footer');
        
        if (footer) {
            if (hasMore) {
                footer.classList.remove('gp-display-none');
                const footerLoadMore = footer.querySelector('.gp-generic-popup--load-more-btn');
                const footerStatus = footer.querySelector('.gp-generic-popup--status');
                if (footerLoadMore) footerLoadMore.classList.remove('gp-display-none');
                if (footerStatus) footerStatus.classList.add('gp-display-none');
            } else {
                footer.classList.add('gp-display-none');
            }
        }
    }

    function handleAjaxError(error) {
        console.error('Error:', error);
        updatePopupUIAfterLoading();
        alert('Error loading data');
    }
    // --- 7. KONEC: OTHER HELPER FUNCTIONS --- //
})();
