<?php
	/*
	Plugin Name:    ACF City Selector
	Plugin URI:     http://acfcs.berryplasman.com
	Description:    An extension for ACF which allows you to select a city based on country and province/state.
	Version:        0.8
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
	if ( ! class_exists( 'ACF_City_Selector' ) ) :

		class ACF_City_Selector {

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
					'version' => '0.8',
					'url'     => plugin_dir_url( __FILE__ ),
					'path'    => plugin_dir_path( __FILE__ )
				);

				// set text domain
				load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

				register_activation_hook( __FILE__,    array( $this, 'acfcs_plugin_activation' ) );
				register_deactivation_hook( __FILE__,  array( $this, 'acfcs_plugin_deactivation' ) );

				// actions
				add_action( 'acf/include_field_types',      array( $this, 'acfcs_include_field_types' ) );    // v5
				add_action( 'acf/register_fields',          array( $this, 'acfcs_include_field_types' ) );    // v4
				add_action( 'admin_enqueue_scripts',        array( $this, 'acfcs_add_css' ) );
				add_action( 'admin_menu',                   array( $this, 'acfcs_add_admin_pages' ) );
				add_action( 'admin_init',                   array( $this, 'acfcs_errors' ) );
				// add_action( 'save_post',                    array( $this, 'acfcs_before_save' ), 10, 3 );

				// always load, move to $this->
				add_action( 'init',                         array( $this, 'acfcs_upload_csv_file' ) );
				add_action( 'init',                         array( $this, 'acfcs_do_something_with_file' ) );
				add_action( 'init',                         array( $this, 'acfcs_import_raw_data' ) );
				add_action( 'init',                         array( $this, 'acfcs_import_preset_countries' ) );
				add_action( 'init',                         array( $this, 'acfcs_preserve_settings' ) );
				add_action( 'init',                         array( $this, 'acfcs_truncate_table' ) );

				// filters
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),  array( $this, 'acfcs_settings_link' ) );

				// always load
				$this->acfcs_admin_menu();
				$this->acfcs_load_admin_pages();
				$this->acfcs_check_uploads_folder();
				$this->acfcs_check_table();

				include( 'inc/donate-box.php' );
				include( 'inc/help-tabs.php' );
				include( 'inc/country-field.php' );
				include( 'inc/verify-csv-data.php' );
			}


			/*
			 * Do stuff upon plugin activation
			 */
			public function acfcs_plugin_activation() {
				if ( false == get_option( 'acfcs_preserve_settings' ) ) {
					$this->acfcs_create_fill_db();
				}
			}


			/*
			 * Do stuff upon plugin activation
			 */
			public function acfcs_plugin_deactivation() {
			    // nothing yet
                // @TODO: delete any settings
			}


			/*
			 * Prepare database upon plugin activation
			 */
			public function acfcs_create_fill_db() {
				$this->acfcs_check_table();
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				ob_start();
				global $wpdb;
                require_once( 'lib/import_nl.php' );
                require_once( 'lib/import_be.php' );
                require_once( 'lib/import_lux.php' );
				$sql = ob_get_clean();
				dbDelta( $sql );
			}


			/*
			 * Check if table exists
			 */
			public function acfcs_check_table() {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				ob_start();
				global $wpdb;
				?>
				CREATE TABLE <?php echo $wpdb->prefix; ?>cities (
					id int(6) unsigned NOT NULL auto_increment,
					city_name varchar(50) NULL,
					state_code varchar(2) NULL,
					state_name varchar(50) NULL,
					country_code varchar(2) NULL,
					country varchar(50) NULL,
					PRIMARY KEY  (id)
				)
                COLLATE <?php echo $wpdb->collate; ?>;
				<?php
				$sql = ob_get_clean();
				dbDelta( $sql );

			}


			/**
             * Force update_post_meta in v4 because values are not saved (probably not needed anymore)
             *
			 * @param $post_id
			 * @param $post
			 * @param $update
			 */
			public function acfcs_before_save( $post_id, $post, $update ) {

				// bail early if no ACF data
				if ( ! isset( $_POST['acf'] ) ) {
					return;
				}

				// only run with v4
				if ( 5 > get_option( 'acf_version' ) ) {

					$field_name = '';
					$fields     = $_POST['acf'];
					$new_value  = '';
					if ( is_array( $fields ) && count( $fields ) > 0 ) {
						foreach( $fields as $key => $value ) {
							$field = get_field_object( $key );
							if ( isset( $field['type' ] ) && $field['type'] == 'acf_city_selector' ) {
								$field_name = $field['name'];
								$new_value  = $value;
								break;
							}
						}
					}

					// store data in $field_name
					update_post_meta( $post_id, $field_name, $new_value );
				}
			}


			/*
			 * Check if (upload) folder exists
			 */
			public function acfcs_check_uploads_folder() {

				$target_folder = wp_upload_dir()['basedir'] . '/acfcs/';
				if ( ! file_exists( $target_folder ) ) {
					mkdir( $target_folder, 0755 );
				}
			}


			/*
			 * Load admin pages
			 */
			public function acfcs_load_admin_pages() {
				include( 'inc/dashboard-page.php' );
				include( 'inc/settings-page.php' );
				include( 'inc/pro-page.php' );
			}


			/*
			 * Upload CSV file
			 */
			public function acfcs_upload_csv_file() {
				if ( isset( $_POST["upload_csv_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["upload_csv_nonce"], 'upload-csv-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

						return;
					} else {

						$this->acfcs_check_uploads_folder();
						$target_dir  = wp_upload_dir()['basedir'] . '/acfcs/';
						$target_file = $target_dir . basename( $_FILES['csv_upload']['name'] );

						if ( move_uploaded_file( $_FILES['csv_upload']['tmp_name'], $target_file ) ) {

							// file uploaded succeeded
							$this->acfcs_errors()->add( 'success_file_uploaded', sprintf( esc_html__( "File '%s' is successfully uploaded and now shows under 'Select files to import'", 'acf-city-selector' ), $_FILES['csv_upload']['name'] ) );

							return;

						} else {

							// file upload failed
							$this->acfcs_errors()->add( 'error_file_uploaded', esc_html__( 'Upload failed. Please try again.', 'acf-city-selector' ) );

							return;
						}
					}
				}
			}


			/**
			 * Read uploaded file for verification or import
			 * Delete file is also included in this function
			 */
			public function acfcs_do_something_with_file() {

				if ( isset( $_POST["select_file_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["select_file_nonce"], 'select-file-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong. Please try again.', 'acf-city-selector' ) );

						return;
					} else {

						if ( ! isset( $_POST['file_name'] ) ) {
							$this->acfcs_errors()->add( 'error_no_file_selected', esc_html__( "You didn't select a file.", 'acf-city-selector' ) );

							return;
						}

						$file_name = $_POST['file_name'];
						$import    = ! empty( $_POST['import'] ) ? $_POST['import'] : false;
						$remove    = ! empty( $_POST['remove'] ) ? $_POST['remove'] : false;
						$verify    = ! empty( $_POST['verify'] ) ? $_POST['verify'] : false;

						if ( ! empty( $verify ) ) {

							$read_data     = acfcs_read_file_only( $file_name[0] );
							$verified_data = acfcs_verify_csv_data( $read_data );

							if ( false != $verified_data ) {
								$this->acfcs_errors()->add( 'success_no_errors_in_csv', esc_html__( 'Congratulations, there appear to be no errors in your CSV.', 'acf-city-selector' ) );

								do_action( 'acfcs_after_success_verify' );

								return;
							}

						} elseif ( ! empty( $import ) ) {

							// import data
							$read_data     = acfcs_read_file_only( $file_name[0] );
							$verified_data = acfcs_verify_csv_data( $read_data );
							if ( false != $verified_data ) {
								$line_number = 0;
								foreach ( $verified_data as $line ) {
									$line_number ++;

									$city         = $line[0];
									$state_abbr   = $line[1];
									$state        = $line[2];
									$country_abbr = $line[3];
									$country      = $line[4];

									$city_row = array(
										'city_name'    => $city,
										'state_code'   => $state_abbr,
										'state_name'   => $state,
										'country_code' => $country_abbr,
										'country'      => $country,
									);

									global $wpdb;
									$wpdb->insert( $wpdb->prefix . 'cities', $city_row );

								}

								$this->acfcs_errors()->add( 'success_lines_imported', sprintf( esc_html__( 'Congratulations. You have successfully imported %d cities.', 'acf-city-selector' ), $line_number ) );

								do_action( 'acfcs_after_success_import' );

								return;
							}

						} elseif ( ! empty( $remove ) ) {

							if ( isset( $_POST['file_name'] ) ) {
								foreach ( $_POST['file_name'] as $file_name ) {
									// delete file
									unlink( wp_upload_dir()['basedir'] . '/acfcs/' . $file_name );
								}
								if ( count( $_POST['file_name'] ) == 1 ) {
									$this->acfcs_errors()->add( 'success_file_deleted', sprintf( esc_html__( 'File "%s" successfully deleted.', 'acf-city-selector' ), $file_name ) );

									return;
								} else {
									$this->acfcs_errors()->add( 'success_files_deleted', esc_html__( 'Files successfully deleted.', 'acf-city-selector' ) );

									return;
								}
							}
						}
					}
				}
			}


			/*
			 * Import raw csv data
			 */
			public function acfcs_import_raw_data() {
				if ( isset( $_POST["import_raw_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["import_raw_nonce"], 'import-raw-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

						return;
					} else {

						if ( isset( $_POST['verify'] ) ) {
							$verify_data = acfcs_verify_csv_data( $_POST['raw_csv_import'] );
							if ( false != $verify_data ) {
								$this->acfcs_errors()->add( 'success_csv_valid', esc_html__( 'Congratulations, your CSV data seems valid.', 'acf-city-selector' ) );
							}

						} elseif ( isset( $_POST['import'] ) ) {

							$verified_data = acfcs_verify_csv_data( $_POST['raw_csv_import'] );
							if ( false != $verified_data ) {
								// import data
								global $wpdb;
								$line_number = 0;
								foreach ( $verified_data as $line ) {
									$line_number ++;

									$city         = $line[0];
									$state_abbr   = $line[1];
									$state        = $line[2];
									$country_abbr = $line[3];
									$country      = $line[4];

									$city_row = array(
										'city_name'    => $city,
										'state_code'   => $state_abbr,
										'state_name'   => $state,
										'country_code' => $country_abbr,
										'country'      => $country,
									);

									global $wpdb;
									$wpdb->insert( $wpdb->prefix . 'cities', $city_row );

								}
								$this->acfcs_errors()->add( 'success_cities_imported', sprintf( _n( 'Congratulations, you imported %d city.', 'Congratulations, you imported %d cities.', $line_number, 'acf-city-selector' ), $line_number ) );

								do_action( 'acfcs_after_success_import_raw' );

								return;

							}
						}
					}
				}
			}


			/*
			 * Import preset countries
			 */
			public function acfcs_import_preset_countries() {
				if ( isset( $_POST["import_actions_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["import_actions_nonce"], 'import-actions-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

						return;
					} else {

						if ( isset( $_POST['import_nl'] ) || isset( $_POST['import_be'] ) || isset( $_POST['import_lux'] ) ) {
							require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
							ob_start();
							global $wpdb;
							if ( isset( $_POST['import_be'] ) && 1 == $_POST["import_be"] ) {
								require_once( 'lib/import_be.php' );
								do_action( 'acfcs_after_success_import_be' );
							}
							if ( isset( $_POST['import_lux'] ) && 1 == $_POST["import_lux"] ) {
								require_once( 'lib/import_lux.php' );
								do_action( 'acfcs_after_success_import_lu' );
							}
							if ( isset( $_POST['import_nl'] ) && 1 == $_POST["import_nl"] ) {
								require_once( 'lib/import_nl.php' );
								do_action( 'acfcs_after_success_import_nl' );
							}
							$sql = ob_get_clean();
							dbDelta( $sql );
						}
					}
				}
			}


			/*
			 * Truncate cities table
			 */
			public function acfcs_truncate_table() {
				if ( isset( $_POST["truncate_table_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["truncate_table_nonce"], 'truncate-table-nonce' ) ) {
						$this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

						return;
					} else {

						if ( isset( $_POST['delete_cities'] ) ) {
							if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {

								global $wpdb;
								$prefix = $wpdb->get_blog_prefix();
								$wpdb->query( 'TRUNCATE TABLE ' . $prefix . 'cities' );
								$this->acfcs_errors()->add( 'success_table_truncated', esc_html__( 'All cities are deleted.', 'acf-city-selector' ) );

								do_action( 'acfcs_after_success_nuke' );

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
						$this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

						return;
					} else {

						if ( isset( $_POST['preserve_settings'] ) ) {
							update_option( 'acfcs_preserve_settings', 1, true );
						} else {
							delete_option( 'acfcs_preserve_settings' );
						}
						$this->acfcs_errors()->add( 'success_settings_saved', esc_html__( 'Settings saved', 'acf-city-selector' ) );
					}
				}
			}


			/**
			 * Error function
			 *
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
				if ( $codes = ACF_City_Selector::acfcs_errors()->get_error_codes() ) {
					if ( is_wp_error( ACF_City_Selector::acfcs_errors() ) ) {

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
								$prefix     = esc_html__( 'Warning', 'action-logger' );
							} elseif ( strpos( $code, 'info' ) !== false ) {
								$span_class = 'notice-info ';
								$prefix     = false;
							} else {
								$error      = true;
								$span_class = 'notice-error ';
								$prefix     = esc_html__( 'Error', 'action-logger' );
							}
						}
						echo '<div class="error notice ' . $span_class . 'is-dismissible">';
						foreach ( $codes as $code ) {
							$message = ACF_City_Selector::acfcs_errors()->get_error_message( $code );
							echo '<div class="">';
							if ( true == $prefix ) {
								echo '<strong>' . $prefix . ':</strong> ';
							}
							echo $message;
							echo '</div>';
							echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice', 'action-logger' ) . '</span></button>';
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
			 * Admin menu
			 */
			public static function acfcs_admin_menu() {
				$gopro = ( defined( 'ENV' ) && ENV == 'development' ) ? ' | <a href="' . site_url() . '/wp-admin/options-general.php?page=acfcs-pro">' . esc_html__( 'Go Pro', 'acf-city-selector' ) . '</a>' : false;

				return '<p class="acfcs-admin-menu"><a href="' . site_url() . '/wp-admin/options-general.php?page=acfcs-options">' . esc_html__( 'Dashboard', 'acf-city-selector' ) . '</a> | <a href="' . site_url() . '/wp-admin/options-general.php?page=acfcs-settings">' . esc_html__( 'Settings', 'acf-city-selector' ) . '</a>' . $gopro . '</p>';
			}


			/*
			 * Adds admin pages
			 */
			public function acfcs_add_admin_pages() {
				add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-options', 'acfcs_options' );
				add_submenu_page( null, 'Settings', 'Settings', 'manage_options', 'acfcs-settings', 'acfcs_settings' );
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
		new ACF_City_Selector();


		// class_exists check
	endif;
