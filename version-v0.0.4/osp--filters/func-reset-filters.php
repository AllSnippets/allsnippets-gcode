<?php defined('WPINC') || die;

add_action('wp_ajax_all_snippets__ajax__reset_filters__vsh0_0_4', 'all_snippets__ajaxfunc__reset_filters__vsh0_0_4');
function all_snippets__ajaxfunc__reset_filters__vsh0_0_4() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) {
        wp_send_json_error('[RESET FILTERS] Missing nonce constant.');
    }

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) {
        wp_send_json_error('[RESET FILTERS] Invalid nonce.');
    }

    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) {
        wp_send_json_error('[RESET FILTERS] Missing permission.');
    }
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION --- //
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';

    if (empty($user_id) || empty($page_slug) || empty($plugin_slug)) {
        wp_send_json_error('[RESET FILTERS] Missing required data (user_id, page_slug or plugin_slug).');
    }

    // Priprava imena opcije (Lowercase za vrednost v bazi)
    $option_name = 'userpref';
    // --- 2. INITIALIZATION --- //



    // --- 3. MAIN LOGIC --- //
    // Izbriši userpref iz baze preko našega shared helperja
    // Ker resetiramo vse filtre in nastavitve (stari JSON), izbrišemo celotno opcijo za tega uporabnika.
    $delete_result = all_snippets__helper__delete_option__vsh0_0_4([
        'plugin_slug' => $plugin_slug,
        'load_at'     => 'pg-' . $page_slug,
        'user_id'     => $user_id,
        'option_name' => $option_name
    ]);
    // --- 3. KONEC: MAIN LOGIC --- //



    // --- 4. RESPONSE --- //
    if ($delete_result) {
        wp_send_json_success('[RESET FILTERS] All filters and settings have been successfully reset.');
    } else {
        // Če opcija sploh ne obstaja, delete_option vrne false, a to ni nujno napaka za uporabnika.
        wp_send_json_success('[RESET FILTERS] Filters were already at default values.');
    }
    // --- 4. KONEC: RESPONSE --- //
}
