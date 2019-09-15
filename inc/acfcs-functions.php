<?php
    /**
     * Checks if there any cities in the database
     *
     * @return bool
     */
    function acfcs_has_cities() {
        global $wpdb;
        $results = $wpdb->get_results( "SELECT *
            FROM " . $wpdb->prefix . "cities
            LIMIT 5
        " );
        
        if ( count( $results ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Checks if files are uploaded
     *
     * @return array
     */
    function acfcs_check_if_files() {
        
        $target_dir = wp_upload_dir()[ 'basedir' ] . '/acfcs';
        $file_index = scandir( $target_dir );
        
        if ( is_array( $file_index ) ) {
            $actual_files = [];
            foreach ( $file_index as $file ) {
                if ( '.DS_Store' != $file && '.' != $file && '..' != $file ) {
                    $actual_files[] = $file;
                }
            }
            if ( ! empty( $actual_files ) ) {
                return $actual_files;
            }
        }
        
        return [];
    }
    
    /**
     * Convert data from an uploaded CSV file to an array
     *
     * @param        $file_name
     * @param string $delimiter
     * @param bool   $verify
     * @param bool   $preview
     *
     * @return array|bool
     */
    function acfcs_csv_to_array( $file_name, $delimiter = ",", $verify = false ) {
    
        $csv_array   = [];
        $empty_array = false;
        $new_array   = [];
        if ( ( $handle = fopen( wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name, "r" ) ) !== false ) {
            $column_benchmark = 5;
            $line_number      = 0;
            $value_length     = 254;
            while ( ( $csv_line = fgetcsv( $handle, 1000, "{$delimiter}" ) ) !== false ) {
                $line_number++;
                $csv_array[ 'delimiter' ] = $delimiter;
                
                // if column count doesn't match benchmark
                if ( count( $csv_line ) != $column_benchmark ) {
                    // if column count < benchmark
                    if ( count( $csv_line ) < $column_benchmark ) {
                        $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'acf-city-selector' ) );
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'acf-city-selector' ), $line_number, $error_message ) );
                        
                    } elseif ( count( $csv_line ) > $column_benchmark ) {
                        // if column count > benchmark
                        $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'acf-city-selector' ) );
                        if ( false === $verify ) {
                            // for real
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html( __( 'There are too many columns on line %d. %s', 'acf-city-selector' ) ), $line_number, $error_message ) );
                    }
                    // delete file
                    unlink( wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name );
                    
                }
                
                if ( ACF_City_Selector::acfcs_errors()->get_error_codes() ) {
                    $empty_array = true;
                    $new_array   = [];
                } else {
                    // create a new array for each row
                    $new_line = [];
                    foreach ( $csv_line as $item ) {
                        
                        if ( strlen( $item ) > $value_length ) {
                            ACF_City_Selector::acfcs_errors()->add( 'error_too_long_value', esc_html( sprintf( __( "The value '%s' is too long.", "acf-city-selector" ), $item ) ) );
                            
                            return false;
                        }
                        $new_line[] = $item;
                    }
                    if ( ! empty( $new_line ) ) {
                        $new_array[] = $new_line;
                    }
                }
            }
            fclose( $handle );
            
            /**
             * Don't add data if there are any errors. This to prevent rows which had no error from outputting
             * on the preview page.
             */
            if ( ! empty( $new_array ) && false == $empty_array ) {
                $csv_array[ 'data' ] = array_values( $new_array );
            }
        }
        
        return $csv_array;
    }
    
    
    /**
     * Verify raw csv import
     *
     * @param bool $csv_data
     * @return array|bool
     */
    function acfcs_verify_csv_data( $csv_data = false, $delimiter = "," ) {

        if ( false != $csv_data ) {

            if ( is_array( $csv_data ) ) {
                // @TODO: is this still needed since an array is not outputted anymore
                $lines = $csv_data;
            } else {
                $lines = explode( "\r\n", $csv_data );
            }

            $line_number      = 0;
            $column_benchmark = 5;

            foreach ( $lines as $line ) {
                $line_number++;
                
                if ( ! is_array( $csv_data ) ) {
                    $line_array = explode( ",", $line );
                }
                
                if ( count( $line_array ) != $column_benchmark ) {
                    // length of a line is not correct
                    if ( count( $line_array ) < $column_benchmark ) {
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html__( 'There are too few columns on line %d.', 'acf-city-selector' ), $line_number ) );

                        return false;

                    } elseif ( count( $line_array ) > $column_benchmark ) {
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html__( 'There are too many columns on line %d.', 'acf-city-selector' ), $line_number ) );

                        return false;
                    }
                }

                $element_counter = 0;
                foreach( $line_array as $element ) {
                    $element_counter++;
                    if ( $element_counter == 2 ) {
                        if ( 2 != strlen( $element ) ) {
                            ACF_City_Selector::acfcs_errors()->add( 'error_wrong_state_length', sprintf( esc_html__( 'The length of the state abbreviation on line %d is incorrect.', 'acf-city-selector' ), $line_number ) );

                            return false;
                        }
                    }
                    if ( $element_counter == 4 ) {
                        if ( 2 != strlen( $element ) ) {
                            ACF_City_Selector::acfcs_errors()->add( 'error_wrong_country_length', sprintf( esc_html__( 'The length of the country abbreviation on line %d is incorrect.', 'acf-city-selector' ), $line_number ) );

                            return false;
                        }
                    }
                }
                $validated_data[] = $line_array;
            }

            return $validated_data;
        }

        return false;
    }

    /**
     * Read file and spit out an array
     * @TODO: probably delete ?
     *
     * @return array|bool
     */
    function acfcs_read_file_only( $file_name = false ) {

        $csv_array = array();
        if ( false != $file_name ) {
    
            $file_location = wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name;
            if ( ( $handle = fopen( $file_location, "r" ) ) !== false ) {
                $line_number = 0;

                while (($csv_line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $line_number++;
                    $csv_array[] = $csv_line;
                }
                fclose( $handle );
            }
    
            return $csv_array;
        }

        return false;
    }
