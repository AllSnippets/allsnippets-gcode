<?php defined('WPINC') || die;
require_once dirname(__DIR__) . '/constants/database-table-names.php';

if (!function_exists('all_snippets__helper__create_error_log_table__vsh0_0_2')) {
    function all_snippets__helper__create_error_log_table__vsh0_0_2() {
        global $wpdb;
        $table_name = $wpdb->prefix . ALL_SNIPPETS__DATABASE__TABLE_ERROR_LOG;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source VARCHAR(100) DEFAULT NULL,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            guest_id VARCHAR(50) DEFAULT NULL,
            error_message TEXT DEFAULT NULL,
            error_data LONGTEXT DEFAULT NULL,
            url VARCHAR(255) DEFAULT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY source (source),
            KEY user_id (user_id),
            KEY guest_id (guest_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
