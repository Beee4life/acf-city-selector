<?php
    /*
    Plugin Name:    ACF City Selector
    Plugin URI:     https://acfcs.berryplasman.com
    Description:    An extension for ACF which allows you to select a city based on country and province/state.
    Version:        0.10-beta
    Author:         Beee
    Author URI:     https://berryplasman.com
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
                    'version'       => '0.10-beta',
                    'url'           => plugin_dir_url( __FILE__ ),
                    'path'          => plugin_dir_path( __FILE__ ),
                    'upload_folder' => wp_upload_dir()[ 'basedir' ] . '/acfcs/',
                );

                // set text domain
                load_plugin_textdomain( 'acf-city-selector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

                register_activation_hook( __FILE__,    array( $this, 'acfcs_plugin_activation' ) );
                register_deactivation_hook( __FILE__,  array( $this, 'acfcs_plugin_deactivation' ) );

                // actions
                add_action( 'acf/include_field_types',      array( $this, 'acfcs_include_field_types' ) );    // v5
                add_action( 'acf/register_fields',          array( $this, 'acfcs_include_field_types' ) );    // v4
                add_action( 'admin_enqueue_scripts',        array( $this, 'acfcs_add_css' ) );
                add_action( 'admin_menu',                   array( $this, 'acfcs_add_admin_pages' ) );

                add_action( 'admin_init',                   array( $this, 'acfcs_admin_menu' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_errors' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_upload_csv_file' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_do_something_with_file' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_import_raw_data' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_import_preset_countries' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_preserve_settings' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_truncate_table' ) );
                add_action( 'admin_init',                   array( $this, 'acfcs_delete_rows' ) );

                add_action( 'plugins_loaded',               array( $this, 'acfcs_change_plugin_order' ), 5 );
                add_action( 'plugins_loaded',               array( $this, 'acfcs_check_for_acf' ), 6 );

                // filters
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'acfcs_settings_link' ) );
                add_filter( 'plugin_row_meta',      array( $this, 'acfcs_meta_links'), 10, 2 );

                include( 'inc/acfcs-donate-box.php' );
                include( 'inc/acfcs-functions.php' );
                include( 'inc/acfcs-help-tabs.php' );
                include( 'inc/country-field.php' );

            }


            /**
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
             * Check if ACF is active and if not add an admin notice
             */
            public function acfcs_check_for_acf() {
                if ( ! class_exists( 'acf' ) ) {
                    add_action( 'admin_notices', function () {
                        echo '<div class="error"><p>';
                        echo sprintf( __( '"%s" is not activated. This plugin <strong>must</strong> be activated, because without it "%s" won\'t work. Activate it <a href="%s">here</a>.', 'acf-city-selector' ),
                            'Advanced Custom Fields',
                            'ACF City Selector',
                            esc_url( admin_url( 'plugins.php?s=acf&plugin_status=inactive' ) ) );
                        echo '</p></div>';
                    });
                }
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


            /*
             * Check if (upload) folder exists
             */
            public function acfcs_check_uploads_folder() {
                $target_folder = $this->settings[ 'upload_folder' ];
                if ( ! file_exists( $target_folder ) ) {
                    mkdir( $target_folder, 0755 );
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
                        $target_file = $this->settings[ 'upload_folder' ] . basename( $_FILES[ 'csv_upload' ][ 'name' ] );

                        if ( move_uploaded_file( $_FILES[ 'csv_upload' ][ 'tmp_name' ], $target_file ) ) {

                            // file uploaded succeeded
                            $this->acfcs_errors()->add( 'success_file_uploaded', sprintf( esc_html__( "File '%s' is successfully uploaded and now shows under 'Select files to import'", 'acf-city-selector' ), $_FILES[ 'csv_upload' ][ 'name' ] ) );

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

                if ( isset( $_POST[ 'acfcs_select_file_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_select_file_nonce' ], 'acfcs-select-file-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong. Please try again.', 'acf-city-selector' ) );

                        return;

                    } else {

                        if ( ! isset( $_POST[ 'acfcs_file_name' ] ) ) {
                            $this->acfcs_errors()->add( 'error_no_file_selected', esc_html__( "You didn't select a file.", 'acf-city-selector' ) );

                            return;
                        }

                        $file_name = $_POST[ 'acfcs_file_name' ];
                        $delimiter = ! empty( $_POST[ 'delimiter' ] ) ? $_POST[ 'delimiter' ] : ',';
                        $import    = isset( $_POST[ 'import' ] ) ? true : false;
                        $remove    = isset( $_POST[ 'remove' ] ) ? true : false;
                        $verify    = isset( $_POST[ 'verify' ] ) ? true : false;

                        if ( true === $verify ) {
                            $csv_array = acfcs_csv_to_array( $file_name, $delimiter, $verify );
                            if ( isset( $csv_array[ 'data' ] ) ) {
                                $this->acfcs_errors()->add( 'success_no_errors_in_csv', esc_html__( 'Congratulations, there appear to be no errors in your CSV.', 'acf-city-selector' ) );

                                do_action( 'acfcs_after_success_verify' );

                                return;
                            }

                        } elseif ( true === $import ) {

                            // import data
                            $csv_array = acfcs_csv_to_array( $file_name, $delimiter, $verify );
                            if ( isset( $csv_array[ 'data' ] ) && ! empty( $csv_array[ 'data' ] ) ) {
                                $line_number = 0;
                                foreach ( $csv_array[ 'data' ] as $line ) {
                                    $line_number++;

                                    $city         = $line[ 0 ];
                                    $state_abbr   = $line[ 1 ];
                                    $state        = $line[ 2 ];
                                    $country_abbr = $line[ 3 ];
                                    $country      = $line[ 4 ];

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

                        } elseif ( true === $remove ) {

                            if ( isset( $_POST[ 'acfcs_file_name' ] ) ) {
                                // delete file
                                unlink( $this->settings[ 'upload_folder' ] . $file_name );
                                // @TODO: add if for success
                                $this->acfcs_errors()->add( 'success_file_deleted', sprintf( esc_html__( 'File "%s" successfully deleted.', 'acf-city-selector' ), $file_name ) );
                            }
                        }
                    }
                }
            }


            /*
             * Import raw csv data (not in use yet)
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
                                // import data
                                global $wpdb;
                                $line_number = 0;
                                foreach ( $verified_data as $line ) {
                                    $line_number++;

                                    $city         = $line[ 0 ];
                                    $state_abbr   = $line[ 1 ];
                                    $state        = $line[ 2 ];
                                    $country_abbr = $line[ 3 ];
                                    $country      = $line[ 4 ];

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
                                $this->acfcs_errors()->add( 'success_cities_imported', sprintf( _n( 'Congratulations, you imported 1 city.', 'Congratulations, you imported %d cities.', $line_number, 'acf-city-selector' ), $line_number ) );

                                do_action( 'acfcs_after_success_import_raw' );
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

                        if ( isset( $_POST[ 'import_nl' ] ) || isset( $_POST[ 'import_be' ] ) || isset( $_POST[ 'import_lux' ] ) ) {
                            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                            ob_start();
                            global $wpdb;
                            if ( isset( $_POST[ 'import_be' ] ) && 1 == $_POST[ "import_be" ] ) {
                                require_once( 'lib/import_be.php' );
                                do_action( 'acfcs_after_success_import_be' );
                            }
                            if ( isset( $_POST[ 'import_lux' ] ) && 1 == $_POST[ "import_lux" ] ) {
                                require_once( 'lib/import_lux.php' );
                                do_action( 'acfcs_after_success_import_lu' );
                            }
                            if ( isset( $_POST[ 'import_nl' ] ) && 1 == $_POST[ "import_nl" ] ) {
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
                if ( isset( $_POST[ 'acfcs_truncate_table_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_truncate_table_nonce' ], 'acfcs-truncate-table-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {

                        if ( isset( $_POST[ 'delete_cities' ] ) ) {
                            if ( isset( $_POST[ 'delete_cities' ] ) && 1 == $_POST[ "delete_cities" ] ) {
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
                if ( isset( $_POST[ 'acfcs_preserve_settings_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'acfcs_preserve_settings_nonce' ], 'acfcs-preserve-settings-nonce' ) ) {
                        $this->acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

                        return;
                    } else {

                        if ( isset( $_POST[ 'preserve_settings' ] ) ) {
                            update_option( 'acfcs_preserve_settings', 1, true );
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
                                $ids[]    = $split[ 0 ];
                                $cities[] = $split[ 1 ];
                            }

                            $cities = implode( ', ', $cities );
                            $row_ids = implode( ',', $ids );
                            $amount = $wpdb->query(
                                "
                                DELETE FROM " . $wpdb->prefix . "cities
                                WHERE id IN (" . $row_ids . ")
                                "
                            );

                            if ( $amount > 0 ) {
                                $row_count = count( $ids );
                                $this->acfcs_errors()->add( 'success_row_delete', sprintf( _n( 'You have deleted the city %s.', 'You have deleted the following cities: %s.', $row_count, 'acf-city-selector' ), $cities ) );
                            }
                        }
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
                        $span_class = false;
                        $prefix     = false;
                        foreach ( $codes as $code ) {
                            if ( strpos( $code, 'success' ) !== false ) {
                                $span_class = 'notice--success ';
                                $prefix     = false;
                            } elseif ( strpos( $code, 'error' ) !== false ) {
                                $span_class = 'notice--error ';
                                $prefix     = esc_html__( 'Warning', 'action-logger' );
                            } elseif ( strpos( $code, 'info' ) !== false ) {
                                $span_class = 'notice--info ';
                                $prefix     = false;
                            } else {
                                $span_class = 'notice--error ';
                                $prefix     = esc_html__( 'Error', 'action-logger' );
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
                include_once( 'fields/acf-city_selector-v' . $version . '.php' );
            }


            /*
             * Add settings link on plugin page
             */
            public function acfcs_settings_link( $links ) {
                $settings_link = '<a href="options-general.php?page=acfcs-dashboard">' . esc_html__( 'Dashboard', 'acf-city-selector' ) . '</a>';
                array_unshift( $links, $settings_link );

                return $links;
            }


            /**
             * Add links below plugin description
             *
             * @param $links
             * @param $file
             *
             * @return array
             */
            public function acfcs_meta_links( $links, $file ) {

                $visit_plugin_link = array_pop( $links );
                if ( strpos( $file, 'ACF_City_Selector.php' ) !== false ) {
                    $new_links[ 'documentation' ] = '<a href="https://acfcs.berryplasman.com/documentation">Documentation</a>';
                    if ( defined( 'WP_TESTING' ) && WP_TESTING == 1 ) {
                        // $new_links[ 'gopro' ] = '<a href="' . admin_url() . 'options-general.php?page=acfcs-go-pro' . '">Go Pro</a>';
                    }
                    $new_links[] = $visit_plugin_link;
                    $links       = array_merge( $links, $new_links );
                }

                return $links;
            }


            /*
             * Admin menu
             */
            public static function acfcs_admin_menu() {
                $admin_url  = admin_url( 'options-general.php?page=' );
                $dashboard  = '<a href="' . $admin_url . 'acfcs-dashboard">' . esc_html__( 'Dashboard', 'acf-city-selector' ) . '</a>';
                $gopro      = false;
                $preview    = false;
                $search     = false;
                $settings   = ' | <a href="' . $admin_url . 'acfcs-settings">' . esc_html__( 'Settings', 'acf-city-selector' ) . '</a>';
                $show_gopro = true;

                if ( true === acfcs_has_cities() ) {
                    $search = ' | <a href="' . $admin_url . 'acfcs-search">' . esc_html__( 'Search', 'acf-city-selector' ) . '</a>';
                }

                if ( ! empty ( acfcs_check_if_files() ) ) {
                    $preview = ' | <a href="' . $admin_url . 'acfcs-preview">' . esc_html__( 'Preview', 'acf-city-selector' ) . '</a>';
                }

                if ( defined( 'WP_ENV' ) && WP_ENV == 'development' && defined( 'WP_TESTING' ) && WP_TESTING == 1 && false != $show_gopro ) {
                    $gopro = ' | <a href="' . $admin_url . 'acfcs-go-pro">' . esc_html__( 'Go Pro', 'acf-city-selector' ) . '</a>';
                }

                return '<p class="acfcs-admin-menu">' . $dashboard . $search . $preview . $settings . $gopro . '</p>';
            }

            /**
             * Move array element to specific position
             *
             * @param $array
             * @param $from_index
             * @param $to_index
             */
            public static function acfcs_move_array_element( &$array, $from_index, $to_index ) {
                $out = array_splice( $array, $from_index, 1 );
                array_splice( $array, $to_index, 0, $out );
            }

            /*
             * Adds admin pages
             */
            public function acfcs_add_admin_pages() {

                include( 'inc/acfcs-dashboard.php' );
                add_options_page( 'ACF City Selector', 'City Selector', 'manage_options', 'acfcs-dashboard', 'acfcs_dashboard' );

                include( 'inc/acfcs-preview.php' );
                add_submenu_page( null, 'Preview data', 'Preview data', 'manage_options', 'acfcs-preview', 'acfcs_preview_page' );

                include( 'inc/acfcs-settings.php' );
                add_submenu_page( null, 'Settings', 'Settings', 'manage_options', 'acfcs-settings', 'acfcs_settings' );

                if ( true == acfcs_has_cities() ) {
                    include( 'inc/acfcs-search.php' );
                    add_submenu_page( null, 'City Overview', 'City Overview', 'manage_options', 'acfcs-search', 'acfcs_search' );
                }

                if ( defined( 'WP_ENV' ) && WP_ENV == 'development' && defined( 'WP_TESTING' ) && WP_TESTING == 1 ) {
                    include( 'inc/acfcs-go-pro.php' );
                    add_submenu_page( null, 'Pro', 'Pro', 'manage_options', 'acfcs-go-pro', 'acfcs_go_pro' );
                }
            }


            /*
             * Adds CSS on the admin side
             */
            public function acfcs_add_css() {
                wp_enqueue_style( 'acf-city-selector', plugins_url( 'assets/css/acf-city-selector.css', __FILE__ ), [], $this->settings[ 'version' ] );
            }
        }

        // initialize
        new ACF_City_Selector();


        // class_exists check
    endif;
