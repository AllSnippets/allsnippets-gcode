<?php defined('WPINC') || die;
add_action('admin_init', 'all_snippets__hook__maintenance_check__vsh0_0_2', 1);
if (!function_exists('all_snippets__hook__maintenance_check__vsh0_0_2')) {
    function all_snippets__hook__maintenance_check__vsh0_0_2() {
        // Samo 1x na request
        static $checked = false;
        if ($checked) return;
        $checked = true;

        // --- 1. SETUP & LOCK FILE --- //
        // 1. Določi pot do skupne mape za vzdrževanje v uploads/all-snippets-plugins-data
        $uploads_dir = wp_upload_dir();
        $maintenance_dir = $uploads_dir['basedir'] . '/all-snippets-plugins-data/maintenance-check/';

        // Ustvari mapo, če ne obstaja
        if (!is_dir($maintenance_dir)) {
            wp_mkdir_p($maintenance_dir);
            // Dodaj index.php za varnost
            file_put_contents($maintenance_dir . 'index.php', '<?php defined(\'WPINC\') || die;');
        }

        // 2. Preveri časovni žig (Lock file logic)
        $lock_files = glob($maintenance_dir . 'sys_check_*.lock');
        $is_check_needed = true;
        $week_in_seconds = 7 * 24 * 60 * 60;
        $current_time = time();

        if ($lock_files) {
            foreach ($lock_files as $file) {
                // Izvleci timestamp iz imena datoteke
                if (preg_match('/sys_check_(\d+)\.lock$/', basename($file), $matches)) {
                    $timestamp = (int)$matches[1];

                    // Če je datoteka veljavna (mlajša od 1 tedna), ne rabimo preverjati
                    if (($current_time - $timestamp) < $week_in_seconds) {
                        $is_check_needed = false;
                    } else {
                        // Če je potekla, jo izbriši, da se ne kopičijo
                        @unlink($file);
                    }
                } else {
                    // Izbriši datoteke s čudnimi imeni, če ustrezajo vzorcu
                    @unlink($file);
                }
            }
        }

        // Če ni potrebe po preverjanju, prekini izvajanje
        if (!$is_check_needed) {
            return;
        }

        // Ustvari nov lock file za naslednji teden
        file_put_contents($maintenance_dir . 'sys_check_' . $current_time . '.lock', '<?php defined(\'WPINC\') || die;');
        // --- 1. KONEC: SETUP & LOCK FILE --- //



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
            // Dobimo mapo vtičnika (npr. 'all-broken-media' ali 'all-mini-cart')
            $slug = dirname($plugin_path);

            // Preskoči vtičnike, ki niso del našega ekosistema (ne začnejo se z 'all-')
            if (strpos($slug, 'all-') !== 0) {
                continue;
            }

            // Konstruiramo prefiks konstant (npr. 'all-mini-cart' -> 'ALL_MINI_CART')
            $prefix = strtoupper(str_replace('-', '_', $slug));

            // Ime konstante za cache datoteko
            $const_name = $prefix . '__WP_CONTENT__CACHE_JSON';
            if (!defined($const_name) && defined($prefix . '__DATABASE__CACHE_JSON')) {
                $const_name = $prefix . '__DATABASE__CACHE_JSON';
            }

            // Če konstanta ni definirana, ta vtičnik verjetno ne uporablja tega sistema preverjanja
            if (!defined($const_name)) {
                continue;
            }

            $sys_log_file_base = constant($const_name);

            // Generiramo ID-je za ime datoteke
            $sys_id = preg_replace('/[^a-z0-9\-]+/', '-', str_replace('.', '-', $host));
            $node_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;

            // Sestavi polno pot do datoteke
            $sys_log_file = $sys_log_file_base . $sys_id . '-' . $node_id . '.json';

            // Če datoteka ne obstaja, potem itak ni kaj preverjati (in ni nujno napaka, morda je free verzija)
            if (!file_exists($sys_log_file)) {
               continue;
            }

            $db_integrity_error = false;
            $error_code = '';

            $sys_data = json_decode(file_get_contents($sys_log_file), true);
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