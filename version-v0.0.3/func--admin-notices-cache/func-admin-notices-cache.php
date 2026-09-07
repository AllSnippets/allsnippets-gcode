<?php defined('WPINC') || die;
add_action('admin_notices', 'all_snippets__hook__cache_admin_notices__vsh0_0_3');
if (!function_exists('all_snippets__hook__cache_admin_notices__vsh0_0_3')) {
    // Ta funkcija se izvede na 'admin_notices' hooku
    function all_snippets__hook__cache_admin_notices__vsh0_0_3() {
        // --- 1. PERMISSION & CACHE CHECK --- //
        // Če uporabnik nima pravic, ne kažemo ničesar
        if (!current_user_can('manage_options')) return;

        // Uporabimo globalni cache statusov
        global $all_snippets_status_cache;
        
        // Če cache še ni napolnjen, ga poskusimo napolniti zdaj
        if (empty($all_snippets_status_cache) || !is_array($all_snippets_status_cache)) {
            if (function_exists('all_snippets__helper__cache_results__vsh0_0_3')) {
                all_snippets__helper__cache_results__vsh0_0_3();
            }
        }

        // Ponovno preverimo
        if (empty($all_snippets_status_cache) || !is_array($all_snippets_status_cache)) {
            return;
        }
        // --- 1. KONEC: PERMISSION & CACHE CHECK --- //


        
        // --- 2. PREPARE NOTICES --- //
        $issues_found = array();
        $trial_active = array();

        foreach ($all_snippets_status_cache as $plugin_slug => $status) {
            // Iz slug-a naredimo lepo ime (npr. all-broken-media -> All Broken Media)
            $display_name = ucwords(str_replace(['-', '_'], ' ', $plugin_slug));

            if ($status === 'nima' || $status === 'expired') {
                $issues_found[] = $display_name;
            } elseif ($status === 'proba') {
                $trial_active[] = $display_name;
            }
        }
        // --- 2. KONEC: PREPARE NOTICES --- //



        // --- 3. DISPLAY NOTICES --- //
        // Prikaz obvestil (uporabljamo nevtralne izraze)

        // 1. Kritična obvestila (manjkajoč cache/licenca)
        if (!empty($issues_found)) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>System Alert:</strong> The following components require attention (License Data Missing):</p>
                <ul style="list-style-type: disc; margin-left: 20px; margin-bottom: 10px;">
                    <?php foreach ($issues_found as $name): ?>
                        <li><strong><?php echo esc_html($name); ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }

        // 2. Obvestila o poskusnem obdobju (nevtralno)
        if (!empty($trial_active)) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>Maintenance Notice:</strong> The following components are running in Trial Mode:</p>
                <ul style="list-style-type: disc; margin-left: 20px; margin-bottom: 10px;">
                    <?php foreach ($trial_active as $name): ?>
                        <li><?php echo esc_html($name); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }
        // --- 3. KONEC: DISPLAY NOTICES --- //
    }
}