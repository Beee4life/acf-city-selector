<?php

	/**
	 * @param bool $csv_data
	 * @return array|bool
	 */
	function acfcs_verify_csv_data( $csv_data = false ) {

		if ( false != $csv_data ) {

			if ( is_array( $csv_data ) ) {
				$lines = $csv_data;
			} else {
				$lines = explode( "\n", $csv_data );
			}

			$validated_csv    = array();
			$line_number      = 0;
			$column_benchmark = 5;

			foreach ( $lines as $line ) {
				$line_number++;

				if ( ! is_array( $csv_data ) ) {
					$line = explode( ",", $line );
				}

				if ( count( $line ) != $column_benchmark ) {
					// length of a line if not correct
					if ( count( $line ) < $column_benchmark ) {
						ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html__( 'There are too few columns on line %d.', 'acf-city-selector' ), $line_number ) );

						return false;

					} elseif ( count( $line ) > $column_benchmark ) {
						ACF_City_Selector::acfcs_errors()->add( 'error_no_correct_columns', sprintf( esc_html__( 'There are too many columns on line %d.', 'acf-city-selector' ), $line_number ) );

						return false;

					}
				}

				$element_counter = 0;
                foreach( $line as $element ) {
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

                // all good
                $validated_csv[] = $line;
			}

			return $validated_csv;
		}

		return false;
	}

	/**
	 * Read file and spit out an array
	 * @return array|bool
	 */
	function acfcs_read_file_only( $file_name = false ) {

		$csv_array = array();
		if ( false != $file_name ) {

			$file_location = wp_upload_dir()['basedir'] . '/acfcs/' . $file_name;
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
