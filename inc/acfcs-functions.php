<?php
    // function to check for field values
    include( 'acfcs-field-settings.php' );
    /**
     * Create an array with countries
     *
     * @param array $field
     *
     * @return array
     */
    function acfcs_populate_country_select( $field = [] ) {

        $show_labels = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : false;
        $countries   = acfcs_get_countries( true, $show_labels );

        return $countries;
    }


    /**
     * Create an array with states based on a country code
     *
     * @param array $field
     *
     * @return array
     */
    function acfcs_populate_state_select( $country_code = false, $field = [] ) {

        $show_labels = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : false;
        $countries   = acfcs_get_states( $country_code, true, $show_labels );

        return $countries;
    }


    /**
     * Create an array with cities for a specific province/state
     *
     * @param array $field
     *
     * @return array
     */
    function acfcs_populate_city_select( $country_code = false, $state_code = false, $field = [] ) {

        $show_labels = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : false;
        $countries   = acfcs_get_cities( $country_code, $state_code, $show_labels );

        return $countries;
    }


    /**
     * Create an array with available countries from db.
     * This function makes use of a transient to speed up the process.
     *
     * @param bool $show_first
     * @param bool $show_labels
     *
     * @return array
     */
    function acfcs_get_countries( $show_first = false, $show_labels = false ) {

        $countries = [];
        if ( false !== $show_first ) {
            if ( false != $show_labels ) {
                $countries[ '' ] = '-';
            } else {
                $countries[ '' ] = esc_html__( 'Select a country', 'acf-city-selector' );
            }
        }

        $transient = get_transient( 'acfcs_countries' );
        if ( false == $transient || is_array( $transient ) && empty( $transient ) ) {
            global $wpdb;
            $results = $wpdb->get_results( '
                SELECT * FROM ' . $wpdb->prefix . 'cities
                GROUP BY country
                ORDER BY country ASC
            ' );

            $country_results = [];
            foreach ( $results as $data ) {
                $country_results[ $data->country_code ] = __( $data->country, 'acf-city-selector' );
            }

            set_transient( 'acfcs_countries', $country_results, DAY_IN_SECONDS );
            $countries = array_merge( $countries, $country_results );

        } elseif ( is_array( $transient ) ) {
            $countries = array_merge( $countries, $transient );
        }

        return $countries;
    }


    /**
     * Create an array with states based on a country code
     *
     * @param array $field
     *
     * @return array
     */
    function acfcs_get_states( $country_code = false, $show_first = false, $show_labels = false ) {

        $states = [];
        if ( false !== $show_first ) {
            if ( false != $show_labels ) {
                $states[ '' ] = '-';
            } else {
                $states[ '' ] = esc_html__( 'Select a country first', 'acf-city-selector' ); // when does this show
            }
        }

        if ( false != $country_code ) {
            $transient = get_transient( 'acfcs_states_' . strtolower( $country_code ) );
            if ( false == $transient || is_array( $transient ) && empty( $transient ) ) {
                $order = 'ORDER BY state_name ASC';
                if ( 'FR' == $country_code ) {
                    $order = "ORDER BY LENGTH(state_name), state_name";
                }

                global $wpdb;
                $sql = $wpdb->prepare( "
                    SELECT *
                    FROM " . $wpdb->prefix . "cities
                    WHERE country_code = '%s'
                    GROUP BY state_code
                    " . $order, strtoupper( $country_code )
                );
                $results = $wpdb->get_results( $sql );

                $state_results = array();
                foreach ( $results as $data ) {
                    $state_results[ $country_code . '-' . $data->state_code ] = __( $data->state_name, 'acf_city_selector' );
                }

                set_transient( 'acfcs_states_' . strtolower( $country_code ), $state_results, DAY_IN_SECONDS );

            } else {
                $states = array_merge( $states, $transient );
            }
        }

        return $states;
    }

    /**
     * Create an array with cities for a certain country/state
     *
     * @param bool $country_code
     * @param bool $state_code
     *
     * @return array
     */
    function acfcs_get_cities( $country_code = false, $state_code = false, $show_labels = false ) {

        $cities = [];
        if ( 0 != $show_labels ) {
            $cities[ '' ] = '-';
        } else {
            $cities[ '' ] = esc_html__( 'Select a city', 'acf-city-selector' );
        }

        // @TODO: MAYBE add transient
        if ( false !== $country_code ) {
            global $wpdb;
            $query = 'SELECT * FROM ' . $wpdb->prefix . 'cities';
            if ( $country_code && $state_code ) {
                if ( 3 < strlen( $state_code ) ) {
                    $state_code = substr( $state_code, 3 );
                }
                $query .= " WHERE country_code = '{$country_code}' AND state_code = '{$state_code}'";
            } elseif ( $country_code ) {
                $query .= " WHERE country_code = '{$country_code}'";
            }
            $query        .= " ORDER BY state_name, city_name ASC";
            $results      = $wpdb->get_results( $query );
            $city_counter = 1;
            foreach ( $results as $data ) {
                $city_results[ $data->city_name ] = __( $data->city_name, 'acf_city_selector' );
                $city_counter++;
            }
            if ( isset( $city_results ) ) {
                $cities = array_merge( $cities, $city_results );
            }
        }

        return $cities;

    }


    /**
     * Get country name by country code (used in search)
     *
     * @param $country_code
     *
     * @return mixed
     */
    function acfcs_get_country_name( $country_code = false ) {

        if ( false != $country_code ) {
            global $wpdb;
            $country = $wpdb->get_row( "SELECT country FROM {$wpdb->prefix}cities WHERE country_code = '{$country_code}'" );
            if ( isset( $country->country ) ) {
                return $country->country;
            }
        }

        return $country_code;

    }


    /**
     * Checks if there any cities in the database (for page availability)
     *
     * @return bool
     */
    function acfcs_has_cities() {
        global $wpdb;
        $results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cities LIMIT 2 ' );

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
        if ( is_dir( $target_dir ) ) {
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
    function acfcs_csv_to_array( $file_name, $delimiter = ',', $verify = false ) {

        $csv_array   = [];
        $empty_array = false;
        $new_array   = [];
        if ( ( $handle = fopen( wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name, "r" ) ) !== false ) {
            $column_benchmark = 5;
            $line_number      = 0;
            while ( ( $csv_line = fgetcsv( $handle, apply_filters( 'acfcs_line_length', 1000 ), "{$delimiter}" ) ) !== false ) {
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
                    if ( file_exists( wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name ) ) {
                        unlink( wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name );
                    }

                }

                if ( ACF_City_Selector::acfcs_errors()->get_error_codes() ) {
                    $empty_array = true;
                    $new_array   = [];
                } else {
                    // create a new array for each row
                    $new_line = [];
                    foreach ( $csv_line as $item ) {
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
            if ( ! empty( $new_array ) && false === $empty_array ) {
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
                // @TODO: check if this is still needed since an array is not outputted anymore
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
     * Get packages through REST
     *
     * @param bool $retry
     *
     * @return array
     */
    function acfcs_get_packages( $retry = false ) {
        try {
            $handle = curl_init();
            $url    = ACFCS_WEBSITE_URL . '/wp-json/packages/v1/all';
            curl_setopt( $handle, CURLOPT_URL, $url );
            curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
            $response = json_decode( curl_exec( $handle ) );
            curl_close( $handle );
        }
        catch (\Exception $e) {
            $response = [];
        }

        return $response;
    }

    /**
     * Check depth of array
     * @param $array
     *
     * @return int|mixed
     */
    function acfcs_check_array_depth( $array ) {
        $max_depth = 1;

        foreach( $array as $value ) {
            if ( is_array( $value ) ) {
                $depth = acfcs_check_array_depth( $value ) + 1;

                if ( $depth > $max_depth ) {
                    $max_depth = $depth;
                }
            }
        }

        return $max_depth;
    }


    /**
     * Get country info for debug
     *
     * @return array
     */
    function acfcs_get_country_info() {

        global $wpdb;
        $results = $wpdb->get_results( '
                SELECT country_code FROM ' . $wpdb->prefix . 'cities
                GROUP BY country_code
                ORDER BY country_code ASC
            ' );

        $acfcs_info = [];
        foreach ( $results as $data ) {
            $country_code                = $data->country_code;
            $results                     = $wpdb->get_results( '
                SELECT * FROM ' . $wpdb->prefix . 'cities
                WHERE country_code = \'' . $country_code . '\'
                ORDER BY country_code ASC
            ' );
            $acfcs_info[ $country_code ] = [
                'count' => count( $results ),
                'name'  => acfcs_get_country_name( $country_code ),
            ];
        }

        return $acfcs_info;
    }
