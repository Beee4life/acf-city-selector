<?php

	/**
	 * Set admin-ajax.php on the front side (by default it is available only for Backend)
	 */
	function city_selector_ajaxurl() {
		?>
        <script type="text/javascript">
            var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
        </script>
		<?php
	}
	add_action( 'wp_head', 'city_selector_ajaxurl' );

	/**
     * Fill the countries select
     *
	 * @param null $selectedCountry
	 * @param $field
	 *
	 * @return array
	 */
	function populate_country_select( $selectedCountry = null, $field ) {

		global $wpdb;
		$db = $wpdb->get_results( "
        SELECT * FROM " . $wpdb->prefix . "cities
        group by country_code
        order by country ASC
    " );

		$items = array();
		if ( null == $selectedCountry ) {
			if ( $field['show_labels'] == 1 ) {
				$items[] = '-';
			} else {
				$items[] = __( 'Select country', 'acf-city-selector' );
			}
		}
		foreach ( $db as $data ) {
			$items[ $data->country_code ] = $data->country;
		}
		if ( count( $items ) > 1 ) {
			// TO DO: print countries to file to index for translation
		}

		return $items;
	}

    /**
     * Create an array with states based on a Country Code.
     *
     * @param bool|string $country_code
     *
     * @return array
     */
    function get_states( $country_code = false ) {

        if ( ! $country_code ) {
            $country_code = $country_code;
        }

        global $wpdb;

        $items = array();

        $sql = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "cities
            WHERE country_code = '%s'
            group by state_code
            order by states ASC",  $country_code );

        $db = $wpdb->get_results( $sql );

        foreach ( $db as $data ) {
            $items[ $data->state_code ] = $data->states;
        }
        return $items;
    }

	/*
     * Get states by related Country Code
     *
	 * @param bool $country_code
     * @return JSON Object
	 */
	function get_states_call( $country_code = false ) {

		if ( ! $country_code ) {
			$country_code = $_POST['country_code'];
		}

		global $wpdb;

        $sql = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "cities
        WHERE country_code = '%s'
        group by state_code
        order by states ASC",  $country_code );

        $db = $wpdb->get_results( $sql );

		$items                    = array();
		$items[0]['country_code'] = "";
		$items[0]['state_code']   = "";
		$items[0]['states']       = __( 'Select provence/state', 'acf-city-selector' );
		$i                        = 1;

        // @TODO: check if $field['show_labels'] == 1
        // if == 1, $items[0]['states'] = '-';

		foreach ( $db as $data ) {
			$items[ $i ]['country_code'] = $data->country_code;
			$items[ $i ]['state_code']   = $data->state_code;
			if ( $data->states != 'N/A' ) {
				$items[ $i ]['states'] = $data->states;
			} else {
				$items[ $i ]['states'] = $data->country;
			}
			$i ++;
		}
		ob_clean();
		echo json_encode( $items );
		die();
	}

	/*
	 * Get cities by related State Code or Country Code (IF State code == "00" or States == 'N/A')
	 *
	 * @return JSON Object
	 */
	function get_cities_call() {

		if ( trim( $_POST['row_code'] ) ) {
			$codes        = explode( '-', $_POST['row_code'] );
			$country_code = $codes[0];
			$state_code   = $codes[1];

			global $wpdb;

			if ( $state_code == '00' ) {
				$db = $wpdb->get_results( "
                SELECT * FROM " . $wpdb->prefix . "cities
                WHERE country_code = '" . $country_code . "'
                order by city_name_ascii ASC
            " );
			} else {
				$db = $wpdb->get_results( "
                SELECT * FROM " . $wpdb->prefix . "cities
                WHERE state_code = '" . $state_code . "'
                AND country_code='" . $country_code . "'
                order by city_name_ascii ASC
            " );
			}
			$items                 = array();
			$items[0]['id']        = "";
			$items[0]['city_name'] = __( 'Select city', 'acf-city-selector' );
			$i                     = 1;

			foreach ( $db as $data ) {
				$items[ $i ]['id']        = $data->state_code;
				$items[ $i ]['city_name'] = $data->city_name_ascii;
				$i ++;
			}
			ob_clean();
			echo json_encode( $items );
			die();
		}
	}

	add_action( 'wp_ajax_get_states_call', 'get_states_call' );
	add_action( 'wp_ajax_nopriv_get_states_call', 'get_states_call' );
	add_action( 'wp_ajax_get_cities_call', 'get_cities_call' );
	add_action( 'wp_ajax_nopriv_get_cities_call', 'get_cities_call' );
