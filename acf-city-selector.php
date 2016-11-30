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
if ( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( ! class_exists('acf_plugin_city_selector') ) :

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
        $plugin = plugin_basename(__FILE__);

        include( 'inc/country-field.php' );

        add_action( 'acf/include_field_types',      array( $this, 'include_field_types' ) );    // v5
        add_action( 'acf/register_fields',          array( $this, 'include_field_types' ) );    // v4
        add_action( 'admin_menu',                   array( $this, 'admin_menu' ) );             // add settings page
        add_action( 'admin_enqueue_scripts',        array( $this, 'ACFCS_admin_addCSS' ) );     // add css in admin
        add_action( 'init',                         array( $this, 'truncate_db' ) );            // optionn to truncate table
        // add_action( 'init',                     array( $this, 'write_to_file' ) );

        add_filter( "plugin_action_links_$plugin",  array( $this, 'acfcs_settings_link' ) );    // adds settings link to plugin page

    }

        function create_fill_db() {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            ob_start();
            require_once('lib/prepare-tables.php');
            $sql = ob_get_clean();
            dbDelta( $sql );
        }

        function write_to_file() {
            $url = wp_nonce_url('options-general.php?page=acfcs-options', 'acf-city-selector');
            if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null) ) ) {
                return; // stop processing here
            }

            if ( ! WP_Filesystem( $creds ) ) {
                request_filesystem_credentials( $url, '', true, false, null );
                return;
            }

            global $wp_filesystem;
            $wp_filesystem->put_contents(
                '/example.txt',
                'Example contents of a file',
                FS_CHMOD_FILE // predefined mode settings for WP files
            );

        }

        function truncate_db() {
            if ( isset( $_POST["acf_nuke_nonce"] )) {
                if ( ! wp_verify_nonce( $_POST["acf_nuke_nonce"], 'acf-nuke-nonce' ) ) {
                    return;
                } else {
                    if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
                        global $wpdb;
                        $wpdb->query( 'TRUNCATE ' . $wpdb->prefix . 'cities' );
                    }
                }
            }
        }

        /**
         * Add settings link on plugin page
         * @author c.bavota (http://bavotasan.com/2009/a-settings-link-for-your-wordpress-plugins/)
         */

        function acfcs_settings_link( $links ) {
              $settings_link = '<a href="options-general.php?page=acfcs-options">Settings</a>';
              array_unshift( $links, $settings_link );
              return $links;
        }

        /**
         * Adds a page in the settings menu
         */
        function admin_menu() {
            add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-options', array( $this, 'acfcs_options' ) );
        }

        /**
         * Content for the settings page
         */
        function acfcs_options() {
            if ( ! current_user_can( 'manage_options' ) )  {
                wp_die( __('You do not have sufficient permissions to access this page.') );
            }

            if ( isset( $_POST["acf_nuke_nonce"] )) {
                if ( ! wp_verify_nonce( $_POST["acf_nuke_nonce"], 'acf-nuke-nonce' ) ) {
                    return;
                } else {
                    if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
                        echo '<div class="updated"><p><strong>' . __( 'Your cities table has been emptied.', 'acf-city-selector' ) . '</strong></p></div>';
                    }
                }
            }

            // Now display the settings editing screen
            echo '<div class="wrap">';
            echo '<div id="icon-options-general" class="icon32"><br /></div>';

            // header
            echo "<h1>" . __( 'ACF City Selector Settings', 'acf-city-selector' ) . "</h1>";
            echo "<p>" . sprintf( __( 'On this page you can find some helpful info about the %s plugin as well as some settings.', 'acf-city-selector' ), 'ACF City Selector' ) . "</p>";

            // left part
            echo '<div class="admin_left">';
                echo '<form method="post" action="">';

                echo '<h2>' . __( 'General info', 'acf-city-selector' ) . '</h2>';
                echo '<p>' . sprintf( __( 'This plugin requires %s to be activated to work.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/">Advanced Custom Fields</a>' ) . '</p>';

                echo '<hr />';

                echo '<h3>' . __( 'Clear the database', 'acf-city-selector' ) . '</h3>';
                echo '<p>' . __( "By selecting this option, you will remove all cities, which are present in the database. This is handy if you don't need the preset cities or you want a fresh start.", 'acf-city-selector' ) . '</p>';
                echo '<input name="acf_nuke_nonce" id="" value="' . wp_create_nonce( 'acf-nuke-nonce' ).'" type="hidden" />';

                echo '<p>';
                    echo '<span class="acfcs_label">' . __( 'Delete all cities from the database', 'acf-city-selector' ) . '</span>';
                    echo '<span class="acfcs_input"><input type="checkbox" name="delete_cities" id="delete_cities" value="1" /></span>';
                echo '</p>';

                submit_button();
            echo '</div><!-- end .admin_left -->';

            echo '<div class="admin_right">';

                echo '<h3>About the plugin</h3>';
                echo '<p>This plugin is an extension for <a href="https://www.advancedcustomfields.com/" target="_blank">Advanced Custom Fields</a>. I built it because there was no properly working plugin which did this.</p>';
                echo '<p><a href="http://www.berryplasman.com/wordpress/acf-city-selector/?utm_source=wpadmin&utm_medium=about_plugin&utm_campaign=acf-plugin" target="_blank">Click here</a> for a demo on my site.</p>';
                echo '<hr />';

                echo '<h3>About Beee</h3>';
                echo '<p>If you need a Wordpress designer/coder to do work on your site, hit me up.';
                echo 'Check my <a href="http://www.berryplasman.com/portfolio/?utm_source=wpadmin&utm_medium=about_beee&utm_campaign=tinynavplugin" target="_blank">portfolio</a>.</p>';
                echo '<hr />';

                echo '<h3>Support</h3>';
                echo '<p>If you need support for this plugin or if you have some good suggestions for improvements and/or new features, please turn to <a href="https://github.com/Beee4life/acf-city-selector/issues" target="_blank">GitHub</a>.</p>';
                echo '<hr />';

                echo '<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank"><img src="' . plugins_url('assets/img/paypal_donate.gif', __FILE__) . '" alt="" class="donateimg" /></a>';
                echo __( 'If you like this plugin, buy me a coke to show your appreciation so I can continue to develop it.', 'acf-city-selector' ) . '</p>';
            echo '</div><!-- end .admin_right -->';

        }

        /**
         * Adds CSS on the admin side
         */
        function ACFCS_admin_addCSS() {
            wp_enqueue_style( 'acf-city-selector', plugins_url('assets/css/acf-city-selector.css', __FILE__) );
        }

        /*
        *  include_field_types
        *
        *  This function will include the field type class
        *
        *  @type    function
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

if ( ! function_exists( 'donate_meta_box' ) ) {
        function donate_meta_box() {
            if ( apply_filters( 'remove_donate_nag', false ) ) {
                return;
            }

            $id             = 'donate-beee';
            $title          = '<a style="text-decoration: none; font-size: 1em;" href="https://github.com/beee4life" target="_blank">Beee says "Thank you"</a>';
            $callback       = 'show_donate_meta_box';
            $screens        = array();
            $context        = 'side';
            $priority       = 'low';
            add_meta_box( $id, $title, $callback, $screens, $context, $priority );

        } // end function donate_meta_box
        add_action( 'add_meta_boxes', 'donate_meta_box' );

        function show_donate_meta_box() {
            echo '<p style="margin-bottom: 0;">Thank you for installing the \'City Selector\' plugin. I hope you enjoy it. Please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank">consider a donation</a> if you do, so I can continue to improve it even more.</p>';
        }
    } // end if !function_exists
?>
