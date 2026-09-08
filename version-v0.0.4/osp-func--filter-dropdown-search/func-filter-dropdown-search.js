(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const searchInputSelector = '.gp-filter-dropdown--search';
    const dropdownRootSelector = '.gp-filter-dropdown--root';
    const noResultsClass = 'gp-filter-dropdown--no-results';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Delegiran search funkcionalnost za vse filter dropdown-e
    document.addEventListener('input', function (e) {
        if (!e.target.matches(searchInputSelector)) return;

        const root = e.target.closest(dropdownRootSelector);
        if (!root) return;

        performSearch(root, e.target.value.toLowerCase());
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function performSearch(root, searchTerm) {
        const { itemType, dataAttribute } = getItemTypeInfo(root);
        if (!itemType || !dataAttribute) return;

        // Priprava elementov za prikaz/skrivanje
        const options = root.querySelectorAll('[' + dataAttribute + ']');
        const noItemValue = '__no_' + itemType + '__';
        const noItemText = 'no ' + itemType + ' snippets without ' + itemType;

        let visibleCount = 0;

        // Filtriranje opcij
        options.forEach(function (option) {
            const isVisible = shouldShowOption(option, searchTerm, dataAttribute, noItemValue, noItemText);
            
            if (isVisible) {
                option.style.display = '';
                visibleCount++;
            } else {
                option.style.display = 'none';
            }
        });

        updateNoResultsMessage(root, searchTerm, visibleCount);
    }

    function shouldShowOption(option, searchTerm, dataAttribute, noItemValue, noItemText) {
        const itemPath = option.getAttribute(dataAttribute);

        // Posebna obravnava za "No Item" opcijo
        if (itemPath === noItemValue) {
            return noItemText.indexOf(searchTerm) !== -1;
        }

        // Univerzalno iskanje teksta
        const optionText = option.querySelector('.gp-filter-dropdown--option-text') ||
            option.querySelector('span[style*="text-align: left"]') ||
            option.querySelector('span');
        const itemText = optionText ? optionText.textContent.toLowerCase() : '';

        return itemText.indexOf(searchTerm) !== -1;
    }

    function updateNoResultsMessage(root, searchTerm, visibleCount) {
        const scrollableContainer = root.querySelector('.gp-filter-dropdown--scrollable');
        let noResultsMessage = root.querySelector('.' + noResultsClass);

        if (!noResultsMessage && scrollableContainer) {
            noResultsMessage = createNoResultsElement();
            scrollableContainer.appendChild(noResultsMessage);
        }

        if (noResultsMessage) {
            noResultsMessage.style.display = (searchTerm && visibleCount === 0) ? 'block' : 'none';
        }
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    function getItemTypeInfo(root) {
        const allElements = root.querySelectorAll('*');
        
        for (let i = 0; i < allElements.length; i++) {
            const element = allElements[i];
            const attributes = element.attributes;
            for (let j = 0; j < attributes.length; j++) {
                const attr = attributes[j];
                if (attr.name.startsWith('data-') && 
                    !['data-plugin-slug', 'data-page-slug', 'data-admin-obj-name', 'data-page-url'].includes(attr.name)) {
                    return {
                        itemType: attr.name.replace('data-', ''),
                        dataAttribute: attr.name
                    };
                }
            }
        }
        return { itemType: null, dataAttribute: null };
    }

    function createNoResultsElement() {
        const div = document.createElement('div');
        div.className = noResultsClass;
        div.style.cssText = 'padding: 12px; text-align: center; color: #666; font-size: 12px; display: none;';
        div.textContent = 'No results found';
        return div;
    }
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
