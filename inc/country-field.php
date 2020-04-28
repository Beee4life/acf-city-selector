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
    function acfcs_populate_country_select( $field ) {

        global $wpdb;
        $db = $wpdb->get_results( "
            SELECT * FROM " . $wpdb->prefix . "cities
            group by country_code
            order by country ASC
        " );

        $items = [];
        if ( $field[ 'show_labels' ] == 1 ) {
            $items[] = '-';
        } else {
            $items[] = esc_html__( 'Select a country', 'acf-city-selector' );
        }
        foreach ( $db as $data ) {
            $items[ $data->country_code ] = __( $data->country, 'acf-city-selector' );
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

        if ( ! $country_code && isset( $_POST[ 'country_code' ] ) ) {
            $country_code = $_POST[ 'country_code' ];
        }

        global $wpdb;

        $items = array();

        if ( false !== $country_code ) {
            $sql = $wpdb->prepare( "
                SELECT *
                FROM " . $wpdb->prefix . "cities
                WHERE country_code = '%s'
                group by state_code
                order by state_name ASC",  $country_code
            );
            $db = $wpdb->get_results( $sql );

            foreach ( $db as $data ) {
                $items[ $country_code . '-' . $data->state_code ] = $data->state_name;
            }
        }

        return $items;
    }

    /**
     * Create an array with cities for a certain country and possibly state
     *
     * @param bool $country_code
     * @param bool $state_code
     */
    function get_cities( $country_code = false, $state_code = false ) {

        global $wpdb;
        $items = array();
        $query = "SELECT * FROM " . $wpdb->prefix . "cities";
        if ( $country_code ) {
            $query .= " WHERE country_code = '{$country_code}'";
        }
        if ( $state_code ) {
            $query .= " WHERE state_code = '{$country_code}-{$state_code}'";
        }
        $query .= " group by state_code";
        $query .= " order by state_name ASC";
        $db    = $wpdb->get_results( $query );

        foreach ( $db as $data ) {
            $items[ $data->state_code ] = $data->state_name;
        }

        return $items;

    }

    /*
     * Get states by country code
     *
     * @param bool $country_code
     * @return JSON Object
     */
    function get_states_call( $data = false ) {
        if ( ! isset( $data[ 'country_code' ] ) ) {
            if ( isset( $_POST[ 'country_code' ] ) ) {
                $country_code = $_POST[ 'country_code' ];
            }
        } else {
            $country_code = $data[ 'country_code' ];
        }

        global $wpdb;
        $sql = $wpdb->prepare( "
            SELECT *
            FROM " . $wpdb->prefix . "cities
            WHERE country_code = '%s'
            group by state_code
            order by state_name ASC", $country_code
        );

        $query_results                = $wpdb->get_results( $sql );
        $items                        = array();
        $items[ 0 ][ 'country_code' ] = "";
        $items[ 0 ][ 'state_code' ]   = "";
        $items[ 0 ][ 'state_name' ]   = esc_html__( 'Select a province/state', 'acf-city-selector' );
        $i                            = 1;

        foreach ( $query_results as $data ) {
            $items[ $i ][ 'country_code' ] = $data->country_code;
            $items[ $i ][ 'state_code' ]   = $data->state_code;
            if ( $data->state_name != 'N/A' ) {
                $items[ $i ][ 'state_name' ] = $data->state_name;
            } else {
                $items[ $i ][ 'state_name' ] = $data->country;
            }
            $i++;
        }
        echo json_encode( $items );
        wp_die();
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
            // @TODO: check if i need trim
            if ( trim( $_POST[ 'state_code' ] ) ) {
                $country_code = false;
                $state_code   = false;
                if ( 5 == strlen( $_POST[ 'state_code' ] ) ) {
                    $codes        = explode( '-', $_POST[ 'state_code' ] );
                    $country_code = $codes[ 0 ];
                    $state_code   = $codes[ 1 ];
                } elseif ( 2 == strlen( $_POST[ 'state_code' ] ) ) {
                    $state_code   = $_POST[ 'state_code' ];
                }

                global $wpdb;

                // @TODO: look into when/why it's '00'
                if ( $state_code == '00' ) {
                    $db = $wpdb->get_results( "
                    SELECT *
                    FROM " . $wpdb->prefix . "cities
                    WHERE country_code = '" . $country_code . "'
                    order by city_name ASC
                " );
                } elseif ( false !== $state_code && false !== $country_code ) {
                    $db = $wpdb->get_results( "
                    SELECT *
                    FROM " . $wpdb->prefix . "cities
                    WHERE state_code = '" . $state_code . "'
                    AND country_code='" . $country_code . "'
                    order by city_name ASC
                " );
                } elseif ( false !== $country_code ) {
                    // @TODO: create fallback
                }
                $items                     = array();
                $items[ 0 ][ 'id' ]        = '';
                $items[ 0 ][ 'city_name' ] = esc_html__( 'Select a city', 'acf-city-selector' );
                $i                         = 1;
                foreach ( $db as $data ) {
                    $items[ $i ][ 'id' ]        = $data->state_code;
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
