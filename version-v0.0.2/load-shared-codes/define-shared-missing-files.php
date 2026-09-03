<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__get_effective_shared_list__vsh0_0_2')) {
    function all_snippets__helper__get_effective_shared_list__vsh0_0_2($plugin_prefix, $constant_name, $current_page) {
        // Sestavimo polno ime konstante, npr. ALL_LICENSES + __LIST__SHARED_PHP -> ALL_LICENSES__LIST__SHARED_PHP
        $name = $plugin_prefix . $constant_name;

        // Če konstanta obstaja, vzamemo njeno vrednost (seznam datotek), sicer prazen seznam
        $list = defined($name) ? constant($name) : [];

        // Če je seznam prazen, nimamo kaj delati, vrnemo prazno
        if (empty($list)) return [];

        // Filtriraj glede na 'pages' array in vrni samo datoteke za trenutno stran
        // array_reduce "zmanjša" array v en sam rezultat (tukaj v seznam datotek $acc)
        return array_reduce($list, function($acc, $group) use ($current_page) {
            // Če je trenutna stran v seznamu 'pages' te skupine, dodamo datoteke v skupni seznam ($acc)
            return (isset($group['pages']) && in_array($current_page, $group['pages']))
                ? array_merge($acc, $group['files']) // Dodaj datoteke
                : $acc; // Ne dodaj ničesar
        }, []);
    }
}



// Glavna funkcija: Preveri obstoj shared datotek in sproži prenos, če manjkajo
if (!function_exists('all_snippets__helper__check_shared_files_existence__vsh0_0_2')) {
    function all_snippets__helper__check_shared_files_existence__vsh0_0_2($plugin_prefix, $plugin_pages = []) {
        // --- 1. PREPARATION & CHECKS --- //
        // Pridobimo trenutno stran in akcijo iz URL-ja (npr. ?page=all-licenses)
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $slug = strtolower($plugin_prefix); // npr. ALL_LICENSES -> all_licenses

        // Preverimo, ali smo na strani vtičnika ali pa se izvaja AJAX klic vtičnika
        // Če nismo nič od tega, funkcija zaključi (return) in ne dela ničesar
        if (!in_array($page, $plugin_pages) &&
            !(defined('DOING_AJAX') && DOING_AJAX && (strpos($action, $slug) === 0 || strpos($action, 'all_snippets') === 0))) {
            return;
        }
        // --- 1. KONEC: PREPARATION & CHECKS --- //



        // --- 2. HELPER CLOSURE --- //
        // Pripravimo funkcijo za preverjanje, ki jo bomo poklicali dvakrat
        $check_missing = function() use ($plugin_prefix, $page) {
            // Sestavimo pot do mape s shared kodo: .../version-v0.0.1/
            $base = rtrim(constant($plugin_prefix . '__SHARED_CODE_DIR'), '/') . '/';

            // Seznam stvari za preverit: [končnica, seznam_datotek, podmapa (opcijsko)]
            $checks = [
                ['.php', all_snippets__helper__get_effective_shared_list__vsh0_0_2($plugin_prefix, '__LIST__SHARED_PHP', $page)],
                ['.js', all_snippets__helper__get_effective_shared_list__vsh0_0_2($plugin_prefix, '__LIST__SHARED_JS', $page)],
                // Pri core JS moramo iz arraya objektov dobiti samo slug (ime datoteke)
                ['.js', array_column(all_snippets__helper__get_effective_shared_list__vsh0_0_2($plugin_prefix, '__LIST__SHARED_CORE_JS', $page), 'slug')],
                ['.css', all_snippets__helper__get_effective_shared_list__vsh0_0_2($plugin_prefix, '__LIST__SHARED_CSS', $page), 'css-styles/']
            ];

            // Gremo čez vsako kategorijo in preverimo vsako datoteko
            foreach ($checks as $item) {
                $ext = $item[0]; // npr. .php
                $subdir = isset($item[2]) ? $item[2] : ''; // npr. css-styles/ ali prazno
                foreach ($item[1] as $f) {
                    // Če datoteka ne obstaja, takoj vrnemo TRUE (manjka!)
                    $full_path = $base . $subdir . $f . $ext;
                    if (!file_exists($full_path)) {
                        error_log('[ALL_SNIPPETS] Missing shared file: ' . $full_path);
                        return true;
                    }
                }
            }
            return false; // Vse datoteke obstajajo
        };
        // --- 2. KONEC: HELPER CLOSURE --- //



        // --- 3. EXECUTION LOGIC --- //
        // 1. Prvo preverjanje: Ali kaj manjka?
        if ($check_missing()) {
            // Če manjka, poskusimo prenesti kodo iz GitHub-a (če funkcija obstaja)
            if (function_exists('all_snippets__helper__dowload_shared_files')) {
                all_snippets__helper__dowload_shared_files();
            }
            // 2. Drugo preverjanje: Ali še vedno kaj manjka po poskusu prenosa?
            // Če še vedno manjka, nastavimo konstanto MISSING na TRUE
            if ($check_missing()) {
                if (!defined($plugin_prefix . '__SHARED_FILES_MISSING')) {
                    define($plugin_prefix . '__SHARED_FILES_MISSING', true);
                }
            } else {
                // Če je bil prenos uspešen in nič ne manjka, nastavimo na FALSE
                if (!defined($plugin_prefix . '__SHARED_FILES_MISSING')) {
                    define($plugin_prefix . '__SHARED_FILES_MISSING', false);
                }
            }
        } else {
            // Če že v prvo nič ne manjka, nastavimo na FALSE
            if (!defined($plugin_prefix . '__SHARED_FILES_MISSING')) {
                define($plugin_prefix . '__SHARED_FILES_MISSING', false);
            }
        }
        // --- 3. KONEC: EXECUTION LOGIC --- //
    }
}