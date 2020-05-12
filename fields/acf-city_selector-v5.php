<?php
    // exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    if ( ! class_exists( 'acf_field_city_selector' ) ) :

        /**
         * Main class
         */
        class acf_field_city_selector extends acf_field {

            /*
             * Function index
             * - construct( $settings )
             * - render_field_settings( $field )
             * - render_field( $field )
             * - input_admin_head()
             * - load_value( $value, $post_id, $field )
             * - validate_value( $valid, $value, $field, $input )
             */

            /*
             *  __construct
             *
             *  This function will setup the class functionality
             *
             *  @param   n/a
             *  @return  n/a
             */
            function __construct( $settings ) {

                $this->name     = 'acf_city_selector';
                $this->label    = 'City Selector';
                $this->category = 'Choice';
                $this->defaults = array(
                    'show_labels' => 1,
                );

                /*
                *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
                *  var message = acf._e('FIELD_NAME', 'error');
                */

                $this->l10n = array(
                    'i18n_select_city' => __( 'Select a city', 'acf-city-selector' ), // shown after country change
                );

                $this->settings = $settings;

                // do not delete!
                parent::__construct();

            }

            /*
             * render_field_settings()
             *
             * Create extra settings for your field. These are visible when editing a field
             *
             * @type    action
             * @param   $field (array) the $field being edited
             * @return  n/a
             */
            function render_field_settings( $field ) {

                $select_options = array(
                    1 => __( 'Yes', 'acf-city-selector' ),
                    0 => __( 'No', 'acf-city-selector' )
                );
                acf_render_field_setting( $field, array(
                    'choices'      => $select_options,
                    'instructions' => esc_html__( 'Show field labels above the dropdown menus', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Show labels', 'acf-city-selector' ),
                    'layout'       => 'horizontal',
                    'name'         => 'show_labels',
                    'type'         => 'radio',
                    'value'        => $field[ 'show_labels' ],
                ) );

                $field_vars[ 'show_labels' ] = 0;
                $countries                   = acfcs_populate_country_select( $field_vars );
                acf_render_field_setting( $field, array(
                    'choices'      => $countries,
                    'instructions' => esc_html__( 'Pre-select a default country when creating a new post or adding a new row in a repeater', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Default country', 'acf-city-selector' ),
                    'name'         => 'default_country',
                    'type'         => 'select',
                ) );

            }

            /*
             * render_field()
             *
             * Create the HTML interface for your field
             *
             * @type    action
             * @param   $field (array) the $field being edited
             * @return  n/a
             */
            function render_field( $field ) {

                // $selected_country = false;
                // $post_id = get_the_ID();

                $countries         = acfcs_populate_country_select( $field );
                $default_country   = ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
                $field_id          = $field[ 'id' ];
                $field_name        = $field[ 'name' ];
                $prefill_cities    = [];
                $prefill_states    = [];
                $selected_city     = false;
                $selected_country  = false;
                $selected_selected = ' selected="selected"';
                $selected_state    = false;
                $show_labels       = $field[ 'show_labels' ];

                if ( is_array( $field[ 'value' ] ) && ! empty( $field[ 'value' ] ) ) {
                    $selected_country = ( isset( $field[ 'value' ][ 'countryCode' ] ) ) ? $field[ 'value' ][ 'countryCode' ] : false;
                    $selected_state   = ( isset( $field[ 'value' ][ 'stateCode' ] ) ) ? $field[ 'value' ][ 'stateCode' ] : false;
                    $selected_city    = ( isset( $field[ 'value' ][ 'cityName' ] ) ) ? $field[ 'value' ][ 'cityName' ] : false;
                }

                if ( false !== $default_country && false == $selected_country ) {
                    // New post
                    // Load all states for $default_country
                    $first_option   = [ '' => esc_html__( 'Select a province/state', 'acf-city-selector' ) ];
                    $states         = acfcs_populate_state_select( $default_country, $field );
                    $prefill_states = array_merge( $first_option, $states );
                } elseif ( false != $selected_country ) {
                    $country_state_code = $field[ 'value' ][ 'stateCode' ];
                    if ( false !== $selected_country ) {
                        $prefill_states = acfcs_populate_state_select( $selected_country, $field );
                    }
                    if ( $country_state_code ) {
                        $state_code = $country_state_code;
                        if ( 3 < strlen( $country_state_code ) ) {
                            $state_code = substr( $country_state_code, 3, 3 );
                        }
                        $prefill_cities = acfcs_populate_city_select( $selected_country, $state_code, $field );
                    }
                }
            ?>
                <div class="dropdown-box cs-countries">
                    <?php if ( 1 == $show_labels ) { ?>
                        <div class="acf-input-header">
                            <?php esc_html_e( 'Select a country', 'acf-city-selector' ); ?>
                        </div>
                    <?php } ?>
                    <label for="<?php echo $field_id; ?>countryCode" class="screen-reader-text">
                        <?php esc_html_e( 'Select a country', 'acf-city-selector' ); ?>
                    </label>
                    <select name="<?php echo $field_name; ?>[countryCode]" id="<?php echo $field_id; ?>countryCode" class="countrySelect">
                        <?php
                            foreach ( $countries as $country_code => $country ) {
                                $selected = false;
                                if ( false !== $selected_country ) {
                                    if ( $selected_country == $country_code ) {
                                        $selected = $selected_selected;
                                    }
                                } elseif ( ! empty( $default_country ) ) {
                                    if ( $default_country == $country_code ) {
                                        $selected = $selected_selected;
                                    }
                                }
                            ?>
                            <option value="<?php echo $country_code; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="dropdown-box cs-provinces">
                    <?php if ( 1 == $show_labels ) { ?>
                        <div class="acf-input-header">
                            <?php esc_html_e( 'Select a province/state', 'acf-city-selector' ); ?>
                        </div>
                    <?php } ?>
                    <label for="<?php echo $field_id; ?>stateCode" class="screen-reader-text">
                        <?php esc_html_e( 'Select a province/state', 'acf-city-selector' ); ?>
                    </label>
                    <select name="<?php echo $field_name; ?>[stateCode]" id="<?php echo $field_id; ?>stateCode" class="countrySelect">
                        <?php
                            if ( ! empty( $prefill_states ) ) {
                                foreach( $prefill_states as $country_state_code => $label ) {
                                    $selected = false;
                                    $selected = ( $selected_state == $country_state_code ) ? $selected_selected : false;
                                    ?>
                                    <option value="<?php echo $country_state_code; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                    <?php
                                }
                            } else {
                                // content will be dynamically generated on.change country
                            }
                        ?>
                    </select>
                </div>

                <div class="dropdown-box cs-cities">
                    <?php if ( 1 == $show_labels ) { ?>
                        <div class="acf-input-header">
                            <?php esc_html_e( 'Select a city', 'acf-city-selector' ); ?>
                        </div>
                    <?php } ?>
                    <label for="<?php echo $field_id; ?>cityName" class="screen-reader-text">
                        <?php esc_html_e( 'Select a city', 'acf-city-selector' ); ?>
                    </label>
                    <select name="<?php echo $field_name; ?>[cityName]" id="<?php echo $field_id; ?>cityName" class="countrySelect">
                        <?php
                            if ( ! empty( $prefill_cities ) ) {
                                foreach( $prefill_cities as $city_name => $label ) {
                                    $selected = false;
                                    $selected = ( $selected_city == $city_name ) ? $selected_selected : false;
                                    ?>
                                    <option value="<?php echo $city_name; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                    <?php
                                }
                            } else {
                                // content will be dynamically generated on.change country
                            }
                        ?>
                    </select>
                </div>
                <?php
            }


            /*
             * input_admin_head()
             *
             * This action is called in the admin_head action on the edit screen where your field is created.
             * Use this action to add CSS and JavaScript to assist your render_field() action.
             *
             * @type    action (admin_head)
             * @param   n/a
             * @return  n/a
             */
            function input_admin_head() {

                $plugin_url     = $this->settings[ 'url' ];
                $plugin_version = $this->settings[ 'version' ];

                wp_register_script( 'acf-city-selector-js', "{$plugin_url}assets/js/city-selector.js", array( 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acf-city-selector-js' );
            }

            /*
             * load_value()
             *
             * This filter is applied to the $value after it is loaded from the db
             * This returns false if no country/state is selected (but empty values are stored)
             *
             * @type    filter
             * @param   $value (mixed) the value found in the database
             * @param   $post_id (mixed) the $post_id from which the value was loaded
             * @param   $field (array) the field array holding all the field options
             * @return  $value
             */
            function load_value( $value, $post_id, $field ) {

                if ( isset( $value[ 'countryCode' ] ) && '0' == $value[ 'countryCode' ] ) {
                    error_log( 'countryCode == 0' );
                }

                $country_code = ( isset( $value[ 'countryCode' ] ) ) ? $value[ 'countryCode' ] : false;
                $state_code   = ( isset( $value[ 'stateCode' ] ) ) ? substr( $value[ 'stateCode' ], 3 ) : false;

                if ( strlen( $country_code ) == 2 && ! empty( $stateCode ) ) {
                    global $wpdb;
                    $table                  = $wpdb->prefix . 'cities';
                    $row                    = $wpdb->get_row( "SELECT country, state_name FROM $table WHERE country_code= '$country_code' AND state_code= '$state_code'" );
                    $value[ 'stateCode' ]   = $state_code;
                    $value[ 'stateName' ]   = ( isset( $row->state_name ) ) ? $row->state_name : false;
                    $value[ 'countryName' ] = ( isset( $row->country ) ) ? $row->country : false;
                }

                return $value;
            }


            /*
            *  update_value()
            *
            *  This filter is applied to the $value before it is saved in the db
            *
            *  @param	$value (mixed) the value found in the database
            *  @param	$post_id (mixed) the $post_id from which the value was loaded
            *  @param	$field (array) the field array holding all the field options
            *  @return	$value
            */
            function update_value( $value, $post_id, $field ) {

                // if nothing is selected, set value to false
                if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                    $value = false;
                } elseif ( empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                    $value = false;
                }

                return $value;

            }

            /*
             * validate_value()
             *
             * This filter is used to perform validation on the value prior to saving.
             * All values are validated regardless of the field's required setting. This allows you to validate and return
             * messages to the user if the value is not correct
             *
             * @param   $valid (boolean) validation status based on the value and the field's required setting
             * @param   $value (mixed) the $_POST value
             * @param   $field (array) the field array holding all the field options
             * @param   $input (string) the corresponding input name for $_POST value
             * @return  $valid
             */
            function validate_value( $valid, $value, $field, $input ) {

                if ( 1 == $field[ 'required' ] ) {
                    if ( ! isset( $value[ 'cityName' ] ) ) {
                        $valid = __( "You didn't select a city", "acf-city-selector" );
                    }
                }

                return $valid;
            }
        }

        // initialize
        new acf_field_city_selector( $this->settings );

    endif; // class_exists check
