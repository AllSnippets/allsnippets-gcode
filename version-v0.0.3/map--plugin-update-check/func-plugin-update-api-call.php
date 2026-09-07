<?php defined('WPINC') || die;
// ZAČETEK KODE: Klic API-ja na allsnippets.com in ustvarjanje lastnega transienta
// Ta koda se naloži na lokalni instalaciji in pošilja zahteve na allsnippets.com API
// Dinamično preverja vse vtičnike z Author = "AllSnippets"
// OPOBA: Ta datoteka je ločena od func-plugin-update-wordpress-transient.php, ker gre za dve različni funkcionalnosti:
//        - func-plugin-update-api-call: kliče API na allsnippets.com in naredi lasten transient (48 ur)
//        - func-plugin-update-wordpress-transient: za WordPress transient, uporablja naš cache (ne kliče API-ja)

// ZAČETEK KODE: Preverjanje posodobitev preko WordPress filtra
// Preveri, če je filter že registriran (prepreči večkratno registracijo, če se datoteka naloži iz različnih vtičnikov)
if (!has_filter('pre_set_site_transient_update_plugins', 'all_snippets__hook__update_check_updates__vsh0_0_3')) {
    add_filter('pre_set_site_transient_update_plugins', 'all_snippets__hook__update_check_updates__vsh0_0_3', 10, 1);
}

function all_snippets__hook__update_check_updates__vsh0_0_3($transient) {
    // OPOBA: Kako deluje sistem z lastnim transientom in WordPress-ovim transientom:
    // 1. WordPress ima svoj 'update_plugins' transient, ki se posodablja vsakih 12 ur (WordPress-ov standardni proces)
    // 2. Naša koda se "vmešava" v ta proces preko filtra 'pre_set_site_transient_update_plugins'
    // 3. Ko WordPress posodobi svoj transient (vsakih 12 ur), se ta funkcija izvede
    // 4. Naša funkcija preveri, ali imamo lasten transient 'allsnippets_plugin_updates_cache' za 48 ur:
    //    - Če ga imamo in je še veljaven → uporabi te podatke (ne kliče API-ja) → zmanjša obremenitev strani
    //    - Če ga nimamo ali je potekel → pokliče API in shrani rezultate v lasten transient za 48 ur
    // 5. Nato doda podatke v WordPress-ov 'update_plugins' transient
    // REZULTAT: WordPress-ov transient se še vedno posodablja vsakih 12 ur (standardni proces),
    //           vendar naša koda ne kliče API-ja vsakih 12 ur, ampak samo vsakih 48 ur (ker uporablja lasten transient)

    // --- 1. VALIDATION --- //
    if (empty($transient->checked)) {
        return $transient;
    }

    // Preveri, ali je trenutna domena allsnippets.com - če je, ne kličemo API-ja, ker bi se klical sam sebe
    $current_domain = parse_url(home_url(), PHP_URL_HOST);
    if ($current_domain === 'allsnippets.com' || $current_domain === 'www.allsnippets.com') {
        return $transient;
    }

    // --- 1. KONEC: VALIDATION --- //



    // --- 2. CACHE CHECK --- //
    // Začetek: Preveri lasten transient za cache-iranje rezultatov (48 ur)
    // OPOBA: Lasten transient (48 ur) zmanjša obremenitev strani - API se kliče samo vsakih 48 ur namesto vsakih 12 ur
    $cache_key = 'allsnippets_plugin_updates_cache';
    $cached_data = get_site_transient($cache_key);
    $cache_duration = 48 * HOUR_IN_SECONDS; // 48 ur

    // Če imamo cache-ane podatke in so še veljavni, uporabimo jih
    // OPOBA: Tu ne kličemo API-ja - uporabimo podatke iz lastnega transienta (zmanjša obremenitev strani)
    if ($cached_data !== false && is_array($cached_data)) {
        foreach ($cached_data as $plugin_file => $plugin_update_data) {
            if (isset($transient->checked[$plugin_file])) {
                $current_version = $transient->checked[$plugin_file];
                if (isset($plugin_update_data['new_version']) && version_compare($current_version, $plugin_update_data['new_version'], '<')) {
                    // Dodaj podatke v WordPress-ov 'update_plugins' transient (ki se posodablja vsakih 12 ur)
                    $transient->response[$plugin_file] = (object) $plugin_update_data;
                }
            }
        }
        return $transient;
    }
    // Konec: Preveri lasten transient za cache-iranje rezultatov
    // --- 2. KONEC: CACHE CHECK --- //



    // --- 3. API CALL & PROCESS --- //
    // Začetek: Če nimamo cache-anih podatkov, pokličemo API in shranimo rezultate
    // OPOBA: Tu kličemo API samo, če lasten transient ne obstaja ali je potekel (vsakih 48 ur)
    $update_results = array();

    // Preveri vse naložene vtičnike
    foreach ($transient->checked as $plugin_file => $current_version) {
        // Pridobi podatke o vtičniku
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);

        // Preveri, ali je Author = "AllSnippets"
        // POMEMBNO: get_plugin_data() vrača Author kot HTML link (če je Author URI definiran), zato odstranimo HTML tagge
        $author = isset($plugin_data['Author']) ? wp_strip_all_tags($plugin_data['Author']) : '';
        if (empty($author) || $author !== 'AllSnippets') {
            continue;
        }

        // Izvleci slug iz plugin file (prvi del pred /)
        $plugin_slug = explode('/', $plugin_file)[0];

        // Pošlji zahtevo na API
        $api_url = 'https://allsnippets.com/wp-json/allsnippets/v1/check-update';
        $response = wp_remote_post($api_url, array(
            'timeout' => 10,
            'body' => array(
                'plugin_slug' => $plugin_slug,
                'current_version' => $current_version
            )
        ));

        if (is_wp_error($response)) {
            continue;
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            continue;
        }

        // Preveri, ali je na voljo nova verzija
        if (isset($data['new_version']) && version_compare($current_version, $data['new_version'], '<')) {
            $update_data = array(
                'slug' => $plugin_slug,
                'plugin' => $plugin_file,
                'new_version' => $data['new_version'],
                'url' => isset($data['homepage']) ? $data['homepage'] : 'https://allsnippets.com',
                'package' => isset($data['download_url']) ? $data['download_url'] : '',
                'tested' => isset($data['tested']) ? $data['tested'] : get_bloginfo('version'),
                'requires' => isset($data['requires']) ? $data['requires'] : '4.0',
                'requires_php' => isset($data['requires_php']) ? $data['requires_php'] : '5.6',
            );

            $transient->response[$plugin_file] = (object) $update_data;
            $update_results[$plugin_file] = $update_data;
        } else {
            // Tudi če ni posodobitve, shranimo to v cache, da ne bomo klicali API-ja za ta vtičnik
            $update_results[$plugin_file] = null;
        }
    }

    // Shrani rezultate v lasten transient za 48 ur
    // OPOBA: Shranimo rezultate v lasten transient, da naslednjič (ko se WordPress-ov transient posodobi po 12 urah)
    //        ne bomo klicali API-ja, ampak bomo uporabili te cache-ane podatke
    set_site_transient($cache_key, $update_results, $cache_duration);
    // Konec: Če nimamo cache-anih podatkov, pokličemo API in shranimo rezultate
    // --- 3. KONEC: API CALL & PROCESS --- //

    return $transient;
}