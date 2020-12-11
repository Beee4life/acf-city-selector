<?php
    /*
    Plugin Name:    ACF City Selector
    Plugin URI:     https://acf-city-selector.com
    Description:    An extension for ACF which allows you to select a city based on country and province/state.
    Version:        0.32.0
    Tested up to:   5.5.3
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
    if ( ! class_exists( 'ACF_City_Selector' ) ) :

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
                    'db_version'    => '1.0',
                    'path'          => plugin_dir_path( __FILE__ ),
                    'upload_folder' => wp_upload_dir()[ 'basedir' ] . '/acfcs/',
                    'url'           => plugin_dir_url( __FILE__ ),
                    'version'       => '0.32.0',
                );

                if ( ! class_exists( 'ACFCS_WEBSITE_URL' ) ) {
                    define( 'ACFCS_WEBSITE_URL', 'https://acf-city-selector.com' );
                }

                if ( ! defined( 'ACFCS_PLUGIN_PATH' ) ) {
                    $plugin_path = $this->settings[ 'path' ];
                    define( 'ACFCS_PLUGIN_PATH', $plugin_path );
                }

                if ( ! defined( 'ACFCS_PLUGIN_URL' ) ) {
                    $plugin_url = $this->settings[ 'url' ];
                    define( 'ACFCS_PLUGIN_URL', $plugin_url );
                }

                // set text domain
                load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

                register_activation_hook( __FILE__,    array( $this, 'acfcs_plugin_activation' ) );
                register_deactivation_hook( __FILE__,  array( $this, 'acfcs_plugin_deactivation' ) );

                // actions
                add_action( 'acf/include_field_types',      array( $this, 'acfcs_include_field_types' ) );    // v5
                add_action( 'acf/register_fields',          array( $this, 'acfcs_include_field_types' ) );    // v4

                add_action( 'admin_enqueue_scripts',        array( $this, 'acfcs_add_scripts' ) );
                add_action( 'wp_enqueue_scripts',           array( $this, 'acfcs_add_scripts' ) );

                add_action( 'admin_menu',                   array( $this, 'acfcs_add_admin_pages' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_admin_menu' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_delete_countries' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_delete_rows' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_delete_all_transients' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_do_something_with_file' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_errors' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_import_preset_countries' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_import_raw_data' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_preserve_settings' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_truncate_table' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_upload_csv_file' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_check_table' ) );
                add_action( 'admin_notices',                array( $this, 'acfcs_check_for_beta' ) );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_change_plugin_order' ), 5 );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_check_for_acf' ), 6 );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_check_acf_version' ) );

                // Plugin's own actions
                add_action( 'acfcs_after_success_country_remove',   array( $this, 'acfcs_delete_transients' ) );
                add_action( 'acfcs_after_success_import',           array( $this, 'acfcs_delete_transients' ) );
                add_action( 'acfcs_after_success_import_be',        array( $this, 'acfcs_delete_transients' ) );
                add_action( 'acfcs_after_success_import_nl',        array( $this, 'acfcs_delete_transients' ) );
                add_action( 'acfcs_after_success_import_raw',       array( $this, 'acfcs_delete_transients' ) );
                add_action( 'acfcs_after_success_nuke',             array( $this, 'acfcs_delete_transients' ) );

                // filters
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'acfcs_settings_link' ) );

                include 'inc/acfcs-functions.php';
                include 'inc/acfcs-help-tabs.php';
                include 'inc/acfcs-i18n.php';
                include 'inc/country-field.php';

            }


            /*
             * Do stuff upon plugin activation
             */
            public function acfcs_plugin_activation() {
                $this->acfcs_check_table();
                $this->acfcs_check_uploads_folder();
                $this->acfcs_copy_file( 'nl' );
                $this->acfcs_copy_file( 'be' );
                if ( false == get_option( 'acfcs_preserve_settings' ) ) {
                    $this->acfcs_fill_database();
                }
            }


            /*
             * Do stuff upon plugin activation
             */
            public function acfcs_plugin_deactivation() {
                delete_option( 'acfcs_db_version' );
                $this->acfcs_delete_transients();
                // other important stuff gets done in uninstall.php
            }


            /**
             * Copy source files to upload folder if needed
             *
             * @param $file_name
             */
            function acfcs_copy_file( $file_name ) {
                if ( $file_name ) {
                    if ( file_exists( $this->settings[ 'path' ] . 'lib/' . $file_name . '.csv' ) ) {
                        copy( $this->settings[ 'path' ] . 'lib/' . $file_name . '.csv', acfcs_upload_folder( '/' ) . $file_name . '.csv' );
                    }
                }
            }


            /*
             * Prepare database upon plugin activation
             */
            public function acfcs_fill_database() {
                $countries = [ 'nl', 'be' ];
                foreach( $countries as $country ) {
                    acfcs_import_data( $country . '.csv' );
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
            public function acfcs_check_uploads_folder() {
                $target_folder = acfcs_upload_folder( '/' );
                if ( ! file_exists( $target_folder ) ) {
                    mkdir( $target_folder, 0755 );
                }
            }


            /**
             * Delete country transient
             *
             * @param $country_code
             */
            public function acfcs_delete_transients( $country_code = false ) {
                if ( false != $country_code ) {
                    delete_transient( 'acfcs_states_' . strtolower( $country_code ) );
                    delete_transient( 'acfcs_cities_' . strtolower( $country_code ) );
                } else {
                    delete_transient( 'acfcs_countries' );
                    // get all countries
                    $countries = acfcs_get_countries( false, false, true );
                    if ( ! empty( $countries ) ) {
                        foreach( $countries as $country_code => $label ) {
                            do_action( 'acfcs_after_success_import', $country_code );
                        }
                    }
                }
            }


            /*
             * Upload CSV file
             */
            public function acfcs_upload_csv_file() {
                if ( isset( $_POST[ 'acfcs_upload_csv_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_upload_csv_nonce' ], 'acfcs-upload-csv-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        $this->acfcs_check_uploads_folder();
                        $target_file = acfcs_upload_folder( '/' ) . basename( $_FILES[ 'csv_upload' ][ 'name' ] );
                        if ( move_uploaded_file( $_FILES[ 'csv_upload' ][ 'tmp_name' ], $target_file ) ) {
                            $this->acfcs_errors()->add( 'success_file_uploaded', sprintf( esc_html__( "File '%s' is successfully uploaded and now shows under 'Select files to import'", 'acf-city-selector' ), $_FILES[ 'csv_upload' ][ 'name' ] ) );
                            do_action( 'acfcs_after_success_file_upload' );

                            return;
                        } else {
                            $this->acfcs_errors()->add( 'error_file_uploaded', esc_html__( 'Upload failed. Please try again.', 'acf-city-selector' ) );

                            return;
                        }
                    }
                }
            }


            /*
             * Read uploaded file for verification, import or delete
             */
            public function acfcs_do_something_with_file() {
                if ( isset( $_POST[ 'acfcs_select_file_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_select_file_nonce' ], 'acfcs-select-file-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        if ( empty( $_POST[ 'acfcs_file_name' ] ) ) {
                            $this->acfcs_errors()->add( 'error_no_file_selected', esc_html__( "You didn't select a file.", 'acf-city-selector' ) );

                            return;
                        }

                        $file_name = $_POST[ 'acfcs_file_name' ];
                        $delimiter = ! empty( $_POST[ 'acfcs_delimiter' ] ) ? $_POST[ 'acfcs_delimiter' ] : apply_filters( 'acfcs_delimiter', ';' );
                        $max_lines = isset( $_POST[ 'acfcs_max_lines' ] ) ? $_POST[ 'acfcs_max_lines' ] : false;
                        $import    = isset( $_POST[ 'import' ] ) ? true : false;
                        $remove    = isset( $_POST[ 'remove' ] ) ? true : false;
                        $verify    = isset( $_POST[ 'verify' ] ) ? true : false;

                        if ( true === $verify ) {
                            acfcs_verify_data( $file_name, $delimiter, $verify );
                        } elseif ( true === $import ) {
                            acfcs_import_data( $file_name, $delimiter, $verify, $max_lines );
                        } elseif ( true === $remove ) {
                            acfcs_delete_file( $file_name );
                        }
                    }
                }
            }


            /*
             * Import raw csv data
             */
            public function acfcs_import_raw_data() {
                if ( isset( $_POST[ 'acfcs_import_raw_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_import_raw_nonce' ], 'acfcs-import-raw-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        $verified_data = acfcs_verify_csv_data( $_POST[ 'acfcs_raw_csv_import' ] );
                        if ( isset( $_POST[ 'verify' ] ) ) {
                            if ( false != $verified_data ) {
                                $this->acfcs_errors()->add( 'success_csv_valid', esc_html__( 'Congratulations, your CSV data seems valid.', 'acf-city-selector' ) );
                            }
                        } elseif ( isset( $_POST[ 'import' ] ) ) {
                            if ( false != $verified_data ) {
                                acfcs_import_data( $verified_data );
                            }
                        }
                    }
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
                                if ( ! file_exists( $this->settings[ 'path' ] . 'lib/be.csv' ) ) {
                                    $this->acfcs_copy_file( 'be' );
                                }
                                acfcs_import_data( 'be.csv' );
                                do_action( 'acfcs_after_success_import_be', 'be' );
                            }
                            if ( isset( $_POST[ 'import_nl' ] ) && 1 == $_POST[ 'import_nl' ] ) {
                                if ( ! file_exists( $this->settings[ 'path' ] . 'lib/nl.csv' ) ) {
                                    $this->acfcs_copy_file( 'nl' );
                                }
                                acfcs_import_data( 'nl.csv' );
                                do_action( 'acfcs_after_success_import_nl', 'nl' );
                            }
                            do_action( 'acfcs_after_success_import' );
                        }
                    }
                }
            }


            /*
             * Truncate cities table
             */
            public function acfcs_truncate_table() {
                if ( isset( $_POST[ 'acfcs_truncate_table_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_truncate_table_nonce' ], 'acfcs-truncate-table-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        global $wpdb;
                        $prefix = $wpdb->get_blog_prefix();
                        $wpdb->query( 'TRUNCATE TABLE ' . $prefix . 'cities' );
                        $this->acfcs_errors()->add( 'success_table_truncated', esc_html__( 'All cities are deleted.', 'acf-city-selector' ) );
                        do_action( 'acfcs_after_success_nuke' );
                    }
                }
            }


            /*
             * Preserve settings
             */
            public function acfcs_preserve_settings() {
                if ( isset( $_POST[ 'acfcs_preserve_settings_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_preserve_settings_nonce' ], 'acfcs-preserve-settings-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        if ( isset( $_POST[ 'preserve_settings' ] ) ) {
                            update_option( 'acfcs_preserve_settings', 1 );
                        } else {
                            delete_option( 'acfcs_preserve_settings' );
                        }
                        $this->acfcs_errors()->add( 'success_settings_saved', esc_html__( 'Settings saved', 'acf-city-selector' ) );
                    }
                }
            }


            /*
             * Delete rows manually
             */
            public function acfcs_delete_rows() {
                if ( isset( $_POST[ 'acfcs_delete_row_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_delete_row_nonce' ], 'acfcs-delete-row-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        global $wpdb;
                        if ( is_array( $_POST[ 'row_id' ] ) ) {
                            foreach( $_POST[ 'row_id' ] as $row ) {
                                $split    = explode( ' ', $row, 2 );
                                if ( isset( $split[ 0 ] ) && isset( $split[ 1 ] ) ) {
                                    $ids[]    = $split[ 0 ];
                                    $cities[] = $split[ 1 ];
                                }
                            }
                            $cities  = implode( ', ', $cities );
                            $row_ids = implode( ',', $ids );
                            $amount  = $wpdb->query("
                                DELETE FROM " . $wpdb->prefix . "cities
                                WHERE id IN (" . $row_ids . ")
                            ");

                            if ( $amount > 0 ) {
                                $this->acfcs_errors()->add( 'success_row_delete', sprintf( _n( 'You have deleted the city %s.', 'You have deleted the following cities: %s.', $amount, 'acf-city-selector' ), $cities ) );
                            }
                        }
                    }
                }
            }


            /*
             * Delete transients
             */
            public function acfcs_delete_all_transients() {
                if ( isset( $_POST[ 'acfcs_delete_transients' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_delete_transients' ], 'acfcs-delete-transients-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        $this->acfcs_delete_transients();
                        ACF_City_Selector::acfcs_errors()->add( 'success_transients_delete', esc_html__( 'You have successfully deleted all transients.', 'acf-city-selector' ) );
                    }
                }
            }


            /*
             * Delete countries manually
             */
            public function acfcs_delete_countries() {
                if ( isset( $_POST[ 'acfcs_remove_countries_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_remove_countries_nonce' ], 'acfcs-remove-countries-nonce' ) ) {
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {
                        if ( empty( $_POST[ 'delete_country' ] ) ) {
                            ACF_City_Selector::acfcs_errors()->add( 'error_no_country_selected', esc_html__( "You didn't select any countries, please try again.", 'acf-city-selector' ) );

                            return;
                        } else {
                            acfcs_delete_countries( $_POST[ 'delete_country' ] );
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
             * Adds admin pages
             */
            public function acfcs_add_admin_pages() {
                include 'admin/acfcs-dashboard.php';
                add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-dashboard', 'acfcs_dashboard' );

                include 'admin/acfcs-preview.php';
                add_submenu_page( null, 'Preview data', 'Preview data', 'manage_options', 'acfcs-preview', 'acfcs_preview_page' );

                include 'admin/acfcs-settings.php';
                add_submenu_page( null, 'Settings', 'Settings', 'manage_options', 'acfcs-settings', 'acfcs_settings' );

                if ( true == acfcs_has_cities() ) {
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

    endif;
