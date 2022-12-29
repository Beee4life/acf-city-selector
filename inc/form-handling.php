<?php
    /**
     * Handle CSV upload form
     */
    function acfcs_upload_csv_file() {
        if ( isset( $_POST[ 'acfcs_upload_csv_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_upload_csv_nonce' ], 'acfcs-upload-csv-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                ACF_City_Selector::acfcs_check_uploads_folder();
                $target_file = acfcs_upload_folder( '/' ) . basename( $_FILES[ 'acfcs_csv_upload' ][ 'name' ] );
                if ( move_uploaded_file( $_FILES[ 'acfcs_csv_upload' ][ 'tmp_name' ], $target_file ) ) {
                    ACF_City_Selector::acfcs_errors()->add( 'success_file_uploaded', sprintf( esc_html__( "File '%s' is successfully uploaded and now shows under 'Select files to import'", 'acf-city-selector' ), $_FILES[ 'acfcs_csv_upload' ][ 'name' ] ) );
                    do_action( 'acfcs_after_success_file_upload' );
                } else {
                    ACF_City_Selector::acfcs_errors()->add( 'error_file_uploaded', esc_html__( 'Upload failed. Please try again.', 'acf-city-selector' ) );
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_upload_csv_file' );


    /**
     * Handle process CSV form
     */
    function acfcs_do_something_with_file() {
        if ( isset( $_POST[ 'acfcs_select_file_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_select_file_nonce' ], 'acfcs-select-file-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( empty( $_POST[ 'acfcs_file_name' ] ) ) {
                    ACF_City_Selector::acfcs_errors()->add( 'error_no_file_selected', esc_html__( "You didn't select a file.", 'acf-city-selector' ) );

                    return;
                }

                $file_name = $_POST[ 'acfcs_file_name' ];
                $delimiter = ! empty( $_POST[ 'acfcs_delimiter' ] ) ? sanitize_text_field( $_POST[ 'acfcs_delimiter' ] ) : apply_filters( 'acfcs_delimiter', ';' );
                $import    = isset( $_POST[ 'acfcs_import' ] ) ? true : false;
                $max_lines = isset( $_POST[ 'acfcs_max_lines' ] ) ? (int) $_POST[ 'acfcs_max_lines' ] : false;
                $remove    = isset( $_POST[ 'acfcs_remove' ] ) ? true : false;
                $verify    = isset( $_POST[ 'acfcs_verify' ] ) ? true : false;

                if ( true === $verify ) {
                    acfcs_verify_data( $file_name, $delimiter, $verify );
                } elseif ( true === $import ) {
                    acfcs_import_data( $file_name, '', $delimiter, $verify, $max_lines );
                } elseif ( true === $remove ) {
                    acfcs_delete_file( $file_name );
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_do_something_with_file' );


    /**
     * Handle importing of raw CSV data
     */
    function acfcs_import_raw_data() {
        if ( isset( $_POST[ 'acfcs_import_raw_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_import_raw_nonce' ], 'acfcs-import-raw-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                $verified_data = acfcs_verify_csv_data( sanitize_textarea_field( $_POST[ 'acfcs_raw_csv_import' ] ) );
                if ( isset( $_POST[ 'acfcs_verify' ] ) ) {
                    if ( false != $verified_data ) {
                        ACF_City_Selector::acfcs_errors()->add( 'success_csv_valid', esc_html__( 'Congratulations, your CSV data seems valid.', 'acf-city-selector' ) );
                    }
                } elseif ( isset( $_POST[ 'acfcs_import' ] ) ) {
                    if ( false != $verified_data ) {
                        acfcs_import_data( $verified_data );
                    }
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_import_raw_data' );


    /**
     * Handle form to delete one or more countries
     */
    function acfcs_delete_countries() {
        if ( isset( $_POST[ 'acfcs_remove_countries_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_remove_countries_nonce' ], 'acfcs-remove-countries-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( empty( $_POST[ 'acfcs_delete_country' ] ) ) {
                    ACF_City_Selector::acfcs_errors()->add( 'error_no_country_selected', esc_html__( "You didn't select any countries, please try again.", 'acf-city-selector' ) );
                } else {
                    if ( is_array( $_POST[ 'acfcs_delete_country' ] ) ) {
                        acfcs_delete_country( $_POST[ 'acfcs_delete_country' ] );
                    }
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_delete_countries' );


    /**
     * Form to delete individual rows/cities
     */
    function acfcs_delete_rows() {
        if ( isset( $_POST[ 'acfcs_delete_row_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_delete_row_nonce' ], 'acfcs-delete-row-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                global $wpdb;
                if ( is_array( $_POST[ 'row_id' ] ) ) {
                    foreach( $_POST[ 'row_id' ] as $row ) {
                        $sanitized_row = sanitize_text_field( $row );
                        $split    = explode( ' ', $sanitized_row, 2 );
                        if ( isset( $split[ 0 ] ) && isset( $split[ 1 ] ) ) {
                            $ids[]    = $split[ 0 ];
                            $cities[] = $split[ 1 ];
                        }
                    }
					$cities  = implode( ', ', $cities );
					$row_ids = implode( ',', $ids );
					$table   = $wpdb->prefix . 'cities';
					$query   = $wpdb->prepare( "DELETE FROM {$table} WHERE id IN (%s)", $row_ids );
					$amount  = $wpdb->query( $query );

                    if ( $amount > 0 ) {
                        ACF_City_Selector::acfcs_errors()->add( 'success_row_delete', sprintf( _n( 'You have deleted the city %s.', 'You have deleted the following cities: %s.', $amount, 'acf-city-selector' ), $cities ) );
                    }
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_delete_rows' );


    /**
     * Form to handle deleting of all transients
     */
    function acfcs_delete_all_transients() {
        if ( isset( $_POST[ 'acfcs_delete_transients' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_delete_transients' ], 'acfcs-delete-transients-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                do_action( 'acfcs_delete_transients' );
                ACF_City_Selector::acfcs_errors()->add( 'success_transients_delete', esc_html__( 'You have successfully deleted all transients.', 'acf-city-selector' ) );
            }
        }
    }
    add_action( 'admin_init', 'acfcs_delete_all_transients' );


    /**
     * Delete contents of entire cities table
     */
    function acfcs_truncate_table() {
        if ( isset( $_POST[ 'acfcs_truncate_table_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_truncate_table_nonce' ], 'acfcs-truncate-table-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                global $wpdb;
                $prefix = $wpdb->get_blog_prefix();
                $wpdb->query( 'TRUNCATE TABLE ' . $prefix . 'cities' );
                ACF_City_Selector::acfcs_errors()->add( 'success_table_truncated', esc_html__( 'All cities are deleted.', 'acf-city-selector' ) );
                do_action( 'acfcs_after_success_nuke' );
            }
        }
    }
    add_action( 'admin_init', 'acfcs_truncate_table' );


    /**
     * Handle preserve settings option
     */
    function acfcs_delete_settings() {
        if ( isset( $_POST[ 'acfcs_remove_cities_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_remove_cities_nonce' ], 'acfcs-remove-cities-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( isset( $_POST[ 'remove_cities_table' ] ) ) {
                    update_option( 'acfcs_delete_cities_table', 1 );
                } else {
                    delete_option( 'acfcs_delete_cities_table' );
                }
                ACF_City_Selector::acfcs_errors()->add( 'success_settings_saved', esc_html__( 'Settings saved', 'acf-city-selector' ) );
            }
        }
    }
    add_action( 'admin_init', 'acfcs_delete_settings' );


    /**
     * Manually import default available countries
     */
    function acfcs_import_preset_countries() {
        if ( isset( $_POST[ 'acfcs_import_actions_nonce' ] ) ) {
            if ( ! wp_verify_nonce( $_POST[ 'acfcs_import_actions_nonce' ], 'acfcs-import-actions-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( isset( $_POST[ 'acfcs_import_be' ] ) || isset( $_POST[ 'acfcs_import_nl' ] ) ) {
                    if ( isset( $_POST[ 'acfcs_import_be' ] ) && 1 == $_POST[ 'acfcs_import_be' ] ) {
                        acfcs_import_data( 'be.csv', ACFCS_PLUGIN_PATH . 'import/' );
                        do_action( 'acfcs_delete_transients', 'be' );
                    }
                    if ( isset( $_POST[ 'acfcs_import_nl' ] ) && 1 == $_POST[ 'acfcs_import_nl' ] ) {
                        acfcs_import_data( 'nl.csv', ACFCS_PLUGIN_PATH . 'import/' );
                        do_action( 'acfcs_delete_transients', 'nl' );
                    }
                    do_action( 'acfcs_after_success_import' );
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_import_preset_countries' );
