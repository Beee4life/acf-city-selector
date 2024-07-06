<?php
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
