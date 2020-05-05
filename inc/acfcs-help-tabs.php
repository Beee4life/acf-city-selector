<?php

    /**
     * Add help tabs
     *
     * @param $screen
     *
     * @return bool
     */
    function acfcs_help_tabs( $screen ) {

        $screen_array = array(
            'settings_page_acfcs-dashboard',
            'settings_page_acfcs-settings',
        );
        if ( isset( $screen->id ) && in_array( $screen->id, $screen_array ) ) {

            if ( 'settings_page_acfcs-dashboard' == $screen->id ) {
                $on_this_page = esc_html__( 'On this page you can import cities by either CSV file or raw (pasted) CSV data.', 'acf-city-selector' );
                $field_info = '<p>' . esc_html__( 'The required order is "City,State code,State,Country code,Country".', 'acf-city-selector' ) . '</p>
                        <table class="">
                        <thead>
                        <tr>
                        <th>' . esc_html__( 'Field', 'acf-city-selector' ) . '</th>
                        <th>' . esc_html__( 'What to enter', 'acf-city-selector' ) . '</th>
                        <th>' . esc_html__( 'Note', 'acf-city-selector' ) . '</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                        <td>' . esc_html__( 'City', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'full name', 'acf-city-selector' ) . '</td>
                        <td>&nbsp;</td>
                        </tr>
                        <tr>
                        <td>' . esc_html__( 'State code', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'state abbreviation', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'max 3 characters', 'acf-city-selector' ) . '</td>
                        </tr>
                        <tr>
                        <td>' . esc_html__( 'State', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'full state name', 'acf-city-selector' ) . '</td>
                        <td>&nbsp;</td>
                        </tr>
                        <tr>
                        <td>' . esc_html__( 'Country code', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'country abbreviation', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'exactly 2 characters', 'acf-city-selector' ) . '</td>
                        </tr>
                        <tr>
                        <td>' . esc_html__( 'Country', 'acf-city-selector' ) . '</td>
                        <td>' . esc_html__( 'full country name', 'acf-city-selector' ) . '</td>
                        <td>&nbsp;</td>
                        </tr>
                        </tbody>
                        </table>';

                $screen->add_help_tab( array(
                    'id'      => 'import-file',
                    'title'   => esc_html__( 'Import CSV from file', 'acf-city-selector' ),
                    'content' =>
                        '<h5>Import CSV from file</h5>
                        <p>' . $on_this_page . '</p>
                        <p>' . esc_html__( 'You can only upload *.csv files.', 'acf-city-selector' ) . '</p>'
                        . $field_info
                ) );

                $screen->add_help_tab( array(
                    'id'      => 'import-raw',
                    'title'   => esc_html__( 'Import raw CSV data', 'acf-city-selector' ),
                    'content' =>
                        '<h5>Import cities through CSV data</h5>
                        <p>' . $on_this_page . '</p>
                        <p>' . esc_html__( 'Raw CSV data has to be formatted (and ordered) in a certain way, otherwise it won\'t work.', 'acf-city-selector' ) . '</p>'
                        . $field_info
                ) );
            }

            get_current_screen()->set_help_sidebar(
                '<p><strong>' . esc_html__( 'Author\'s website', 'acf-city-selector' ) . '</strong></p>
                <p><a href="https://berryplasman.com?utm_source=' . $_SERVER[ 'SERVER_NAME' ] . '&utm_medium=plugin_admin&utm_campaign=free_promo">berryplasman.com</a></p>'
            );
        }

        return false;

    }
    add_filter( 'current_screen', 'acfcs_help_tabs' );
