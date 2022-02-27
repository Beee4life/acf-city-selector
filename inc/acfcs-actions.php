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
                }
            }
        }
    }
    add_action( 'acfcs_delete_transients', 'acfcs_delete_transients' );
    add_action( 'acfcs_after_success_import', 'acfcs_delete_transients' );
    add_action( 'acfcs_after_success_import_raw', 'acfcs_delete_transients' );
    add_action( 'acfcs_after_success_nuke', 'acfcs_delete_transients' );
