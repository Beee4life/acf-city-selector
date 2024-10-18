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
    
    
    function acfcs_admin_menu() {
        $dashboard_url  = admin_url( 'options-general.php?page=' );
        $admin_url      = admin_url( 'options.php?page=' );
        $current_class  = ' class="current_page"';
        $url_array      = [];
        
        if ( isset( $_SERVER[ 'HTTP_HOST' ] ) && isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
            $url_array = wp_parse_url( esc_url( sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_HOST' ] ) ) . sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ) ) );
        }
        $acfcs_subpage = ( isset( $url_array[ 'query' ] ) ) ? substr( $url_array[ 'query' ], 11 ) : false;
        
        $pages = [
            'dashboard' => esc_html__( 'Dashboard', 'acf-city-selector' ),
            'settings'  => esc_html__( 'Settings', 'acf-city-selector' ),
        ];
        if ( true === acfcs_has_cities() ) {
            $pages[ 'search' ] = esc_html__( 'Search', 'acf-city-selector' );
        }
        if ( ! empty ( acfcs_check_if_files() ) ) {
            $pages[ 'preview' ] = esc_html__( 'Preview', 'acf-city-selector' );
        }
        if ( current_user_can( apply_filters( 'acfcs_user_cap', 'manage_options' ) ) ) {
            $pages[ 'info' ] = esc_html__( 'Info', 'acf-city-selector' );
        }
        
        $pages[ 'countries' ] = esc_html__( 'Get more countries', 'acf-city-selector' );
        
        echo '<p class="acfcs-admin-menu">';
        foreach( $pages as $slug => $label ) {
            $current_page = ( $acfcs_subpage == $slug ) ? $current_class : false;
            $current_page = ( 'countries' == $slug ) ? ' class="cta"' : $current_page;
            echo ( 'dashboard' != $slug ) ? ' | ' : false;
            switch( $slug ) {
                case 'dashboard':
                    $url = sprintf( '%sacfcs-%s', $dashboard_url, $slug );
                    break;
                default:
                    $url = sprintf( '%sacfcs-%s', $admin_url, $slug );
            }
            echo sprintf( '<a href="%s"%s>%s</a>', esc_url_raw( $url ), esc_attr( $current_page ), esc_html( $label ) );
        }
        echo '</p>';
    }
    add_action( 'acfcs_admin_menu', 'acfcs_admin_menu' );
