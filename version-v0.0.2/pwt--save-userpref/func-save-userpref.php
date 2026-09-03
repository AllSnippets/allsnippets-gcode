<?php defined('WPINC') || die;

add_action('wp_ajax_all_snippets__ajax__save_userpref_admin_page__vsh0_0_2', 'all_snippets__ajaxfunc__save_userpref_admin_page__vsh0_0_2');
function all_snippets__ajaxfunc__save_userpref_admin_page__vsh0_0_2() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    
    if (!defined($nonce_constant)) {
        wp_send_json_error('[SAVE USERPREF] Missing nonce constant.');
    }

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) {
        wp_send_json_error('[SAVE USERPREF] Invalid nonce.');
    }

    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
        wp_send_json_error('[SAVE USERPREF] Missing permission.');
    }
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION --- //
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';
    $new_preferences = isset($_POST['user_preferences']) ? $_POST['user_preferences'] : [];

    if (empty($user_id) || empty($page_slug) || empty($plugin_slug)) {
        wp_send_json_error('[SAVE USERPREF] Missing required data (user_id, page_slug or plugin_slug).');
    }

    // Helper funkcije (Database-based)
    $helper_get_func = 'all_snippets__helper__userpref_get_data__vsh0_0_2';
    $helper_save_func = 'all_snippets__helper__userpref_save_data__vsh0_0_2';

    if (!function_exists($helper_get_func) || !function_exists($helper_save_func)) {
        wp_send_json_error('[SAVE USERPREF] Helper functions are not available.');
    }
    // --- 2. KONEC: INITIALIZATION --- //



    // --- 3. MAIN LOGIC --- //
    // A) Pridobi obstoječe preference iz baze
    $existing_preferences = call_user_func($helper_get_func, $plugin_slug, $user_id, $page_slug);
    
    // B) Priprava in sanitizacija podatkov
    $user_preferences = is_array($existing_preferences) ? $existing_preferences : [];

    // Limit
    if (isset($new_preferences['limit'])) {
        $user_preferences['limit'] = intval($new_preferences['limit']);
    }

    // Sorting
    if (isset($new_preferences['orderby'])) {
        $user_preferences['orderby'] = sanitize_text_field($new_preferences['orderby']);
    }
    if (isset($new_preferences['order'])) {
        $user_preferences['order'] = sanitize_text_field($new_preferences['order']);
    }

    // Columns
    if (isset($new_preferences['columns'])) {
        $user_preferences['columns'] = array_map('sanitize_text_field', $new_preferences['columns']);
    }

    // Table visibility
    if (isset($new_preferences['tables'])) {
        $user_preferences['tables'] = array_map('sanitize_text_field', $new_preferences['tables']);
    }

    // Dinamično obravnava vseh filter polj
    foreach ($new_preferences as $key => $value) {
        if ($key === 'filter' || strpos($key, 'filter_') === 0) {
            if (is_array($value)) {
                $sanitized_filter = [];
                foreach ($value as $filter_path => $mode) {
                    $sanitized_filter[sanitize_text_field($filter_path)] = sanitize_text_field($mode);
                }
                $user_preferences[$key] = $sanitized_filter;
            } else {
                $user_preferences[$key] = sanitize_text_field($value);
            }
        }
    }

    // C) Shranjevanje v bazo
    $save_result = call_user_func($helper_save_func, $plugin_slug, $user_id, $user_preferences, $page_slug);
    // --- 3. KONEC: MAIN LOGIC --- //



    // --- 4. RESPONSE --- //
    if ($save_result) {
        wp_send_json_success('[SAVE USERPREF] Settings saved to database.');
    } else {
        wp_send_json_error('[SAVE USERPREF] Error: Could not save settings.');
    }
    // --- 4. KONEC: RESPONSE --- //
}
