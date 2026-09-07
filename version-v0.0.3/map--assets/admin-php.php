<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__load_shared_php__vsh0_0_3')) {
    function all_snippets__helper__load_shared_php__vsh0_0_3($plugin_prefix, $current_page) {
        // --- 1. SETUP --- //
        // Pripravimo verzijo in pot do mape (npr. .../version-v0.0.1/)
        $shared_version = constant($plugin_prefix . '__SHARED_CODE_VERSION');
        $base_dir = rtrim(constant($plugin_prefix . '__SHARED_CODE_DIR'), '/') . '/';

        // Pridobi seznam PHP datotek, ki so relevantne za trenutno stran
        $list_php = all_snippets__helper__get_effective_shared_list__vsh0_0_3($plugin_prefix, '__LIST__SHARED_PHP', $current_page);
        // --- 1. KONEC: SETUP --- //


        // --- 2. LOOP & LOAD --- //
        // Gremo čez seznam in naložimo vsako datoteko
        foreach ($list_php as $file_slug) {
            $shared_file_path = $base_dir . $file_slug . '.php'; // Sestavimo polno pot
            if (file_exists($shared_file_path)) {
                require_once $shared_file_path; // Naloži datoteko, če obstaja
            }
        }
        // --- 2. KONEC: LOOP & LOAD --- //
    }
}