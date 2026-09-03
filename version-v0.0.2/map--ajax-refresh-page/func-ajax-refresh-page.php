<?php defined('WPINC') || die;
add_action('wp_ajax_all_snippets__ajax__refresh_page__vsh0_0_2', 'all_snippets__ajaxfunc__refresh_page__vsh0_0_2');
function all_snippets__ajaxfunc__refresh_page__vsh0_0_2() {
    // --- 1. SECURITY CHECK --- //
    $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';

    // Dinamično določi nonce konstanto na podlagi plugin_slug
    $nonce_constant = strtoupper(str_replace('-', '_', $plugin_slug)) . '__NONCE_NAME';
    if (!defined($nonce_constant)) wp_send_json_error('Missing nonce constant.');

    if (!check_ajax_referer(constant($nonce_constant), 'nonce', false)) wp_send_json_error('Invalid nonce.');
    if (!current_user_can('manage_options') && !current_user_can('manage_woocommerce')) wp_send_json_error('Missing permission.');
    // --- 1. KONEC: SECURITY CHECK --- //



    // --- 2. INITIALIZATION & CONSTANTS --- //
    // Priprava imena funkcije za klic
    $page_slug = isset($_POST['page_slug']) ? sanitize_text_field($_POST['page_slug']) : '';
    $display_func_name = 'all_snippets__helper__' . $page_slug;
    // --- 2. KONEC: INITIALIZATION & CONSTANTS --- //



    // --- 3. INPUT VALIDATION & PREPARATION --- //
    if (empty($page_slug)) {
        wp_send_json_error('Missing page_slug.');
    }
    
    // Nastavi $_GET parametre za simulacijo page load-a
    foreach ($_POST as $key => $value) {
        $_GET[$key] = $value;
    }
    // --- 3. KONEC: INPUT VALIDATION & PREPARATION --- //



    // --- 4. MAIN LOGIC --- //
    // Klic display funkcije
    if (function_exists($display_func_name)) {
        $html = call_user_func($display_func_name, true);
    } else {
        $html = 'Error: Display function not found.';
    }
    // --- 4. KONEC: MAIN LOGIC --- //



    // --- 5. RESPONSE --- //
    echo $html;
    wp_die();
    // --- 5. KONEC: RESPONSE --- //
}