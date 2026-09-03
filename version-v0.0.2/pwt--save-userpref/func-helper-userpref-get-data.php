<?php defined('WPINC') || die;
function all_snippets__helper__userpref_get_data__vsh0_0_2($plugin_slug, $user_id, $page_slug) {
    // --- 1. INPUT VALIDATION & DEFAULTS --- //
    if (empty($plugin_slug) || empty($user_id) || empty($page_slug)) {
        return [];
    }

    // Priprava imena opcije (Lowercase za vrednost v bazi)
    // Ime opcije je fiksno 'userpref', ker stran določamo s stolpcem load_at (pg-[page_slug])
    $option_name = 'userpref';
    // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



    // --- 2. MAIN LOGIC --- //
    // Pridobi podatke iz naše tabele preko shared helperja
    $user_preferences = all_snippets__helper__get_option__vsh0_0_2([
        'plugin_slug' => $plugin_slug,
        'load_at'     => 'pg-' . $page_slug,
        'user_id'     => intval($user_id),
        'option_name' => $option_name,
        'default'     => []
    ]);
    // --- 2. KONEC: MAIN LOGIC --- //



    // --- 3. RETURN --- //
    // Če ni array, vrni prazen array
    if (!is_array($user_preferences)) {
        return [];
    }

    return $user_preferences;
    // --- 3. KONEC: RETURN --- //
}
