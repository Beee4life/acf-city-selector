<?php
    // if uninstall.php is not called by WordPress, die
    if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
        die;
    }

    // drop the database table
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cities");

	$target_folder = wp_upload_dir()['basedir'] . '/acfcs';
    rmdir( $target_folder );

    // drop all settings from db (if any)
?>
