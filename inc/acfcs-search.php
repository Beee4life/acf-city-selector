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
        $cities                  = [];
        $countries               = [];
        $search_criteria_state   = false;
        $searched_term           = false;
        $selected_limit          = false;
        $united_states           = __( 'United States', 'acf-city-selector' );

        // get cities by country
        $results = $wpdb->get_results( "SELECT *
            FROM " . $wpdb->prefix . "cities
            group by country_code
            order by country ASC
        " );

        // if there is at least 1 country
        if ( count( $results ) > 0 ) {
            foreach ( $results as $data ) {
                $countries[] = [
                    'code' => $data->country_code,
                    'name' => __( $data->country, 'acf-city-selector' ),
                ];
            }

            // if there is more than 1 country, place default language/country on top
            if ( count( $countries ) > 1 ) {
                $language_code = get_option( 'WPLANG' );
                if ( false != $language_code ) {
                    if ( 2 == strlen( $language_code ) ) {
                        $country_code = $language_code;
                    } else {
                        $country_code = substr( $language_code, 3, 2 );
                    }

                    foreach ( $countries as $key => $country ) {
                        if ( $country_code == $country[ 'code' ] ) {
                            $array_key = $key;
                        }
                    }
                    if ( isset( $array_key ) ) {
                        ACF_City_Selector::acfcs_move_array_element( $countries, $array_key, 0 );
                    }
                }
            }

            // get states for these countries
            if ( ! empty( $countries ) ) {
                $states = [];
                foreach ( $countries as $country ) {
                    $states[] = array(
                        'state' => 'open_optgroup',
                        'name'  => acfcs_get_country_name( $country[ 'code' ] ),
                    );
                    $order = 'ORDER BY state_name ASC';
                    if ( 'FR' == $country[ 'code' ] ) {
                        $order = "ORDER BY LENGTH(state_name), state_name";
                    }
                    $sql = $wpdb->prepare( "
                        SELECT *
                        FROM " . $wpdb->prefix . "cities
                        WHERE country_code = '%s'
                        GROUP BY state_code
                        " . $order, $country[ 'code' ]
                    );
                    $results = $wpdb->get_results( $sql );

                    if ( count( $results ) > 0 ) {
                        foreach ( $results as $data ) {
                            $states[] = array(
                                'state' => strtolower( $data->country_code ) . '-' . strtolower( $data->state_code ),
                                'name'  => __( $data->state_name, 'acf-city-selector' ) . ' (' . $data->country_code . ')',
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
            $search_limit            = false;
            $selected_limit          = ( isset( $_POST[ 'acfcs_limit' ] ) ) ? $_POST[ 'acfcs_limit' ] : false;
            $search_criteria_state   = ( isset( $_POST[ 'acfcs_state' ] ) ) ? $_POST[ 'acfcs_state' ] : false;
            $search_criteria_country = ( isset( $_POST[ 'acfcs_country' ] ) ) ? $_POST[ 'acfcs_country' ] : false;
            $searched_term           = ( isset( $_POST[ 'acfcs_search' ] ) ) ? $_POST[ 'acfcs_search' ] : false;
            $where                   = [];

            if ( false != $search_criteria_state ) {
                $where[] = "state_code = '" . substr( $search_criteria_state, 3, 3) . "' AND country_code = '" . substr( $search_criteria_state, 0, 2) . "'";
            } elseif ( false != $search_criteria_country ) {
                $where[] = "country_code = '" . $search_criteria_country . "'";
            }
            if ( false != $searched_term ) {
                $search[] = 'state_name LIKE "%' . $searched_term . '%"';
                $search[] = 'country LIKE "%' . $searched_term . '%"';
                $search[] = 'city_name LIKE "%' . $searched_term . '%"';
                $where[] = implode( ' OR ', $search );
            }
            if ( false != $selected_limit ) {
                $search_limit = "LIMIT " . $selected_limit;
            }

            if ( ! empty( $where ) ) {
                $where = "WHERE " . implode( ' AND ', $where );
            } else {
                $where = false;
            }

            $cities = $wpdb->get_results("SELECT *
                FROM " . $wpdb->prefix . "cities
                " . $where . "
                order by country ASC, state_name ASC, city_name ASC
                " . $search_limit . "
            " );

            $result_count = count( $cities );
        }

        // output
        ?>
        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="admin_left">

                <h2><?php esc_html_e( 'Search for cities', 'acf-city-selector' ); ?></h2>

                <form enctype="multipart/form-data" action="<?php echo admin_url( 'options-general.php?page=acfcs-search' ); ?>" method="POST">
                    <input name="acfcs_search_form" type="hidden" value="1" />
                    <?php if ( count( $countries ) > 0 ) { ?>
                        <?php // if there's only 1 country, no need to add country dropdown ?>
                        <?php if ( count( $countries ) > 1 ) { ?>
                            <div class="acfcs__search-criteria acfcs__search-criteria--country">
                                <label>
                                    <select name="acfcs_country" class="">
                                        <option value=""><?php _e( 'Select a country', 'acf-city-selector' ); ?></option>
                                        <?php foreach( $countries as $country ) { ?>
                                            <?php $selected = ( $country[ 'code' ] == $search_criteria_country ) ? ' selected="selected"' : false; ?>
                                            <option value="<?php echo $country[ 'code' ]; ?>"<?php echo $selected; ?>><?php echo __( $country[ 'name' ], 'acf-city-selector' ); ?></option>
                                        <?php } ?>
                                    </select>
                                </label>
                            </div>

                            <div class="acfcs__search-criteria acfcs__search-criteria--or">
                                <small><?php esc_html_e( 'OR', 'acf-city-selector' ); ?></small>
                            </div>
                        <?php } ?>

                        <div class="acfcs__search-criteria acfcs__search-criteria--state">
                            <label>
                                <select name="acfcs_state" class="">
                                    <option value=""><?php _e( 'Select a province/state', 'acf-city-selector' ); ?></option>
                                    <?php foreach( $states as $state ) { ?>
                                        <?php if ( 'open_optgroup' == $state[ 'state' ] ) { ?>
                                            <optgroup label="<?php echo $state[ 'name' ]; ?>">
                                        <?php } ?>
                                        <?php if ( strpos( $state[ 'state' ], 'optgroup' ) === false ) { ?>
                                            <?php $selected = ( $state[ 'state' ] == $search_criteria_state ) ? ' selected="selected"' : false; ?>
                                            <option value="<?php echo $state[ 'state' ]; ?>"<?php echo $selected; ?>><?php echo __( $state[ 'name' ], 'acf-city-selector' ); ?></option>
                                        <?php } ?>
                                        <?php if ( 'close_optgroup' == $state[ 'state' ] ) { ?>
                                            </optgroup>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>

                        <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                        <div class="acfcs__search-criteria acfcs__search-criteria--search">
                            <label>
                                <input name="acfcs_search" value="<?php if ( false != $searched_term ) { echo $searched_term; } ?>" placeholder="<?php esc_html_e( 'Search term', 'acf-city-selector' ); ?>">
                            </label>
                        </div>

                        <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                        <div class="acfcs__search-criteria acfcs__search-criteria--limit">
                            <label>
                                <select name="acfcs_limit" class="">
                                    <option value="0"><?php esc_html_e( 'Limit', 'acf-city-selector' ); ?></option>
                                    <?php
                                        $limits = [ 10, 20, 50, 100 ];
                                        foreach( $limits as $limit ) {
                                            $selected = ( $limit == $selected_limit ) ? ' selected' : false;
                                            echo '<option value="' . $limit . '" ' . $selected . '>' . $limit . '</option>';
                                        }
                                    ?>
                                </select>
                            </label>
                        </div>
                    <?php } ?>

                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Search', 'acf-city-selector' ); ?>" />
                </form>

                <?php if ( isset( $_GET[ 'acfcs-search' ] ) && empty( $cities ) ) { ?>
                    <p>
                        <?php _e( 'No results, please try again.', 'acf-city-selector'); ?>
                    </p>
                <?php } elseif ( ! empty( $cities ) ) { ?>
                    <?php // results output here ?>
                    <form enctype="multipart/form-data" action="<?php echo admin_url( 'options-general.php?page=acfcs-cities' ); ?>" method="POST">
                        <input name="acfcs_delete_row_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-delete-row-nonce' ); ?>" />
                        <div class="acfcs__search-results">
                            <p><?php echo $result_count; ?> <?php esc_html_e( 'results',  'acf-city-selector' ); ?></p>
                            <table class="acfcs__table acfcs__table--search">
                                <thead>
                                <tr>
                                    <th>
                                        <?php esc_html_e( 'Row ID', 'acf-city-selector' ); ?>
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
                                <?php foreach( $cities as $city ) { ?>
                                    <tr>
                                        <td>
                                            <?php echo $city->id; ?>
                                        </td>
                                        <td>
                                            <label>
                                                <input name="row_id[]" type="checkbox" value="<?php echo $city->id; ?> <?php echo $city->city_name; ?>">
                                            </label>
                                        </td>
                                        <td>
                                            <?php echo $city->city_name; ?>
                                        </td>
                                        <td>
                                            <?php echo $city->state_name; ?>
                                        </td>
                                        <td>
                                            <?php echo __( $city->country, 'acf-city-selector' ); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>

                            <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Delete selected', 'acf-city-selector' ); ?>" />
                        </div>
                    </form>
                <?php } ?>
            </div>

            <?php include( 'admin-right.php' ); ?>

        </div>
        <?php
    }

