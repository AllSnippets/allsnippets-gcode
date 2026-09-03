<?php defined('WPINC') || die;
add_action('wp_ajax_all_snippets__ajax__reset_filters__vsh0_0_2', 'all_snippets__ajaxfunc__reset_filters__vsh0_0_2');
function all_snippets__ajaxfunc__reset_filters__vsh0_0_2() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) wp_send_json_error('Missing nonce constant.');

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) wp_send_json_error('Invalid nonce.');
    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) wp_send_json_error('Missing permission.');
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION & CONSTANTS --- //
    $slug_for_constant_clean = str_replace('-', '_', $plugin_slug);
    $userpref_dir_constant = strtoupper($slug_for_constant_clean) . '__DATABASE__USERPREF_DIR';
    $userpref_prefix_constant = strtoupper($slug_for_constant_clean) . '__DATABASE__USERPREF_JSON_PREFIX';
    // --- 2. KONEC: INITIALIZATION & CONSTANTS --- //



    // --- 3. INPUT VALIDATION & PREPARATION --- //
    if (!isset($_POST['user_id']) || !isset($_POST['plugin_slug']) || !isset($_POST['page_slug'])) {
        wp_send_json_error('Missing required data.');
    }

    $user_id = intval($_POST['user_id']);
    $page_slug = sanitize_text_field($_POST['page_slug']);

    if (!defined($userpref_dir_constant) || !defined($userpref_prefix_constant)) {
        wp_send_json_error('Userpref constants are not defined for plugin: ' . $plugin_slug);
    }

    $file_path = constant($userpref_dir_constant) . 'pg-' . $page_slug . '/' . constant($userpref_prefix_constant) . intval($user_id) . '.json';
    // --- 3. KONEC: INPUT VALIDATION & PREPARATION --- //



    // --- 4. MAIN LOGIC --- //
    // Izbriši datoteko z userpref
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    // --- 4. KONEC: MAIN LOGIC --- //



    // --- 5. RESPONSE --- //
    wp_send_json_success('All filters and settings have been successfully reset.');
    // --- 5. KONEC: RESPONSE --- //
}