<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__load_shared_global_css__vsh0_0_4')) {
    function all_snippets__helper__load_shared_global_css__vsh0_0_4($plugin_prefix, $files = null) {
        $shared_version = constant($plugin_prefix . '__SHARED_CODE_VERSION');
        $base_url = rtrim(constant($plugin_prefix . '__SHARED_CODE_URL'), '/') . '/css-styles/';
        $version_suffix = '-v' . str_replace('.', '-', $shared_version);

        if ($files === null) {
            $files = [
                'gap--class',
                'gap--colors',
            ];
        }

        foreach ($files as $file_slug) {
            $handle = 'allsnippets-shared--' . $file_slug . $version_suffix;
            wp_enqueue_style(
                $handle,
                $base_url . $file_slug . '.css',
                [],
                $shared_version
            );
        }
    }
}
