<?php
    // if uninstall.php is not called by WordPress, die
    if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
        die();
    }

    if ( false != get_option( 'acfcs_delete_cities_table' ) ) {
        // drop table
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cities");
    }
