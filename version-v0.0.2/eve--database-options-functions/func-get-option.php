<?php defined('WPINC') || die;
require_once dirname(__DIR__) . '/constants/database-table-names.php';
if (!function_exists('all_snippets__helper__get_option__vsh0_0_2')) {
    function all_snippets__helper__get_option__vsh0_0_2($args) {
        global $wpdb, $all_snippets_options_cache;


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

        // Preveri v globalnem cache-u (Smart Autoload)
        $cache_plugin_key = ($plugin_slug === null) ? 'shared' : $plugin_slug;
        $cache_user_key = ($args['user_id'] === null) ? 'global' : 'user_' . $args['user_id'];
        
        if (isset($all_snippets_options_cache[$cache_plugin_key][$args['load_at']][$cache_user_key][$args['option_name']])) {
            return $all_snippets_options_cache[$cache_plugin_key][$args['load_at']][$cache_user_key][$args['option_name']];
        }

        // Če tabela ne obstaja, vrnemo privzeto vrednost
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return $args['default'];
        }
        // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



        // --- 2. MAIN LOGIC --- //
        $plugin_slug_sql = ($plugin_slug === null) ? "plugin_slug IS NULL" : $wpdb->prepare("plugin_slug = %s", $plugin_slug);
        $user_id_sql = ($args['user_id'] === null) ? "user_id IS NULL" : $wpdb->prepare("user_id = %d", $args['user_id']);

        $query = "SELECT option_value FROM $table_name 
             WHERE $plugin_slug_sql 
             AND load_at = %s 
             AND $user_id_sql 
             AND option_name = %s";

        $value = $wpdb->get_var($wpdb->prepare($query, $args['load_at'], $args['option_name']));
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        if ($value === null) {
            return $args['default'];
        }

        $unserialized_value = maybe_unserialize($value);

        // Shranimo v cache za naslednjič
        $all_snippets_options_cache[$cache_plugin_key][$args['load_at']][$cache_user_key][$args['option_name']] = $unserialized_value;

        return $unserialized_value;
        // --- 3. KONEC: RETURN --- //
    }
}
