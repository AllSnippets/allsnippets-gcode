<?php defined('WPINC') || die;

add_action('wp_ajax_all_snippets__ajax__save_column_order__vsh0_0_3', 'all_snippets__ajaxfunc__save_column_order__vsh0_0_3');
function all_snippets__ajaxfunc__save_column_order__vsh0_0_3() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) {
        wp_send_json_error('[SAVE COLUMN ORDER] Missing nonce constant.');
    }

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) {
        wp_send_json_error('[SAVE COLUMN ORDER] Invalid nonce.');
    }

    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
        wp_send_json_error('[SAVE COLUMN ORDER] Missing permission.');
    }
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION --- //
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';

    if (empty($user_id) || empty($page_slug) || empty($plugin_slug)) {
        wp_send_json_error('[SAVE COLUMN ORDER] Missing required data (user_id, page_slug or plugin_slug).');
    }

    $column_order = isset($_POST['column_order']) ? $_POST['column_order'] : [];
    $column_widths_raw = isset($_POST['column_widths']) ? $_POST['column_widths'] : [];

    // Sanitiziraj širine - samo številke (px)
    $column_widths = [];
    foreach ($column_widths_raw as $col => $width) {
        $column_widths[sanitize_text_field($col)] = !empty($width) ? intval($width) : '';
    }

    // Helper functions (Database-based)
    $helper_get_func = 'all_snippets__helper__userpref_get_data__vsh0_0_3';
    $helper_save_func = 'all_snippets__helper__userpref_save_data__vsh0_0_3';
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. MAIN LOGIC --- //
    // A) Pridobi obstoječe preference iz baze
    $user_preferences = call_user_func($helper_get_func, $plugin_slug, $user_id, $page_slug);
    if (!is_array($user_preferences)) {
        $user_preferences = [];
    }

    // B) Posodobi preference
    $user_preferences['column_order'] = array_map('sanitize_text_field', $column_order);
    $user_preferences['column_widths'] = $column_widths;

    // C) Shrani posodobljene preference v bazo
    $save_result = call_user_func($helper_save_func, $plugin_slug, $user_id, $user_preferences, $page_slug);
    // --- 3. MAIN LOGIC --- //



    // --- 4. RESPONSE --- //
    if ($save_result) {
        wp_send_json_success('[SAVE COLUMN ORDER] Column order has been saved.');
    } else {
        wp_send_json_error('[SAVE COLUMN ORDER] Error saving column order.');
    }
    // --- 4. KONEC: RESPONSE --- //
}
