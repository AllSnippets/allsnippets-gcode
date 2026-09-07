<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__create_options_table__vsh0_0_3')) {
    function all_snippets__helper__create_options_table__vsh0_0_3() {
        global $wpdb;
        $table_name = $wpdb->prefix . ALL_SNIPPETS__DATABASE__TABLE_OPTIONS;
        $charset_collate = $wpdb->get_charset_collate();

        // Unikaten ključ zdaj vključuje plugin_slug in load_at za popolno varnost
        // plugin_slug je lahko NULL za globalne sistemske nastavitve (npr. maintenance lock)
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            plugin_slug VARCHAR(100) DEFAULT NULL,
            load_at VARCHAR(100) DEFAULT 'global' NOT NULL,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            option_name VARCHAR(191) NOT NULL,
            expiration BIGINT(20) UNSIGNED DEFAULT NULL,
            option_value LONGTEXT NOT NULL,

            PRIMARY KEY (id),
            UNIQUE KEY unique_option (plugin_slug, load_at, user_id, option_name),
            KEY plugin_slug (plugin_slug),
            KEY load_at (load_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        return true;
    }
}
