<?php defined('WPINC') || die;
// Globalna spremenljivka za shranjevanje statusov (runtime cache)
global $all_snippets_status_cache;
$all_snippets_status_cache = [];

// En sam hook, ki kliče glavno funkcijo - ONEMOGOČENO (kličemo ročno po potrebi)
if (!function_exists('all_snippets__helper__cache_results__vsh0_0_2')) {
    // ENA GLAVNA FUNKCIJA ZA PREVERJANJE VSEH VTIČNIKOV NAENKRAT
    function all_snippets__helper__cache_results__vsh0_0_2() {
        global $all_snippets_status_cache;
        static $has_run = false;

        // Preprečimo večkratno izvajanje v istem requestu
        if ($has_run) return;
        $has_run = true;

        // --- 1. SETUP & CONFIG --- //
        // 1. Priprava okolja (domena, site_id)
        $domain = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'unknown';
        $clean_domain = preg_replace('/[^a-z0-9\-]+/', '-', str_replace('.', '-', $domain));
        $site_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;
        $response_keys = ['x7q9z', 'a1b2c', 'ok9lm'];
        
        $plugins_to_check = [];
        $needs_api_update = false;
        // --- 1. KONEC: SETUP & CONFIG --- //


        // --- 2. FIND PLUGINS --- //
        // 2. Iskanje vseh aktivnih 'all-' vtičnikov preko konstant
        $all_constants = get_defined_constants(true);
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $slug) {
                $suffix = '__CACHE_SLUG';
                // Iščemo konstante, ki se končajo na _CACHE_SLUG in začnejo z ALL_
                if (strpos($name, 'ALL_') === 0 && substr($name, -strlen($suffix)) === $suffix) {
                    // Sestavimo ime konstante za pot do JSON datoteke
                    // Npr. iz ALL_BROKEN_MEDIA__CACHE_SLUG dobimo prefix ALL_BROKEN_MEDIA
                    // Uporabimo dinamično dolžino suffix-a namesto hardcoded številke
                    $prefix = substr($name, 0, -strlen($suffix));
                    $json_const = $prefix . '__DATABASE__CACHE_JSON';
                    
                    if (defined($json_const)) {
                        $path_base = constant($json_const);
                        $plugins_to_check[$slug] = $path_base;
                    }
                }
            }
        }

        // 3. Prvo preverjanje vseh datotek (Batch Check)
        foreach ($plugins_to_check as $slug => $path_base) {
            $status = 'nima';
            $file_path = $path_base . $clean_domain . '-' . $site_id . '.json';

            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                $data = json_decode($content, true);

                // Validacija vsebine
                if (is_array($data) && isset($data['e'], $data['s'], $data['h'])) {
                    // Preverimo potek
                    if (time() > intval($data['e'])) {
                        $status = 'expired'; // Potreben update
                        $needs_api_update = true;
                    } else {
                        // Preverimo podpis (hash)
                        $flagged = in_array($data['s'], $response_keys, true);
                        
                        // Pridobimo pravi slug za preverjanje
                        // Če JSON vsebuje 'p' parameter (plugin slug), uporabimo tega namesto konstante
                        // API vrača čist slug (npr. 'all-broken-media'), medtem ko je konstanta lahko 'all-broken-media--slug'
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
                    $needs_api_update = true; // Pokvarjena datoteka
                }
            } else {
                $needs_api_update = true; // Manjka datoteka
            }
            
            $all_snippets_status_cache[$slug] = $status;
        }
        // --- 3. KONEC: BATCH CHECK --- //


        // --- 4. UPDATE IF NEEDED --- //
        // 4. Če karkoli manjka ali je poteklo, pokličemo API (samo enkrat za vse!)
        if ($needs_api_update && function_exists('all_snippets__helper__cache_get__vsh0_0_2')) {
            all_snippets__helper__cache_get__vsh0_0_2(); // Ta funkcija bo osvežila datoteke

            // 5. Ponovno preverimo datoteke (Re-Check po update-u)
            foreach ($plugins_to_check as $slug => $path_base) {
                // Ponovimo isto logiko preverjanja (skrajšano za berljivost, ker je identična)
                $status = 'nima';
                $file_path = $path_base . $clean_domain . '-' . $site_id . '.json';

                if (file_exists($file_path)) {
                    $content = file_get_contents($file_path);
                    $data = json_decode($content, true);
                    if (is_array($data) && isset($data['e'], $data['s'], $data['h'])) {
                        // Tokrat ne preverjamo poteka strogo, ker smo ravnokar posodobili
                        // Če je API vrnil stare podatke, pač so stari, ampak 'nima' pomeni da ni dostopa
                        $flagged = in_array($data['s'], $response_keys, true);
                        $check_str = ($flagged ? 'result' : 'no_result') . '|' . $data['e'] . '|' . $slug . '|' . $domain . '|' . $site_id;
                        
                        if (hash_equals(hash('sha256', $check_str), $data['h'])) {
                            if ($flagged) $status = 'ima';
                            elseif (isset($data['t']) && strpos($data['t'], 'ft_') === 0) {
                                $decoded = base64_decode(substr($data['t'], 3));
                                if (intval(substr($decoded, 0, -4)) > time()) $status = 'proba';
                            }
                        }
                    }
                }
                // Posodobimo globalni status
                $all_snippets_status_cache[$slug] = $status;
            }
        }
        // --- 4. KONEC: UPDATE IF NEEDED --- //
    }
}