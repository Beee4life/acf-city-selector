<?php
    /**
     * Add help tabs
     *
     * @param $screen
     *
     * @return bool
     */
    function acfcs_help_tabs( $screen ) {

        if ( isset( $screen->id ) ) {
            if ( strpos( $screen->id, 'acfcs' ) !== false ) {
                // @TODO: better rendering
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
                        sprintf( '<h5>%s</h5>', esc_html__( 'Import CSV from file', 'acf-city-selector' ) ) . '
                        <p>' . $on_this_page . '</p>
                        <p>' . esc_html__( 'You can only upload *.csv files.', 'acf-city-selector' ) . '</p>'
                        . $field_info
                ) );

                $screen->add_help_tab( array(
                    'id'      => 'import-raw',
                    'title'   => esc_html__( 'Import raw CSV data', 'acf-city-selector' ),
                    'content' =>
                        sprintf( '<h5>%s</h5>', esc_html__( 'Import cities through CSV data', 'acf-city-selector' ) ) . '
                        <p>' . $on_this_page . '</p>
                        <p>' . esc_html__( 'Raw CSV data has to be formatted (and ordered) in a certain way, otherwise it won\'t work.', 'acf-city-selector' ) . '</p>'
                        . $field_info
                ) );

                $screen->add_help_tab( array(
                    'id'      => 'preview-data',
                    'title'   => esc_html__( 'Preview CSV data', 'acf-city-selector' ),
                    'content' =>
                        sprintf( '<h5>%s</h5>', esc_html__( 'Preview CSV data', 'acf-city-selector' ) ) . '
                        <p>' . esc_html__( 'On the preview page, you can preview uploaded csv files. Not to be confused with search where you can search imported cities. Please keep in mind, if you preview an uploaded csv file, the file will get verified and it can be deleted if it contains errors.', 'acf-city-selector' ) . '</p>
                        '
                ) );

                $screen->add_help_tab( array(
                    'id'      => 'search-data',
                    'title'   => esc_html__( 'Search CSV data', 'acf-city-selector' ),
                    'content' =>
                        '<h5>Preview CSV data</h5>
                        <p>' . esc_html__( 'On the search page, you can search in imported cities. Not to be confused with preview where you can preview uploaded csv files.', 'acf-city-selector' ) . '</p>
                        '
                ) );

                $screen->add_help_tab( array(
                    'id'      => 'more-countries',
                    'title'   => esc_html__( 'More countries', 'acf-city-selector' ),
                    'content' =>
                        sprintf( '<h5>%s</h5>', esc_html__( 'More countries', 'acf-city-selector' ) ) . '
                        <p>' . sprintf( __( 'If you need more countries, you can get them on the official website: %s.', 'acf-city-selector' ), '<a href="' . ACFCS_WEBSITE_URL . '/get-countries/" target="_blank" rel="noopener">acf-city-selector.com</a>' ) . '</p>
                        '
                ) );

                get_current_screen()->set_help_sidebar(
                    '<p><strong>' . esc_html__( 'Official website', 'acf-city-selector' ) . '</strong></p>
                <p><a href="' . ACFCS_WEBSITE_URL . '?utm_source=' . $_SERVER[ 'SERVER_NAME' ] . '&utm_medium=plugin_admin&utm_campaign=free_promo">acf-city-selector.com</a></p>'
                );
            }
        }

        return false;

    }
    add_action( 'current_screen', 'acfcs_help_tabs' );
