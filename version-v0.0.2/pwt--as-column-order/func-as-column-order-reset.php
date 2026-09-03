<?php defined('WPINC') || die;

add_action('wp_ajax_all_snippets__ajax__reset_column_order__vsh0_0_2', 'all_snippets__ajaxfunc__reset_column_order__vsh0_0_2');
function all_snippets__ajaxfunc__reset_column_order__vsh0_0_2() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) {
        wp_send_json_error('[RESET COLUMN ORDER] Missing nonce constant.');
    }

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) {
        wp_send_json_error('[RESET COLUMN ORDER] Invalid nonce.');
    }

    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
        wp_send_json_error('[RESET COLUMN ORDER] Missing permission.');
    }
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION --- //
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';

    if (empty($user_id) || empty($page_slug) || empty($plugin_slug)) {
        wp_send_json_error('[RESET COLUMN ORDER] Missing required data (user_id, page_slug or plugin_slug).');
    }

    // Helper functions (Database-based)
    $helper_get_func = 'all_snippets__helper__userpref_get_data__vsh0_0_2';
    $helper_save_func = 'all_snippets__helper__userpref_save_data__vsh0_0_2';
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. MAIN LOGIC --- //
    // A) Pridobi obstoječe preference iz baze
    $user_preferences = call_user_func($helper_get_func, $plugin_slug, $user_id, $page_slug);
    if (!is_array($user_preferences)) {
        $user_preferences = [];
    }

    // B) Odstrani column_order in column_widths iz preference
    unset($user_preferences['column_order']);
    unset($user_preferences['column_widths']);

    // C) Shrani posodobljene preference v bazo
    $save_result = call_user_func($helper_save_func, $plugin_slug, $user_id, $user_preferences, $page_slug);
    // --- 3. KONEC: MAIN LOGIC --- //



    // --- 4. RESPONSE --- //
    if ($save_result) {
        wp_send_json_success('[RESET COLUMN ORDER] Column order has been reset.');
    } else {
        wp_send_json_error('[RESET COLUMN ORDER] Error saving preferences.');
    }
    // --- 4. KONEC: RESPONSE --- //
}
