<?php
    /*
    Plugin Name:    ACF City Selector
    Plugin URI:     https://acf-city-selector.com
    Description:    An extension for ACF which allows you to select a city based on country and province/state.
    Version:        0.33.0
    Tested up to:   5.6
    Requires PHP:   7.0
    Author:         Beee
    Author URI:     https://berryplasman.com
    Text Domain:    acf-city-selector
    Domain Path:    /languages
    License:        GPLv2 or later
    License URI:    https://www.gnu.org/licenses/gpl.html
    Contributors:   Jarah de Jong
    */

    // exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    // check if class already exists
    if ( ! class_exists( 'ACF_City_Selector' ) ) {

        /*
         * Main class
         */
        class ACF_City_Selector {

            /*
             * __construct
             *
             * This function will setup the class functionality
             */
            public function __construct() {

                $this->settings = array(
                    'db_version' => '1.0',
                    'url'        => plugin_dir_url( __FILE__ ),
                    'version'    => '0.33.0',
                );

                if ( ! class_exists( 'ACFCS_WEBSITE_URL' ) ) {
                    define( 'ACFCS_WEBSITE_URL', 'https://acf-city-selector.com' );
                }

                if ( ! defined( 'ACFCS_PLUGIN_PATH' ) ) {
                    $plugin_path = plugin_dir_path( __FILE__ );
                    define( 'ACFCS_PLUGIN_PATH', $plugin_path );
                }

                load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

                register_activation_hook( __FILE__,    array( $this, 'acfcs_plugin_activation' ) );
                register_deactivation_hook( __FILE__,  array( $this, 'acfcs_plugin_deactivation' ) );

                add_action( 'acf/register_fields',          array( $this, 'acfcs_include_field_types' ) );    // v4
                add_action( 'acf/include_field_types',      array( $this, 'acfcs_include_field_types' ) );    // v5

                add_action( 'admin_enqueue_scripts',        array( $this, 'acfcs_add_scripts' ) );
                add_action( 'wp_enqueue_scripts',           array( $this, 'acfcs_add_scripts' ) );

                add_action( 'admin_menu',                   array( $this, 'acfcs_add_admin_pages' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_admin_menu' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_errors' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_import_preset_countries' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_check_table' ) );
                add_action( 'admin_notices',                array( $this, 'acfcs_check_for_beta' ) );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_change_plugin_order' ), 5 );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_check_for_acf' ), 6 );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_check_acf_version' ) );

                // filters
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'acfcs_settings_link' ) );

                include 'inc/acfcs-actions.php';
                include 'inc/acfcs-functions.php';
                include 'inc/acfcs-help-tabs.php';
                include 'inc/acfcs-i18n.php';
                include 'inc/acfcs-ajax.php';
                include 'inc/form-handling.php';

            }


            /*
             * Do stuff upon plugin activation
             */
            public function acfcs_plugin_activation() {
                $this->acfcs_check_table();
                $this->acfcs_check_uploads_folder();
                if ( false == get_option( 'acfcs_preserve_settings' ) ) {
                    $this->acfcs_fill_database();
                }
            }


            /*
             * Do stuff upon plugin activation
             */
            public function acfcs_plugin_deactivation() {
                delete_option( 'acfcs_db_version' );
                // this hook is here because didn't want to create a new hook for an existing action
                do_action( 'acfcs_delete_transients' );
                // other important stuff gets done in uninstall.php
            }


            /*
             * Prepare database upon plugin activation
             */
            public function acfcs_fill_database() {
                $countries = [ 'nl', 'be' ];
                foreach( $countries as $country ) {
                    acfcs_import_data( $country . '.csv', ACFCS_PLUGIN_PATH . 'import/' );
                }
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
             */
            public static function acfcs_check_uploads_folder() {
                $target_folder = acfcs_upload_folder( '/' );
                if ( ! file_exists( $target_folder ) ) {
                    mkdir( $target_folder, 0755 );
                }
            }


            /*
             * Import preset countries
             */
            public function acfcs_import_preset_countries() {
                if ( isset( $_POST[ 'acfcs_import_actions_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_import_actions_nonce' ], 'acfcs-import-actions-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        if ( isset( $_POST[ 'import_be' ] ) || isset( $_POST[ 'import_nl' ] ) ) {
                            if ( isset( $_POST[ 'import_be' ] ) && 1 == $_POST[ 'import_be' ] ) {
                                acfcs_import_data( 'be.csv', ACFCS_PLUGIN_PATH . 'import/' );
                                do_action( 'acfcs_delete_transients', 'be' );
                            }
                            if ( isset( $_POST[ 'import_nl' ] ) && 1 == $_POST[ 'import_nl' ] ) {
                                acfcs_import_data( 'nl.csv', ACFCS_PLUGIN_PATH . 'import/' );
                                do_action( 'acfcs_delete_transients', 'nl' );
                            }
                            do_action( 'acfcs_after_success_import' );
                        }
                    }
                }
            }


            /*
             * Error function
             *
             * @return WP_Error
             */
            public static function acfcs_errors() {
                static $wp_error; // Will hold global variable safely

                return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
            }


            /*
             * Displays error messages from form submissions
             */
            public static function acfcs_show_admin_notices() {
                if ( $codes = ACF_City_Selector::acfcs_errors()->get_error_codes() ) {
                    if ( is_wp_error( ACF_City_Selector::acfcs_errors() ) ) {
                        $span_class = false;
                        $prefix     = false;
                        foreach ( $codes as $code ) {
                            if ( strpos( $code, 'success' ) !== false ) {
                                $span_class = 'notice--success ';
                                $prefix     = false;
                            } elseif ( strpos( $code, 'error' ) !== false ) {
                                $span_class = 'notice--error ';
                                $prefix     = esc_html__( 'Error', 'acf-city-selector' );
                            } elseif ( strpos( $code, 'warning' ) !== false ) {
                                $span_class = 'notice--warning ';
                                $prefix     = esc_html__( 'Warning', 'acf-city-selector' );
                            } elseif ( strpos( $code, 'info' ) !== false ) {
                                $span_class = 'notice--info ';
                                $prefix     = false;
                            } else {
                                $span_class = 'notice--error ';
                                $prefix     = esc_html__( 'Error', 'acf-city-selector' );
                            }
                        }
                        echo '<div class="acfcs__notice notice ' . $span_class . 'is-dismissible">';
                        foreach ( $codes as $code ) {
                            $message = ACF_City_Selector::acfcs_errors()->get_error_message( $code );
                            echo '<div class="">';
                            if ( true == $prefix ) {
                                echo '<strong>' . $prefix . ':</strong> ';
                            }
                            echo $message;
                            echo '</div>';
                            echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice', 'acf-city-selector' ) . '</span></button>';
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
                if ( ! $version ) { $version = 4; }
                include_once( 'fields/acf-city-selector-v' . $version . '.php' );
            }


            /*
             * Add settings link on plugin page
             *
             * @param $links
             *
             * @return array
             */
            public function acfcs_settings_link( $links ) {
                $settings_link = [ 'settings' => '<a href="options-general.php?page=acfcs-dashboard">' . esc_html__( 'Settings', 'acf-city-selector' ) . '</a>' ];
                $links         = array_merge( $settings_link, $links );

                return $links;
            }


            /*
             * Admin menu
             */
            public static function acfcs_admin_menu() {
                $admin_url      = admin_url( 'options-general.php?page=' );
                $preview        = false;
                $search         = false;
                $url_array      = parse_url( $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ] );

                if ( isset( $url_array[ 'query' ] ) ) {
                    $acfcs_subpage = substr( $url_array[ 'query' ], 11 );
                }

                $current_page = ( isset( $acfcs_subpage ) && 'dashboard' == $acfcs_subpage ) ? ' class="current_page"' : false;
                $dashboard    = '<a href="' . $admin_url . 'acfcs-dashboard"' . $current_page . '>' . esc_html__( 'Dashboard', 'acf-city-selector' ) . '</a>';
                $current_page = ( isset( $acfcs_subpage ) && 'settings' == $acfcs_subpage ) ? ' class="current_page"' : false;
                $settings     = ' | <a href="' . $admin_url . 'acfcs-settings"' . $current_page . '>' . esc_html__( 'Settings', 'acf-city-selector' ) . '</a>';

                if ( true === acfcs_has_cities() ) {
                    $current_page = ( isset( $acfcs_subpage ) && 'search' == $acfcs_subpage ) ? ' class="current_page"' : false;
                    $search = ' | <a href="' . $admin_url . 'acfcs-search"' . $current_page . '>' . esc_html__( 'Search', 'acf-city-selector' ) . '</a>';
                }

                if ( ! empty ( acfcs_check_if_files() ) ) {
                    $current_page = ( isset( $acfcs_subpage ) && 'preview' == $acfcs_subpage ) ? ' class="current_page"' : false;
                    $preview = ' | <a href="' . $admin_url . 'acfcs-preview"' . $current_page . '>' . esc_html__( 'Preview', 'acf-city-selector' ) . '</a>';
                }

                $current_page = ( isset( $acfcs_subpage ) && 'info' == $acfcs_subpage ) ? ' class="current_page"' : false;
                $info = ' | <a href="' . $admin_url . 'acfcs-info"' . $current_page . '>' . esc_html__( 'Info', 'acf-city-selector' ) . '</a>';

                $countries = ' | <a href="' . $admin_url . 'acfcs-countries" class="cta">' . esc_html__( 'Get more countries', 'acf-city-selector' ) . '</a>';

                $menu = '<p class="acfcs-admin-menu">' . $dashboard . $search . $preview . $settings . $info . $countries . '</p>';

                return $menu;
            }


            /*
             * Add admin notices
             */
            public function acfcs_check_for_beta() {
                $screen = get_current_screen();
                if ( strpos( $screen->id, 'acfcs' ) !== false ) {
                    // Check if it's a beta version
                    if ( strpos( $this->settings[ 'version' ], 'beta' ) !== false ) {
                    ?>
                        <div class="notice notice-warning is-dismissible">
                            <p><?php echo sprintf( esc_html__( "Please be aware, you're using a beta version of \"%s\".", 'acf-city-selector' ), 'ACF City Selector' ); ?></p>
                        </div>
                    <?php
                    }
                }
            }


            /*
             * Check if ACF is active and if not add an admin notice
             */
            public function acfcs_check_for_acf() {
                if ( ! class_exists( 'acf' ) ) {
                    add_action( 'admin_notices', function () {
                        echo '<div class="notice notice-error"><p>';
                        echo sprintf( __( '"%s" is not activated. This plugin <strong>must</strong> be activated, because without it "%s" won\'t work. Activate it <a href="%s">here</a>.', 'acf-city-selector' ),
                            'Advanced Custom Fields',
                            'ACF City Selector',
                            esc_url( admin_url( 'plugins.php?s=acf&plugin_status=inactive' ) ) );
                        echo '</p></div>';
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
                            echo '<div class="notice notice-error"><p>';
                            echo sprintf( __( '<b>Warning</b>: The "%s" plugin will probably not work properly (anymore) with %s v4.x. Please upgrade to PRO.', 'acf-city-selector' ), 'City Selector', 'Advanced Custom Fields' );
                            echo '</p></div>';
                        } );
                    }
                }
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
            public static function acfcs_move_array_element( &$array, $from_index, $to_index ) {
                $splice = array_splice( $array, $from_index, 1 );
                array_splice( $array, $to_index, 0, $splice );
            }


            /*
             * Add admin pages
             */
            public function acfcs_add_admin_pages() {
                include 'admin/acfcs-dashboard.php';
                add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-dashboard', 'acfcs_dashboard' );

                if ( ! empty( acfcs_check_if_files() ) ) {
                    include 'admin/acfcs-preview.php';
                    add_submenu_page( null, 'Preview data', 'Preview data', 'manage_options', 'acfcs-preview', 'acfcs_preview_page' );
                }

                include 'admin/acfcs-settings.php';
                add_submenu_page( null, 'Settings', 'Settings', 'manage_options', 'acfcs-settings', 'acfcs_settings' );

                if ( true === acfcs_has_cities() ) {
                    include 'admin/acfcs-search.php';
                    add_submenu_page( null, 'City Overview', 'City Overview', 'manage_options', 'acfcs-search', 'acfcs_search' );
                }

                include 'admin/acfcs-info.php';
                add_submenu_page( null, 'Info', 'Info', 'manage_options', 'acfcs-info', 'acfcs_info_page' );

                include 'admin/acfcs-countries.php';
                add_submenu_page( null, 'Get countries', 'Get countries', 'manage_options', 'acfcs-countries', 'acfcs_country_page' );
            }


            /*
             * Adds CSS on the admin side
             */
            public function acfcs_add_scripts() {
                wp_enqueue_style( 'acfcs-general', plugins_url( 'assets/css/general.css', __FILE__ ), [], $this->settings[ 'version' ] );
                if ( is_admin() ) {
                    wp_enqueue_style( 'acfcs-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), [], $this->settings[ 'version' ] );
                    wp_register_script( 'acfcs-admin', plugins_url( 'assets/js/upload-csv.js', __FILE__ ), [ 'jquery' ], $this->settings[ 'version' ] );
                    wp_enqueue_script( 'acfcs-admin' );
                }
            }
        }

        new ACF_City_Selector();

    }
