<?php
    // if uninstall.php is not called by WordPress, die
    if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
        die();
    }

    if ( false != get_option( 'acfcs_delete_cities_table' ) ) {
        global $wpdb;
        $wpdb->query($wpdb->prepare( "DROP TABLE IF EXISTS %i", $wpdb->prefix . 'cities' ) );
    }
