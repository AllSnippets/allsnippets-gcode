<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__set_transient__vsh0_0_2')) {
    function all_snippets__helper__set_transient__vsh0_0_2($args) {
        global $wpdb;


        // --- 1. INPUT VALIDATION & DEFAULTS --- //
        $table_name = $wpdb->prefix . '_allsnippets__options';

        $defaults = [
            'plugin_slug'  => '',
            'load_at'      => 'global',
            'user_id'      => null,
            'option_name'  => '',
            'expiration'   => null, // v sekundah (null = nikoli ne poteče)
            'option_value' => ''
        ];

        $args = wp_parse_args($args, $defaults);

        if (empty($args['option_name'])) {
            return false;
        }

        // Pretvori prazne vrednosti v NULL (Rule 49: OCD poimenovanje)
        $plugin_slug = !empty($args['plugin_slug']) ? $args['plugin_slug'] : null;

        // Preveri če tabela obstaja, če ne, jo ustvari
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            all_snippets__helper__create_options_table__vsh0_0_2();
        }

        $cache_plugin_key = ($plugin_slug === null) ? 'shared' : $plugin_slug;
        $cache_key = $cache_plugin_key . ':' . $args['option_name'];
        // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



        // --- 2. MAIN LOGIC --- //
        // A) Poskusi shraniti v Redis (če obstaja)
        if (wp_using_ext_object_cache()) {
            wp_cache_set($cache_key, $args['option_value'], 'all_snippets', $args['expiration'] ?? 0);
        }

        // B) Vedno shrani tudi v tvojo čisto tabelo (za varnost in OCD red)
        $serialized_value = maybe_serialize($args['option_value']);
        
        // Če je expiration 0 ali prazen, mora biti NULL v bazi (nikoli ne poteče)
        $exp_timestamp = (!empty($args['expiration']) && (int)$args['expiration'] > 0) ? (time() + (int)$args['expiration']) : null;
        
        $plugin_slug_sql = ($plugin_slug === null) ? "plugin_slug IS NULL" : $wpdb->prepare("plugin_slug = %s", $plugin_slug);
        $user_id_sql = ($args['user_id'] === null) ? "user_id IS NULL" : $wpdb->prepare("user_id = %d", $args['user_id']);

        $query = "SELECT id FROM $table_name WHERE $plugin_slug_sql AND load_at = %s AND $user_id_sql AND option_name = %s";

        $exists = $wpdb->get_var($wpdb->prepare($query, $args['load_at'], $args['option_name']));

        if ($exists) {
            $wpdb->update($table_name, 
                ['option_value' => $serialized_value, 'expiration' => $exp_timestamp], 
                ['id' => $exists]
            );
        } else {
            $wpdb->insert($table_name, [
                'plugin_slug'  => $plugin_slug,
                'load_at'      => $args['load_at'],
                'user_id'      => $args['user_id'],
                'option_name'  => $args['option_name'],
                'expiration'   => $exp_timestamp,
                'option_value' => $serialized_value
            ]);
        }
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return true;
        // --- 3. KONEC: RETURN --- //
    }
}
