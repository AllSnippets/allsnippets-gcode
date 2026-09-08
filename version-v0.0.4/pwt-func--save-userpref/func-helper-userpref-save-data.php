<?php defined('WPINC') || die;
function all_snippets__helper__userpref_save_data__vsh0_0_4($user_id, $user_preferences, $page_slug, $base_dir, $file_prefix) {
    // --- 1. INPUT VALIDATION --- //
    // Preveri, da so preference array
    if (!is_array($user_preferences)) {
        return false;
    }
    // --- 1. KONEC: INPUT VALIDATION --- //



    // --- 2. MAIN LOGIC --- //
    // Pridobi pot do datoteke
    $file_path = $base_dir . 'pg-' . $page_slug . '/' . $file_prefix . intval($user_id) . '.json';

    // Ustvari mapo, če ne obstaja
    $userpref_dir = dirname($file_path);
    if (!file_exists($userpref_dir)) {
        wp_mkdir_p($userpref_dir);
    }

    // Kodiraj v JSON z lepim formatiranjem
    $json_content = json_encode($user_preferences, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Shrani v datoteko
    $result = file_put_contents($file_path, $json_content, LOCK_EX);
    // --- 2. KONEC: MAIN LOGIC --- //



    // --- 3. RETURN --- //
    return $result !== false;
    // --- 3. KONEC: RETURN --- //
}