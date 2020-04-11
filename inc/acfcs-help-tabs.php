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
                $screen->add_help_tab( array(
                    'id'      => 'import-file',
                    'title'   => esc_html__( 'Import CSV from file', 'acf-city-selector' ),
                    'content' =>
                        '<h5>Import CSV from file</h5>
                        <p>' . esc_html__( 'On this page you can import a CSV file which contains cities to import.', 'acf-city-selector' ) . '</p>
                        <p>' . esc_html__( 'You can only upload *.csv files.', 'acf-city-selector' ) . '</p>
                        <p>' . esc_html__( 'The required order is "City,State code,State,Country code,Country".', 'acf-city-selector' ) . '</p>
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
                        <td>' . esc_html__( 'exactly 2 characters', 'acf-city-selector' ) . '</td>
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
                        </table>'
                ) );

                $screen->add_help_tab( array(
                    'id'      => 'import-raw',
                    'title'   => esc_html__( 'Import raw CSV data', 'acf-city-selector' ),
                    'content' =>
                        '<h5>Import cities through CSV data</h5>
                        <p>' . esc_html__( 'On this page you can import cities. You can select cities from The Netherlands, Belgium and Luxembourg which come included in the plugin.', 'acf-city-selector' ) . '</p>
                        <p>' . esc_html__( 'You can also import raw csv data, but this has to be formatted (and ordered) in a certain way, otherwise it won\'t work.', 'acf-city-selector' ) . '</p>
                        <p>' . esc_html__( 'The required order is "City,State code,State,Country code,Country".', 'acf-city-selector' ) . '</p>
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
                        <td>' . esc_html__( 'exactly 2 characters', 'acf-city-selector' ) . '</td>
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
                        </table>'
                ) );

            }

            if ( 'settings_page_acfcs-settings' == $screen->id ) {
                $screen->add_help_tab( array(
                    'id'      => 'import-file',
                    'title'   => esc_html__( 'Import preset countries', 'acf-city-selector' ),
                    'content' => '<h5>Import preset countries</h5>
                        <p>' . esc_html__( 'On this page you can (re-)import the countries which come with the plugin when it\'s installed; Netherlands, Belgium and Luxembourg (if needed).', 'acf-city-selector' ) . '</p>
                        <h5>Clear database</h5>
                        <p>' . esc_html__( 'There\'s also an option to delete all cities, which can be helpful if you activate the plugin a second time. Right now all cities are imported again if you activate the plugin. This will also happen if you still have all the cities in the database from a previous activation.', 'acf-city-selector' ) . '</p>
                        <h5>Preserve settings</h5>
                        <p>' . esc_html__( 'If you select preserve settings, all values will not be deleted from the database when the plugin is deleted.', 'acf-city-selector' ) . '</p>'
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
