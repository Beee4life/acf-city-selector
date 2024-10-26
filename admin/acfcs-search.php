<?php
    /*
     * Content for the search page
     */
    function acfcs_search() {

        if ( ! current_user_can( apply_filters( 'acfcs_user_cap', 'manage_options' ) ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'acf-city-selector' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $all_countries           = acfcs_get_countries( false );
        $cities                  = array();
        $city_array              = array();
        $countries               = array();
        
        $search_criteria_state   = false;
        $search_criteria_country = false;
        $searched_orderby        = false;
        $searched_term           = false;
        $selected_limit          = false;
        $limit                   = 100;
        $states                  = acfcs_get_states_optgroup();
        
        if ( isset( $_POST[ 'acfcs_search_form_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_search_form_nonce' ] ) ), 'acfcs-search-form-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
                return;
            } else {
                $search_criteria_state   = ( ! empty( $_POST[ 'acfcs_state' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_state' ] ) ) : false;
                $search_criteria_country = ( ! empty( $_POST[ 'acfcs_country' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_country' ] ) ) : false;
                $searched_orderby        = ( ! empty( $_POST[ 'acfcs_orderby' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_orderby' ] ) ) : false;
                $searched_term           = ( ! empty( $_POST[ 'acfcs_search' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_search' ] ) ) : false;
                $selected_limit          = ( ! empty( $_POST[ 'acfcs_limit' ] ) ) ? (int) $_POST[ 'acfcs_limit' ] : $limit;
            }
        }
        
        // if there is at least 1 country
        if ( ! empty( $all_countries ) ) {
            foreach ( $all_countries as $country_code => $country_name ) {
                $countries[] = [
                    'code' => $country_code,
                    'name' => esc_attr( $country_name ),
                ];
            }
        }

        // if user has searched
        if ( isset( $_POST[ 'acfcs_search_form' ] ) ) {
            $cities = acfcs_get_searched_cities();

            foreach( $cities as $key => $city_object ) {
                $city_object->country = acfcs_get_country_name( strtolower( $city_object->country_code ) );
                $city_array[] = (array) $city_object;
            }

            if ( ! empty( $city_array ) ) {
                uasort( $city_array, 'acfcs_sort_array_with_quotes' );
            }
            $result_count = count( $city_array );
        }
        ?>
        <div class="wrap acfcs">
            <h1>ACF City Selector</h1>
            
            <?php do_action( 'acfcs_admin_menu' ); ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">
                        <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Search for cities', 'acf-city-selector' ) ); ?>

                        <?php if ( count( $countries ) == 0 ) { ?>
                            <?php /* translators: %s link tag, %s, dashboard link, %s anchor */ ?>
                            <?php echo sprintf( '<div>%s</div>', sprintf( esc_html__( "You haven't imported any cities yet. Import any files from your %s.", 'acf-city-selector' ), sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=acfcs-dashboard' ) ), esc_html__( 'dashboard', 'acf-city-selector' ) ) ) ); ?>
                        <?php } else { ?>
                            <form action="" method="POST">
                                <input name="acfcs_search_form" type="hidden" value="1" />
                                <input name="acfcs_search_form_nonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'acfcs-search-form-nonce' ) ); ?>" />

                                <div class="acfcs__search-form">
                                    <?php // if there's only 1 country, no need to add country dropdown ?>
                                    <?php if ( count( $countries ) > 1 ) { ?>
                                        <div class="acfcs__search-criteria acfcs__search-criteria--country">
                                            <label for="acfcs_country" class="screen-reader-text"><?php echo esc_attr( apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) ) ); ?></label>
                                            <select name="acfcs_country" id="acfcs_country">
                                                <option value="">
                                                    <?php echo esc_attr( apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) ) ); ?>
                                                </option>
                                                <?php foreach( $countries as $country ) { ?>
                                                    <?php $selected = ( $country[ 'code' ] == $search_criteria_country ) ? ' selected="selected"' : false; ?>
                                                    <option value="<?php echo esc_attr( strtolower( $country[ 'code' ] ) ); ?>"<?php echo esc_attr( $selected ); ?>>
                                                        <?php echo esc_html( $country[ 'name' ] ); ?>
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
                                            <?php echo esc_attr( apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) ) ); ?>
                                        </label>
                                        <select name="acfcs_state" id="acfcs_state">
                                            <option value="">
                                                <?php echo esc_attr( apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) ) ); ?>
                                            </option>
                                            <?php
                                                foreach( $states as $state ) {
                                                    if ( 'open_optgroup' == $state[ 'state' ] ) {
                                                        echo '<optgroup label="'. esc_attr( $state[ 'name' ] ) . '">';
                                                    }
                                                    if ( strpos( $state[ 'state' ], 'optgroup' ) === false ) {
                                                        $selected = ( $state[ 'state' ] == $search_criteria_state ) ? ' selected="selected"' : false;
                                                        echo '<option value="' . esc_attr( $state[ 'state' ] ) . '"' . esc_attr( $selected ) . '>' . esc_attr( $state[ 'name' ] ) . '</option>';
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
                                        <label for="acfcs_search" class="screen-reader-text"><?php esc_attr_e( 'Search term', 'acf-city-selector' ); ?></label>
                                        <input name="acfcs_search" id="acfcs_search" type="text" value="<?php if ( false != $searched_term ) { echo esc_attr( stripslashes( $searched_term ) ); } ?>" placeholder="<?php esc_html_e( 'City name', 'acf-city-selector' ); ?>">
                                    </div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--limit">
                                        <label for="acfcs_limit" class="screen-reader-text"><?php esc_attr_e( 'Limit', 'acf-city-selector' ); ?></label>
                                        <input name="acfcs_limit" id="acfcs_limit" type="number" value="<?php if ( false != $selected_limit ) { echo esc_attr( $selected_limit) ; } ?>" placeholder="<?php esc_html_e( 'Limit', 'acf-city-selector' ); ?>">
                                    </div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--plus">+</div>

                                    <div class="acfcs__search-criteria acfcs__search-criteria--orderby">
                                        <label for="acfcs_orderby" class="screen-reader-text"><?php esc_attr_e( 'Order by', 'acf-city-selector' ); ?></label>
                                        <select name="acfcs_orderby" id="acfcs_orderby">
                                            <option value="">
                                                <?php esc_attr_e( 'Order by', 'acf-city-selector' ); ?>
                                            </option>
                                            <?php
                                                $orderby = [
                                                    esc_attr__( 'City', 'acf-city-selector' ),
                                                    esc_attr__( 'State', 'acf-city-selector' ),
                                                ];
                                                foreach( $orderby as $criterium ) {
                                                    $selected = ( $criterium == $searched_orderby ) ? ' selected' : false;
                                                    echo '<option value="' . esc_attr( $criterium ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( ucfirst( $criterium ) ) . '</option>';
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
                                <?php esc_html_e( 'No results, please try again.', 'acf-city-selector'); ?>
                            </p>
                        <?php } elseif ( ! empty( $cities ) ) { ?>
                            <form enctype="multipart/form-data" action="" method="POST">
                                <input name="acfcs_delete_row_nonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'acfcs-delete-row-nonce' ) ); ?>" />
                                <div class="acfcs__search-results">
                                    <?php echo sprintf( '<p class="hide568">%s</p>', esc_html__( 'Table scrolls horizontally.', 'acf-city-selector' ) ); ?>
                                    <?php echo sprintf( '<p>%d %s</p>', esc_html( $result_count ), esc_html( _n( 'result',  'results', $result_count, 'acf-city-selector' ) ) ); ?>
                                    <?php
                                        $table_headers = [
                                            __( 'ID', 'acf-city-selector' ),
                                            __( 'Select', 'acf-city-selector' ),
                                            __( 'City', 'acf-city-selector' ),
                                            __( 'State', 'acf-city-selector' ),
                                            __( 'Country', 'acf-city-selector' ),
                                        ];
                                    ?>

                                    <table class="acfcs__table acfcs__table--search scrollable">
                                        <thead>
                                        <tr>
                                            <?php foreach( $table_headers as $column ) { ?>
                                                <?php echo sprintf( '<th>%s</th>', esc_attr( $column ) ); ?>
                                            <?php } ?>
                                        </tr>
                                        </thead>
                                        <?php foreach( $city_array as $city ) { ?>
                                            <tr>
                                                <?php echo sprintf( '<td>%s</td>', esc_attr( $city[ 'id' ] ) ); ?>
                                                <?php echo sprintf( '<td>%s</td>', sprintf( '<label>%s</label>', sprintf( '<input name="row_id[]" type="checkbox" value="%s %s">', esc_attr( $city[ 'id' ] ), esc_attr( $city[ 'city_name' ] ) ) ) ); ?>
                                                <?php echo sprintf( '<td>%s</td>', esc_attr( $city[ 'city_name' ] ) ); ?>
                                                <?php echo sprintf( '<td>%s</td>', esc_attr( $city[ 'state_name' ] ) ); ?>
                                                <?php echo sprintf( '<td>%s</td>', esc_attr( $city[ 'country' ], 'acf-city-selector' ) ); ?>
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

