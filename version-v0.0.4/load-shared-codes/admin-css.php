<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__load_shared_css__vsh0_0_4')) {
    function all_snippets__helper__load_shared_css__vsh0_0_4($plugin_prefix, $current_page, $hook) {
        // --- 1. SETUP --- //
        // Priprava verzije in URL-ja
        $shared_version = constant($plugin_prefix . '__SHARED_CODE_VERSION');
        $base_url = rtrim(constant($plugin_prefix . '__SHARED_CODE_URL'), '/') . '/css-styles/';
        $version_suffix = '-v' . str_replace('.', '-', $shared_version);

        // Pridobi seznam CSS datotek za trenutno stran
        $list_css = all_snippets__helper__get_effective_shared_list__vsh0_0_4($plugin_prefix, '__LIST__SHARED_CSS', $current_page);
        // --- 1. KONEC: SETUP --- //


        // --- 2. LOOP & ENQUEUE --- //
        foreach ($list_css as $file_slug) {
            // Sestavimo ime handle-a z verzijo
            $handle_with_version = 'allsnippets-shared-' . $file_slug . $version_suffix;

            wp_enqueue_style(
                $handle_with_version,
                $base_url . $file_slug . '.css', // URL do CSS datoteke
                [], // Brez odvisnosti
                $shared_version // Verzija
            );
        }
        // --- 2. KONEC: LOOP & ENQUEUE --- //
    }
}