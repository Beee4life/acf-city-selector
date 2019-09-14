<?php
    /*
     * Content for the settings page
     */
    function acfcs_cities() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        // get all countries from database
        global $wpdb;
        $cities                  = [];
        $countries               = [];
        $search_criteria_state   = false;
        $search_criteria_country = false;
        $searched_term           = false;
        $selected_limit          = false;
        
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
                    'name' => $data->country,
                ];
            }
            
            // echo '<pre>'; var_dump($countries); echo '</pre>'; exit;
            
            // if there is more than 1 country, place NL on top
            if ( count( $countries ) > 1 ) {
                $array_key_nl = false;
                $counter = 1;
                // echo '<pre>'; var_dump($countries); echo '</pre>'; exit;
                foreach ( $countries as $key => $country ) {
                    if ( $counter == 1 ) {
                        $first_array_key = $key;
                    }
                    if ( 'NL' == $country[ 'code' ] ) {
                        $array_key_nl = $key;
                    }
                }
                
                if ( false != $array_key_nl ) {
                    acfcs_move_array_element( $countries, $array_key_nl, 0 );
                }
            }
            
            // get states for these countries
            if ( ! empty( $countries ) ) {
                $states = [];
                foreach ( $countries as $country ) {
                    $results = $wpdb->get_results(
                        "SELECT * FROM " . $wpdb->prefix . "cities
                            WHERE country_code = '" . $country[ 'code' ] . "'
                            group by state_code
                            order by state_name ASC
                        " );
    
                    if ( count( $results ) > 0 ) {
                        foreach ( $results as $data ) {
                            $states[] = array(
                                'state' => $data->country_code . '-' . $data->state_code,
                                'name'  => $data->state_name,
                            );
                        }
                    }
                }
            }
        }
        
        if ( isset( $_GET[ 'acfcs-search' ] ) ) {
            $search_limit            = false;
            $selected_limit          = ( isset( $_POST[ 'acfcs_limit' ] ) ) ? $_POST[ 'acfcs_limit' ] : false;
            $search_criteria_state   = ( isset( $_POST[ 'acfcs_state' ] ) ) ? $_POST[ 'acfcs_state' ] : false;
            $search_criteria_country = ( isset( $_POST[ 'acfcs_country' ] ) ) ? $_POST[ 'acfcs_country' ] : false;
            $searched_term           = ( isset( $_POST[ 'acfcs_search' ] ) ) ? $_POST[ 'acfcs_search' ] : false;
            $where                   = [];
            
            if ( false != $search_criteria_state ) {
                $where[] .= "state_code = '" . substr( $search_criteria_state, 3, 2) . "' AND country_code = '" . substr( $search_criteria_state, 0, 2) . "'";
            } elseif ( false != $search_criteria_country ) {
                $where[] = "country_code = " . $search_criteria_country;
            }
            if ( false != $searched_term ) {
                $search[] = 'state_name LIKE "%' . $searched_term . '%"';
                $search[] = 'country LIKE "%' . $searched_term . '%"';
                $search[] = 'city_name LIKE "%' . $searched_term . '%"';
                $where[] = implode( ' OR ', $search );
            }
            if ( '0' != $selected_limit ) {
                $search_limit = "LIMIT " . $selected_limit;
            }
            
            $where = implode( ' AND ', $where );
            
            $cities = $wpdb->get_results("SELECT *
                FROM " . $wpdb->prefix . "cities
                WHERE " . $where . "
                order by country ASC
                " . $search_limit . "
            " );
            
            $result_count = count( $cities );
        }
        ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1><?php esc_html_e( 'ACF City Selector', 'acf-city-selector' ); ?></h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <!-- left part -->
            <div class="admin_left">

                <h2><?php esc_html_e( 'Search for cities', 'acf-city-selector' ); ?></h2>
                <div>
                    <small>
<!--                        Select only 1 criteria.-->
                    </small>
                </div>
    
                <form enctype="multipart/form-data" action="<?php echo admin_url( 'options-general.php?page=acfcs-cities&acfcs-search' ); ?>" method="POST">
                    <?php if ( count( $countries ) > 0 ) { ?>
                        <div class="acfcs__search-criteria acfcs__search-criteria--country">
                            <label>
                                <select name="acfcs_country" class="">
                                    <option value=""><?php _e( 'Select a country', 'acfcs' ); ?></option>
                                    <?php foreach( $countries as $country ) { ?>
                                        <option value="<?php echo $country[ 'code' ]; ?>"><?php echo $country[ 'name' ]; ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>
    
                        <div class="acfcs__search-criteria acfcs__search-criteria--or">
                            <small>OR</small>
                        </div>
    
                        <div class="acfcs__search-criteria acfcs__search-criteria--state">
                            <label>
                                <select name="acfcs_state" class="">
                                    <option value=""><?php _e( 'Select a state', 'acfcs' ); ?></option>
                                    <?php foreach( $states as $state ) { ?>
                                        <?php
                                            $selected = false;
                                            if ( false != $search_criteria_state ) {
                                                if ( $state[ 'state' ] == $search_criteria_state ) {
                                                    $selected = ' selected="selected"';
                                                }
                                            }
                                        ?>
                                        <option value="<?php echo $state[ 'state' ]; ?>"<?php echo $selected; ?>><?php echo $state[ 'name' ]; ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>
    
                        <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                        <div class="acfcs__search-criteria acfcs__search-criteria--search">
                            <label>
                                <input name="acfcs_search" value="<?php if ( false != $searched_term ) { echo $searched_term; } ?>" placeholder="<?php esc_html_e( 'Free search', 'acfcs' ); ?>">
                            </label>
                        </div>
    
                        <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                        <div class="acfcs__search-criteria acfcs__search-criteria--limit">
                            <label>
                                <select name="acfcs_limit" class="">
                                    <option value="0"><?php esc_html_e( 'Limit', 'acfcs' ); ?></option>
                                    <?php
                                        $limits = [ 10, 20, 50, 100 ];
                                        foreach( $limits as $limit ) {
                                            $selected = false;
                                            if ( $limit == $selected_limit ) {
                                                $selected = ' selected="selected"';
                                            }
                                            echo '<option value="' . $limit . '" ' . $selected . '>' . $limit . '</option>';
                                        }
                                    ?>
                                </select>
                            </label>
                        </div>
                    <?php } ?>
                    
                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Search', 'acf-city-selector' ); ?>" />
                </form>
                
                <?php if ( ! empty( $cities ) ) { ?>
                    <form enctype="multipart/form-data" action="<?php echo admin_url( 'options-general.php?page=acfcs-cities' ); ?>" method="POST">
                        <input name="acfcs_delete_row_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-delete-row-nonce' ); ?>" />
                        <div class="acfcs__search-results">
                            <p><?php echo $result_count; ?> <?php esc_html_e( 'results',  'acfcs' ); ?></p>
                            <table class="acfcs__table acfcs__table--search">
                                <thead>
                                <tr>
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
                                            <label>
                                                <input name="row_id[]" type="checkbox" value="<?php echo $city->id; ?> <?php echo $city->city_name; ?>"> <?php echo $city->id; ?>
                                            </label>
                                        </td>
                                        <td>
                                            <?php echo $city->city_name; ?>
                                        </td>
                                        <td>
                                            <?php echo $city->state_name; ?>
                                        </td>
                                        <td>
                                            <?php _e( $city->country, 'acfcs' ); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
    
                            <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Delete selected', 'acf-city-selector' ); ?>" />
                        </form>
                        
                    </div>
                <?php } ?>

            </div><!-- end .admin_left -->

            <?php include( 'admin-right.php' ); ?>

        </div><!-- end .wrap -->
        <?php
    }

