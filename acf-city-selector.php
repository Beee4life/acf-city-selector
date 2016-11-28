<?php

/*
Plugin Name: ACF City Selector (not finished)
Plugin URI: http://berryplasman.com/wordpress/acf-city-selector
Description: An extension for ACF which allows you to select a city based on country and provence/state.
Version: 0.1 (beta)
Author: Beee
Author URI: http://berryplasman.com
Text Domain: acf-city-selector
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl.html
Contributors: Fabrizio Sabato - http://deskema.it
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_plugin_city_selector') ) :

class acf_plugin_city_selector {

    /*
    *  __construct
    *
    *  This function will setup the class functionality
    *
    *  @type    function
    *  @date    17/02/2016
    *  @since   1.0.0
    *
    *  @param   n/a
    *  @return  n/a
    */

    public function __construct() {

        // vars
        $this->settings = array(
            'version'   => '0.1',
            'url'       => plugin_dir_url( __FILE__ ),
            'path'      => plugin_dir_path( __FILE__ )
        );

        // set text domain
        // https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
        load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

        register_activation_hook( __FILE__,     array( $this, 'create_fill_db' ) );

        include( 'inc/country-field.php' );

        // include field
        add_action( 'acf/include_field_types',  array( $this, 'include_field_types' ) ); // v5
        add_action( 'acf/register_fields',      array( $this, 'include_field_types' ) ); // v4

    }

    function create_fill_db() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        ob_start();
        require_once('lib/prepare-tables.php');
        $sql = ob_get_clean();
        dbDelta( $sql );
    }


    /*
    *  include_field_types
    *
    *  This function will include the field type class
    *
    *  @type    function
    *  @date    17/02/2016
    *  @since   1.0.0
    *
    *  @param   $version (int) major ACF version. Defaults to false
    *  @return  n/a
    */

    function include_field_types( $version = false ) {

        // support empty $version
        if( !$version ) $version = 4;

        // include
        include_once('fields/acf-city_selector-v' . $version . '.php');

    }

}

// initialize
new acf_plugin_city_selector();


// class_exists check
endif;

?>
