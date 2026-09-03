<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__load_shared_core_js__vsh0_0_2')) {
    function all_snippets__helper__load_shared_core_js__vsh0_0_2($plugin_prefix, $current_page, $hook) {
        // --- 1. SETUP --- //
        $shared_version = constant($plugin_prefix . '__SHARED_CODE_VERSION');
        $base_url = rtrim(constant($plugin_prefix . '__SHARED_CODE_URL'), '/') . '/';
        $version_suffix = '-v' . str_replace('.', '-', $shared_version);

        // Pridobi seznam core knjižnic za trenutno stran
        $list_core_js = all_snippets__helper__get_effective_shared_list__vsh0_0_2($plugin_prefix, '__LIST__SHARED_CORE_JS', $current_page);
        // --- 1. KONEC: SETUP --- //


        // --- 2. LOOP & ENQUEUE --- //
        foreach ($list_core_js as $file_info) {
            // Sestavimo ime handle-a
            $handle_with_version = basename($file_info['slug']) . $version_suffix;

            // Dodaj verzijo tudi vsem odvisnostim (dependencies) te knjižnice
            // Npr. če knjižnica rabi 'my-lib', bo ta postala 'my-lib-v0-0-1'
            $deps = array_map(function($dep) use ($version_suffix) {
                return $dep . $version_suffix;
            }, $file_info['deps']);

            wp_enqueue_script(
                $handle_with_version,
                $base_url . $file_info['slug'] . '.js',
                $deps,
                $shared_version,
                true
            );

            // Lokalizacija (če je potrebna)
            $func_name = strtolower($plugin_prefix) . '__localize_script';
            if (function_exists($func_name)) {
                $func_name($handle_with_version);
            }
        }
        // --- 2. KONEC: LOOP & ENQUEUE --- //
    }
}