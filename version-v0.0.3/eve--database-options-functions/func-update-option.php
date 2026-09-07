<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__update_option__vsh0_0_3')) {
    function all_snippets__helper__update_option__vsh0_0_3($args) {
        global $wpdb;


        // --- 1. INPUT VALIDATION & DEFAULTS --- //
        $table_name = $wpdb->prefix . ALL_SNIPPETS__DATABASE__TABLE_OPTIONS;

        // Privzete vrednosti
        $defaults = [
            'plugin_slug'  => '',
            'load_at'      => 'global',
            'user_id'      => null,
            'option_name'  => '',
            'option_value' => ''
        ];

        $args = wp_parse_args($args, $defaults);

        // Preveri če tabela obstaja, če ne, jo ustvari
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            all_snippets__helper__create_options_table__vsh0_0_3();
        }

        if (empty($args['option_name'])) {
            return false;
        }

        // Pretvori prazne vrednosti v NULL (Rule 49: OCD poimenovanje)
        $plugin_slug = !empty($args['plugin_slug']) ? $args['plugin_slug'] : null;
        // --- 1. KONEC: INPUT VALIDATION & DEFAULTS --- //



        // --- 2. MAIN LOGIC --- //
        $serialized_value = maybe_serialize($args['option_value']);

        // Preveri če vrstica že obstaja za to unikatno kombinacijo
        $plugin_slug_sql = ($plugin_slug === null) ? "plugin_slug IS NULL" : $wpdb->prepare("plugin_slug = %s", $plugin_slug);
        $user_id_sql = ($args['user_id'] === null) ? "user_id IS NULL" : $wpdb->prepare("user_id = %d", $args['user_id']);

        $query = "SELECT id FROM $table_name 
             WHERE $plugin_slug_sql 
             AND load_at = %s 
             AND $user_id_sql 
             AND option_name = %s";

        $exists = $wpdb->get_var($wpdb->prepare($query, $args['load_at'], $args['option_name']));

        if ($exists) {
            // POSODOBI (vrednost in OBVEZNO ponastavi expiration na NULL, ker to ni transient)
            $wpdb->update(
                $table_name,
                [
                    'expiration'   => null,
                    'option_value' => $serialized_value
                ],
                ['id' => $exists]
            );
        } else {
            // VSTAVI NOVO (expiration je NULL po defaultu, a ga vseeno eksplicitno nastavimo za OCD red)
            $wpdb->insert(
                $table_name,
                [
                    'plugin_slug'  => $plugin_slug,
                    'load_at'      => $args['load_at'],
                    'user_id'      => $args['user_id'],
                    'option_name'  => $args['option_name'],
                    'expiration'   => null,
                    'option_value' => $serialized_value
                ]
            );
        }
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return true;
        // --- 3. KONEC: RETURN --- //
    }
}
