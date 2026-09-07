<?php defined('WPINC') || die;
function all_snippets__helper__userpref_get_data__vsh0_0_3($user_id, $page_slug, $base_dir, $file_prefix) {
    // --- 1. INPUT VALIDATION --- //
    // Pridobi pot do datoteke
    $file_path = $base_dir . 'pg-' . $page_slug . '/' . $file_prefix . intval($user_id) . '.json';

    // Ustvari mapo, če ne obstaja
    $userpref_dir = dirname($file_path);
    if (!file_exists($userpref_dir)) {
        wp_mkdir_p($userpref_dir);
    }

    // Če datoteka ne obstaja, vrni prazen array
    if (!file_exists($file_path)) {
        return [];
    }
    // --- 1. KONEC: INPUT VALIDATION --- //



    // --- 2. MAIN LOGIC --- //
    // Preberi vsebino datoteke
    $file_content = file_get_contents($file_path);
    if ($file_content === false) {
        return [];
    }

    // Dekodiraj JSON
    $user_preferences = json_decode($file_content, true);
    // --- 2. KONEC: MAIN LOGIC --- //



    // --- 3. RETURN --- //
    // Če dekodiranje ni uspelo ali ni array, vrni prazen array
    if (!is_array($user_preferences)) {
        return [];
    }

    return $user_preferences;
    // --- 3. KONEC: RETURN --- //
}