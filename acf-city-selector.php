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
				add_action( 'admin_menu',                   array( $this, 'admin_menu' ) );
				add_action( 'init',                         array( $this, 'truncate_db' ) );            // option to truncate table
				add_action( 'init',                         array( $this, 'db_actions' ) );             // option to truncate table

				add_filter( "plugin_action_links_$plugin",  array(
					$this,
					'acfcs_settings_link'
				) );    // adds settings link to plugin page

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
			 * Truncate cities table
			 */
			public function truncate_db() {
				if ( isset( $_POST["acf_nuke_nonce"] ) ) {
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

			/*
			 * Import actions
			 */
			public function db_actions() {
				if ( isset( $_POST["db_actions_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["db_actions_nonce"], 'db-actions-nonce' ) ) {
						return;
					} else {

						if ( isset( $_POST['delete_cities'] ) || isset( $_POST['import_nl'] ) || isset( $_POST['import_be'] ) || isset( $_POST['import_lux'] ) ) {
							global $wpdb;
							if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
								$wpdb->query( 'TRUNCATE ' . $wpdb->prefix . 'cities' );
							}
							if ( isset( $_POST['import_nl'] ) || isset( $_POST['import_be'] ) || isset( $_POST['import_lux'] ) ) {
								require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
								ob_start();
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
			public function admin_menu() {
				add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-options', array(
					$this,
					'acfcs_options'
				) );
			}

			/*
			 * Content for the settings page
			 */
			public function acfcs_options() {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
				}

				if ( isset( $_POST["db_actions_nonce"] ) ) {
					if ( ! wp_verify_nonce( $_POST["db_actions_nonce"], 'db-actions-nonce' ) ) {
						return;
					} else {
						if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
							echo '<div class="updated"><p><strong>' . __( 'Your cities table has been emptied.', 'acf-city-selector' ) . '</strong></p></div>';
						}
						if ( isset( $_POST['import_nl'] ) && 1 == $_POST["import_nl"] ) {
							echo '<div class="updated"><p><strong>' . __( 'You successfully imported all cities in The Netherlands.', 'acf-city-selector' ) . '</strong></p></div>';
						}
						if ( isset( $_POST['import_be'] ) && 1 == $_POST["import_be"] ) {
							echo '<div class="updated"><p><strong>' . __( 'You successfully imported all cities in Belgium.', 'acf-city-selector' ) . '</strong></p></div>';
						}
						if ( isset( $_POST['import_lux'] ) && 1 == $_POST["import_lux"] ) {
							echo '<div class="updated"><p><strong>' . __( 'You successfully imported all cities in Luxembourg.', 'acf-city-selector' ) . '</strong></p></div>';
						}
					}
				}
				?>

				<div class="wrap">
					<div id="icon-options-general" class="icon32"><br /></div>

					<h1><?php esc_html_e( 'ACF City Selector Settings', 'acf-city-selector' ); ?></h1>
					<p><?php sprintf( esc_html__( 'On this page you can find some helpful info about the %s plugin as well as some settings.', 'acf-city-selector' ), 'ACF City Selector' ); ?></p>

					<!-- left part -->
					<div class="admin_left">
						<form method="post" action="">

						<h2><?php esc_html_e( 'General info', 'acf-city-selector' ); ?></h2>
						<p><?php sprintf( esc_html__( 'This plugin requires %s to be activated to work.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/">Advanced Custom Fields</a>' ); ?></p>

						<hr />

						<input name="db_actions_nonce" value="<?php wp_create_nonce( 'db-actions-nonce' ); ?>" type="hidden" />

						<h3><?php esc_html_e( 'Clear the database', 'acf-city-selector' ); ?></h3>
						<p><?php esc_html_e( "By selecting this option, you will remove all cities, which are present in the database. This is handy if you don't need the preset cities or you want a fresh start.", 'acf-city-selector' ); ?></p>

						<p>
						<span class="acfcs_input"><input type="checkbox" name="delete_cities" id="delete_cities" value="1" /></span>
						<span class="acfcs_label"><?php __( 'Delete all cities from the database', 'acf-city-selector' ); ?></span>
						</p>

						<hr />

						<h3><?php esc_html_e( 'Import countries', 'acf-city-selector' ); ?></h3>
						<p><?php esc_html_e( "Here you can (re-)import individual countries (and of 'course its states/cities).", 'acf-city-selector' ); ?></p>

						<p>
						<span class="acfcs_input"><input type="checkbox" name="import_nl" id="import_nl" value="1" /></span>
						<span class="acfcs_label"><?php __( 'Import cities in Holland/The Netherlands', 'acf-city-selector' ); ?> (2449)</span>
						</p>

						<p>
						<span class="acfcs_input"><input type="checkbox" name="import_be" id="import_be" value="1" /></span>
						<span class="acfcs_label"><?php __( 'Import cities in Belgium', 'acf-city-selector' ); ?> (1166)</span>
						</p>

						<p>
						<span class="acfcs_input"><input type="checkbox" name="import_lux" id="import_lux" value="1" /></span>
						<span class="acfcs_label"><?php __( 'Import cities in Luxembourg', 'acf-city-selector' ); ?> (12)</span>
						</p>

						<?php submit_button(); ?>

					</div><!-- end .admin_left -->

					<div class="admin_right">

						<h3><?php esc_html_e( 'About the plugin', 'acf-city-selector' ); ?></h3>
						<p><?php sprintf( esc_html__( 'This plugin is an extension for %s. I built it because there was no properly working plugin which did this.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/" target="_blank">Advanced Custom Fields</a>' ); ?></p>
						<p><a href="http://www.berryplasman.com/wordpress/acf-city-selector/?utm_source=wpadmin&utm_medium=about_plugin&utm_campaign=acf-plugin" target="_blank">Click here</a> for a demo on my site.</p>

						<hr />

						<h3><?php esc_html_e( 'About Beee', 'acf-city-selector' ); ?></h3>
						<p><?php esc_html_e( 'If you need a Wordpress designer/coder to do work on your site, hit me up.', 'acf-city-selector' ); ?></p>

						<hr />

						<h3>Support</h3>
						<p><?php sprintf( esc_html__( 'If you need support for this plugin or if you have some good suggestions for improvements and/or new features, please turn to %s', 'acf-city-selector' ), '<a href="https://github.com/Beee4life/acf-city-selector/issues" target="_blank">GitHub</a>' ); ?>.</p>
						<hr />

						<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank"><img src="<?php echo plugins_url( 'assets/img/paypal_donate.gif', __FILE__ ); ?>" alt="" class="donateimg" /></a>
						<?php esc_html_e( 'If you like this plugin, buy me a coke to show your appreciation so I can continue to develop it.', 'acf-city-selector' ); ?></p>

					</div><!-- end .admin_right -->
<?php
			}

			/*
			 * Adds CSS on the admin side
			 */
			function ACFCS_admin_addCSS() {
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

