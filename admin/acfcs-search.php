<?php
    /*
     * Content for the search page
     */
    function acfcs_search() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        // get all countries from database
        global $wpdb;
        $cities                  = array();
        $countries               = array();
        $search_criteria_state   = ( isset( $_POST[ 'acfcs_state' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_state' ] ) : false;
        $search_criteria_country = ( isset( $_POST[ 'acfcs_country' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_country' ] ) : false;
        $searched_orderby        = ( ! empty( $_POST[ 'acfcs_orderby' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_orderby' ] ) : false;
        $searched_term           = ( ! empty( $_POST[ 'acfcs_search' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_search' ] ) : false;
        $selected_limit          = ( ! empty( $_POST[ 'acfcs_limit' ] ) ) ? (int) $_POST[ 'acfcs_limit' ] : 100;

        // get cities by country
        $results = acfcs_get_countries( false );

        // if there is at least 1 country
        if ( ! empty( $results ) ) {
            foreach ( $results as $country_code => $label ) {
                $countries[] = [
                    'code' => $country_code,
                    'name' => esc_attr__( $label, 'acf-city-selector' ),
                ];
            }

            // get states for these countries
            if ( ! empty( $countries ) ) {
                $states = array();
                foreach ( $countries as $country ) {
                    $states[] = array(
                        'state' => 'open_optgroup',
                        'name'  => esc_attr__( acfcs_get_country_name( $country[ 'code' ] ), 'acf-city-selector' ),
                    );
                    $order = 'ORDER BY state_name ASC';
                    if ( 'FR' == $country[ 'code' ] ) {
                        $order = "ORDER BY LENGTH(state_name), state_name";
                    }
                    $sql = $wpdb->prepare( "
                        SELECT *
                        FROM " . $wpdb->prefix . "cities
                        WHERE country_code = %s
                        GROUP BY state_code
                        " . $order, $country[ 'code' ]
                    );
                    $results = $wpdb->get_results( $sql );

                    if ( count( $results ) > 0 ) {
                        foreach ( $results as $data ) {
                            $states[] = array(
                                'state' => strtolower( $data->country_code ) . '-' . strtolower( $data->state_code ),
                                'name'  => esc_attr__( $data->state_name, 'acf-city-selector' ),
                            );
                        }
                    }
                    $states[] = array(
                        'state' => 'close_optgroup',
                        'name'  => '',
                    );
                }
            }
        }

        // if has searched
        if ( isset( $_POST[ 'acfcs_search_form' ] ) ) {
            $search_limit = false;
            $where        = array();

            if ( false != $search_criteria_state ) {
                $where[] = "state_code = '" . substr( $search_criteria_state, 3, 3) . "' AND country_code = '" . substr( $search_criteria_state, 0, 2) . "'";
            } elseif ( false != $search_criteria_country ) {
                $where[] = "country_code = '" . $search_criteria_country . "'";
            }
            if ( false != $searched_term ) {
                $search[] = 'city_name LIKE "%' . $searched_term . '%"';

                if ( $search_criteria_country || $search_criteria_state ) {
                    $where[] = '(' . implode( ' OR ', $search ) . ')';
                } else {
                    $where[] = implode( ' OR ', $search );
                }

            }
            if ( 0 != $selected_limit ) {
                $search_limit = "LIMIT " . $selected_limit;
            }

            if ( ! empty( $where ) ) {
                $where   = "WHERE " . implode( ' AND ', $where );
            } else {
                $where = false;
            }

            if ( 'state' == $searched_orderby ) {
                $orderby = 'ORDER BY state_name ASC, city_name ASC';
            } else {
                $orderby = 'ORDER BY city_name ASC, state_name ASC';
            }

            $sql = "SELECT *
                FROM " . $wpdb->prefix . "cities
                " . $where . "
                " . $orderby . "
                " . $search_limit . "
            ";
            $cities     = $wpdb->get_results( $sql );
            $city_array = array();
            foreach( $cities as $city_object ) {
                $city_array[] = (array) $city_object;
            }
            if ( ! empty( $city_array ) ) {
                uasort( $city_array, 'acfcs_sort_array_with_quotes' );
            }
            $result_count = count( $city_array );
        }

        // output
        ?>
        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">

                        <h2>
                            <?php esc_html_e( 'Search for cities', 'acf-city-selector' ); ?>
                        </h2>

                        <?php if ( count( $countries ) > 0 ) { ?>
                            <form action="" method="POST">
                                <input name="acfcs_search_form" type="hidden" value="1" />

                                <div class="acfcs__search-form">
                                    <?php // if there's only 1 country, no need to add country dropdown ?>
                                    <?php if ( count( $countries ) > 1 ) { ?>
                                        <div class="acfcs__search-criteria acfcs__search-criteria--country">
                                            <label for="acfcs_country" class="screen-reader-text"><?php echo apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) ); ?></label>
                                            <select name="acfcs_country" id="acfcs_country">
                                                <option value="">
                                                    <?php echo apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) ); ?>
                                                </option>
                                                <?php foreach( $countries as $country ) { ?>
                                                    <?php $selected = ( $country[ 'code' ] == $search_criteria_country ) ? ' selected="selected"' : false; ?>
                                                    <option value="<?php echo $country[ 'code' ]; ?>"<?php echo $selected; ?>>
                                                        <?php _e( $country[ 'name' ], 'acf-city-selector' ); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="acfcs__search-criteria acfcs__search-criteria--or">
                                            <small><?php esc_html_e( 'OR', 'acf-city-selector' ); ?></small>
                                        </div>
                                    <?php } ?>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--state">
                                        <label for="acfcs_state" class="screen-reader-text">
                                            <?php echo apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) ); ?>
                                        </label>
                                        <select name="acfcs_state" id="acfcs_state">
                                            <option value="">
                                                <?php echo apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) ); ?>
                                            </option>
                                            <?php
                                                foreach( $states as $state ) {
                                                    if ( 'open_optgroup' == $state[ 'state' ] ) {
                                                        echo '<optgroup label="'. $state[ 'name' ] . '">';
                                                    }
                                                    if ( strpos( $state[ 'state' ], 'optgroup' ) === false ) {
                                                        $selected = ( $state[ 'state' ] == $search_criteria_state ) ? ' selected="selected"' : false;
                                                        echo '<option value="' . $state[ 'state' ] . '"' . $selected . '>' . esc_html__( $state[ 'name' ], 'acf-city-selector' ) . '</option>';
                                                    }
                                                    if ( 'close_optgroup' == $state[ 'state' ] ) {
                                                        echo '</optgroup>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--search">
                                        <label for="acfcs_search" class="screen-reader-text">
                                            <?php esc_html_e( 'Search term', 'acf-city-selector' ); ?>
                                        </label>
                                        <input name="acfcs_search" id="acfcs_search" type="text" value="<?php if ( false != $searched_term ) { echo stripslashes( $searched_term ); } ?>" placeholder="<?php esc_html_e( 'City name', 'acf-city-selector' ); ?>">
                                    </div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--limit">
                                        <label for="acfcs_limit" class="screen-reader-text">
                                            <?php esc_html_e( 'Limit', 'acf-city-selector' ); ?>
                                        </label>
                                        <input name="acfcs_limit" id="acfcs_limit" type="number" value="<?php if ( false != $selected_limit ) { echo $selected_limit; } ?>" placeholder="<?php esc_html_e( 'Limit', 'acf-city-selector' ); ?>">
                                    </div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--orderby">
                                        <label for="acfcs_orderby" class="screen-reader-text">
                                            <?php esc_html_e( 'Order by', 'acf-city-selector' ); ?>
                                        </label>
                                        <select name="acfcs_orderby" id="acfcs_orderby">
                                            <option value="">
                                                <?php esc_html_e( 'Order by', 'acf-city-selector' ); ?>
                                            </option>
                                            <?php
                                                $orderby = [
                                                    esc_attr__( 'City', 'acf-city-selector' ),
                                                    esc_attr__( 'State', 'acf-city-selector' ),
                                                ];
                                                foreach( $orderby as $criterium ) {
                                                    $selected = ( $criterium == $searched_orderby ) ? ' selected' : false;
                                                    echo '<option value="' . $criterium . '" ' . $selected . '>' . ucfirst( $criterium ) . '</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--submit">
                                        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Search', 'acf-city-selector' ); ?>" />
                                    </div>
                                </div>
                            </form>
                        <?php } ?>

                        <?php // Results output below ?>
                        <?php if ( isset( $_POST[ 'acfcs_search_form' ] ) && empty( $cities ) ) { ?>
                            <p>
                                <br />
                                <?php _e( 'No results, please try again.', 'acf-city-selector'); ?>
                            </p>
                        <?php } elseif ( ! empty( $cities ) ) { ?>
                            <form enctype="multipart/form-data" action="" method="POST">
                                <input name="acfcs_delete_row_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-delete-row-nonce' ); ?>" />
                                <div class="acfcs__search-results">
                                    <p class="hide568">
                                        <small>
                                            <?php _e( 'Table scrolls horizontally.', 'acf-city-selector' ); ?>
                                        </small>
                                    </p>
                                    <p>
                                        <?php echo $result_count; ?> <?php esc_html_e( 'results',  'acf-city-selector' ); ?>
                                    </p>
                                    <table class="acfcs__table acfcs__table--search scrollable">
                                        <thead>
                                        <tr>
                                            <th>
                                                <?php esc_html_e( 'ID', 'acf-city-selector' ); ?>
                                            </th>
                                            <th>
                                                <?php esc_html_e( 'Select', 'acf-city-selector' ); ?>
                                            </th>
                                            <th>
                                                <?php esc_html_e( 'City', 'acf-city-selector' ); ?>
                                            </th>
                                            <th>
                                                <?php esc_html_e( 'State', 'acf-city-selector' ); ?>
                                            </th>
                                            <th>
                                                <?php esc_html_e( 'Country', 'acf-city-selector' ); ?>
                                            </th>
                                        </tr>
                                        </thead>
                                        <?php foreach( $city_array as $city ) { ?>
                                            <tr>
                                                <td>
                                                    <?php echo $city[ 'id' ]; ?>
                                                </td>
                                                <td>
                                                    <label>
                                                        <input name="row_id[]" type="checkbox" value="<?php echo $city[ 'id' ]; ?> <?php echo $city[ 'city_name' ]; ?>">
                                                    </label>
                                                </td>
                                                <td>
                                                    <?php echo $city[ 'city_name' ]; ?>
                                                </td>
                                                <td>
                                                    <?php echo $city[ 'state_name' ]; ?>
                                                </td>
                                                <td>
                                                    <?php _e( $city[ 'country' ], 'acf-city-selector' ); ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </table>

                                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Delete selected', 'acf-city-selector' ); ?>" />
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>

                <?php include 'admin-right.php'; ?>

            </div>

        </div>
        <?php
    }

