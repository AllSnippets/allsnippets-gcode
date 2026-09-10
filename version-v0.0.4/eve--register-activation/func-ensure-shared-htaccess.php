<?php defined('WPINC') || die;
add_action('init', 'all_snippets__helper__ensure_shared_htaccess__vsh0_0_4');
if (!function_exists('all_snippets__helper__ensure_shared_htaccess__vsh0_0_4')) {
    function all_snippets__helper__ensure_shared_htaccess__vsh0_0_4() {
        // --- 1. INPUT VALIDATION --- //
        if (!defined('WP_CONTENT_DIR')) {
            return false;
        }
        // Samo 1x na request
        static $done = false;
        if ($done) {
            return true;
        }
        $done = true;
        // --- 1. KONEC: INPUT VALIDATION --- //



        // --- 2. MAIN LOGIC --- //
        // En .htaccess za cel allsnippets-data in cel allsnippets-gcode, ne per vtičnik.
        $contents = "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar)$\">\nRequire all denied\n</FilesMatch>\n";
        $data_dir = WP_CONTENT_DIR . '/allsnippets-data';
        $gcode_dir = WP_CONTENT_DIR . '/allsnippets-gcode';
        if (!is_dir($data_dir) && !wp_mkdir_p($data_dir)) {
            $data_dir = '';
        }
        $dirs = [];
        if ($data_dir !== '' && is_dir($data_dir)) {
            $dirs[] = $data_dir;
        }
        if (is_dir($gcode_dir)) {
            $dirs[] = $gcode_dir;
        }
        foreach ($dirs as $dir) {
            $path = rtrim($dir, '/\\') . '/.htaccess';
            if (!file_exists($path)) {
                file_put_contents($path, $contents);
            }
        }
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return true;
        // --- 3. KONEC: RETURN --- //
    }
}
