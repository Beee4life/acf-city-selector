<?php
    global $wpdb;
    // die($wpdb->prefix);

    $drop_cities = 'DROP TABLE ' . $wpdb->prefix . 'cities;';
    // die($drop_cities);
    echo $drop_cities;

/*
DROP TABLE IF EXISTS <?php echo $wpdb->prefix; ?>cities;
DROP TABLE IF EXISTS <?php echo $wpdb->prefix; ?>countries;
DROP TABLE IF EXISTS <?php echo $wpdb->prefix; ?>provences;
DROP TABLE IF EXISTS sd8_cities;
DROP TABLE IF EXISTS sd8_countries;
DROP TABLE IF EXISTS sd8_provences;
*/
?>
