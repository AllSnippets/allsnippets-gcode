<?php defined('WPINC') || die;
// ZAČETEK KODE: Podatki za WordPress transient iz našega cache-a
// Ta koda se naloži na lokalni instalaciji in uporablja podatke iz lastnega transienta
// Dinamično preverja vse vtičnike z Author = "AllSnippets"
// OPOBA: Ta datoteka je ločena od func-plugin-update-api-call.php, ker gre za dve različni funkcionalnosti:
//        - func-plugin-update-api-call: kliče API na allsnippets.com in naredi lasten transient (48 ur)
//        - func-plugin-update-wordpress-transient: za WordPress transient, uporablja naš cache (ne kliče API-ja)
// OPOBA: Ta datoteka kliče API samo, če je potrebno (ko uporabnik klikne "View version details" in ni cache-anih podatkov)

// Preveri, če je filter že registriran (prepreči večkratno registracijo, če se datoteka naloži iz različnih vtičnikov)
if (!has_filter('plugins_api', 'all_snippets__hook__update_plugin_info__vsh0_0_3')) {
    add_filter('plugins_api', 'all_snippets__hook__update_plugin_info__vsh0_0_3', 20, 3);
}

function all_snippets__hook__update_plugin_info__vsh0_0_3($result, $action, $args) {
    // --- 1. VALIDATION --- //
    if ($action !== 'plugin_information') {
        return $result;
    }

    // Preveri, ali je trenutna domena allsnippets.com - če je, ne kličemo API-ja, ker bi se klical sam sebe
    $current_domain = parse_url(home_url(), PHP_URL_HOST);
    if ($current_domain === 'allsnippets.com' || $current_domain === 'www.allsnippets.com') {
        return $result;
    }

    // Preveri, ali gre za AllSnippets vtičnik
    if (!isset($args->slug)) {
        return $result;
    }
    // --- 1. KONEC: VALIDATION --- //



    // --- 2. FIND PLUGIN --- //
    // Poišči vtičnik po slug-u
    $all_plugins = get_plugins();
    $plugin_file = null;

    foreach ($all_plugins as $file => $plugin_data) {
        $plugin_slug = explode('/', $file)[0];
        // POMEMBNO: get_plugins() vrača Author kot HTML link (če je Author URI definiran), zato odstranimo HTML tagge
        $author = isset($plugin_data['Author']) ? wp_strip_all_tags($plugin_data['Author']) : '';
        if ($plugin_slug === $args->slug && !empty($author) && $author === 'AllSnippets') {
            $plugin_file = $file;
            break;
        }
    }

    if (!$plugin_file) {
        return $result;
    }
    // --- 2. KONEC: FIND PLUGIN --- //



    // --- 3. API CALL --- //
    // Pridobi podatke o vtičniku
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
    $current_version = $plugin_data['Version'];
    $plugin_slug = explode('/', $plugin_file)[0];

    // Pošlji zahtevo na API
    $api_url = 'https://allsnippets.com/wp-json/allsnippets/v1/check-update';
    $response = wp_remote_post($api_url, array(
        'timeout' => 10,
        'body' => array(
            'plugin_slug' => $plugin_slug,
            'current_version' => $current_version,
            'action' => 'plugin_information'
        )
    ));

    if (is_wp_error($response)) {
        return $result;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($data['new_version']) && version_compare($current_version, $data['new_version'], '<')) {
        $result = (object) array(
            'slug' => $plugin_slug,
            'name' => isset($plugin_data['Name']) ? $plugin_data['Name'] : $plugin_slug,
            'version' => $data['new_version'],
            'author' => isset($data['author']) ? $data['author'] : 'AllSnippets',
            'author_profile' => isset($data['author_profile']) ? $data['author_profile'] : 'https://allsnippets.com',
            'homepage' => isset($data['homepage']) ? $data['homepage'] : 'https://allsnippets.com',
            'download_link' => isset($data['download_url']) ? $data['download_url'] : '',
            'sections' => array(
                'description' => isset($data['description']) ? $data['description'] : '',
                'changelog' => isset($data['changelog']) ? $data['changelog'] : '',
            ),
            'tested' => isset($data['tested']) ? $data['tested'] : get_bloginfo('version'),
            'requires' => isset($data['requires']) ? $data['requires'] : '4.0',
            'requires_php' => isset($data['requires_php']) ? $data['requires_php'] : '5.6',
        );
    }
    // --- 3. KONEC: API CALL --- //



    // --- 4. RETURN --- //
    return $result;
    // --- 4. KONEC: RETURN --- //
}