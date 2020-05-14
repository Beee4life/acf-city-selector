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
            global $wpdb;
            $country_code = $_POST[ 'country_code' ];
            $order        = 'ORDER BY state_name ASC';
            if ( 'FR' == $country_code ) {
                $order = "ORDER BY LENGTH(state_name), state_name";
            }
            $sql = $wpdb->prepare( "
                SELECT *
                FROM " . $wpdb->prefix . "cities
                WHERE country_code = '%s'
                GROUP BY state_code
                " . $order, $country_code
            );

            $query_results                 = $wpdb->get_results( $sql );
            $items                         = array();
            $items[ 0 ][ 'country_code' ]  = '';
            $items[ 0 ][ 'country_state' ] = '';
            $items[ 0 ][ 'state_code' ]    = '';
            $items[ 0 ][ 'state_name' ]    = esc_html__( 'Select a province/state', 'acf-city-selector' );
            $i                             = 1;

            foreach ( $query_results as $data ) {
                $items[ $i ][ 'country_code' ] = $data->country_code;
                $items[ $i ][ 'state_code' ]   = $data->state_code;
                if ( $data->state_name != 'N/A' ) {
                    $items[ $i ][ 'state_name' ]    = $data->state_name;
                    $items[ $i ][ 'country_state' ] = $data->country_code . '-' . $data->state_code;
                } else {
                    $items[ $i ][ 'state_name' ]    = $data->country;
                    $items[ $i ][ 'country_state' ] = $data->country_code . '-' . $data->state_code;
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
     * Get cities by state code or country code (IF state code == "00" or states == 'N/A')
     *
     * @return JSON Object
     */
    function get_cities_call() {

        if ( isset( $_POST[ 'state_code' ] ) ) {
            $country_code = false;
            $state_code   = false;
            if ( 5 <= strlen( $_POST[ 'state_code' ] ) ) {
                $codes        = explode( '-', $_POST[ 'state_code' ] );
                $country_code = $codes[ 0 ];
                $state_code   = $codes[ 1 ];
            } elseif ( 2 == strlen( $_POST[ 'state_code' ] ) ) {
                // if 2 == strlen( $_POST[ 'state_code' ] ) then it's a country code
                // get all cities for $_POST[ 'state_code' ]
                $country_code = $_POST[ 'state_code' ];
                $state_code   = false;
            } elseif ( strpos( $_POST[ 'state_code' ], 'FR-' ) !== false ) {
                $country_code = substr( $_POST[ 'state_code' ], 0, 2 );
                $state_code   = substr( $_POST[ 'state_code' ], 3 );
            }

            global $wpdb;

            // @TODO: look into when/why it's '00'
            if ( $state_code == '00' ) {
                error_log("state_code == '00'");
                $sql = $wpdb->prepare( "
                        SELECT *
                        FROM " . $wpdb->prefix . "cities
                        WHERE country_code = '%s'
                        GROUP BY state_code
                        ORDER BY city_name ASC", $country_code
                );
                $results = $wpdb->get_results( $sql );
            } elseif ( false !== $state_code && false !== $country_code ) {
                $sql = $wpdb->prepare( "
                        SELECT *
                        FROM " . $wpdb->prefix . "cities
                        WHERE state_code = '%s'
                            AND country_code='%s'
                        ORDER BY city_name ASC", $state_code, $country_code
                );
                $results = $wpdb->get_results( $sql );
            } elseif ( false !== $country_code ) {
                $sql = $wpdb->prepare( "
                        SELECT *
                        FROM " . $wpdb->prefix . "cities
                        WHERE country_code='%s'
                        ORDER BY city_name ASC", $country_code
                );
                $results = $wpdb->get_results( $sql );
            }
            $items                     = array();
            $items[ 0 ][ 'id' ]        = '';
            $items[ 0 ][ 'city_name' ] = esc_html__( 'Select a city', 'acf-city-selector' ); // shown after state change
            $i                         = 1;
            if ( isset( $results ) && is_array( $results ) ) {
                foreach ( $results as $data ) {
                    $items[ $i ][ 'id' ]        = $data->city_name;
                    $items[ $i ][ 'city_name' ] = $data->city_name;
                    $i++;
                }
                echo json_encode( $items );
                wp_die();
            }
        }
    }
    add_action( 'wp_ajax_get_cities_call', 'get_cities_call' );
    add_action( 'wp_ajax_nopriv_get_cities_call', 'get_cities_call' );
