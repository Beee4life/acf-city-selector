<?php
    // if uninstall.php is not called by WordPress, die
    if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
        die();
    }

    if ( false == get_option( 'acfcs_preserve_settings' ) ) {
        global $wpdb;
        // drop table
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cities");

        $target_folder = wp_upload_dir()[ 'basedir' ] . '/acfcs';
        // remove folder
        rmdir( $target_folder );

        delete_transient( 'acfcs_countries' );
    }
