(function () {
    'use strict';

    // --- 1. CONFIGURATION --- //
    const mainViewSelector = '.gp-main-view--root';
    const configAttributes = [
        { key: 'pluginSlug', attr: 'pluginSlug', error: 'data-plugin-slug' },
        { key: 'pageSlug', attr: 'pageSlug', error: 'data-page-slug' },
        { key: 'adminObjName', attr: 'adminObjName', error: 'data-admin-obj-name' },
        { key: 'pageUrl', attr: 'pageUrl', error: 'data-page-url' },
        { key: 'pagePrefix', attr: 'pagePrefix', optional: true }
    ];
    // --- 1. KONEC: CONFIGURATION --- //



    // --- 2. INITIALIZATION --- //
    // Zaženi inicializacijo (funkcija že počaka, če element ni najden)
    initializeSharedPageConfig();
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. EVENT LISTENERS --- //
    // Ta skripta nima event listenerjev.
    // --- 3. KONEC: EVENT LISTENERS --- //



    // --- 4. UI LOGIC FUNCTIONS --- //
    function initializeSharedPageConfig() {
        // Preveri, če je že inicializiran
        if (window.sharedPageConfig) {
            return;
        }

        // Poišči element z class gp-main-view--root (na njemu so vsi data atributi)
        const datasetElement = document.querySelector(mainViewSelector);
        if (!datasetElement) {
            // Če ni datasetElement, počakaj malo in poskusi znova (morda se še naloži)
            setTimeout(initializeSharedPageConfig, 100);
            return;
        }

        const config = {};
        let hasError = false;

        // Preberi vse atribute iz elementa in jih shrani v config objekt
        for (const { key, attr, error, optional } of configAttributes) {
            const value = datasetElement.dataset[attr];
            if (!value) {
                if (!optional) {
                    console.error(`[SHARED CONFIG] ${error} atribut je prazen`);
                    hasError = true;
                    break;
                }
                // Če je opcijski in manjka, ga preskočimo
                continue;
            }
            config[key] = value;
        }

        if (hasError) {
            return;
        }

        window.sharedPageConfig = {
            ...config,
            buildSelector: buildSelector
        };
    }

    // Helper funkcija za gradnjo CSS selectorjev
    function buildSelector(suffix) {
        if (!window.sharedPageConfig) return suffix;

        // Uporabimo prefix iz atributa ali privzeto 'pg-'
        const prefix = window.sharedPageConfig.pagePrefix || 'pg-';

        // Odstranimo začetno piko, če obstaja
        const cleanSuffix = suffix.startsWith('.') ? suffix.slice(1) : suffix;

        // Gradimo selector: pluginSlug + pagePrefix + pageSlug + suffix
        return `.${window.sharedPageConfig.pluginSlug}.${prefix}${window.sharedPageConfig.pageSlug}.${cleanSuffix}`;
    }
    // --- 4. KONEC: UI LOGIC FUNCTIONS --- //



    // --- 5. OTHER HELPER FUNCTIONS --- //
    // Funkcija buildSelector je že izpostavljena preko window.sharedPageConfig
    // --- 5. KONEC: OTHER HELPER FUNCTIONS --- //
})();
