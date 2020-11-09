<?php

    /*
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
    add_action( 'login_head', 'city_selector_ajaxurl' );

    /*
     * Get states by country code
     *
     * @param bool $country_code
     * @return JSON Object
     */
    function get_states_call() {

        if ( isset( $_POST[ 'country_code' ] ) ) {
            $country_code     = $_POST[ 'country_code' ];
            $states_transient = acfcs_get_states( $country_code );

            $i          = 1;
            $items      = [];
            $items[ 0 ] = [
                'country_code'  => '',
                'country_state' => '',
                'state_code'    => '',
                'state_name'    => apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) ),
            ];

            foreach ( $states_transient as $key => $label ) {
                $items[ $i ][ 'country_code' ] = $country_code;
                $items[ $i ][ 'state_code' ]   = $key;
                if ( $label != 'N/A' ) {
                    $items[ $i ][ 'state_name' ]    = $label;
                    $items[ $i ][ 'country_state' ] = $key;
                } else {
                    $items[ $i ][ 'state_name' ]    = $country_code;
                    $items[ $i ][ 'country_state' ] = $key;
                }
                $i++;
            }
            echo json_encode( $items );
            wp_die();
        }
    }
    add_action( 'wp_ajax_get_states_call', 'get_states_call' );
    add_action( 'wp_ajax_nopriv_get_states_call', 'get_states_call' );

    /*
     * Get cities by state code and/or country code
     *
     * @return JSON Object
     */
    function get_cities_call() {

        if ( isset( $_POST[ 'state_code' ] ) ) {

            $country_code = false;
            $field        = [];
            $items        = [];
            $state_code   = false;

            if ( ! empty( $_POST[ 'post_id' ] ) ) {
                $fields = get_field_objects( $_POST[ 'post_id' ] );
                if ( ! empty( $fields ) ) {
                    $field = acfcs_get_field_settings( $fields );
                }
            }

            if ( ! isset( $field[ 'show_labels' ] ) ) {
                $field[ 'show_labels' ] = true;
            }

            if ( 6 <= strlen( $_POST[ 'state_code' ] ) ) {
                $codes        = explode( '-', $_POST[ 'state_code' ] );
                $country_code = $codes[ 0 ];
                $state_code   = $codes[ 1 ];
            } elseif ( strpos( $_POST[ 'state_code' ], 'FR-' ) !== false ) {
                $country_code = substr( $_POST[ 'state_code' ], 0, 2 );
                $state_code   = substr( $_POST[ 'state_code' ], 3 );
            } elseif ( 2 == strlen( $_POST[ 'state_code' ] ) ) {
                // if 2 == strlen( $_POST[ 'state_code' ] ) then it's probably a country code
                // this is probably never reached, but just in case...
                $country_code = $_POST[ 'state_code' ];
                $state_code   = false;
            } elseif ( ! empty( $_POST[ 'state_code' ] ) ) {
                $codes        = explode( '-', $_POST[ 'state_code' ] );
                $country_code = $codes[ 0 ];
                $state_code   = $codes[ 1 ];
            } else {
                if ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) {
                    $country_code = $field[ 'default_country' ];
                }
            }

            $cities_transient = acfcs_get_cities( $country_code, $state_code, $field );

            if ( ! empty( $cities_transient ) ) {
                foreach ( $cities_transient as $city ) {
                    $items[] = [
                        'id'        => $city,
                        'city_name' => $city,
                    ];
                }
                echo json_encode( $items );
                wp_die();
            }
        }
    }
    add_action( 'wp_ajax_get_cities_call', 'get_cities_call' );
    add_action( 'wp_ajax_nopriv_get_cities_call', 'get_cities_call' );
