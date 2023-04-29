<?php
    /*
    Plugin Name:    ACF City Selector
    Plugin URI:     https://acf-city-selector.com
    Description:    An extension for ACF which allows you to select a city based on country and province/state.
    Version:        1.11.0
    Tested up to:   6.1.1
    Requires PHP:   7.0
    Author:         Beee
    Author URI:     https://berryplasman.com
    Text Domain:    acf-city-selector
    Domain Path:    /languages
    License:        GPLv2 or later
    License URI:    https://www.gnu.org/licenses/gpl.html
    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    if ( ! class_exists( 'ACF_City_Selector' ) ) {

        /*
         * Main class
         */
        class ACF_City_Selector {

            /*
             * __construct
             *
             * This function will set up the class functionality
             */
            public function __construct() {

                $this->settings = array(
                    'db_version' => '1.0',
                    'url'        => plugin_dir_url( __FILE__ ),
                    'version'    => '1.11.0',
                );

                if ( ! class_exists( 'ACFCS_WEBSITE_URL' ) ) {
                    define( 'ACFCS_WEBSITE_URL', 'https://acf-city-selector.com' );
                }

                if ( ! defined( 'ACFCS_PLUGIN_PATH' ) ) {
                    $plugin_path = plugin_dir_path( __FILE__ );
                    define( 'ACFCS_PLUGIN_PATH', $plugin_path );
                }

                register_activation_hook( __FILE__,             array( $this, 'acfcs_plugin_activation' ) );
                register_deactivation_hook( __FILE__,           array( $this, 'acfcs_plugin_deactivation' ) );

                add_action( 'acf/register_fields',                  array( $this, 'acfcs_include_field_types' ) ); // v4
                add_action( 'acf/include_field_types',              array( $this, 'acfcs_include_field_types' ) ); // v5

                add_action( 'admin_enqueue_scripts',                array( $this, 'acfcs_add_scripts' ) );
                add_action( 'wp_enqueue_scripts',                   array( $this, 'acfcs_add_scripts' ) );

                add_action( 'admin_menu',                           array( $this, 'acfcs_add_admin_pages' ) );
                add_action( 'admin_init',                           array( $this, 'acfcs_admin_menu' ) );
                add_action( 'admin_init',                           array( $this, 'acfcs_errors' ) );
                add_action( 'admin_init',                           array( $this, 'acfcs_check_table' ) );
                add_action( 'admin_notices',                        array( $this, 'acfcs_check_cities' ) );
                add_action( 'init',                           		array( $this, 'acfcs_load_textdomain' ) );
                add_action( 'plugins_loaded',                       array( $this, 'acfcs_change_plugin_order' ), 5 );
                add_action( 'plugins_loaded',                       array( $this, 'acfcs_check_for_acf' ), 6 );
                add_action( 'plugins_loaded',                       array( $this, 'acfcs_check_acf_version' ) );

                add_action( 'acf/input/admin_l10n',                 array( $this, 'acfcs_error_messages' ) );

                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'acfcs_settings_link' ) );

                // functions & hooks
                include 'inc/acfcs-actions.php';
                include 'inc/acfcs-functions.php';
                include 'inc/acfcs-help-tabs.php';
                include 'inc/acfcs-i18n.php';
                include 'inc/acfcs-ajax.php';
                include 'inc/form-handling.php';

                $this->l10n = acfcs_get_js_translations();

                // admin pages
                include 'admin/acfcs-dashboard.php';
                include 'admin/acfcs-preview.php';
                include 'admin/acfcs-settings.php';
                include 'admin/acfcs-search.php';
                include 'admin/acfcs-info.php';
                include 'admin/acfcs-countries.php';

            }


            /*
             * Do stuff upon plugin activation
             */
            public function acfcs_plugin_activation() {
                $this->acfcs_check_table();
                $this->acfcs_check_uploads_folder();
            }


            /*
             * Do stuff upon plugin activation
             */
            public function acfcs_plugin_deactivation() {
                delete_option( 'acfcs_db_version' );
                // this hook is here because I didn't want to create a new hook for an existing action
                do_action( 'acfcs_delete_transients' );
                // other important stuff gets done in uninstall.php
            }


            /*
             * Check if table exists
             */
            public function acfcs_check_table() {
                $acfcs_db_version = get_option( 'acfcs_db_version', false );
                if ( false == $acfcs_db_version || $acfcs_db_version != $this->settings[ 'db_version' ] ) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                    ob_start();
                    global $wpdb;
                    ?>
                    CREATE TABLE <?php echo $wpdb->prefix; ?>cities (
                    id int(6) unsigned NOT NULL auto_increment,
                    city_name varchar(50) NULL,
                    state_code varchar(3) NULL,
                    state_name varchar(50) NULL,
                    country_code varchar(2) NULL,
                    country varchar(50) NULL,
                    PRIMARY KEY  (id)
                    )
                    COLLATE <?php echo $wpdb->collate; ?>;
                    <?php
                    $sql = ob_get_clean();
                    dbDelta( $sql );
                    update_option( 'acfcs_db_version', $this->settings[ 'db_version' ] );
                }
            }


            /*
             * Check if (upload) folder exists
             * If not, create it.
             */
            public static function acfcs_check_uploads_folder() {
                $target_folder = acfcs_upload_folder( '/' );
                if ( ! file_exists( $target_folder ) ) {
                    mkdir( $target_folder, 0755 );
                }
            }


			/**
			 * Check if cities need to be re-imported
			 *
			 * @return void
			 */
            public function acfcs_check_cities() {
				if ( '1.7.0' < $this->settings[ 'version' ] && false == get_option( 'acfcs_city_update_1_8_0' ) ) {
					$countries = [ 'nl', 'be' ];
					foreach( $countries as $country_code ) {
						if ( true === acfcs_has_cities( $country_code ) ) {
							$reimport[] = $country_code;
						}
					}
					if ( isset( $reimport ) ) {
						$country_name = 1 === count( $reimport ) ? acfcs_get_country_name( $reimport[ 0 ] ) : false;
						$notice       = sprintf( __( 'Several %s had broken ascii characters. You need to re-import these countries to get the correct city names.', 'acf-city-selector' ), _n( sprintf( __( 'cities in %s', 'acf-city-selector' ), $country_name ), __( 'cities in Belgium and Netherlands', 'acf-city-selector' ), count( $reimport ), 'acf-city-selector' ) );
						echo sprintf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $notice );
					} else {
						update_option( 'acfcs_city_update_1_8_0', 'done' );
					}
				}
            }


            /**
             * Add our error messages to acf filter
             *
             * @param $messages
             *
             * @return mixed
             */
            public function acfcs_error_messages( $messages ) {
                if ( isset( $messages[ 'validation' ] ) ) {
                    $messages[ 'validation' ] = array_merge( $messages[ 'validation' ], $this->l10n );
                } else {
                    $messages[ 'validation' ] = $this->l10n;
                }

                return $messages;
            }


            /*
             * Error function
             *
             * @return WP_Error
             */
            public static function acfcs_errors() {
                static $wp_error;

                return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
            }


            /*
             * Displays error messages from form submissions
             */
            public static function acfcs_show_admin_notices() {
                if ( $codes = ACF_City_Selector::acfcs_errors()->get_error_codes() ) {
                    if ( is_wp_error( ACF_City_Selector::acfcs_errors() ) ) {
                        $span_class = false;
                        foreach ( $codes as $code ) {
                            if ( strpos( $code, 'success' ) !== false ) {
                                $span_class = 'notice--success ';
                            } elseif ( strpos( $code, 'error' ) !== false ) {
                                $span_class = 'error ';
                            } elseif ( strpos( $code, 'warning' ) !== false ) {
                                $span_class = 'notice--warning ';
                            } elseif ( strpos( $code, 'info' ) !== false ) {
                                $span_class = 'notice--info ';
                            } else {
                                $span_class = 'notice--error ';
                            }
                        }
                        echo sprintf( '<div id="message" class="notice %s is-dismissible">', $span_class );
                        foreach ( $codes as $code ) {
                            echo sprintf( '<p>%s</p>', ACF_City_Selector::acfcs_errors()->get_error_message( $code ) );
                        }
                        echo '</div>';
                    }
                }
            }


            /**
             * include_field_types
             *
             * This function will include the field type class
             *
             * @param bool $version (int) major ACF version. Defaults to false
             */
            public function acfcs_include_field_types( $version = false ) {
                if ( ! $version ) {
                    $version = 4;
                }
                include_once 'admin/acf-city-selector-v' . $version . '.php';
            }


            /*
             * Add settings link on plugin page
             *
             * @param $links
             *
             * @return array
             */
            public function acfcs_settings_link( $links ) {
                $settings_link = [ 'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=acfcs-dashboard' ), esc_html__( 'Settings', 'acf-city-selector' ) ) ];

                return array_merge( $settings_link, $links );
            }


            /*
             * Admin menu
             */
            public static function acfcs_admin_menu() {
                $admin_url      = admin_url( 'options-general.php?page=' );
                $current_class  = ' class="current_page"';
                $url_array      = parse_url( esc_url_raw( $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ] ) );
                $acfcs_subpage = ( isset( $url_array[ 'query' ] ) ) ? substr( $url_array[ 'query' ], 11 ) : false;

                $pages = [
                    'dashboard' => esc_html__( 'Dashboard', 'acf-city-selector' ),
                    'settings'  => esc_html__( 'Settings', 'acf-city-selector' ),
                ];
                if ( true === acfcs_has_cities() ) {
                    $pages[ 'search' ] = esc_html__( 'Search', 'acf-city-selector' );
                }
                if ( ! empty ( acfcs_check_if_files() ) ) {
                    $pages[ 'preview' ] = esc_html__( 'Preview', 'acf-city-selector' );
                }
                if ( current_user_can( apply_filters( 'acfcs_user_cap', 'manage_options' ) ) ) {
                    $pages[ 'info' ] = esc_html__( 'Info', 'acf-city-selector' );
                }

                $pages[ 'countries' ] = esc_html__( 'Get more countries', 'acf-city-selector' );

                ob_start();
                foreach( $pages as $slug => $label ) {
                    $current_page = ( $acfcs_subpage == $slug ) ? $current_class : false;
                    $current_page = ( 'countries' == $slug ) ? ' class="cta"' : $current_page;
                    echo ( 'dashboard' != $slug ) ? ' | ' : false;
                    echo '<a href="' . $admin_url . 'acfcs-' . $slug . '"' . $current_page . '>' . $label . '</a>';
                }
                $menu_items = ob_get_clean();
                $menu       = sprintf( '<p class="acfcs-admin-menu">%s</p>', $menu_items );

                return $menu;
            }


            /*
             * Check if ACF is active and if not add an admin notice
             */
            public function acfcs_check_for_acf() {
                if ( ! class_exists( 'acf' ) ) {
                    add_action( 'admin_notices', function () {
                        $message = sprintf( __( '"%s" is not activated. This plugin <strong>must</strong> be activated, because without it "%s" won\'t work. Activate it <a href="%s">here</a>.', 'acf-city-selector' ),
                            'Advanced Custom Fields',
                            'ACF City Selector',
                            esc_url( admin_url( 'plugins.php?s=acf&plugin_status=inactive' ) ) );
                        echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
                    });
                }
            }


            /*
             * Add admin notice when ACF version < 5
             */
            public function acfcs_check_acf_version() {
                if ( ! function_exists( 'get_plugins' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                $plugins = get_plugins();

                if ( isset( $plugins[ 'advanced-custom-fields-pro/acf.php' ] ) ) {
                    if ( $plugins[ 'advanced-custom-fields-pro/acf.php' ][ 'Version' ] < 5 && is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
                        add_action( 'admin_notices', function () {
                            $message = sprintf( __( '%s: The "%s" plugin will probably not work properly (anymore) with %s v4.x. Please upgrade to PRO.', 'acf-city-selector' ),
                                sprintf( '<b>%s</b>', __( 'Warning', 'acf-city-selector' ) ),
                                'City Selector',
                                'Advanced Custom Fields'
                            );
                            echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
                        } );
                    }
                }
            }


            public function acfcs_load_textdomain() {
				load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
            }


            /*
             * Change plugin order so ACFCS loads after ACF
             */
            public function acfcs_change_plugin_order() {
                $active_plugins = get_option( 'active_plugins' );
                $acfcs_key      = array_search( 'acf-city-selector/ACF_City_Selector.php', $active_plugins );
                $acf_key        = array_search( 'advanced-custom-fields-pro/acf.php', $active_plugins );
                if ( false !== $acf_key && false !== $acfcs_key ) {
                    if ( $acfcs_key < $acf_key ) {
                        $this->acfcs_move_array_element( $active_plugins, $acfcs_key, $acf_key );
                        update_option( 'active_plugins', $active_plugins, true );
                    }
                }
            }


            /**
             * Move array element to specific position
             *
             * @param $array
             * @param $from_index
             * @param $to_index
             */
            public function acfcs_move_array_element( &$array, $from_index, $to_index ) {
                $splice = array_splice( $array, $from_index, 1 );
                array_splice( $array, $to_index, 0, $splice );
            }


            /*
             * Add admin pages
             */
            public function acfcs_add_admin_pages() {
                add_options_page( 'ACF City Selector', 'City Selector', apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-dashboard', 'acfcs_dashboard' );
                add_submenu_page( null, __( 'Preview data', 'acf-city-selector' ), __( 'Preview data', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-preview', 'acfcs_preview_page' );
                add_submenu_page( null, __( 'Settings', 'acf-city-selector' ), __( 'Settings', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-settings', 'acfcs_settings' );
                add_submenu_page( null, __( 'Get countries', 'acf-city-selector' ), __( 'Get countries', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-countries', 'acfcs_country_page' );
                add_submenu_page( null, __( 'Search', 'acf-city-selector' ), __( 'Search', 'acf-city-selector' ),  apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-search', 'acfcs_search' );
                add_submenu_page( null, __( 'Info', 'acf-city-selector' ), __( 'Info', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-info', 'acfcs_info_page' );
            }


            /*
             * Adds CSS on the admin side
             */
            public function acfcs_add_scripts() {
                wp_enqueue_style( 'acfcs-general', plugins_url( 'assets/css/general.css', __FILE__ ), [], $this->settings[ 'version' ] );
                if ( is_admin() ) {
                    wp_enqueue_style( 'acfcs-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), [], $this->settings[ 'version' ] );
                    wp_register_script( 'acfcs-upload', plugins_url( 'assets/js/upload-csv.js', __FILE__ ), [ 'jquery' ], $this->settings[ 'version' ] );
                    wp_enqueue_script( 'acfcs-upload' );
                }
            }
        }

        new ACF_City_Selector();

    }
