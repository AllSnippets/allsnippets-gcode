(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const dropdownRootSelector = '.gp-filter-dropdown--root';
    const inputSelector = 'input[type="radio"]';
    const textElementSelector = '.gp-filter-dropdown--text';
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Ni posebne inicializacije, vse deluje na dogodke.
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Delegiran handler za spremembo radio button-ov
    document.addEventListener('change', function (e) {
        const target = e.target;

        // Preveri, ali je input znotraj dropdown root-a
        const root = target.closest(dropdownRootSelector);
        if (!root) return;

        // Preveri, ali je to filter dropdown input
        if (!target.matches(inputSelector)) return;

        e.preventDefault();
        updateDropdownText(target);
    });
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    // Univerzalna funkcija za posodabljanje dropdown teksta
    function updateDropdownText(inputElement) {
        // Poišči root element dropdown-a
        const root = inputElement.closest(dropdownRootSelector);
        if (!root) return;

        // Poišči podatke o tipu elementa
        const { itemType, dataAttribute } = getItemTypeInfo(root);
        if (!itemType || !dataAttribute) return;

        // Dinamično sestavi vrednosti za "No Item" opcijo
        const noItemValue = '__no_' + itemType + '__';

        // Poišči vse input elemente z exclude vrednostjo v tem dropdown-u
        const allInputs = root.querySelectorAll('[value="exclude"]:checked');

        let excludedCount = 0;
        let excludedItems = [];
        let noItemExcluded = false;

        // Univerzalno: input elementi uporabljajo data-filter-path
        const pathAttribute = 'data-filter-path';

        allInputs.forEach(function (radio) {
            const itemPath = radio.getAttribute(pathAttribute);
            if (!itemPath) return;

            if (itemPath === noItemValue) {
                noItemExcluded = true;
            } else {
                excludedCount++;
                excludedItems.push(itemPath);
            }
        });

        // Dinamično sestavi in posodobi dropdown text
        const dropdownText = generateDropdownText(itemType, excludedCount, noItemExcluded, excludedItems);
        const dropdownTextElement = root.querySelector(textElementSelector);
        if (dropdownTextElement) {
            dropdownTextElement.textContent = dropdownText;
        }

        // Posodobi vizualne razrede (included/excluded)
        updateVisualClasses(root, dataAttribute);
    }

    function generateDropdownText(itemType, excludedCount, noItemExcluded, excludedItems) {
        const itemTypePlural = itemType + 's';
        const itemTypeCapitalized = itemType.charAt(0).toUpperCase() + itemType.slice(1);
        const itemTypePluralCapitalized = itemTypePlural.charAt(0).toUpperCase() + itemTypePlural.slice(1);

        if (excludedCount === 0 && !noItemExcluded) {
            return 'All ' + itemTypePluralCapitalized;
        } else if (excludedCount === 1 && !noItemExcluded) {
            return '🚫 ' + excludedItems[0];
        } else if (excludedCount === 0 && noItemExcluded) {
            return '🚫 No ' + itemTypeCapitalized;
        } else {
            const totalExcluded = excludedCount + (noItemExcluded ? 1 : 0);
            return '🚫 ' + totalExcluded + ' ' + itemTypePlural + ' excluded';
        }
    }

    function updateVisualClasses(root, dataAttribute) {
        const allOptions = root.querySelectorAll('[' + dataAttribute + ']');

        allOptions.forEach(function (option) {
            const itemPath = option.getAttribute(dataAttribute);
            if (!itemPath) return;

            const optionInputExclude = root.querySelector('input[data-filter-path="' + itemPath + '"][value="exclude"]');
            const optionInputInclude = root.querySelector('input[data-filter-path="' + itemPath + '"][value="include"]');

            const isExcluded = optionInputExclude && optionInputExclude.checked;
            const isIncluded = optionInputInclude && optionInputInclude.checked;

            option.classList.remove('excluded', 'included');

            if (isExcluded) {
                option.classList.add('excluded');
            } else if (isIncluded) {
                option.classList.add('included');
            }
        });
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
                    !['data-plugin-slug', 'data-page-slug', 'data-admin-obj-name', 'data-page-url', 'data-filter-path'].includes(attr.name)) {
                    return {
                        itemType: attr.name.replace('data-', ''),
                        dataAttribute: attr.name
                    };
                }
            }
        }
        return { itemType: null, dataAttribute: null };
    }
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
