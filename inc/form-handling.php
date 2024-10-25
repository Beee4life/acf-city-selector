<?php
    /**
     * Handle CSV upload form
     */
    function acfcs_upload_csv_file() {
        if ( isset( $_POST[ 'acfcs_upload_csv_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_upload_csv_nonce' ] ) ), 'acfcs-upload-csv-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                ACF_City_Selector::acfcs_check_uploads_folder();
                $local_file_path = isset( $_FILES[ 'acfcs_csv_upload' ][ 'name' ] ) ? acfcs_upload_folder( '/' ) . basename( sanitize_text_field( $_FILES[ 'acfcs_csv_upload' ][ 'name' ] ) ) : '';
                $file_type       = wp_check_filetype( basename( $local_file_path ), null );

                if ( 'text/csv' === $file_type[ 'type' ] && isset( $_FILES[ 'acfcs_csv_upload' ][ 'tmp_name' ] ) ) {
                    if ( copy( sanitize_text_field( $_FILES[ 'acfcs_csv_upload' ][ 'tmp_name' ] ), $local_file_path ) ) {
                        $file_data = [
                            'file_name'     => basename( $local_file_path ),
                            'file_location' => $local_file_path,
                        ];
                        $attachment_args = [
                            'guid'        => wp_upload_dir()[ 'url' ] . '/' . basename( $local_file_path ),
                            'post_title'  => $file_data[ 'file_name' ],
                            'post_author' => get_current_user_id(),
                            'post_date'   => gmdate( 'Y-m-d H:i:s' ),
                            'post_status' => 'inherit',
                        ];
                        $attachment_id = wp_insert_attachment( $attachment_args, $local_file_path );
                        if ( ! is_wp_error( $attachment_id ) && 0 < $attachment_id ) {
                            /* translators: %s file name */
                            ACF_City_Selector::acfcs_errors()->add( 'success_file_uploaded', sprintf( esc_html__( "File '%s' is successfully uploaded and now shows under 'Select files to import'", 'acf-city-selector' ), sanitize_text_field( wp_unslash( $_FILES[ 'acfcs_csv_upload' ][ 'name' ] ) ) ) );
                            do_action( 'acfcs_after_success_file_upload' );
                        } else {
                            ACF_City_Selector::acfcs_errors()->add( 'error_file_uploaded', esc_html__( 'Upload failed. Please try again.', 'acf-city-selector' ) );
                        }
                    }
                } else {
                    ACF_City_Selector::acfcs_errors()->add( 'error_no_csv', esc_html__( 'Only csv files are allowed.', 'acf-city-selector' ) );
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_select_file_nonce' ] ) ), 'acfcs-select-file-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( empty( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_file_name' ] ) ) ) ) {
                    ACF_City_Selector::acfcs_errors()->add( 'error_no_file_selected', esc_html__( "You didn't select a file.", 'acf-city-selector' ) );

                    return;
                }

                $file_name = sanitize_text_field( wp_unslash( $_POST[ 'acfcs_file_name' ] ) );
                $delimiter = ! empty( $_POST[ 'acfcs_delimiter' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_delimiter' ] ) ) : apply_filters( 'acfcs_delimiter', ';' );
                $import    = isset( $_POST[ 'acfcs_import' ] ) ? true : false;
                $max_lines = isset( $_POST[ 'acfcs_max_lines' ] ) ? (int) $_POST[ 'acfcs_max_lines' ] : false;
                $remove    = isset( $_POST[ 'acfcs_remove' ] ) ? true : false;
                $verify    = isset( $_POST[ 'acfcs_verify' ] ) ? true : false;

                if ( true === $verify ) {
                    acfcs_verify_data( $file_name, $delimiter, $verify );
                } elseif ( true === $import ) {
                    acfcs_import_data( $file_name, '', $delimiter, $verify, $max_lines );
                } elseif ( true === $remove ) {
                    do_action( 'acfcs_delete_file', $file_name );
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_import_raw_nonce' ] ) ), 'acfcs-import-raw-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                $verified_data = isset( $_POST[ 'acfcs_raw_csv_import' ] ) ? acfcs_verify_csv_data( sanitize_textarea_field( wp_unslash( $_POST[ 'acfcs_raw_csv_import' ] ) ) ) : [];
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_remove_countries_nonce' ] ) ), 'acfcs-remove-countries-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( empty( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_delete_country' ] ) ) ) ) {
                    ACF_City_Selector::acfcs_errors()->add( 'error_no_country_selected', esc_html__( "You didn't select any countries, please try again.", 'acf-city-selector' ) );
                } else {
                    if ( is_array( $_POST[ 'acfcs_delete_country' ] ) ) {
                        acfcs_delete_country( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_delete_country' ] ) ) );
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_delete_row_nonce' ] ) ), 'acfcs-delete-row-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                global $wpdb;
                if ( isset( $_POST[ 'row_id' ] ) && is_array( $_POST[ 'row_id' ] ) ) {
                    // @TODO: test this
                    foreach( sanitize_text_field( wp_unslash( $_POST[ 'row_id' ] ) ) as $row ) {
                        $sanitized_row = sanitize_text_field( $row );
                        $split         = explode( ' ', $sanitized_row, 2 );
                        
                        if ( isset( $split[ 0 ] ) && isset( $split[ 1 ] ) ) {
                            $ids[]    = $split[ 0 ];
                            $cities[] = $split[ 1 ];
                        }
                    }
                    
                    $city_string = implode( ', ', $cities );
                    $row_ids     = implode( ',', $ids );
                    $table       = $wpdb->prefix . 'cities';
                    $amount      = $wpdb->query( $wpdb->prepare( "DELETE FROM %i WHERE id IN (%s)", $table, $row_ids ) );

                    if ( $amount > 0 ) {
                        /* translators: 1 city name, 2 city names */
                        ACF_City_Selector::acfcs_errors()->add( 'success_row_delete', sprintf( _n( 'You have deleted the city %s.', 'You have deleted the following cities: %s.', count($cities), 'acf-city-selector' ), $city_string ) );
                    }
                }
                
            }
        }
    }
    add_action( 'admin_init', 'acfcs_delete_rows' );


    /**
     * Delete contents of entire cities table
     */
    function acfcs_truncate_table() {
        if ( isset( $_POST[ 'acfcs_truncate_table_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_truncate_table_nonce' ] ) ), 'acfcs-truncate-table-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                global $wpdb;
                $table = $wpdb->prefix . 'cities';
                $wpdb->query( $wpdb->prepare( "TRUNCATE TABLE %i", $table ) );
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_remove_cities_nonce' ] ) ), 'acfcs-remove-cities-nonce' ) ) {
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_import_actions_nonce' ] ) ), 'acfcs-import-actions-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                if ( isset( $_POST[ 'acfcs_import_be' ] ) || isset( $_POST[ 'acfcs_import_nl' ] ) ) {
                    if ( isset( $_POST[ 'acfcs_import_be' ] ) && 1 == $_POST[ 'acfcs_import_be' ] ) {
                        acfcs_import_data( 'be.csv', ACFCS_PLUGIN_PATH . 'import/' );
                    }
                    if ( isset( $_POST[ 'acfcs_import_nl' ] ) && 1 == $_POST[ 'acfcs_import_nl' ] ) {
                        acfcs_import_data( 'nl.csv', ACFCS_PLUGIN_PATH . 'import/' );
                    }
                    do_action( 'acfcs_after_success_import' );
                }
            }
        }
    }
    add_action( 'admin_init', 'acfcs_import_preset_countries' );
