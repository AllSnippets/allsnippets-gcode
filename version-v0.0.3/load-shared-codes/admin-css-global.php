<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__load_shared_global_css__vsh0_0_3')) {
    function all_snippets__helper__load_shared_global_css__vsh0_0_3($plugin_prefix) {
        $shared_version = constant($plugin_prefix . '__SHARED_CODE_VERSION');
        $base_url = rtrim(constant($plugin_prefix . '__SHARED_CODE_URL'), '/') . '/css-styles/';
        $version_suffix = '-v' . str_replace('.', '-', $shared_version);

        wp_enqueue_style(
            'allsnippets-shared-gp--global-class' . $version_suffix,
            $base_url . 'gp--global-class.css',
            [],
            $shared_version
        );
    }
}