<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__load_shared_js__vsh0_0_4')) {
    function all_snippets__helper__load_shared_js__vsh0_0_4($plugin_prefix, $current_page, $hook) {
        // --- 1. PREPARATION --- //
        // Priprava verzije in URL-ja do shared mape
        $shared_version = constant($plugin_prefix . '__SHARED_CODE_VERSION');
        $base_url = rtrim(constant($plugin_prefix . '__SHARED_CODE_URL'), '/') . '/';
        $version_suffix = '-v' . str_replace('.', '-', $shared_version); // npr. -v0-0-1

        // Pridobi odvisnosti (npr. jquery) in seznam JS datotek za trenutno stran
        $deps = defined($plugin_prefix . '__DEPENDANCIES') ? constant($plugin_prefix . '__DEPENDANCIES') : [];
        $list_js = all_snippets__helper__get_effective_shared_list__vsh0_0_4($plugin_prefix, '__LIST__SHARED_JS', $current_page);
        // --- 1. KONEC: PREPARATION --- //

        // --- 2. LOOP & ENQUEUE --- //
        foreach ($list_js as $file_slug) {
            // Sestavimo unikatno ime ročice (handle) z verzijo, da se izognemo konfliktom
            $handle_with_version = basename($file_slug) . $version_suffix;

            // Naloži skripto (enqueue)
            wp_enqueue_script(
                $handle_with_version,
                $base_url . $file_slug . '.js', // URL do datoteke
                $deps, // Odvisnosti
                $shared_version, // Verzija datoteke (za cache busting)
                true // Naloži v footer
            );

            // Pokliči vtičnikovo funkcijo za lokalizacijo (wp_localize_script), če obstaja
            // To omogoča vtičniku, da pošlje podatke iz PHP v JS
            $func_name = strtolower($plugin_prefix) . '__localize_script';
            if (function_exists($func_name)) {
                $func_name($handle_with_version);
            }
        }
        // --- 2. KONEC: LOOP & ENQUEUE --- //
    }
}