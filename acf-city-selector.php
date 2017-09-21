<?php
	/*
	Plugin Name:    ACF City Selector (not finished)
	Plugin URI:     http://berryplasman.com/wordpress/acf-city-selector
	Description:    An extension for ACF which allows you to select a city based on country and provence/state.
	Version:        0.1 (beta)
	Author:         Beee
	Author URI:     http://berryplasman.com
	Text Domain:    acf-city-selector
	License:        GPLv2 or later
	License URI:    https://www.gnu.org/licenses/gpl.html
	Contributors:   Fabrizio Sabato - http://deskema.it
	*/

	// exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	// check if class already exists
	if ( ! class_exists( 'acf_plugin_city_selector' ) ) :

		class acf_plugin_city_selector {

			/*
			 * __construct
			 *
			 * This function will setup the class functionality
			 *
			 * @param   n/a
			 * @return  n/a
			 */
			public function __construct() {

				$this->settings = array(
					'version' => '0.1',
					'url'     => plugin_dir_url( __FILE__ ),
					'path'    => plugin_dir_path( __FILE__ )
				);

				// set text domain
				// info: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
				load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

				$plugin = plugin_basename( __FILE__ );

				register_activation_hook( __FILE__,         array( $this, 'plugin_activation' ) );
				register_deactivation_hook( __FILE__,       array( $this, 'plugin_deactivation' ) );

				add_action( 'acf/include_field_types',      array( $this, 'include_field_types' ) );    // v5
				add_action( 'acf/register_fields',          array( $this, 'include_field_types' ) );    // v4 (not done)
				add_action( 'admin_enqueue_scripts',        array( $this, 'ACFCS_admin_addCSS' ) );     // add css in admin
				add_action( 'admin_menu',                   array( $this, 'add_admin_page' ) );
				add_action( 'admin_menu',                   array( $this, 'add_settings_page' ) );
				add_action( 'init',                         array( $this, 'truncate_db' ) );
				add_action( 'init',                         array( $this, 'import_actions' ) );
				add_action( 'init',                         array( $this, 'preserve_settings' ) );

				add_filter( "plugin_action_links_$plugin",  array(
					$this,
					'acfcs_settings_link'
				) );    // adds settings link to plugin page

                $this->load_admin_page();

				include( 'inc/country-field.php' );
			}


			/*
			 * Do stuff upon plugin activation
			 */
			public function plugin_activation() {
				$this->create_fill_db();
			}

			/*
			 * Do stuff upon plugin activation
			 */
			public function plugin_deactivation() {
			    // nothing yet
			}

			/*
			 * Prepare database upon plugin activation
			 */
			public function create_fill_db() {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				ob_start();
				require_once( 'lib/prepare-tables.php' );
				$sql = ob_get_clean();
				dbDelta( $sql );
			}

			/*
			 * Load admin page
			 */
			public function load_admin_page() {
				include( 'admin-page.php' );
			}

			/*
			 * Load admin page
			 */
			public function load_settings_page() {
				include( 'settings-page.php' );
			}

			/*
			 * Truncate cities table
			 */
			public function truncate_db() {
				if ( isset( $_POST["truncate_table_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["truncate_table_nonce"], 'truncate-table-nonce' ) ) {
						// @TODO: Throw error
						return;
					} else {

						if ( isset( $_POST['delete_cities'] ) ) {
							if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
								$this->truncate_db();
							}
						}
					}
				}
			}

			/*
			 * Preserve settings
			 */
			public function preserve_settings() {
				if ( isset( $_POST["preserve_settings_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["preserve_settings_nonce"], 'preserve-settings-nonce' ) ) {
					    echo '<pre>'; var_dump($_POST); echo '</pre>'; exit;
						// @TODO: Throw error
                        die('error');
						return;
					} else {

						if ( isset( $_POST['preserve_settings'] ) ) {
						    update_option( 'acfcs_preserve_settings', 1, true );
						} else {
							delete_option( 'acfcs_preserve_settings' );
                        }
					}
				}
			}


			/*
			 * Import actions
			 */
			public function import_actions() {
				if ( isset( $_POST["import_actions_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["import_actions_nonce"], 'import-action-nonce' ) ) {
						// @TODO: Throw error
						return;
					} else {

						if ( isset( $_POST['import_nl'] ) || isset( $_POST['import_be'] ) || isset( $_POST['import_lux'] ) ) {
							require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
							ob_start();
							global $wpdb;
							if ( isset( $_POST['import_nl'] ) && 1 == $_POST["import_nl"] ) {
								require_once( 'lib/import_nl.php' );
							}
							if ( isset( $_POST['import_be'] ) && 1 == $_POST["import_be"] ) {
								require_once( 'lib/import_be.php' );
							}
							if ( isset( $_POST['import_lux'] ) && 1 == $_POST["import_lux"] ) {
								require_once( 'lib/import_lux.php' );
							}
							$sql = ob_get_clean();
							dbDelta( $sql );
						}
					}
				}
			}

			/*
			 * include_field_types
			 *
			 * This function will include the field type class
			 *
			 * @type    function
			 * @param   $version (int) major ACF version. Defaults to false
			 * @return  n/a
			 */
			public function include_field_types( $version = false ) {

				// support empty $version
				if ( ! $version ) {
					$version = 4;
				}

				// include
				include_once( 'fields/acf-city_selector-v' . $version . '.php' );

			}

			/*
			 * Add settings link on plugin page
			 */
			public function acfcs_settings_link( $links ) {
				$settings_link = '<a href="options-general.php?page=acfcs-options">' . esc_html__( 'Settings', 'acf-city-selector' ) . '</a>';
				array_unshift( $links, $settings_link );

				return $links;
			}

			/*
			 * Adds a page in the settings menu
			 */
			public function add_admin_page() {
				add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-options', 'acfcs_options' );
			}

			/*
			 * Adds a page in the settings menu
			 */
			public function add_settings_page() {
				add_submenu_page( null, 'Settings', 'Settings', 'manage_options', 'acfcs-settings', 'acfcs_settings' );
			}

			/*
			 * Adds CSS on the admin side
			 */
			public function ACFCS_admin_addCSS() {
				wp_enqueue_style( 'acf-city-selector', plugins_url( 'assets/css/acf-city-selector.css', __FILE__ ) );
			}
		}

		// initialize
		new acf_plugin_city_selector();


		// class_exists check
	endif;

	if ( ! function_exists( 'donate_meta_box' ) ) {
		function donate_meta_box() {
			if ( apply_filters( 'remove_acf_cs_donate_nag', false ) ) {
				return;
			}

			$id       = 'donate-acf-cs';
			$title    = '<a style="text-decoration: none; font-size: 1em;" href="https://github.com/beee4life" target="_blank">Beee says "Thank you"</a>';
			$callback = 'show_donate_meta_box';
			$screens  = array();
			$context  = 'side';
			$priority = 'low';
			add_meta_box( $id, $title, $callback, $screens, $context, $priority );

		} // end function donate_meta_box
		add_action( 'add_meta_boxes', 'donate_meta_box' );

		function show_donate_meta_box() {
			echo '<p style="margin-bottom: 0;">Thank you for installing the \'City Selector\' plugin. I hope you enjoy it. Please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank">consider a donation</a> if you do, so I can continue to improve it even more.</p>';
		}
	} // end if !function_exists

