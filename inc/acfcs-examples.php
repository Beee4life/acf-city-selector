<?php
    /**
     * Filters
     */

    /**
     * Override default delimiter
     *
     * @param $value
     *
     * @return mixed
     */
    function acfcs_delimiter( $delimiter ) {

        // the default $delimiter = ,
        // you can override with ; or |
        return $delimiter;
    }
    add_filter( 'acfcs_delimiter', 'acfcs_delimiter', 11 );

    /**
     * Override line length
     *
     * @param $value
     *
     * @return mixed
     */
    function acfcs_line_length( $length ) {

        // the default $length = 1000
        return $length;
    }
    add_filter( 'acfcs_line_length', 'acfcs_line_length', 11 );
