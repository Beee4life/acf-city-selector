<?php
    // if uninstall.php is not called by WordPress, die
    if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
        die;
    }

    // drop the database table
    if ( false == get_option( 'acfcs_preserve_settings' ) ) {
	    global $wpdb;
	    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cities");

	    $target_folder = wp_upload_dir()['basedir'] . '/acfcs';
	    rmdir( $target_folder );

	    delete_option( 'acfcs_preserve_settings' );
    }

    // drop all settings from db (if any)
?>
