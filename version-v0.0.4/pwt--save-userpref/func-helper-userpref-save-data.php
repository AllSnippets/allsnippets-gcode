<?php defined('WPINC') || die;
function all_snippets__helper__userpref_save_data__vsh0_0_4($plugin_slug, $user_id, $user_preferences, $page_slug) {
    // --- 1. INPUT VALIDATION & DEFAULTS --- //
    if (empty($plugin_slug) || empty($user_id) || empty($page_slug) || !is_array($user_preferences)) {
        return false;
    }

    // Priprava imena opcije (Lowercase za vrednost v bazi)
    // Ime opcije je fiksno 'userpref', ker stran določamo s stolpcem load_at (pg-[page_slug])
    $option_name = 'userpref';
    // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



    // --- 2. MAIN LOGIC --- //
    // Shrani v bazo preko našega shared helperja
    $result = all_snippets__helper__update_option__vsh0_0_4([
        'plugin_slug'  => $plugin_slug,
        'load_at'      => 'pg-' . $page_slug,
        'user_id'      => intval($user_id),
        'option_name'  => $option_name,
        'option_value' => $user_preferences
    ]);
    // --- 2. KONEC: MAIN LOGIC --- //



    // --- 3. RETURN --- //
    return $result;
    // --- 3. KONEC: RETURN --- //
}
