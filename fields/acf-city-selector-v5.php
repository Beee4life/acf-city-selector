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
                $this->category = __( 'Choice', 'acf_city_selector' );
                $this->defaults = array(
                    'show_labels'  => 1,
                    'which_fields' => 'all',
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
                    'instructions' => esc_html__( 'Pre-select a country when creating a new post or adding a new row in a repeater or a layout in flexible content/', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Default country', 'acf-city-selector' ),
                    'name'         => 'default_country',
                    'type'         => 'select',
                ) );

                $select_fields = array(
                    'all'           => __( 'All fields [default]', 'acf-city-selector' ),
                    'country_only'  => __( 'Country only', 'acf-city-selector' ),
                    'country_state' => __( 'Country + State/province', 'acf-city-selector' ),
                    'country_city'  => __( 'Country + City', 'acf-city-selector' ),
                );
                acf_render_field_setting( $field, array(
                    'choices'      => $select_fields,
                    'instructions' => esc_html__( 'Select which fields are used', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Fields to use', 'acf-city-selector' ),
                    'name'         => 'which_fields',
                    'type'         => 'radio',
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
                    // New post with default country, so load all states for $default_country
                    $first_option   = [ '' => esc_html__( 'Select a province/state', 'acf-city-selector' ) ];
                    $states         = acfcs_populate_state_select( $default_country, $field );
                    $prefill_states = array_merge( $first_option, $states );
                } elseif ( false != $selected_country ) {
                    $which_fields = ( isset( $field[ 'which_fields' ] ) ) ? $field[ 'which_fields' ] : false;

                    // check if cities and/or states are needed
                    if ( 'all' == $which_fields ) {
                        if ( false !== $selected_country ) {
                            $setting[ 'show_labels' ] = 0;
                            $prefill_states = acfcs_populate_state_select( $selected_country, $setting );
                            $prefill_cities = acfcs_populate_city_select( $selected_country, $selected_state, $setting );
                            $selected_state = $selected_country . '-' . $selected_state;
                        }
                    } elseif ( 'country_state' == $which_fields ) {
                        if ( false !== $selected_country ) {
                            $prefill_states = acfcs_populate_state_select( $selected_country, $field );
                            $selected_state = $selected_country . '-' . $selected_state;
                        }
                    } elseif ( 'country_city' == $which_fields ) {
                        if ( false !== $selected_country ) {
                            $prefill_cities = acfcs_populate_city_select( $selected_country, $selected_state, $field );
                        }
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

                <?php if ( 'all' == $field[ 'which_fields' ] || strpos( $field[ 'which_fields' ], 'state' ) !== false ) { ?>
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
                <?php } ?>

                <?php if ( 'all' == $field[ 'which_fields' ] || strpos( $field[ 'which_fields' ], 'city' ) !== false ) { ?>
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
                <?php } ?>
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

                wp_register_script( 'acf-city-selector-js', "{$plugin_url}assets/js/city-selector.js", array( 'jquery', 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acf-city-selector-js' );

                // check field settings
                $all_info = acfcs_get_field_settings();

                if ( ! empty( $all_info ) && 1 == acfcs_check_array_depth( $all_info ) ) {
                    $load_vars[ 'default_country' ] = ( isset( $all_info[ 'default_country' ] ) ) ? $all_info[ 'default_country' ] : false;
                    $load_vars[ 'show_labels' ]     = ( isset( $all_info[ 'show_labels' ] ) ) ? $all_info[ 'show_labels' ] : false;
                } else {
                    // @TODO: create fallback for other array depths (flexible content + 1 single field ?)
                    global $post;
                    if ( isset( $post->post_parent ) && isset( $post->ID ) ) {
                        if ( 91 == $post->post_parent && 95 != $post->ID ) {
                            // temp log
                            if ( 'acf-field-group' != get_post_type( get_the_ID() ) ) {
                                error_log( 'Make a fix for post: ' . get_the_ID() );
                            }
                        }
                    }
                }
                $load_vars[ 'which_fields' ] = ( isset( $all_info[ 'which_fields' ] ) ) ? $all_info[ 'which_fields' ] : 'all';

                wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', $load_vars );

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

                // this is here for some debugging. Most likely it will never 'come up'. Will be removed by 1.0.0 at the latest.
                if ( isset( $value[ 'countryCode' ] ) && '0' == $value[ 'countryCode' ] ) { error_log( 'countryCode == 0' ); }

                $country_code = ( isset( $value[ 'countryCode' ] ) ) ? $value[ 'countryCode' ] : false;

                if ( isset( $value[ 'stateCode' ] ) ) {
                    if ( 3 < strlen( $value[ 'stateCode' ] ) ) {
                        $state_code = substr( $value[ 'stateCode' ], 3 );
                    } elseif ( 1 <= strlen( $value[ 'stateCode' ] ) ) {
                        $state_code = $value[ 'stateCode' ];
                    }
                } else {
                    $state_code = false;
                }

                if ( strlen( $country_code ) == 2 && false != $state_code ) {
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

                $required = $field[ 'required' ];
                if ( 0 == $required ) {
                    if ( isset( $fields[ 'which_fields' ] ) && 'all' == $fields[ 'which_fields' ] || ! isset( $fields[ 'which_fields' ] ) ) {
                        // if nothing is selected, set value to false
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        } elseif ( empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $fields[ 'which_fields' ] ) && 'country_state' == $fields[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'stateCode' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $fields[ 'which_fields' ] ) && 'country_city' == $fields[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    }
                } else {
                    // field == required
                    if ( isset( $fields[ 'which_fields' ] ) && 'all' == $fields[ 'which_fields' ] || ! isset( $fields[ 'which_fields' ] ) ) {
                        // if nothing is selected, set value to false
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        } elseif ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'stateCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $fields[ 'which_fields' ] ) && 'country_state' == $fields[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'stateCode' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $fields[ 'which_fields' ] ) && 'country_city' == $fields[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    }
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

                $no_city = __( "You didn't select a city.", 'acf-city-selector' );
                if ( 1 == $field[ 'required' ] ) {
                    $nothing       = __( "You didn't select anything.", 'acf-city-selector' );
                    $no_country    = __( "You didn't select a country.", 'acf-city-selector' );
                    $no_state      = __( "You didn't select a state.", 'acf-city-selector' );
                    $no_state_city = __( "You didn't select a state and city.", 'acf-city-selector' );

                    if ( 'all' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $valid = $nothing;
                        } elseif ( empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $valid = $no_state_city;
                        } elseif ( empty( $value[ 'cityName' ] ) ) {
                            $valid = $no_city;
                        }
                    } elseif ( 'country_only' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) ) {
                            $valid = $no_country;
                        }
                    } elseif ( 'country_state' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) ) {
                            $valid = $nothing;
                        } elseif ( empty( $value[ 'stateCode' ] ) ) {
                            $valid = $no_state;
                        }
                    } elseif ( 'country_city' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $valid = $nothing;
                        } elseif ( empty( $value[ 'cityName' ] ) ) {
                            $valid = $no_city;
                        }
                    }
                }

                return $valid;
            }
        }

        // initialize
        new acf_field_city_selector( $this->settings );

    endif; // class_exists check
