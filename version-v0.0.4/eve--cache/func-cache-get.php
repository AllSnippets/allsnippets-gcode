<?php defined('WPINC') || die;
add_action('admin_post_all_snippets__postaction__button_update__vsh0_0_4', 'all_snippets__postfunc__button_update__vsh0_0_4');
if (!function_exists('all_snippets__postfunc__button_update__vsh0_0_4')) {
    function all_snippets__postfunc__button_update__vsh0_0_4() {
        all_snippets__helper__cache_get__vsh0_0_4();
        $referer = wp_get_referer();
        if ($referer) {
            wp_safe_redirect($referer);
        } else {
            wp_safe_redirect(admin_url());
        }
        exit;
    }
}



add_action('rest_api_init', 'all_snippets__hook__remote_update__vsh0_0_4');
if (!function_exists('all_snippets__hook__remote_update__vsh0_0_4')) {
    function all_snippets__hook__remote_update__vsh0_0_4() {
        register_rest_route('allsnippets-cache/v1', '/refresh', array(
            'methods' => 'POST',
            'callback' => function($request) {
                all_snippets__helper__cache_get__vsh0_0_4();
                return array('status' => 'success');
            },
            'permission_callback' => '__return_true',
        ));
    }
}



if (!function_exists('all_snippets__helper__cache_get__vsh0_0_4')) {
    function all_snippets__helper__cache_get__vsh0_0_4() {
        // --- 1. GATHER PLUGINS --- //
        // 1. Zberemo vse AllSnippets vtičnike iz __CACHE_SLUG konstant
        $plugins_map = [];
        $all_constants = get_defined_constants(true);
        
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $value) {
                $suffix = '__CACHE_SLUG';
                // Primer: ALL_DB_TABLES__CACHE_SLUG => 'all-db-tables'
                if (strpos($name, 'ALL_') === 0 && substr($name, -strlen($suffix)) === $suffix) {
                    // Dobimo prefix brez '__CACHE_SLUG' (dinamična dolžina)
                    $prefix = substr($name, 0, -strlen($suffix)); 
                    $plugins_map[$value] = $value; // Ključ in vrednost sta slug
                }
            }
        }

        if (empty($plugins_map)) {
            return;
        }
        // --- 1. KONEC: GATHER PLUGINS --- //


        // --- 2. API CALL --- //
        // 2. Priprava podatkov za API klic
        $domain = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'unknown';
        $site_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;
        $plugins_list = array_keys($plugins_map);

        // 3. Klic allsnippets.com API-ja
        $response = wp_remote_post('https://allsnippets.com/wp-json/allsnippets/v1/check-cache', array(
            'timeout' => 15,
            'blocking' => true, // Mora počakati na odgovor
            'body' => array(
                'plugins' => $plugins_list, // Pošljemo array slugov
                'domain' => $domain,
                'site_id' => $site_id,
            )
        ));

        if (is_wp_error($response)) {
            error_log('All Snippets Cache Update Failed: ' . $response->get_error_message());
            return;
        }
        // --- 2. KONEC: API CALL --- //



        // --- 3. PROCESS RESPONSE --- //
        // 4. Obdelava JSON odgovora
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // 4. Obdelaj rezultate in shrani v bazo (preko shared helperja)
        if (!is_array($data) || !isset($data['batch_results']) || !is_array($data['batch_results'])) {
            return;
        }

        // Helper za posodabljanje opcij v bazi
        $update_func = 'all_snippets__helper__update_option__vsh0_0_4';
        if (!function_exists($update_func)) {
            return;
        }

        foreach ($data['batch_results'] as $slug => $result) {
            // Preveri, če imamo ta slug in če je rezultat validen
            if (isset($plugins_map[$slug]) && is_array($result) && isset($result['s'], $result['e'])) {
                // Shrani v najino čisto tabelo pod imenom 'cache'
                call_user_func($update_func, [
                    'plugin_slug'  => $slug,
                    'load_at'      => 'global',
                    'option_name'  => 'cache',
                    'option_value' => $result
                ]);
            }
        }
        // --- 3. KONEC: PROCESS RESPONSE --- //
    }
}
