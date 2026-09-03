<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__get_transient__vsh0_0_2')) {
    function all_snippets__helper__get_transient__vsh0_0_2($args) {
        global $wpdb;


        // --- 1. INPUT VALIDATION & DEFAULTS --- //
        $table_name = $wpdb->prefix . ALL_SNIPPETS__DATABASE__TABLE_OPTIONS;

        $defaults = [
            'plugin_slug' => '',
            'load_at'     => 'global',
            'user_id'     => null,
            'option_name' => '',
            'default'     => false
        ];

        $args = wp_parse_args($args, $defaults);

        if (empty($args['option_name'])) {
            return $args['default'];
        }

        // Pretvori prazne vrednosti v NULL (Rule 49: OCD poimenovanje)
        $plugin_slug = !empty($args['plugin_slug']) ? $args['plugin_slug'] : null;

        // Če tabela ne obstaja, vrnemo privzeto vrednost
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return $args['default'];
        }

        $cache_plugin_key = ($plugin_slug === null) ? 'shared' : $plugin_slug;
        $cache_key = $cache_plugin_key . ':' . $args['option_name'];
        // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



        // --- 2. MAIN LOGIC --- //
        // A) Najprej preveri v Redisu
        if (wp_using_ext_object_cache()) {
            $value = wp_cache_get($cache_key, 'all_snippets');
            if ($value !== false) {
                return $value;
            }
        }

        // B) Če v Redisu ni nič, preveri v tvoji tabeli
        $plugin_slug_sql = ($plugin_slug === null) ? "plugin_slug IS NULL" : $wpdb->prepare("plugin_slug = %s", $plugin_slug);
        $user_id_sql = ($args['user_id'] === null) ? "user_id IS NULL" : $wpdb->prepare("user_id = %d", $args['user_id']);

        $query = "SELECT option_value, expiration FROM $table_name 
             WHERE $plugin_slug_sql AND load_at = %s AND $user_id_sql AND option_name = %s";

        $row = $wpdb->get_row($wpdb->prepare($query, $args['load_at'], $args['option_name']));

        if (!$row) {
            return $args['default'];
        }

        // Preveri če je podatek v tabeli že potekel (Timestamp v expiration)
        if ($row->expiration !== null && $row->expiration < time()) {
            // Podatek je potekel, pobriši ga iz baze
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE $plugin_slug_sql AND option_name = %s",
                $args['option_name']
            ));
            return $args['default'];
        }

        $value = maybe_unserialize($row->option_value);

        // Če imamo Redis, ga osvežimo
        if (wp_using_ext_object_cache()) {
            $remaining = ($row->expiration !== null) ? ($row->expiration - time()) : 0;
            wp_cache_set($cache_key, $value, 'all_snippets', $remaining);
        }
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return $value;
        // --- 3. KONEC: RETURN --- //
    }
}
