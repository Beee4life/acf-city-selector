<?php
    /**
     * Get country name + i18n country names
     *
     * These are defined here (just in case) so they are 'picked up' as translatable strings, because not all values occur in the plugin itself.
     *
     * @param $country_code
     *
     * @since 0.29.0
     *
     * @return mixed
     */
    function acfcs_country_i18n( $country_code ) {

        $country_array = array(
            'ad'     => esc_html__( 'Andorra', 'acf-city-selector' ),
            'aw'     => esc_html__( 'Aruba', 'acf-city-selector' ),
            'at'     => esc_html__( 'Austria', 'acf-city-selector' ),
            'au'     => esc_html__( 'Australia', 'acf-city-selector' ),
            'br'     => esc_html__( 'Brazil', 'acf-city-selector' ),
            'ca'     => esc_html__( 'Canada', 'acf-city-selector' ),
            'cn'     => esc_html__( 'China', 'acf-city-selector' ),
            'cw'     => esc_html__( 'Curaçao', 'acf-city-selector' ),
            'europe' => esc_html__( 'Europe', 'acf-city-selector' ),
            'fr'     => esc_html__( 'France', 'acf-city-selector' ),
            'de'     => esc_html__( 'Germany', 'acf-city-selector' ),
            'gd'     => esc_html__( 'Grenada', 'acf-city-selector' ),
            'gb'     => esc_html__( 'Great Britain', 'acf-city-selector' ),
            'lu'     => esc_html__( 'Luxembourg', 'acf-city-selector' ),
            'mx'     => esc_html__( 'Mexico', 'acf-city-selector' ),
            'nl'     => esc_html__( 'Netherlands', 'acf-city-selector' ),
            'nz'     => esc_html__( 'New Zealand', 'acf-city-selector' ),
            'pt'     => esc_html__( 'Portugal', 'acf-city-selector' ),
            'kr'     => esc_html__( 'South Korea', 'acf-city-selector' ),
            'es'     => esc_html__( 'Spain', 'acf-city-selector' ),
            'ch'     => esc_html__( 'Switzerland', 'acf-city-selector' ),
            'us'     => esc_html__( 'United States', 'acf-city-selector' ),
            'uy'     => esc_html__( 'Uruguay', 'acf-city-selector' ),
            'world'  => esc_html__( 'World', 'acf-city-selector' ),
        );

        if ( $country_code && array_key_exists( $country_code, $country_array ) ) {
            return $country_array[ $country_code ];
        }

        return $country_code;
    }
