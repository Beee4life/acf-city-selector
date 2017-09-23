<?php
	/*
	Plugin Name:    ACF City Selector (almost finished)
	Plugin URI:     http://berryplasman.com/wordpress/acf-city-selector
	Description:    An extension for ACF which allows you to select a city based on country and provence/state.
	Version:        0.1 (beta)
	Author:         Beee
	Author URI:     http://berryplasman.com
	Text Domain:    acf-city-selector
	License:        GPLv2 or later
	License URI:    https://www.gnu.org/licenses/gpl.html
	Contributors:   Jarah de Jong
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

				register_activation_hook( __FILE__,         array( $this, 'acfcs_plugin_activation' ) );
				register_deactivation_hook( __FILE__,       array( $this, 'acfcs_plugin_deactivation' ) );

				// actions
				add_action( 'acf/include_field_types',      array( $this, 'acfcs_include_field_types' ) );    // v5
				add_action( 'acf/register_fields',          array( $this, 'acfcs_include_field_types' ) );    // v4 (not done)
				add_action( 'admin_enqueue_scripts',        array( $this, 'acfcs_add_css' ) );
				add_action( 'admin_menu',                   array( $this, 'acfcs_add_admin_page' ) );
				add_action( 'admin_menu',                   array( $this, 'acfcs_add_settings_page' ) );
				add_action( 'admin_menu',                   array( $this, 'acfcs_add_pro_page' ) );
				add_action( 'admin_init',                   array( $this, 'acfcs_errors' ) );

				add_action( 'init',                         array( $this, 'acfcs_trucate_db' ) );
				add_action( 'init',                         array( $this, 'acfcs_import_preset_countries' ) );
				add_action( 'init',                         array( $this, 'acfcs_import_raw_data' ) );
				add_action( 'init',                         array( $this, 'acfcs_preserve_settings' ) );

				// filters
				add_filter( "plugin_action_links_$plugin",  array( $this, 'acfcs_settings_link' ) );

				// always load
				$this->acfcs_load_admin_pages();
				// $this->acfcs_load_admin_page();
				// $this->acfcs_load_settings_page();
				// $this->acfcs_load_pro_page();
				$this->acfcs_admin_menu();

				include( 'inc/help-tabs.php' );
				include( 'inc/country-field.php' );
				include( 'inc/verify-csv-data.php' );
			}


			/*
			 * Do stuff upon plugin activation
			 */
			public function acfcs_plugin_activation() {
				// $this->acfcs_create_fill_db();
			}

			/*
			 * Do stuff upon plugin activation
			 */
			public function acfcs_plugin_deactivation() {
			    // nothing yet
			}

			/*
			 * Prepare database upon plugin activation
			 */
			public function acfcs_create_fill_db() {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				ob_start();
				require_once( 'lib/prepare-tables.php' );
				$sql = ob_get_clean();
				dbDelta( $sql );
			}

			/*
			 * Load admin page
			 */
			public function acfcs_load_admin_pages() {
				include( 'inc/dashboard-page.php' );
				include( 'inc/settings-page.php' );
				include( 'inc/pro-page.php' );
			}

			/*
			 * Load admin page
			 */
			public function acfcs_load_admin_page() {
				include( 'inc/admin-page.php' );
			}

			/*
			 * Load settings page
			 */
			public function acfcs_load_settings_page() {
				include( 'inc/settings-page.php' );
			}

			/*
			 * Load pro page
			 */
			public function acfcs_load_pro_page() {
				include( 'inc/pro-page.php' );
			}

			/*
			 * Truncate cities table
			 */
			public function acfcs_trucate_db() {
				if ( isset( $_POST["truncate_table_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["truncate_table_nonce"], 'truncate-table-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', __( 'Something went wrong, please try again.', 'acf-city-selector' ) );
						return;
					} else {

						if ( isset( $_POST['delete_cities'] ) ) {
							if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
								$this->acfcs_trucate_db();
							}
						}
					}
				}
			}

			/*
			 * Preserve settings
			 */
			public function acfcs_preserve_settings() {
				if ( isset( $_POST["preserve_settings_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["preserve_settings_nonce"], 'preserve-settings-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', __( 'Something went wrong, please try again.', 'acf-city-selector' ) );

						return;
					} else {

						if ( isset( $_POST['preserve_settings'] ) ) {
							update_option( 'acfcs_preserve_settings', 1, true );
						} else {
							delete_option( 'acfcs_preserve_settings' );
						}
						$this->acfcs_errors()->add( 'success_settings_saved', __( 'Settings saved', 'acf-city-selector' ) );
					}
				}
			}


			/*
			 * Import actions
			 */
			public function acfcs_import_preset_countries() {
				if ( isset( $_POST["import_actions_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["import_actions_nonce"], 'import-action-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', __( 'Something went wrong, please try again.', 'acf-city-selector' ) );
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
			 * Import actions
			 */
			public function acfcs_import_raw_data() {
				if ( isset( $_POST["import_raw_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["import_raw_nonce"], 'import-raw-nonce' ) ) {
						// @TODO: Throw error
						$this->acfcs_errors()->add( 'error_no_nonce_match', __( 'Something went wrong, please try again.', 'acf-city-selector' ) );
						return;
					} else {

					    if ( isset( $_POST[ 'verify' ]) ) {
					        // verify data
						    $verify_data = verify_raw_csv_data( $_POST['raw_csv_import'] );
						    if ( false != $verify_data ) {
							    $this->acfcs_errors()->add( 'success_csv_valid', __( 'Congratulations, your csv data seems valid.', 'acf-city-selector' ) );
                            }

                        } elseif ( isset( $_POST[ 'import' ]) ) {
                            // verify data
						    $verify_data = verify_raw_csv_data( $_POST['raw_csv_import'] );
						    if ( false != $verify_data ) {
							    // import data
                                global $wpdb;
                                $count = 0;
                                foreach( $verify_data as $line ) {
	                                $wpdb->insert(
                                        $wpdb->prefix . 'cities',
                                        array(
	                                        'city_name_ascii' => $line[0],
	                                        'state_code'      => $line[1],
	                                        'states'          => $line[2],
	                                        'country_code'    => $line[3],
	                                        'country'         => $line[4],
                                        ),
                                        array(
	                                        '%s',
	                                        '%s',
	                                        '%s',
	                                        '%s',
	                                        '%s',
                                        )
                                    );
	                                $count++;
                                }
							    $this->acfcs_errors()->add( 'success_cities_imported', sprintf( _n( 'Congratulations, you imported %d city.', 'Congratulations, you imported %d cities.', $count, 'acf-city-selector' ), $count ) );
						    }
					    }
					}
				}
			}

			/**
			 * @return WP_Error
			 */
			public static function acfcs_errors() {
				static $wp_error; // Will hold global variable safely
				return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
			}

			/**
			 * Displays error messages from form submissions
			 */
			public static function acfcs_show_admin_notices() {
				if ( $codes = acf_plugin_city_selector::acfcs_errors()->get_error_codes() ) {
					if ( is_wp_error( acf_plugin_city_selector::acfcs_errors() ) ) {

						// Loop error codes and display errors
						$error      = false;
						$span_class = false;
						$prefix     = false;
						foreach ( $codes as $code ) {
							if ( strpos( $code, 'success' ) !== false ) {
								$span_class = 'notice-success ';
								$prefix     = false;
							} elseif ( strpos( $code, 'error' ) !== false ) {
								$span_class = 'notice-error ';
								$prefix     = esc_html( __( 'Warning', 'action-logger' ) );
							} elseif ( strpos( $code, 'info' ) !== false ) {
								$span_class = 'notice-info ';
								$prefix     = false;
							} else {
								$error      = true;
								$span_class = 'notice-error ';
								$prefix     = esc_html( __( 'Error', 'action-logger' ) );
							}
						}
						echo '<div class="notice ' . $span_class . 'is-dismissible">';
						foreach( $codes as $code ) {
							$message = acf_plugin_city_selector::acfcs_errors()->get_error_message( $code );
							echo '<div class="">';
							if ( true == $prefix ) {
								echo '<strong>' . $prefix . ':</strong> ';
							}
							echo $message;
							echo '</div>';
							echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html( __( 'Dismiss this notice', 'action-logger' ) ) . '</span></button>';
						}
						echo '</div>';
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
			public function acfcs_include_field_types( $version = false ) {

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
			public static function acfcs_admin_menu() {
				return '<p class="acfcs-admin-menu"><a href="' . site_url() . '/wp-admin/options-general.php?page=acfcs-options">Dashboard</a> | <a href="' . site_url() . '/wp-admin/options-general.php?page=acfcs-settings">Settings</a> | <a href="' . site_url() . '/wp-admin/options-general.php?page=acfcs-pro">Go Pro</a></p>';

			}

			/*
			 * Adds a page in the settings menu
			 */
			public function acfcs_add_admin_page() {
				add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-options', 'acfcs_options' );
			}

			/*
			 * Adds a (hidden) settings page
			 */
			public function acfcs_add_settings_page() {
				add_submenu_page( null, 'Settings', 'Settings', 'manage_options', 'acfcs-settings', 'acfcs_settings' );
			}

			/*
			 * Adds a (hidden) pro page
			 */
			public function acfcs_add_pro_page() {
				add_submenu_page( null, 'Pro', 'Pro', 'manage_options', 'acfcs-pro', 'acfcs_pro' );
			}

			/*
			 * Adds CSS on the admin side
			 */
			public function acfcs_add_css() {
				wp_enqueue_style( 'acf-city-selector', plugins_url( 'assets/css/acf-city-selector.css', __FILE__ ) );
			}
		}

		// initialize
		new acf_plugin_city_selector();


		// class_exists check
	endif;

	if ( ! function_exists( 'acfcs_donate_meta_box' ) ) {
		function acfcs_donate_meta_box() {
			if ( apply_filters( 'remove_acfcs_donate_nag', false ) ) {
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
		add_action( 'add_meta_boxes', 'acfcs_donate_meta_box' );

		function show_donate_meta_box() {
			echo '<p style="margin-bottom: 0;">' . sprintf( __( 'Thank you for installing the \'City Selector\' plugin. I hope you enjoy it. Please <a href="%s" target="_blank">consider a donation</a> if you do, so I can continue to improve it even more.', 'acf-city-selector' ), esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL' ) ) . '</p>';
		}
	} // end if !function_exists

