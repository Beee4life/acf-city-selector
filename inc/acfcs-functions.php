<?php
    // function to check for field values
    include 'acfcs-field-settings.php';

    /**
     * Create an array with available countries from db.
     * This function makes use of a transient to speed up the process.
     *
     * @param false $show_first
     * @param false $field
     * @param false $force
     *
     * @return array
     */
    function acfcs_get_countries( $show_first = true, $field = false, $force = false ) {

        $countries            = [];
        $select_country_label = apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) );
        $show_labels          = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;

        if ( $show_first ) {
            if ( ! $show_labels ) {
                if ( false !== $select_country_label ) {
                    $countries[ '' ] = $select_country_label;
                } else {
                    $countries[ '' ] = '-';
                }
            } else {
                $countries[ '' ] = '-';
            }
        } else {
            // don't show first
        }

        $transient = get_transient( 'acfcs_countries' );
        if ( false != $force || false == $transient || is_array( $transient ) && empty( $transient ) ) {
            global $wpdb;
            $results = $wpdb->get_results( '
                SELECT * FROM ' . $wpdb->prefix . 'cities
                GROUP BY country
                ORDER BY country ASC
            ' );

            $country_results = [];
            foreach ( $results as $data ) {
                $country_results[ $data->country_code ] = esc_html__( $data->country, 'acf-city-selector' );
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
     * @param false $country_code
     * @param false $show_first
     * @param false $field
     *
     * @return array
     */
    function acfcs_get_states( $country_code = false, $show_first = true, $field = false ) {

        $select_province_state_label = apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) );
        $show_labels                 = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;
        $states                      = [];

        if ( $show_first ) {
            if ( $show_labels ) {
                $states[ '' ] = '-';
            } else {
                $states[ '' ] = $select_province_state_label;
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
                    WHERE country_code = %s
                    GROUP BY state_code
                    " . $order, strtoupper( $country_code )
                );
                $results = $wpdb->get_results( $sql );

                $state_results = array();
                foreach ( $results as $data ) {
                    $state_results[ $country_code . '-' . $data->state_code ] = esc_html__( $data->state_name, 'acf-city-selector' );
                }

                set_transient( 'acfcs_states_' . strtolower( $country_code ), $state_results, DAY_IN_SECONDS );

                $states = array_merge( $states, $state_results );

            } else {
                $states = array_merge( $states, $transient );
            }
        }

        return $states;
    }


    /**
     * Create an array with cities for a certain country/state
     *
     * @param false $country_code
     * @param false $state_code
     * @param false $field
     *
     * @return array
     */
    function acfcs_get_cities( $country_code = false, $state_code = false, $field = false ) {

        $cities            = [];
        $cities_transient  = false;
        $select_city_label = apply_filters( 'acfcs_select_city_label', esc_html__( 'Select a city', 'acf-city-selector' ) );
        $set_transient     = false;
        $show_labels       = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;

        if ( $show_labels ) {
            $cities[ '' ] = '-';
        } else {
            $cities[ '' ] = $select_city_label;
        }

        if ( $country_code && ! $state_code ) {
            $cities_transient = get_transient( 'acfcs_cities_' . strtolower( $country_code ) );
        } elseif ( $country_code && $state_code ) {
            $cities_transient = get_transient( 'acfcs_cities_' . strtolower( $country_code ) . '-' . strtolower( $state_code ) );
        }

        if ( false == $cities_transient || empty( $cities_transient ) ) {
            $set_transient = true;
        } else {
            foreach ( $cities_transient as $data ) {
                $city_array[ esc_attr__( $data, 'acf-city-selector' ) ] = esc_html__( $data, 'acf-city-selector' );
            }
            if ( isset( $city_array ) ) {
                $cities = array_merge( $cities, $city_array );
            }
        }

        if ( $set_transient ) {
            if ( false !== $country_code ) {
                global $wpdb;
                $query = 'SELECT * FROM ' . $wpdb->prefix . 'cities';
                if ( $country_code && $state_code ) {
                    if ( 3 < strlen( $state_code ) ) {
                        $state_code = substr( $state_code, 3 );
                    }
                    $query .= " WHERE country_code = '{$country_code}' AND state_code = '{$state_code}'";
                    $query .= " ORDER BY state_name, city_name ASC";
                } elseif ( $country_code ) {
                    $query .= " WHERE country_code = '{$country_code}'";
                }
                $city_results = [];
                $results      = $wpdb->get_results( $query );
                foreach ( $results as $data ) {
                    $city_results[] = [
                        'city_name' => esc_html__( $data->city_name, 'acf-city-selector' ),
                    ];
                }
                if ( ! empty( $city_results ) ) {
                    uasort( $city_results, 'acfcs_sort_array_with_quotes' );
                }
                foreach ( $city_results as $data ) {
                    $city_array[ esc_attr__( $data[ 'city_name' ], 'acf-city-selector' ) ] = esc_html__( $data[ 'city_name' ], 'acf-city-selector' );
                }
                if ( isset( $city_array ) ) {
                    $cities = array_merge( $cities, $city_array );
                }
                if ( ! $state_code ) {
                    set_transient( 'acfcs_cities_' . strtolower( $country_code ), $city_array, DAY_IN_SECONDS );
                } elseif ( $state_code ) {
                    set_transient( 'acfcs_cities_' . strtolower( $country_code ) . '-' . strtolower( $state_code ), $city_array, DAY_IN_SECONDS );
                }
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
            $country = $wpdb->get_row( $wpdb->prepare( "SELECT country FROM {$wpdb->prefix}cities WHERE country_code = %s", $country_code ) );
            if ( isset( $country->country ) ) {
                return $country->country;
            } else {
                if ( function_exists( 'acfcs_country_i18n' ) ) {
                    $country_name = acfcs_country_i18n( $country_code );
                    if ( $country_code != $country_name ) {
                        return $country_name;
                    }
                }
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
        $results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cities LIMIT 1' );

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
            $excluded_files = [
                '.',
                '..',
                '.DS_Store',
                'debug.json',
            ];

            if ( is_array( $file_index ) ) {
                $actual_files = [];
                foreach ( $file_index as $file ) {
                    if ( ! in_array( $file, $excluded_files ) ) {
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
     * @param false  $verify
     *
     * @return array
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
                        $error_message = esc_html__( 'Since your file is not accurate anymore, the file is deleted.', 'acf-city-selector' );
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html__( 'There are too few columns on line %d. %s', 'acf-city-selector' ), $line_number, $error_message ) );

                    } elseif ( count( $csv_line ) > $column_benchmark ) {
                        // if column count > benchmark
                        $error_message = esc_html__( 'Since your file is not accurate anymore, the file is deleted.', 'acf-city-selector' );
                        if ( false === $verify ) {
                            // for real
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html__( 'There are too many columns on line %d. %s', 'acf-city-selector' ), $line_number, $error_message ) );
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
     * @param false  $csv_data
     * @param string $delimiter
     *
     * @return false
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
     * Get packages through WP_Http
     *
     * @return array|mixed
     */
    function acfcs_get_packages( $endpoint = 'single' ) {

        $url     = ACFCS_WEBSITE_URL . '/wp-json/countries/v1/' . $endpoint;
        $request = new WP_Http;
        $result  = $request->request( $url, array( 'method' => 'GET' ) );
        if ( 200 == $result[ 'response' ][ 'code' ] ) {
            $response = json_decode( $result[ 'body' ] );

            return $response;
        }

        return [];
    }


    /**
     * Check depth of array
     *
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
    function acfcs_get_countries_info() {

        global $wpdb;
        $results = $wpdb->get_results( '
                SELECT country_code FROM ' . $wpdb->prefix . 'cities
                GROUP BY country_code
                ORDER BY country_code ASC
            ' );

        $acfcs_info = [];
        foreach ( $results as $data ) {
            $country_code = $data->country_code;
            $results      = $wpdb->get_results( $wpdb->prepare( '
                SELECT * FROM ' . $wpdb->prefix . 'cities
                WHERE country_code = %s
                ORDER BY country_code ASC
            ', $country_code ) );

            $acfcs_info[ $country_code ] = [
                'country_code' => $country_code,
                'count'        => count( $results ),
                'name'         => acfcs_get_country_name( $country_code ),
            ];
        }

        return $acfcs_info;
    }


    /**
     * Search an array which contains quotes like "'t Veld"
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    function acfcs_sort_array_with_quotes( $a, $b ) {
        return strnatcasecmp( acfcs_custom_sort_with_quotes( $a[ 'city_name' ] ), acfcs_custom_sort_with_quotes( $b[ 'city_name' ] ) );
    }


    /**
     * Sort with quotes
     *
     * @param $city
     *
     * @return string|string[]|null
     */
    function acfcs_custom_sort_with_quotes( $city ) {
        // strip quote marks
        $city = trim( $city, '\'s ' );
        $city = preg_replace( '/^\s*\'s \s+/i', '', $city );

        return $city;
    }


    /**
     * @param $type
     * @param $field
     * @param $stored_value
     * @param $prefill_values
     *
     * @return false|string
     */
    function acfcs_render_dropdown( $type, $field, $stored_value, $prefill_values ) {

        $acfcs_dropdown       = 'acfcs__dropdown';
        $city_label           = apply_filters( 'acfcs_select_city_label', esc_html__( 'Select a city', 'acf-city-selector' ) );
        $countries            = acfcs_get_countries( true, $field );
        $country_label        = apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) );
        $default_country      = ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
        $default_value        = false;
        $field_id             = $field[ 'id' ];
        $field_name           = $field[ 'name' ];
        $prefill_cities       = $prefill_values[ 'prefill_cities' ];
        $prefill_states       = $prefill_values[ 'prefill_states' ];
        $province_state_label = apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) );
        $selected_selected    = ' selected="selected"';
        $show_labels          = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;
        $use_select2          = ( strpos( $field[ 'prefix' ], 'acfcloneindex' ) !== false ) ? false : ( isset( $field[ 'use_select2' ] ) ) ? $field[ 'use_select2' ] : false;
        $dropdown_class       = ( true == $use_select2 ) ? 'select2 ' . $acfcs_dropdown : $acfcs_dropdown;
        $data_label_value     = ( true == $show_labels ) ? '1' : '0';

        switch( $type ) {
            case 'country':
                $default_value  = $default_country;
                $modifier       = 'countries';
                $field_label    = $country_label;
                $field_suffix   = 'countryCode';
                $selected_value = $stored_value;
                $values         = $countries;
                break;
            case 'state':
                $field_label    = $province_state_label;
                $modifier       = 'states';
                $field_suffix   = 'stateCode';
                $selected_value = $stored_value;
                $values         = $prefill_states;
                break;
            case 'city':
                $field_label    = $city_label;
                $modifier       = 'cities';
                $field_suffix   = 'cityName';
                $selected_value = $stored_value;
                $values         = $prefill_cities;
                break;
        }
        $dropdown_class = $dropdown_class . ' ' . $acfcs_dropdown . '--' . $modifier;

        ob_start();
        ?>
        <div class="acfcs__dropdown-box acfcs__dropdown-box--<?php echo $modifier; ?>">
            <?php if ( $show_labels ) { ?>
                <div class="acf-input-header">
                    <?php echo $field_label; ?>
                </div>
            <?php } ?>
            <label for="<?php echo $field_id . $field_suffix; ?>" class="screen-reader-text">
                <?php echo $field_label; ?>
            </label>
            <select name="<?php echo $field_name; ?>[<?php echo $field_suffix; ?>]" id="<?php echo $field_id . $field_suffix; ?>" class="<?php echo $dropdown_class; ?>" data-show-label="<?php echo $data_label_value; ?>">
                <?php
                    if ( ! empty( $values ) ) {
                        foreach ( $values as $key => $label ) {
                            $selected = false;
                            if ( false !== $selected_value ) {
                                $selected = ( $selected_value == $key ) ? $selected_selected : $selected;
                            } elseif ( ! empty( $default_value ) ) {
                                // only when a default country is set
                                $selected = ( $default_value == $key ) ? $selected_selected : $selected;
                            }
                            ?>
                            <option value="<?php echo $key; ?>"<?php echo $selected; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php
                        }
                    }
                ?>
            </select>
        </div>
        <?php
        $dropdown = ob_get_clean();

        return $dropdown;
    }
