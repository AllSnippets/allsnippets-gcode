<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__delete_transient__vsh0_0_2')) {
    function all_snippets__helper__delete_transient__vsh0_0_2($args) {
        global $wpdb;


        // --- 1. INPUT VALIDATION & DEFAULTS --- //
        $table_name = $wpdb->prefix . '_allsnippets__options';

        $defaults = [
            'plugin_slug' => '',
            'load_at'     => 'global',
            'user_id'     => null,
            'option_name' => ''
        ];

        $args = wp_parse_args($args, $defaults);

        if (empty($args['option_name']) || empty($args['plugin_slug'])) {
            return false;
        }

        // Če tabela ne obstaja, nimamo kaj brisati
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return true;
        }

        $cache_key = $args['plugin_slug'] . ':' . $args['option_name'];
        // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



        // --- 2. MAIN LOGIC --- //
        // A) Pobriši iz Redisa
        if (wp_using_ext_object_cache()) {
            wp_cache_delete($cache_key, 'all_snippets');
        }

        // B) Pobriši iz tvoje tabele
        $user_id_sql = ($args['user_id'] === null) ? "user_id IS NULL" : $wpdb->prepare("user_id = %d", $args['user_id']);

        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name 
             WHERE plugin_slug = %s 
             AND load_at = %s 
             AND $user_id_sql 
             AND option_name = %s",
            $args['plugin_slug'],
            $args['load_at'],
            $args['option_name']
        ));
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return true;
        // --- 3. KONEC: RETURN --- //
    }
}
