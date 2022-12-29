<?php
    /**
     * Function to delete transients
     *
     * @param false $country_code
     */
    function acfcs_delete_transients( $country_code = false ) {
        if ( false != $country_code ) {
            delete_transient( 'acfcs_states_' . strtolower( $country_code ) );
            delete_transient( 'acfcs_cities_' . strtolower( $country_code ) );
        } else {
            delete_transient( 'acfcs_countries' );
            $countries = acfcs_get_countries( false, false, true );
            if ( ! empty( $countries ) ) {
                foreach( $countries as $country_code => $label ) {
                    do_action( 'acfcs_delete_transients', $country_code );
					// @TODO: also add states
                }
            }
        }
    }
    add_action( 'acfcs_delete_transients', 'acfcs_delete_transients' );
    add_action( 'acfcs_after_success_import', 'acfcs_delete_transients' );
    add_action( 'acfcs_after_success_import_raw', 'acfcs_delete_transients' );
    add_action( 'acfcs_after_success_nuke', 'acfcs_delete_transients' );


	/**
	 * Do stuff after certain imports
	 *
	 * @param $country_code
	 *
	 * @return void
	 */
	function acfcs_reimport_cities( $country_code = false ) {
		if ( $country_code && in_array( $country_code, [ 'nl', 'be' ] ) ) {
			update_option( 'acfcs_city_update_1_8_0_' . $country_code, 'done' );

			$belgium_done     = get_option( 'acfcs_city_update_1_8_0_be' );
			$netherlands_done = get_option( 'acfcs_city_update_1_8_0_nl' );

			if ( $belgium_done && $netherlands_done ) {
				delete_option( 'acfcs_city_update_1_8_0_be' );
				delete_option( 'acfcs_city_update_1_8_0_nl' );
				update_option( 'acfcs_city_update_1_8_0', 'done' );
			}
		}
	}
	add_action( 'acfcs_after_success_import', 'acfcs_reimport_cities' );


	/**
	 * Save location as single meta values
	 *
	 * @param $value
	 * @param $post_id
	 *
	 * @return void
	 */
	function acfcs_save_single_meta( $value, $post_id ) {
		if ( isset( $_POST[ 'store_meta' ] ) && 1 == $_POST[ 'store_meta' ] ) {
			if ( ! empty( $value[ 'countryCode' ] ) ) {
				update_post_meta( $post_id, 'acfcs_search_country', $value[ 'countryCode' ] );
			}
			if ( ! empty( $value[ 'stateCode' ] ) ) {
				update_post_meta( $post_id, 'acfcs_search_state', $value[ 'stateCode' ] );
			}
			if ( ! empty( $value[ 'cityName' ] ) ) {
				update_post_meta( $post_id, 'acfcs_search_city', $value[ 'cityName' ] );
			}
		} elseif ( $post_id ) {
			// remove meta
			delete_post_meta( $post_id, 'acfcs_search_country' );
			delete_post_meta( $post_id, 'acfcs_search_state' );
			delete_post_meta( $post_id, 'acfcs_search_city' );
		}
	}
	add_action( 'acfcs_store_meta', 'acfcs_save_single_meta', 10, 2 );
