<?php defined('WPINC') || die;
add_action('admin_init', 'all_snippets__hook__maintenance_check__vsh0_0_3', 1);
if (!function_exists('all_snippets__hook__maintenance_check__vsh0_0_3')) {
    function all_snippets__hook__maintenance_check__vsh0_0_3() {
        // Samo 1x na request
        static $checked = false;
        if ($checked) return;
        $checked = true;

        // Helperji za bazo
        $get_transient_func = 'all_snippets__helper__get_transient__vsh0_0_3';
        $set_transient_func = 'all_snippets__helper__set_transient__vsh0_0_3';
        $get_option_func = 'all_snippets__helper__get_option__vsh0_0_3';

        if (!function_exists($get_transient_func) || !function_exists($set_transient_func) || !function_exists($get_option_func)) {
            return;
        }

        // --- 1. LOCK CHECK (Database-based) --- //
        // Preveri, če je bilo preverjanje že opravljeno v zadnjih 7 dneh
        // Uporabljamo transient v najini tabeli. 
        // plugin_slug je NULL, ker je to skupno preverjanje za celoten sistem.
        $lock = call_user_func($get_transient_func, [
            'plugin_slug' => null,
            'option_name' => 'sys_maintenance_lock',
            'default'     => false
        ]);

        if ($lock) {
            return; // Preverjanje še ni potrebno
        }

        // Postavi nov lock za 7 dni (604800 sekund)
        call_user_func($set_transient_func, [
            'plugin_slug' => null,
            'option_name' => 'sys_maintenance_lock',
            'option_value' => time(),
            'expiration'  => 604800
        ]);
        // --- 1. KONEC: LOCK CHECK --- //



        // --- 2. PROCESS PLUGINS --- //
        // 3. Dinamično preverjanje VSEH aktivnih vtičnikov, ki se začnejo z 'all-'
        $active_plugins = (array) get_option('active_plugins', array());

        // Če je multisite, dodaj tudi network active plugine
        if (is_multisite()) {
            $network_active = get_site_option('active_sitewide_plugins');
            if (!empty($network_active)) {
                $active_plugins = array_merge($active_plugins, array_keys($network_active));
            }
        }

        // Priprava za batch reporting (zbiranje napak)
        $batched_errors = [];
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'unknown';

        foreach ($active_plugins as $plugin_path) {
            $slug = dirname($plugin_path);

            // Preskoči vtičnike, ki niso del našega ekosistema
            if (strpos($slug, 'all-') !== 0) {
                continue;
            }

            // Pridobi 'cache' iz baze za ta plugin
            $sys_data = call_user_func($get_option_func, [
                'plugin_slug' => $slug,
                'option_name' => 'cache',
                'default'     => false
            ]);

            // Če podatkov ni, potem ni kaj preverjati
            if (!$sys_data) {
               continue;
            }

            $db_integrity_error = false;
            $error_code = '';

            if (!is_array($sys_data) || !isset($sys_data['s'])) {
                $db_integrity_error = true;
                $error_code = 'err_sys_data_corrupt';
            } else {
                // Verify System Signatures
                $core_signatures = ['x7q9z', 'a1b2c', 'ok9lm'];

                // Preveri podpis
                if (!in_array($sys_data['s'], $core_signatures)) {
                     $db_integrity_error = true;
                     $error_code = 'err_signature_mismatch';
                }
            }

            if ($db_integrity_error) {
                // Dodaj napako v batch seznam
                $batched_errors[] = [
                    'plugin_slug' => $slug,
                    'error_code' => $error_code,
                    'details' => 'Maintenance Error: ' . $error_code
                ];
            }
        }
        // --- 2. KONEC: PROCESS PLUGINS --- //



        // --- 3. ERROR REPORTING --- //
        // Če so bile najdene napake, pošlji EN sam skupinski (batch) request
        if (!empty($batched_errors)) {
            wp_remote_post('https://allsnippets.com/wp-json/allsnippets/v1/report-maintenance', [
                'blocking' => false,
                'body' => [
                    'domain' => $host,
                    'batch_mode' => 'true', // Oznaka, da gre za batch
                    'batched_errors' => json_encode($batched_errors) // Pošlji array kot JSON string
                ],
            ]);
        }
        // --- 3. KONEC: ERROR REPORTING --- //
    }
}
