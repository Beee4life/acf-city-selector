<?php
    // if uninstall.php is not called by WordPress, die
    if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
        die();
    }

    if ( false == get_option( 'acfcs_preserve_settings' ) ) {

        // drop table
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cities");

        // remove folder
        // @TODO: also add filter here, if added
        $target_folder = wp_upload_dir()[ 'basedir' ] . '/acfcs';
        rmdir( $target_folder );
    }
