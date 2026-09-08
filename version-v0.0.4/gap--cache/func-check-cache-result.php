<?php defined('WPINC') || die;
// Globalna spremenljivka za shranjevanje statusov (runtime cache)
global $all_snippets_status_cache;
$all_snippets_status_cache = [];

// En sam hook, ki kliče glavno funkcijo - ONEMOGOČENO (kličemo ročno po potrebi)
if (!function_exists('all_snippets__helper__cache_results__vsh0_0_4')) {
    // ENA GLAVNA FUNKCIJA ZA PREVERJANJE VSEH VTIČNIKOV NAENKRAT
    function all_snippets__helper__cache_results__vsh0_0_4() {
        global $all_snippets_status_cache;
        static $has_run = false;

        // Preprečimo večkratno izvajanje v istem requestu
        if ($has_run) return;
        $has_run = true;

        // --- 1. SETUP & CONFIG --- //
        // 1. Priprava okolja (domena, site_id)
        $domain = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'unknown';
        $site_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;
        $response_keys = ['x7q9z', 'a1b2c', 'ok9lm'];
        
        $plugins_to_check = [];
        $needs_api_update = false;

        // Helperji za bazo
        $get_option_func = 'all_snippets__helper__get_option__vsh0_0_4';
        if (!function_exists($get_option_func)) {
            return;
        }
        // --- 1. KONEC: SETUP & CONFIG --- //


        // --- 2. FIND PLUGINS --- //
        // 2. Iskanje vseh aktivnih 'all-' vtičnikov preko konstant
        $all_constants = get_defined_constants(true);
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $slug) {
                $suffix = '__CACHE_SLUG';
                if (strpos($name, 'ALL_') === 0 && substr($name, -strlen($suffix)) === $suffix) {
                    $plugins_to_check[$slug] = $slug;
                }
            }
        }

        // 3. Preverjanje vseh v bazi (Batch Check)
        foreach ($plugins_to_check as $slug => $plugin_slug) {
            $status = 'nima';
            
            // Pridobi 'cache' iz baze za ta plugin
            $data = call_user_func($get_option_func, [
                'plugin_slug' => $slug,
                'option_name' => 'cache',
                'default'     => false
            ]);

            if ($data && is_array($data)) {
                // Validacija vsebine
                if (isset($data['e'], $data['s'], $data['h'])) {
                    // Preverimo potek
                    if (time() > intval($data['e'])) {
                        $status = 'expired'; // Potreben update
                        $needs_api_update = true;
                    } else {
                        // Preverimo podpis (hash)
                        $flagged = in_array($data['s'], $response_keys, true);
                        
                        // Pridobimo pravi slug za preverjanje
                        // API vrača čist slug (npr. 'all-broken-media')
                        $slug_to_check = isset($data['p']) ? $data['p'] : $slug;
                        
                        $check_str = ($flagged ? 'result' : 'no_result') . '|' . $data['e'] . '|' . $slug_to_check . '|' . $domain . '|' . $site_id;
                        $calculated_hash = hash('sha256', $check_str);
                        
                        if (hash_equals($calculated_hash, $data['h'])) {
                            // Validna datoteka
                            if ($flagged) {
                                $status = 'ima';
                            } else {
                                // Preverimo še trial
                                if (isset($data['t']) && strpos($data['t'], 'ft_') === 0) {
                                    $decoded = base64_decode(substr($data['t'], 3));
                                    $ts = ($decoded) ? intval(substr($decoded, 0, -4)) : 0;
                                    if ($ts > time()) $status = 'proba';
                                }
                            }
                        }
                    }
                } else {
                    $needs_api_update = true; // Pokvarjeni podatki
                }
            } else {
                $needs_api_update = true; // Manjkajo podatki
            }
            
            $all_snippets_status_cache[$slug] = $status;
        }

        // Če kakšen vtičnik nima cache-a ali je potekel, sproži update
        if ($needs_api_update) {
            if (function_exists('all_snippets__helper__cache_get__vsh0_0_4')) {
                all_snippets__helper__cache_get__vsh0_0_4();
            }
        }
        // --- 2. KONEC: FIND PLUGINS --- //
    }
}



if (!function_exists('all_snippets__helper__check_cache_result__vsh0_0_4')) {
    /**
     * Vrne status cache-a za določen vtičnik.
     */
    function all_snippets__helper__check_cache_result__vsh0_0_4($slug) {
        global $all_snippets_status_cache;

        // Če cache še ni bil naložen, ga naložimo zdaj
        if (empty($all_snippets_status_cache)) {
            all_snippets__helper__cache_results__vsh0_0_4();
        }

        return isset($all_snippets_status_cache[$slug]) ? $all_snippets_status_cache[$slug] : 'nima';
    }
}
