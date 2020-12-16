<?php
    /*
     * This file handles the downloading of the JSON info
     */

    // if ( ! defined( 'ABSPATH' ) ) exit;

    header('Content-disposition: attachment; filename=debug.json');
    header('Content-type: application/json');

    if ( isset( $_POST[ 'acfcs_export_json_nonce' ] ) ) {
        if ( ! wp_verify_nonce( $_POST[ 'acfcs_export_json_nonce' ], 'acfcs-export-json-nonce' ) ) {
            ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );

            return;
        } else {
            $json = file_get_contents( acfcs_upload_folder( '/' ) . 'debug.json' );
            echo $json;
        }
    }

