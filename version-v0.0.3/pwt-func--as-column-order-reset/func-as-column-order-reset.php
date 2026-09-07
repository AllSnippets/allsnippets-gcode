<?php defined('WPINC') || die;
add_action('wp_ajax_all_snippets__ajax__reset_column_order__vsh0_0_3', 'all_snippets__ajaxfunc__reset_column_order__vsh0_0_3');
function all_snippets__ajaxfunc__reset_column_order__vsh0_0_3() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) wp_send_json_error('Missing nonce constant.');

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) wp_send_json_error('Invalid nonce.');
    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) wp_send_json_error('Missing permission.');
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION & CONSTANTS --- //
    $slug_for_constant = preg_replace('/^all-/', '', $plugin_slug);
    $base_dir_constant_name = 'ALL_' . strtoupper(str_replace('-', '_', $slug_for_constant)) . '__WP_CONTENT__USERPREF_DIR';
    if (!defined($base_dir_constant_name) && defined('ALL_' . strtoupper(str_replace('-', '_', $slug_for_constant)) . '__DATABASE__USERPREF_DIR')) {
        $base_dir_constant_name = 'ALL_' . strtoupper(str_replace('-', '_', $slug_for_constant)) . '__DATABASE__USERPREF_DIR';
    }
    $file_prefix_constant_name = 'ALL_' . strtoupper(str_replace('-', '_', $slug_for_constant)) . '__WP_CONTENT__USERPREF_JSON_PREFIX';
    if (!defined($file_prefix_constant_name) && defined('ALL_' . strtoupper(str_replace('-', '_', $slug_for_constant)) . '__DATABASE__USERPREF_JSON_PREFIX')) {
        $file_prefix_constant_name = 'ALL_' . strtoupper(str_replace('-', '_', $slug_for_constant)) . '__DATABASE__USERPREF_JSON_PREFIX';
    }

    // Helper functions
    $helper_get_func = 'all_snippets__helper__userpref_get_data__vsh0_0_3';
    $helper_save_func = 'all_snippets__helper__userpref_save_data__vsh0_0_3';
    // --- 2. KONEC: INITIALIZATION & CONSTANTS --- //



    // --- 3. INPUT VALIDATION & PREPARATION --- //
    if (empty($plugin_slug) || !preg_match('/^[a-z0-9\-]+$/', $plugin_slug)) {
        wp_send_json_error('Invalid plugin_slug.');
    }

    if (!defined($base_dir_constant_name) || !defined($file_prefix_constant_name)) {
        wp_send_json_error('Userpref constants do not exist.');
    }

    $base_dir = constant($base_dir_constant_name);
    $file_prefix = constant($file_prefix_constant_name);
    $user_id = get_current_user_id();

    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';
    if (empty($page_slug) || !preg_match('/^[a-z0-9\-_]+$/', $page_slug)) {
        wp_send_json_error('Invalid page_slug.');
    }
    // --- 3. KONEC: INPUT VALIDATION & PREPARATION --- //



    // --- 4. MAIN LOGIC --- //
    $user_preferences = call_user_func($helper_get_func, $user_id, $page_slug, $base_dir, $file_prefix);
    if (!is_array($user_preferences)) {
        $user_preferences = [];
    }

    // Odstrani column_order in column_widths iz preference
    unset($user_preferences['column_order']);
    unset($user_preferences['column_widths']);

    $save_result = call_user_func($helper_save_func, $user_id, $user_preferences, $page_slug, $base_dir, $file_prefix);
    // --- 4. KONEC: MAIN LOGIC --- //



    // --- 5. RESPONSE --- //
    if ($save_result) {
        wp_send_json_success('Column order has been reset.');
    } else {
        wp_send_json_error('Error saving preferences.');
    }
    // --- 5. KONEC: RESPONSE --- //
}