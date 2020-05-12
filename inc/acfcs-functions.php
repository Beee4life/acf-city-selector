<?php

    /**
     * Create an array with states based on a country code
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
     * Create an array with states based on a country code
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

            foreach ( $results as $data ) {
                $countries[ $data->country_code ] = __( $data->country, 'acf-city-selector' );
            }

            set_transient( 'acfcs_countries', $countries, DAY_IN_SECONDS );

        } else {
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
                $countries[ '' ] = '-';
            } else {
                $countries[ '' ] = esc_html__( 'Select a country first', 'acf-city-selector' );
            }
        }

        if ( false != $country_code ) {
            global $wpdb;
            if ( isset( $country_code ) ) {
                $sql = $wpdb->prepare( "
                    SELECT *
                    FROM " . $wpdb->prefix . "cities
                    WHERE country_code = '%s'
                    GROUP BY state_code
                    ORDER BY state_name ASC",  strtoupper( $country_code )
                );
                $results = $wpdb->get_results( $sql );

                $states = array();
                foreach ( $results as $data ) {
                    $states[ $country_code . '-' . $data->state_code ] = $data->state_name;
                }
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
        if ( false != $show_labels ) {
            $cities[ '' ] = '-';
        } else {
            $cities[ '' ] = esc_html__( 'Select a city', 'acf-city-selector' );
        }

        if ( false !== $country_code ) {
            global $wpdb;
            $cities = array();
            $query = 'SELECT * FROM ' . $wpdb->prefix . 'cities';
            if ( $country_code && $state_code ) {
                $query .= " WHERE country_code = '{$country_code}' AND state_code = '{$state_code}'";
            } elseif ( $country_code ) {
                $query .= " WHERE country_code = '{$country_code}'";
            }
            $query   .= " order by state_name, city_name ASC";
            $results = $wpdb->get_results( $query );

            foreach ( $results as $data ) {
                $cities[ $data->city_name ] = $data->city_name;
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
    function acfcs_csv_to_array( $file_name, $delimiter = ",", $verify = false ) {

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
                    unlink( wp_upload_dir()[ 'basedir' ] . '/acfcs/' . $file_name );

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
     * Return the meta values for a post/user
     * @TODO: remove
     *
     * @return array
     */
    function acfcs_get_meta_values() {

        $activate  = false;
        $meta_values = [];

        if ( isset( $_GET[ 'user_id' ] ) ) {
            $activate = true;
            $user_id  = $_GET[ 'user_id' ];
        } elseif ( isset( $_GET[ 'post' ] ) ) {
            $post_id = $_GET[ 'post' ];
            if ( 'acf-field-group' != get_post_type( $post_id ) ) {
                $activate = true;
            }
        } elseif ( isset( $_GET[ 'id' ] ) ) {
            // this is for my own project
            $activate = true;
            $post_id  = $_GET[ 'id' ];
        } else {
            $activate = true;
            if ( defined( 'IS_PROFILE_PAGE' ) ) {
                $user_id = get_current_user_id();
            } else {
                $post_id = get_the_ID();
            }
        }

        if ( false != $activate ) {
            if ( isset( $user_id ) && false !== $user_id ) {
                $fields = get_field_objects( 'user_' . $user_id );
            } elseif ( isset( $post_id ) && false !== $post_id ) {
                $fields = get_field_objects( $post_id ); // all fields incl. index (in case of multiple fields)
            }

            /*
             * Get the field['name'] for the City Selector field
             */
            if ( isset( $fields ) && is_array( $fields ) && count( $fields ) > 0 ) {

                foreach( $fields as $field ) {
                    if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                        $field_name = $field[ 'name' ];
                        break;
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'repeater' ) {
                        $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                        if ( false !== $array_key ) {
                            $city_selector_name = $field[ 'sub_fields' ][ $array_key ][ 'name' ];
                            $repeater_name      = $field[ 'name' ];
                            $repeater_count     = get_post_meta( $post_id, $repeater_name, true );
                            break;
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'group' ) {
                        // get acfcs field_names
                        if ( ! empty( $field[ 'value' ] ) ) {
                            foreach( $field[ 'value' ] as $key => $values ) {
                                if ( is_array( $values ) ) {
                                    $index = array_key_exists( 'countryCode', $values );
                                    if ( true === $index ) {
                                        $field_name = $field[ 'name' ] . '_' . $key;
                                        break;
                                    }
                                }
                            }
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'flexible_content' ) {
                        $flexible_name                 = $field[ 'name' ]; // @TODO: add to return
                        $flexible_content_block_values = $field[ 'value' ];
                        $flexible_names                = [];
                        $flexible_layouts              = $field[ 'layouts' ];

                        foreach( $flexible_layouts as $layout ) {
                            $sub_fields = $layout[ 'sub_fields' ];
                            foreach( $sub_fields as $subfield ) {
                                // if type == acfcs, add name to array
                                if ( 'acf_city_selector' == $subfield[ 'type' ] ) {
                                    // This is to prepare for multiple flex blocks
                                    // $flexible_names[ $flexible_name ][ $layout[ 'name' ] ] = $subfield[ 'name' ];
                                    $flexible_names[ $layout[ 'name' ] ] = $subfield[ 'name' ];
                                }
                            }
                        }

                        $layout_index = 0;
                        $meta_keys    = [];
                        if ( is_array( $flexible_content_block_values ) ) {
                            foreach( $flexible_content_block_values as $values ) {
                                if ( array_key_exists( $values[ 'acf_fc_layout' ], $flexible_names ) ) {
                                    $meta_keys[ $layout_index ] = $flexible_name . '_' . $layout_index . '_' . $flexible_names[ $values[ 'acf_fc_layout' ] ];
                                }
                                $layout_index++;
                            }
                        }
                    }
                }
            }

            /*
             * Get post_meta
             */
            if ( isset( $repeater_count ) && 0 < $repeater_count ) {
                for( $i = 0; $i < $repeater_count; $i++ ) {
                    $repeater_field_name = $repeater_name . '_' . $i . '_' . $city_selector_name;
                    $post_meta[]         = get_post_meta( $post_id, $repeater_field_name, true );
                }

                if ( isset( $post_meta ) && ! empty( $post_meta ) ) {
                    foreach( $post_meta as $meta ) {
                        $meta_values[] = array(
                            'countryCode' => ( isset( $meta[ 'countryCode' ] ) ) ? $meta[ 'countryCode' ] : '',
                            'stateCode'   => ( isset( $meta[ 'stateCode' ] ) ) ? $meta[ 'stateCode' ] : '',
                            'cityName'    => ( isset( $meta[ 'cityName' ] ) ) ? $meta[ 'cityName' ] : '',
                        );
                    }
                }
            } else {
                if ( isset( $field_name ) ) {
                    if ( isset( $user_id ) ) {
                        // user page
                        $meta_values = get_user_meta( $user_id, $field_name, true );
                    } elseif ( isset( $post_id ) ) {
                        // single/group
                        $post_meta = get_post_meta( $post_id, $field_name, true );
                        if ( ! empty( $post_meta[ 'cityName' ] ) ) {
                            $meta_values = array(
                                'countryCode' => ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : '',
                                'stateCode'   => ( isset( $post_meta[ 'stateCode' ] ) ) ? $post_meta[ 'stateCode' ] : '',
                                'cityName'    => ( isset( $post_meta[ 'cityName' ] ) ) ? $post_meta[ 'cityName' ] : '',
                            );
                        }
                    }
                } elseif ( isset( $flexible_name ) ) {
                    // flexible content
                    if ( isset( $meta_keys ) && is_array( $meta_keys ) && ! empty( $meta_keys ) ) {
                        foreach( $meta_keys as $key => $value ) {
                            $meta_values[ $key ] = get_post_meta( $post_id, $value, true );
                        }
                    }
                }
            }

        }

        return $meta_values;

    }
