<?php
    /*
    Plugin Name:    ACF City Selector
    Plugin URI:     https://acf-city-selector.com
    Description:    An extension for ACF which allows you to select a city based on country and province/state.
    Version:        1.19.0
    Tested up to:   7.0
    Requires PHP:   7.4
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

        class ACF_City_Selector {
            private array $settings = array();
            private array $l10n = array();

            public function __construct() {

                $this->settings = [
                    'db_version' => '1.0',
                    'url'        => plugin_dir_url( __FILE__ ),
                    'version'    => '1.19.0',
                ];

                if ( ! class_exists( 'ACFCS_WEBSITE_URL' ) ) {
                    define( 'ACFCS_WEBSITE_URL', 'https://acf-city-selector.com' );
                }

                if ( ! defined( 'ACFCS_PLUGIN_PATH' ) ) {
                    $plugin_path = plugin_dir_path( __FILE__ );
                    define( 'ACFCS_PLUGIN_PATH', $plugin_path );
                }

                register_activation_hook( __FILE__,     [ $this, 'acfcs_plugin_activation' ] );
                register_deactivation_hook( __FILE__,   [ $this, 'acfcs_plugin_deactivation' ] );

                add_action( 'acf/register_fields',      [ $this, 'acfcs_include_field_types' ] ); // v4
                add_action( 'acf/include_field_types',  [ $this, 'acfcs_include_field_types' ] ); // v5

                add_action( 'admin_enqueue_scripts',    [ $this, 'acfcs_add_scripts_admin' ] );
                add_action( 'admin_menu',               [ $this, 'acfcs_add_admin_pages' ] );
                add_action( 'admin_init',               [ $this, 'acfcs_errors' ] );
                add_action( 'admin_init',               [ $this, 'acfcs_check_version' ] );
                add_action( 'admin_init',               [ $this, 'acfcs_check_table' ] );
                add_action( 'admin_notices',            [ $this, 'acfcs_check_cities' ] );
                add_action( 'plugins_loaded',           [ $this, 'acfcs_change_plugin_order' ], 5 );
                add_action( 'plugins_loaded',           [ $this, 'acfcs_check_for_acf' ], 6 );
                add_action( 'plugins_loaded',           [ $this, 'acfcs_check_acf_version' ] );

                add_action( 'acf/input/admin_l10n',     [ $this, 'acfcs_error_messages' ] );

                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'acfcs_settings_link' ] );
                add_filter( 'plugin_row_meta',          [ $this, 'acfcs_set_plugin_meta' ], 10, 2 );

                // functions & hooks
                include 'inc/acfcs-actions.php';
                include 'inc/acfcs-functions.php';
                include 'inc/acfcs-help-tabs.php';
                include 'inc/acfcs-i18n.php';
                include 'inc/acfcs-ajax.php';
                include 'inc/form-handling.php';

                // admin pages
                include 'admin/acfcs-dashboard.php';
                include 'admin/acfcs-preview.php';
                include 'admin/acfcs-settings.php';
                include 'admin/acfcs-search.php';
                include 'admin/acfcs-info.php';
                include 'admin/acfcs-countries.php';
            }

            public function acfcs_plugin_activation() {
                $this->acfcs_check_table();
                $this->acfcs_check_uploads_folder();
                update_option( 'acfcs_version', $this->settings[ 'version' ] );
            }

            public function acfcs_plugin_deactivation() {
                delete_option( 'acfcs_version' );
                delete_option( 'acfcs_db_version' );
                // other important stuff gets done in uninstall.php
            }

            public function acfcs_set_plugin_meta( $links, $file ) {
                static $acfcs_links;
                $acfcs_links = plugin_basename(__FILE__);

                if ( $file === $acfcs_links ) {
                    $links[] = sprintf( '<a href="https://acf-city-selector.com" target="_blank">%s</a>', esc_html__( 'Visit plugin site', 'acf-city-selector' ) );
                }

                return $links;
            }

            public function acfcs_check_version() {
                $acfcs_version = get_option( 'acfcs_version', false );
                if ( false == $acfcs_version || $acfcs_version != $this->settings[ 'version' ] ) {
                    update_option( 'acfcs_version', $this->settings[ 'version' ] );
                }
            }

            public function acfcs_check_table() {
                $acfcs_db_version = get_option( 'acfcs_db_version', false );
                if ( false == $acfcs_db_version || $acfcs_db_version != $this->settings[ 'db_version' ] ) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                    ob_start();
                    global $wpdb;
                    ?>
                    CREATE TABLE <?php echo esc_attr( $wpdb->prefix ); ?>cities (
                    id int(6) unsigned NOT NULL auto_increment,
                    city_name varchar(50) NULL,
                    state_code varchar(3) NULL,
                    state_name varchar(50) NULL,
                    country_code varchar(2) NULL,
                    country varchar(50) NULL,
                    PRIMARY KEY  (id)
                    )
                    COLLATE <?php echo esc_attr( $wpdb->collate ); ?>;
                    <?php
                    $sql = ob_get_clean();
                    dbDelta( $sql );
                    update_option( 'acfcs_db_version', $this->settings[ 'db_version' ] );
                }
            }

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
                        echo '<div class="notice notice-warning is-dismissible"><p>';
                        if ( 1 === count( $reimport ) ) {
                            /* translators: %s country name */
                            printf( esc_html__( 'Several cities in %s had broken ascii characters. You need to re-import these countries to get the correct city names.', 'acf-city-selector' ), esc_html( $country_name ) );
                        } else {
                            esc_html__( 'Several cities in Belgium and Netherlands had broken ascii characters. You need to re-import these countries to get the correct city names.', 'acf-city-selector' );
                        }
                        echo '</p></div>';

                    } else {
                        update_option( 'acfcs_city_update_1_8_0', 'done' );
                    }
                }
            }

            public function acfcs_error_messages( $messages ) {
                if ( isset( $messages[ 'validation' ] ) ) {
                    $messages[ 'validation' ] = array_merge( $messages[ 'validation' ], $this->l10n );
                } else {
                    $messages[ 'validation' ] = $this->l10n;
                }

                return $messages;
            }

            public function acfcs_include_field_types( $version = false ) {
                if ( ! $version ) {
                    $version = 4;
                }
                include_once 'admin/acf-city-selector-v' . $version . '.php';
            }

            public function acfcs_settings_link( $links ) {
                $settings_link = [ 'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=acfcs-dashboard' ), esc_html__( 'Settings', 'acf-city-selector' ) ) ];

                return array_merge( $settings_link, $links );
            }

            public function acfcs_check_for_acf() {
                if ( ! class_exists( 'acf' ) ) {
                    add_action( 'admin_notices', function () {
                        /* translators: %s name current plugin, %s link tag */
                        $message = sprintf( __( '"Advanced Custom Fields" is not activated. This plugin <strong>must</strong> be activated, because without it "%1$s" won\'t work. Activate it <a href="%2$s">here</a>.', 'acf-city-selector' ),
                            'ACF City Selector',
                            esc_url( admin_url( 'plugins.php?s=acf&plugin_status=inactive' ) ) );
                        /* translators: %s message */
                        echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
                    });
                }
            }

            public function acfcs_check_acf_version() {
                if ( ! function_exists( 'get_plugins' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                $plugins = get_plugins();

                if ( isset( $plugins[ 'advanced-custom-fields-pro/acf.php' ] ) ) {
                    if ( $plugins[ 'advanced-custom-fields-pro/acf.php' ][ 'Version' ] < 5 && is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
                        add_action( 'admin_notices', function () {
                            /* translators: %s warning, %s name current plugin */
                            $message = sprintf( __( '%1$s: The "%2$s" plugin will probably not work properly (anymore) with Advanced Custom Fields v4.x. Please upgrade to PRO.', 'acf-city-selector' ),
                                sprintf( '<b>%s</b>', __( 'Warning', 'acf-city-selector' ) ),
                                'City Selector'
                            );
                            /* translators: %s message */
                            echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
                        } );
                    }
                }
            }

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

            public function acfcs_move_array_element( &$array, $from_index, $to_index ) {
                $splice = array_splice( $array, $from_index, 1 );
                array_splice( $array, $to_index, 0, $splice );
            }

            public function acfcs_add_admin_pages() {
                add_options_page( 'ACF City Selector', 'City Selector', apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-dashboard', 'acfcs_dashboard' );
                add_submenu_page( 'options.php', __( 'Preview data', 'acf-city-selector' ), __( 'Preview data', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-preview', 'acfcs_preview_page' );
                add_submenu_page( 'options.php', __( 'Settings', 'acf-city-selector' ), __( 'Settings', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-settings', 'acfcs_settings' );
                add_submenu_page( 'options.php', __( 'Get countries', 'acf-city-selector' ), __( 'Get countries', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-countries', 'acfcs_country_page' );
                add_submenu_page( 'options.php', __( 'Search', 'acf-city-selector' ), __( 'Search', 'acf-city-selector' ),  apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-search', 'acfcs_search' );
                add_submenu_page( 'options.php', __( 'Info', 'acf-city-selector' ), __( 'Info', 'acf-city-selector' ), apply_filters( 'acfcs_user_cap', 'manage_options' ), 'acfcs-info', 'acfcs_info_page' );
            }

            public function acfcs_add_scripts_admin() {
                if ( is_admin() ) {
                    wp_enqueue_style( 'acfcs-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), [], $this->settings[ 'version' ] );
                    wp_register_script( 'acfcs-upload', plugins_url( 'assets/js/upload-csv.js', __FILE__ ), [ 'jquery' ], $this->settings[ 'version' ], [ 'in_footer' => true, 'strategy' => 'defer' ] );
                    wp_enqueue_script( 'acfcs-upload' );
                }
            }

            public static function acfcs_check_uploads_folder() {
                $target_folder = acfcs_upload_folder( '/' );
                if ( ! file_exists( $target_folder ) ) {
                    global $wp_filesystem;
                    if ( empty( $wp_filesystem ) ) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        WP_Filesystem();
                    }

                    $wp_filesystem->mkdir( $target_folder, 0755 );
                }
            }

            public static function acfcs_errors() {
                static $wp_error;

                return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
            }

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
                        echo sprintf( '<div id="message" class="notice %s is-dismissible">', esc_attr( $span_class ) );
                        foreach ( $codes as $code ) {
                            echo sprintf( '<p>%s</p>', esc_html( ACF_City_Selector::acfcs_errors()->get_error_message( $code ) ) );
                        }
                        echo '</div>';
                    }
                }
            }

            public static function get_instance() {
                static $instance;

                if ( null === $instance ) {
                    $instance = new self();
                }

                return $instance;
            }
        }

        ACF_City_Selector::get_instance();

    }
