<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__delete_option__vsh0_0_4')) {
    function all_snippets__helper__delete_option__vsh0_0_4($args) {
        global $wpdb;


        // --- 1. INPUT VALIDATION & DEFAULTS --- //
        $table_name = $wpdb->prefix . ALL_SNIPPETS__DATABASE__TABLE_OPTIONS;

        $defaults = [
            'plugin_slug' => '',
            'load_at'     => 'global',
            'user_id'     => null,
            'option_name' => ''
        ];

        $args = wp_parse_args($args, $defaults);

        if (empty($args['option_name'])) {
            return false;
        }

        // Pretvori prazne vrednosti v NULL (Rule 49: OCD poimenovanje)
        $plugin_slug = !empty($args['plugin_slug']) ? $args['plugin_slug'] : null;

        // Če tabela ne obstaja, nimamo kaj brisati
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return true;
        }
        // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



        // --- 2. MAIN LOGIC --- //
        $plugin_slug_sql = ($plugin_slug === null) ? "plugin_slug IS NULL" : $wpdb->prepare("plugin_slug = %s", $plugin_slug);
        $user_id_sql = ($args['user_id'] === null) ? "user_id IS NULL" : $wpdb->prepare("user_id = %d", $args['user_id']);

        $prepared_query = "DELETE FROM $table_name 
             WHERE $plugin_slug_sql 
             AND option_name = %s 
             AND $user_id_sql";

        $wpdb->query($wpdb->prepare($prepared_query, $args['option_name']));
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return true;
        // --- 3. KONEC: RETURN --- //
    }
}
