<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

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
        $countries            = array();
        $select_country_label = apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) );
        $show_labels          = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;

        if ( $show_first ) {
            $countries[ '' ] = '-';
            if ( ! $show_labels ) {
                $countries[ '' ] = $select_country_label;
            }
        }

		global $wpdb;
		$table   = $wpdb->prefix . 'cities';
		$results = $wpdb->get_results( "SELECT * FROM $table GROUP BY country ORDER BY country ASC" );

        if ( ! empty( $results ) ) {
            $country_results = array();
            foreach ( $results as $data ) {
                if ( isset( $data->country_code ) && isset( $data->country ) ) {
                    $country_results[ $data->country_code ] = esc_html__( $data->country, 'acf-city-selector' );
                }
            }
            $countries = array_merge( $countries, $country_results );
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
        $select_province_state_label = apply_filters( 'acfcs_select_province_state_label', esc_attr__( 'Select a province/state', 'acf-city-selector' ) );
        $show_labels                 = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;
        $states                      = array();

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
                $order = ' ORDER BY state_name ASC';
                if ( 'FR' == $country_code ) {
                    $order = " ORDER BY LENGTH(state_name), state_name";
                }

				global $wpdb;
				$table   = $wpdb->prefix . 'cities';
				$sql     = $wpdb->prepare( "SELECT * FROM $table WHERE country_code = %s GROUP BY state_code" . $order, strtoupper( $country_code ) );
				$results = $wpdb->get_results( $sql );

                $state_results = array();
                foreach ( $results as $data ) {
                    $state_results[ strtoupper( $country_code ) . '-' . $data->state_code ] = esc_attr__( $data->state_name, 'acf-city-selector' );
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
        $cities            = array();
        $cities_transient  = false;
        $select_city_label = apply_filters( 'acfcs_select_city_label', esc_attr__( 'Select a city', 'acf-city-selector' ) );
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
                $city_array[ esc_attr__( $data, 'acf-city-selector' ) ] = esc_attr__( $data, 'acf-city-selector' );
            }
            if ( isset( $city_array ) ) {
                $cities = array_merge( $cities, $city_array );
            }
        }

        if ( $set_transient ) {
            if ( false !== $country_code ) {
                global $wpdb;
				$table = $wpdb->prefix . 'cities';
				$query = "SELECT * FROM $table";
                if ( $country_code && $state_code ) {
                    if ( 3 < strlen( $state_code ) ) {
                        $state_code = substr( $state_code, 3 );
                    }
                    $query = $wpdb->prepare( $query . " WHERE country_code = %s AND state_code = %s ORDER BY state_name, city_name ASC", $country_code, $state_code );
                } elseif ( $country_code ) {
                    $query = $wpdb->prepare( $query . " WHERE country_code = %s", $country_code );
                }
                $city_results = array();
                $results      = $wpdb->get_results( $query );
                foreach ( $results as $data ) {
                    $city_results[] = [
                        'city_name' => $data->city_name,
                    ];
                }

                if ( ! empty( $city_results ) ) {
                    uasort( $city_results, 'acfcs_sort_array_with_quotes' );
                }
                foreach ( $city_results as $data ) {
                    $city_array[ esc_attr__( $data[ 'city_name' ], 'acf-city-selector' ) ] = esc_attr__( $data[ 'city_name' ], 'acf-city-selector' );
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
     * Get country name by country code
     *
     * @param $country_code
     *
     * @return mixed
     */
    function acfcs_get_country_name( $country_code = false ) {
        if ( false != $country_code ) {
			global $wpdb;
			$table   = $wpdb->prefix . 'cities';
			$query   = $wpdb->prepare( "SELECT country FROM $table WHERE country_code = %s", $country_code );
			$country = $wpdb->get_row( $query );

            if ( isset( $country->country ) ) {
                return $country->country;
            } else {
                $country_name = acfcs_country_i18n( $country_code );
                if ( $country_code != $country_name ) {
                    return $country_name;
                }
            }
        }

        return $country_code;
    }


	/**
     * Checks if there are any cities in the database (for page availability)
	 *
	 * @param $country_code
	 *
	 * @return bool
	 */
    function acfcs_has_cities( $country_code = false ) {
        global $wpdb;
		$table = $wpdb->prefix . 'cities';
		$query = "SELECT * FROM $table LIMIT 1";
		if ( $country_code ) {
			$query = $wpdb->prepare( "SELECT * FROM $table WHERE country_code = %s LIMIT 1", $country_code );
		}

        $results = $wpdb->get_results( $query );

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
		$actual_files = [];
		$target_dir   = acfcs_upload_folder();

		if ( is_dir( $target_dir ) ) {
            $file_index = scandir( $target_dir );
            $excluded_files = [
                '.',
                '..',
                '.DS_Store',
                'debug.json',
            ];

            if ( is_array( $file_index ) ) {
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

        return $actual_files;
    }


    /**
     * Convert data from an uploaded CSV file to an array
     *
     * @param        $file_name
     * @param string $delimiter
     * @param string $upload_folder
     * @param false  $verify
     * @param false  $max_lines
     *
     * @return array|WP_Error
     */
    function acfcs_csv_to_array( $file_name, $upload_folder = '', $delimiter = ';', $verify = false, $max_lines = false ) {
        $upload_folder = ( ! empty( $upload_folder ) ) ? $upload_folder : acfcs_upload_folder( '/' );
        $csv_array     = array();
        $empty_array   = false;
        $errors        = ACF_City_Selector::acfcs_errors();
        $new_array     = array();

        if ( ( file_exists( $upload_folder . $file_name ) && $handle = fopen( $upload_folder . $file_name, "r" ) ) !== false ) {
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
                        $errors->add( 'error_no_correct_columns_' . $line_number, sprintf( esc_html__( 'There are too few columns on line %d. %s', 'acf-city-selector' ), $line_number, $error_message ) );

                    } elseif ( count( $csv_line ) > $column_benchmark ) {
                        // if column count > benchmark
                        $error_message = esc_html__( 'Since your file is not accurate anymore, the file is deleted.', 'acf-city-selector' );
                        if ( false === $verify ) {
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        $errors->add( 'error_no_correct_columns_' . $line_number, sprintf( esc_html__( 'There are too many columns on line %d. %s', 'acf-city-selector' ), $line_number, $error_message ) );
                    }
                }

                if ( $errors->has_errors() ) {
                    $empty_array = true;
                    $new_array   = array();
                } else {
                    // create a new array for each row
                    $new_line = array();
                    foreach ( $csv_line as $item ) {
                        $new_line[] = $item;
                    }
                    if ( ! empty( $new_line ) ) {
                        $new_array[] = $new_line;
                    }

                    if ( false != $max_lines ) {
                        if ( $line_number == $max_lines ) {
                            break;
                        }
                    }
                }
            }
            fclose( $handle );

            if ( $errors->has_errors() ) {
                // delete file
                if ( file_exists( acfcs_upload_folder( '/' ) . $file_name ) ) {
                    unlink( acfcs_upload_folder( '/' ) . $file_name );
                    $csv_array[ 'error' ] = 'file_deleted';
                }
            }

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
     * @return array|false
     */
    function acfcs_verify_csv_data( $csv_data = false, $delimiter = ";" ) {
        if ( false != $csv_data ) {
            $column_benchmark = 5;
            $line_number      = 0;
            $lines            = explode( "\r\n", $csv_data );

            foreach ( $lines as $line ) {
                $line_number++;

                if ( ! is_array( $csv_data ) ) {
                    $line_array = explode( $delimiter, $line );
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

                $column_counter = 0;
                foreach( $line_array as $element ) {
                    $column_counter++;
                    if ( $column_counter == 4 ) {
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

        return array();
    }


    /**
     * Get country info for debug
     *
     * @return array
     */
    function acfcs_get_countries_info() {
        global $wpdb;
		$table   = $wpdb->prefix . 'cities';
		$results = $wpdb->get_results( "SELECT country_code FROM $table GROUP BY country_code ORDER BY country_code ASC" );

        $acfcs_info = array();
        foreach ( $results as $data ) {
			$country_code = $data->country_code;
			$query        = $wpdb->prepare( "SELECT * FROM $table WHERE country_code = %s ORDER BY country_code ASC", $country_code );
			$results      = $wpdb->get_results( $query );

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
		if ( strpos( $city, "'s" ) !== false ) {
			$city = trim( $city, '\'s ' );
			$city = preg_replace( '/^\s*\'s \s+/i', '', $city );
		} elseif ( strpos( $city, "'t" ) !== false ) {
			$city = trim( $city, '\'t ' );
			$city = preg_replace( '/^\s*\'t \s+/i', '', $city );
		}

        return $city;
    }


    /**
     * Render select in ACF field
     *
     * @param $type
     * @param $field
     * @param $stored_value
     * @param $prefill_values
     *
     * @return false|string
     */
    function acfcs_render_dropdown( $type, $field, $stored_value, $prefill_values ) {
        $acfcs_dropdown       = 'acfcs__dropdown';
        $city_label           = apply_filters( 'acfcs_select_city_label', esc_attr__( 'Select a city', 'acf-city-selector' ) );
        $countries            = acfcs_get_countries( true, $field );
        $country_label        = apply_filters( 'acfcs_select_country_label', esc_attr__( 'Select a country', 'acf-city-selector' ) );
        $default_country      = ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
        $default_value        = false;
        $field_id             = $field[ 'id' ];
        $field_name           = $field[ 'name' ];
        $prefill_cities       = $prefill_values[ 'prefill_cities' ];
        $prefill_states       = $prefill_values[ 'prefill_states' ];
        $province_state_label = apply_filters( 'acfcs_select_province_state_label', esc_attr__( 'Select a province/state', 'acf-city-selector' ) );
        $selected_selected    = ' selected="selected"';
        $show_labels          = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;
        $use_select2          = ( isset( $field[ 'use_select2' ] ) ) ? $field[ 'use_select2' ] : false;
        $dropdown_class       = ( true == $use_select2 ) ? 'select2 ' . $acfcs_dropdown : $acfcs_dropdown;
        $data_label_value     = ( true == $show_labels ) ? '1' : '0';
        $which_fields         = ( isset( $field[ 'which_fields' ] ) ) ? $field[ 'which_fields' ] : 'all';

        switch( $type ) {
            case 'country':
                $default_value  = $default_country;
                $field_label    = $country_label;
                $field_suffix   = 'countryCode';
                $modifier       = 'countries';
                $selected_value = esc_attr( $stored_value );
                $values         = $countries;
                break;
            case 'state':
                $field_label    = $province_state_label;
                $field_suffix   = 'stateCode';
                $modifier       = 'states';
                $selected_value = esc_attr( $stored_value );
                $values         = $prefill_states;
                break;
            case 'city':
                $field_label    = $city_label;
                $field_suffix   = 'cityName';
                $modifier       = 'cities';
                $selected_value = esc_attr( $stored_value );
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
            <select name="<?php echo $field_name; ?>[<?php echo $field_suffix; ?>]" id="<?php echo $field_id . $field_suffix; ?>" class="<?php echo $dropdown_class; ?>" data-show-labels="<?php echo $data_label_value; ?>" data-which-fields="<?php echo $which_fields; ?>">
                <?php
                    if ( ! empty( $values ) ) {
                        foreach ( $values as $key => $label ) {
                            $selected = false;
                            if ( ! empty( $selected_value ) ) {
                                $selected = ( $selected_value == $key ) ? $selected_selected : false;
                            } elseif ( ! empty( $default_value ) ) {
                                // only when a default country is set
                                $selected = ( $default_value == $key ) ? $selected_selected : false;
                            }
                            echo '<option value="' . $key . '"' . $selected . '>' . $label . '</option>';
                        }
                    }
                ?>
            </select>
        </div>
        <?php
        $dropdown = ob_get_clean();

        return $dropdown;
    }


	function acfcs_render_hidden_field( $name, $value ) {
		if ( $name && $value ) {
			return sprintf( '<input type="hidden" name="%s" value="%s" />', $name, $value );
		}

		return false;
    }


    /**
     * Verify CSV data
     *
     * @param        $file_name
     * @param string $delimiter
     * @param bool   $verify
     */
    function acfcs_verify_data( $file_name, $delimiter = ';', $verify = true ) {
        $csv_array = acfcs_csv_to_array( $file_name, '', $delimiter, $verify );
        if ( isset( $csv_array[ 'data' ] ) ) {
            ACF_City_Selector::acfcs_errors()->add( 'success_no_errors_in_csv', sprintf( esc_html__( 'Congratulations, there appear to be no errors in CSV file: "%s".', 'acf-city-selector' ), $file_name ) );
            do_action( 'acfcs_after_success_verify' );
        }
    }


    /**
     * Import CSV data
     *
     * @param        $file_name
     * @param string $upload_folder
     * @param string $delimiter
     * @param false  $verify
     * @param false  $max_lines
     */
    function acfcs_import_data( $file_name, $upload_folder = '', $delimiter = ';', $verify = false, $max_lines = false ) {
        if ( $file_name ) {
            if ( ! is_array( $file_name ) ) {
                // csv data
                if ( strpos( $file_name, '.csv', -4 ) !== false ) {
                    $csv_array = acfcs_csv_to_array( $file_name, $upload_folder, $delimiter, $verify, $max_lines );

                    if ( ! is_wp_error( $csv_array ) ) {
                        if ( isset( $csv_array[ 'data' ] ) && ! empty( $csv_array[ 'data' ] ) ) {
                            $line_number = 0;
                            foreach ( $csv_array[ 'data' ] as $line ) {
                                $line_number++;

                                $city_row = array(
                                    'city_name'    => $line[ 0 ],
                                    'state_code'   => $line[ 1 ],
                                    'state_name'   => $line[ 2 ],
                                    'country_code' => $line[ 3 ],
                                    'country'      => $line[ 4 ],
                                );

                                global $wpdb;
                                $wpdb->insert( $wpdb->prefix . 'cities', $city_row );
                            }
                            if ( in_array( $file_name, [ 'be.csv', 'nl.csv' ] ) ) {
                                $country_code = substr( $file_name, 0, 2 );
                                ACF_City_Selector::acfcs_errors()->add( 'success_lines_imported_' . $country_code, sprintf( esc_html__( 'You have successfully imported %d cities from "%s".', 'acf-city-selector' ), $line_number, $file_name ) );
                            } else {
                                ACF_City_Selector::acfcs_errors()->add( 'success_lines_imported', sprintf( esc_html__( 'You have successfully imported %d cities from "%s".', 'acf-city-selector' ), $line_number, $file_name ) );
                            }

                            do_action( 'acfcs_after_success_import' );
                        } else {
                            error_log( 'error in ' . $file_name);
                        }
                    } else {
                        error_log('wp error');
                    }
                }
            } else {
                // raw data
                global $wpdb;
                $line_number   = 0;
                $verified_data = $file_name;

                foreach ( $verified_data as $line ) {
                    $line_number++;

                    $city_row = array(
                        'city_name'    => $line[ 0 ],
                        'state_code'   => $line[ 1 ],
                        'state_name'   => $line[ 2 ],
                        'country_code' => $line[ 3 ],
                        'country'      => $line[ 4 ],
                    );

                    $wpdb->insert( $wpdb->prefix . 'cities', $city_row );
                }
                ACF_City_Selector::acfcs_errors()->add( 'success_cities_imported', sprintf( _n( 'Congratulations, you imported 1 city.', 'Congratulations, you imported %d cities.', $line_number, 'acf-city-selector' ), $line_number ) );

                do_action( 'acfcs_after_success_import_raw' );
            }

        } else {
            ACF_City_Selector::acfcs_errors()->add( 'error_no_file_selected', esc_html__( "You didn't select a file.", 'acf-city-selector' ) );
        }
    }

    /**
     * Remove an uploaded file
     *
     * @param false $file_name
     */
    function acfcs_delete_file( $file_name = false ) {
        if ( false != $file_name ) {
            if ( file_exists( acfcs_upload_folder( '/' ) . $file_name ) ) {
                $delete_result = unlink( acfcs_upload_folder( '/' ) . $file_name );
                if ( true === $delete_result ) {
                    ACF_City_Selector::acfcs_errors()->add( 'success_file_deleted', sprintf( esc_html__( 'File "%s" successfully deleted.', 'acf-city-selector' ), $file_name ) );
                    do_action( 'acfcs_after_success_file_delete' );
                } else {
                    ACF_City_Selector::acfcs_errors()->add( 'error_file_deleted', sprintf( esc_html__( 'File "%s" is not deleted. Please try again.', 'acf-city-selector' ), $file_name ) );
                }
            }
        }
    }


    /**
     * Delete one or more countries
     *
     * @param $countries
     */
    function acfcs_delete_country( $countries ) {
        $country_names_and       = false;
        $sanitized_country_codes = array();
        foreach( $countries as $country_code ) {
            $sanitized_country_code    = sanitize_text_field( $country_code );
            $sanitized_country_codes[] = $sanitized_country_code;
            $country_names[]           = acfcs_get_country_name( $sanitized_country_code );
        }
        if ( ! empty( $country_names ) ) {
            $country_names_quotes = "'" . implode( "', '", $country_names ) . "'";
            if ( 1 < count( $country_names ) ) {
                $country_names_and = substr_replace( $country_names_quotes, ' and', strrpos( $country_names_quotes, ',' ), 1 );
            } else {
                $country_names_and = $country_names_quotes;
            }
        }

        if ( ! empty( $sanitized_country_codes ) ) {
            global $wpdb;
			$table          = $wpdb->prefix . 'cities';
			$country_string = strtoupper( "'" . implode( "', '", $sanitized_country_codes ) . "'" );
			$query          = $wpdb->prepare( "DELETE FROM $table WHERE `country_code` IN (%s)", $country_string );
			$result         = $wpdb->query( $query );

            if ( $result > 0 ) {
				ACF_City_Selector::acfcs_errors()->add( 'success_country_remove', sprintf( esc_html__( 'You have successfully removed all entries for %s.', 'acf-city-selector' ), $country_names_and ) );
                foreach( $countries as $country_code ) {
                    do_action( 'acfcs_delete_transients', $country_code );
                }
            }
        }
    }


    /**
     * Get upload folder for plugin, can be overriden with filter
     *
     * @param false $suffix
     *
     * @return mixed|void
     */
    function acfcs_upload_folder( $suffix = false ) {
        $upload_folder = apply_filters( 'acfcs_upload_folder', wp_upload_dir()[ 'basedir' ] . '/acfcs' . $suffix );

        return $upload_folder;
    }


    /**
     * Render preview results
     *
     * @param $csv_data
     *
     * @since 1.5.0
     *
     * @return false|string
     */
    function acfcs_render_preview_results( $csv_data = [] ) {
        $table = '';
		if ( ! empty( $csv_data ) ) {
            $table_columns = [
                esc_html__( 'City', 'acf-city-selector' ),
                esc_html__( 'State code', 'acf-city-selector' ),
                esc_html__( 'State', 'acf-city-selector' ),
                esc_html__( 'Country code', 'acf-city-selector' ),
                esc_html__( 'Country', 'acf-city-selector' ),
            ];
            ob_start();
            foreach( $table_columns as $column ) {
                echo sprintf( '<th>%s</th>', $column );
            }
            $table_headers = ob_get_clean();

            ob_start();
            foreach ( $csv_data as $line ) {
                echo '<tr>';
                foreach ( $line as $column ) {
                    echo sprintf( '<td>%s</td>', stripslashes( htmlspecialchars( $column ) ) );
                }
                echo '</tr>';
            }

            $table_rows = ob_get_clean();
            $table_body = sprintf( '<tbody>%s</tbody>', $table_rows );
            $table      = sprintf( '<table class="acfcs__table acfcs__table--preview-result scrollable">%s%s</table>', $table_headers, $table_body );
        }

		return $table;
    }


    /**
     * Get optgroups for states
     *
     * @since 1.5.0
     *
     * @return array
     */
    function acfcs_get_states_optgroup() {
        $results = acfcs_get_countries( false );
		$states  = [];

        // if there is at least 1 country
        if ( ! empty( $results ) ) {
            foreach ( $results as $country_code => $label ) {
                $countries[] = [
                    'code' => $country_code,
                    'name' => esc_attr__( $label, 'acf-city-selector' ),
                ];
            }

            // get states for these countries
            if ( ! empty( $countries ) ) {
				global $wpdb;
				$table  = $wpdb->prefix . 'cities';

				foreach ( $countries as $country ) {
                    $states[] = array(
                        'state' => 'open_optgroup',
                        'name'  => esc_attr__( acfcs_get_country_name( $country[ 'code' ] ), 'acf-city-selector' ),
                    );

                    $order = 'ORDER BY state_name ASC';
                    if ( 'FR' == $country[ 'code' ] ) {
                        $order = 'ORDER BY LENGTH(state_name), state_name';
                    }

                    $query   = "SELECT * FROM $table WHERE country_code = %s GROUP BY state_code " . $order;
                    $sql     = $wpdb->prepare( $query, $country[ 'code' ] );
                    $results = $wpdb->get_results( $sql );

                    if ( count( $results ) > 0 ) {
                        foreach ( $results as $data ) {
                            $states[] = array(
                                'state' => strtolower( $data->country_code ) . '-' . strtolower( $data->state_code ),
                                'name'  => esc_attr__( $data->state_name, 'acf-city-selector' ),
                            );
                        }
                    }

                    $states[] = array(
                        'state' => 'close_optgroup',
                        'name'  => '',
                    );
                }
            }
        }

		return $states;
    }


    /**
     * Get search results (admin)
     *
     * @since 1.5.0
     *
     * @return array|object|stdClass[]|null
     */
    function acfcs_get_searched_cities() {
        global $wpdb;
        $search_criteria_state   = ( isset( $_POST[ 'acfcs_state' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_state' ] ) : false;
        $search_criteria_country = ( isset( $_POST[ 'acfcs_country' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_country' ] ) : false;
        $search_limit            = false;
        $searched_orderby        = ( ! empty( $_POST[ 'acfcs_orderby' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_orderby' ] ) : false;
        $searched_term           = ( ! empty( $_POST[ 'acfcs_search' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_search' ] ) : false;
        $selected_limit          = ( ! empty( $_POST[ 'acfcs_limit' ] ) ) ? (int) $_POST[ 'acfcs_limit' ] : 100;
        $where                   = array();

        if ( false != $search_criteria_state ) {
            $where[] = "state_code = '" . substr( $search_criteria_state, 3, 3) . "' AND country_code = '" . substr( $search_criteria_state, 0, 2) . "'";
        } elseif ( false != $search_criteria_country ) {
            $where[] = "country_code = '" . $search_criteria_country . "'";
        }
        if ( false != $searched_term ) {
            $search[] = 'city_name LIKE "%' . $searched_term . '%"';

            if ( $search_criteria_country || $search_criteria_state ) {
                $where[] = '(' . implode( ' OR ', $search ) . ')';
            } else {
                $where[] = implode( ' OR ', $search );
            }

        }
        if ( 0 != $selected_limit ) {
            $search_limit = "LIMIT " . $selected_limit;
        }

        if ( ! empty( $where ) ) {
            $where   = "WHERE " . implode( ' AND ', $where );
        } else {
            $where = false;
        }

        if ( 'state' == $searched_orderby ) {
            $orderby = 'ORDER BY state_name ASC, city_name ASC';
        } else {
            $orderby = 'ORDER BY city_name ASC, state_name ASC';
        }

		$table  = $wpdb->prefix . 'cities';
		$query  = $wpdb->prepare( "SELECT * FROM $table %s %s %s", $where, $orderby, $search_limit );
		$cities = $wpdb->get_results( $query );

        return $cities;
    }


    /**
     * Get all translation strings for js/input fields
     *
     * @since 1.5.0
     *
     * @return array
     */
    function acfcs_get_js_translations() {
        $translations = array(
            'no_countries'         => esc_attr__( 'No countries', 'acf-city-selector' ),
            'select_city'          => esc_attr( apply_filters( 'acfcs_select_city_label', __( 'Select a city', 'acf-city-selector' ) ) ),
            'select_country'       => esc_attr( apply_filters( 'acfcs_select_country_label', __( 'Select a country', 'acf-city-selector' ) ) ),
            'select_country_first' => esc_attr( apply_filters( 'acfcs_select_country_first', __( 'No results (yet), first select a country', 'acf-city-selector' ) ) ),
            'select_state'         => esc_attr( apply_filters( 'acfcs_select_province_state_label', __( 'Select a province/state', 'acf-city-selector' ) ) ),
            'select_state_first'   => esc_attr( apply_filters( 'acfcs_select_state_first', __( 'No results (yet), first select a state', 'acf-city-selector' ) ) ),
        );

        return $translations;
    }
