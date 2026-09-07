<?php defined('WPINC') || die;
add_action('wp_ajax_all_snippets__ajax__save_userpref_admin_page__vsh0_0_3', 'all_snippets__ajaxfunc__save_userpref_admin_page__vsh0_0_3');
function all_snippets__ajaxfunc__save_userpref_admin_page__vsh0_0_3() {
    // --- 1. SECURITY CHECK --- //
    // Začetek: Preverjanje varnosti
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) wp_send_json_error('Missing nonce constant.');

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) wp_send_json_error('Invalid nonce.');
    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) wp_send_json_error('Missing permission.');
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION & CONSTANTS --- //
    // Definicija helper funkcij in konstant
    $helper_get_func = 'all_snippets__helper__userpref_get_data__vsh0_0_3';
    $helper_save_func = 'all_snippets__helper__userpref_save_data__vsh0_0_3';
    
    $slug_for_constant_clean = strtoupper(str_replace('-', '_', $plugin_slug));
    $userpref_dir_constant = $slug_for_constant_clean . '__WP_CONTENT__USERPREF_DIR';
    if (!defined($userpref_dir_constant) && defined($slug_for_constant_clean . '__DATABASE__USERPREF_DIR')) {
        $userpref_dir_constant = $slug_for_constant_clean . '__DATABASE__USERPREF_DIR';
    }
    $userpref_prefix_constant = $slug_for_constant_clean . '__WP_CONTENT__USERPREF_JSON_PREFIX';
    if (!defined($userpref_prefix_constant) && defined($slug_for_constant_clean . '__DATABASE__USERPREF_JSON_PREFIX')) {
        $userpref_prefix_constant = $slug_for_constant_clean . '__DATABASE__USERPREF_JSON_PREFIX';
    }
    // --- 2. KONEC: INITIALIZATION & CONSTANTS --- //


    
    // --- 3. INPUT VALIDATION & PREPARATION --- //
    // Preverjanje helper funkcij
    if (!function_exists($helper_get_func) || !function_exists($helper_save_func)) {
        wp_send_json_error('[SAVE USERPREF] Helper functions are not available for plugin: ' . $plugin_slug);
    }

    // Preverjanje konstant
    if (!defined($userpref_dir_constant) || !defined($userpref_prefix_constant)) {
        wp_send_json_error('[SAVE USERPREF] Userpref constants are not available for plugin: ' . $plugin_slug);
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';
    $new_preferences = isset($_POST['user_preferences']) ? $_POST['user_preferences'] : [];

    if (empty($user_id) || empty($page_slug)) {
        wp_send_json_error('[SAVE USERPREF] Missing required data (user_id or page_slug).');
    }

    $base_dir = constant($userpref_dir_constant);
    $file_prefix = constant($userpref_prefix_constant);
    // --- 3. KONEC: INPUT VALIDATION & PREPARATION --- //



    // --- 4. MAIN LOGIC --- //
    // Pridobi obstoječe preference
    $existing_preferences = call_user_func($helper_get_func, $user_id, $page_slug, $base_dir, $file_prefix);
    if (!is_array($existing_preferences)) {
        $existing_preferences = [];
    }

    // Sanitizacija podatkov in združitev z obstoječimi
    $user_preferences = $existing_preferences; // Ohrani obstoječe (vključno z column_order in column_widths)

    // Limit
    $user_preferences['limit'] = isset($new_preferences['limit']) ? intval($new_preferences['limit']) : (isset($existing_preferences['limit']) ? $existing_preferences['limit'] : 20);

    // Sorting
    $user_preferences['orderby'] = isset($new_preferences['orderby']) ? sanitize_text_field($new_preferences['orderby']) : (isset($existing_preferences['orderby']) ? $existing_preferences['orderby'] : '');
    $user_preferences['order'] = isset($new_preferences['order']) ? sanitize_text_field($new_preferences['order']) : (isset($existing_preferences['order']) ? $existing_preferences['order'] : 'DESC');

    // Columns
    if (isset($new_preferences['columns'])) {
        $user_preferences['columns'] = array_map('sanitize_text_field', $new_preferences['columns']);
    } elseif (isset($existing_preferences['columns'])) {
        $user_preferences['columns'] = $existing_preferences['columns'];
    }

    // Table visibility (za all-site-views in all-db-terms)
    if (isset($new_preferences['tables'])) {
        $user_preferences['tables'] = array_map('sanitize_text_field', $new_preferences['tables']);
    } elseif (isset($existing_preferences['tables'])) {
        $user_preferences['tables'] = $existing_preferences['tables'];
    }

    // Dinamično obravnava vseh filter polj
    // Združi vse ključe iz novih in obstoječih preferenc
    $all_keys = array_unique(array_merge(array_keys($new_preferences), array_keys($existing_preferences)));

    foreach ($all_keys as $key) {
        // Preveri, če je ključ filter (se začne s 'filter_' ali je točno 'filter')
        if ($key === 'filter' || strpos($key, 'filter_') === 0) {
            if (isset($new_preferences[$key])) {
                // Preveri, če je filter array (toggle/dropdown) ali string (radio)
                if (is_array($new_preferences[$key])) {
                    // Toggle ali dropdown filter - sanitiziraj vsak element
                    $sanitized_filter = [];
                    foreach ($new_preferences[$key] as $filter_path => $mode) {
                        $sanitized_filter[sanitize_text_field($filter_path)] = sanitize_text_field($mode);
                    }
                    $user_preferences[$key] = $sanitized_filter;
                } else {
                    // Radio filter - enostavna string vrednost
                    $user_preferences[$key] = sanitize_text_field($new_preferences[$key]);
                }
            } elseif (isset($existing_preferences[$key])) {
                // Ohrani obstoječo vrednost, če ni v novih podatkih
                $user_preferences[$key] = $existing_preferences[$key];
            }
        }
    }

    // Shranjevanje nastavitev
    $save_result = call_user_func($helper_save_func, $user_id, $user_preferences, $page_slug, $base_dir, $file_prefix);
    // --- 4. KONEC: MAIN LOGIC --- //



    // --- 5. RESPONSE --- //
    if ($save_result) {
        wp_send_json_success('[SAVE USERPREF] Settings have been successfully saved.');
    } else {
        wp_send_json_error('[SAVE USERPREF] Error: Settings could not be saved.');
    }
    // --- 5. KONEC: RESPONSE --- //
}