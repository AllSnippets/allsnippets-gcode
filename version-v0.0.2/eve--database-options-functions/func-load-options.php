<?php defined('WPINC') || die;
require_once dirname(__DIR__) . '/constants/database-table-names.php';
add_action('plugins_loaded', 'all_snippets__hook__load_options_to_cache__vsh0_0_2', 1);
function all_snippets__hook__load_options_to_cache__vsh0_0_2() {
    global $wpdb, $all_snippets_options_cache;


    // --- 1. INPUT VALIDATION --- //
    $table_name = $wpdb->prefix . ALL_SNIPPETS__DATABASE__TABLE_OPTIONS;
    $all_snippets_options_cache = [];

    // Če tabela ne obstaja, ne nalagamo ničesar
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return false;
    }

    $user_id = get_current_user_id();

    // Določi kontekste nalaganja
    $load_contexts = ['global'];

    if (is_admin()) {
        $load_contexts[] = 'admin-global';
        
        // Poskusi ugotoviti specifičen slug strani iz URL-ja
        if (isset($_GET['page'])) {
            $current_page = sanitize_text_field($_GET['page']);
            
            // Če se stran začne z 'all-' in konča s '-page', izvlečemo sredinski del (npr. slist, bmed)
            if (preg_match('/^all-.*--(.*)-page$/', $current_page, $matches)) {
                $load_contexts[] = 'pg-' . $matches[1];
            } else {
                $load_contexts[] = 'pg-' . $current_page;
            }
        }
    } else {
        $load_contexts[] = 'frontend';
    }
    // --- 1. KONEC: INPUT VALIDATION --- //



    // --- 2. MAIN LOGIC --- //
    // Pripravi SQL za IN stavek
    $placeholders = array_fill(0, count($load_contexts), '%s');
    $context_sql = implode(', ', $placeholders);

    // Pripravimo REGEXP vzorec iz trenutnih kontekstov (npr. ^(global|admin-global|pg-slist)$ ali vsebuje vejice)
    // To nam omogoča, da v bazi hranimo load_at kot "pg-slist,pg-sfold"
    $regex_pattern = '^(' . implode('|', array_map('preg_quote', $load_contexts)) . ')$|,' . implode(',|,', array_map('preg_quote', $load_contexts)) . ',|^' . implode(',|^', array_map('preg_quote', $load_contexts)) . ',|,' . implode('$|,', array_map('preg_quote', $load_contexts)) . '$';

    // Poizvedba: vzamemo globalne (user_id IS NULL) ALI tiste za trenutnega userja (samo tiste, ki niso potekli)
    $query = $wpdb->prepare(
        "SELECT plugin_slug, load_at, option_name, option_value, user_id 
         FROM $table_name 
         WHERE load_at REGEXP %s 
         AND (expiration IS NULL OR expiration > %d) 
         AND (user_id IS NULL" . ($user_id ? $wpdb->prepare(" OR user_id = %d", $user_id) : "") . ")",
        $regex_pattern,
        time()
    );

    $results = $wpdb->get_results($query);

    if ($results) {
        foreach ($results as $row) {
            $cache_key = ($row->user_id === null) ? 'global' : 'user_' . $row->user_id;
            // Cache zdaj vključuje plugin_slug in load_at za popolno ločenost
            $all_snippets_options_cache[$row->plugin_slug][$row->load_at][$cache_key][$row->option_name] = maybe_unserialize($row->option_value);
        }
    }
    // --- 2. KONEC: MAIN LOGIC --- //



    // --- 3. RETURN --- //
    return true;
    // --- 3. KONEC: RETURN --- //
}
