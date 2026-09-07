(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    // Vključimo tudi .gp-ajax-folder-dropdown--root kot možen root
    const dropdownRootSelector = '.gp-filter-dropdown--root, .gp-ajax-folder-dropdown--root';
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

    // POSODOBITEV TEKSTA OB ODPIRANJU DROPDOWNA (fix za "loading" state overwrite)
    // Uporabimo enak pristop kot pri func-folder-add-main-folder.js
    document.addEventListener('click', captureActiveInputOnClick);
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function captureActiveInputOnClick(e) {
        // 1. Preveri standardni filter dropdown gumb
        let triggerBtn = e.target.closest('.gp-filter-dropdown--trigger-btn');
        
        // 2. Preveri še mapni filter dropdown gumb (če prvi ni najden)
        if (!triggerBtn) {
            triggerBtn = e.target.closest('.gp-foldlist--main-table--col-folder--trigger-btn');
        }
        
        if (triggerBtn) {
            // Če je to "folder" filter, je vsebina v globalnem kontejnerju (detached)
            const isFolderFilter = triggerBtn.classList.contains('gp-foldlist--main-table--col-folder--trigger-btn');
            
            let contentContainer;
            
            if (isFolderFilter) {
                // Za folder filter moramo najti globalni kontejner
                contentContainer = document.getElementById('gp-global-folder-dropdown-container');
                if (!contentContainer) {
                    return;
                }
                
                // Generiraj unikaten ID za ta trigger (če ga nima), da ga bomo lahko našli kasneje
                if (!triggerBtn.id) {
                    triggerBtn.id = 'gp-folder-trigger-' + Math.random().toString(36).substr(2, 9);
                }
                
                // Ker vsebina še ni naložena, počakamo na mutacijo in nato označimo vsebino z ID-jem triggerja
            } else {
                // Za standardne dropdown-e je wrapper starš
                const wrapper = triggerBtn.closest('.gp-filter-dropdown--wrapper');
                if (!wrapper) return;
                contentContainer = wrapper.querySelector('.gp-filter-dropdown--content');
            }

            if (!contentContainer) return;

            // Opazuj spremembe v contentContainerju (ko AJAX vstavi HTML)
            const observer = new MutationObserver(function(mutations, obs) {
                
                // Če je folder filter, moramo na novo naloženo vsebino označiti s trigger ID-jem
                if (isFolderFilter) {
                    const root = contentContainer.querySelector('.gp-ajax-folder-dropdown--root');
                    if (root) {
                        // Povežemo dropdown z gumbom, ki ga je odprl
                        root.setAttribute('data-trigger-id', triggerBtn.id);
                    }
                }
                
                // Poišči root element v na novo naloženi vsebini (uporablja posodobljen selector)
                const root = contentContainer.querySelector(dropdownRootSelector);
                if (root) {
                    // Simuliramo update z enim od inputov
                    const anyInput = root.querySelector(inputSelector);
                    if (anyInput) {
                        updateDropdownText(anyInput);
                    }
                    
                    obs.disconnect();
                }
            });

            observer.observe(contentContainer, { childList: true, subtree: true });
            
            setTimeout(() => {
                observer.disconnect();
            }, 10000);
        }
    }

    // Univerzalna funkcija za posodabljanje dropdown teksta
    function updateDropdownText(inputElement) {
        // Poišči root element dropdown-a
        const root = inputElement.closest(dropdownRootSelector);
        if (!root) {
            return;
        }

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
        
        // 1. Poskusi najti tekst element znotraj roota (standardni dropdowni)
        let dropdownTextElement = root.querySelector(textElementSelector);
        
        // 2. Če ga ni (detached folder dropdown), poskusi najti preko trigger ID-ja
        if (!dropdownTextElement && root.hasAttribute('data-trigger-id')) {
            const triggerId = root.getAttribute('data-trigger-id');
            const triggerBtn = document.getElementById(triggerId);
            if (triggerBtn) {
                dropdownTextElement = triggerBtn.querySelector(textElementSelector);
            }
        }
        
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
                // Dodali smo 'data-events-added' in 'data-trigger-id' v izključitve
                if (attr.name.startsWith('data-') && 
                    !['data-plugin-slug', 'data-page-slug', 'data-admin-obj-name', 'data-page-url', 'data-filter-path', 'data-events-added', 'data-trigger-id'].includes(attr.name)) {
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