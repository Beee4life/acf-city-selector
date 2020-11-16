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
                $this->category = __( 'Choice', 'acf-city-selector' );
                $this->defaults = array(
                    'show_labels'  => 1,
                    'which_fields' => 'all',
                    'use_select2'  => 1,
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

                $label_options = array(
                    1 => __( 'Yes', 'acf-city-selector' ),
                    0 => __( 'No', 'acf-city-selector' )
                );
                acf_render_field_setting( $field, array(
                    'choices'      => $label_options,
                    'instructions' => esc_html__( 'Show field labels above the dropdown menus', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Show labels', 'acf-city-selector' ),
                    'layout'       => 'horizontal',
                    'name'         => 'show_labels',
                    'type'         => 'radio',
                    'value'        => $field[ 'show_labels' ],
                ) );

                acf_render_field_setting( $field, array(
                    'choices'      => $label_options,
                    'instructions' => esc_html__( 'Use select2 for dropdowns', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Select2', 'acf-city-selector' ),
                    'layout'       => 'horizontal',
                    'name'         => 'use_select2',
                    'type'         => 'radio',
                    'value'        => $field[ 'use_select2' ],
                ) );

                $countries = acfcs_get_countries( true, false, true );
                acf_render_field_setting( $field, array(
                    'choices'      => $countries,
                    'instructions' => esc_html__( 'Select a default country for a new field', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Default country', 'acf-city-selector' ),
                    'name'         => 'default_country',
                    'type'         => 'select',
                ) );

                $default_country_fields = array(
                    'all'           => __( 'All fields [default]', 'acf-city-selector' ),
                    'country_only'  => __( 'Country only', 'acf-city-selector' ),
                    'country_state' => __( 'Country + State/province', 'acf-city-selector' ),
                    'country_city'  => __( 'Country + City', 'acf-city-selector' ),
                    'state_city'    => __( 'State/province + City', 'acf-city-selector' ),
                );
                acf_render_field_setting( $field, array(
                    'choices'      => $default_country_fields,
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

                $acfcs_dropdown       = 'acfcs__dropdown';
                $city_label           = apply_filters( 'acfcs_select_city_label', esc_html__( 'Select a city', 'acf-city-selector' ) );
                $countries            = acfcs_get_countries( true, $field );
                $country_label        = apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) );
                $default_country      = ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
                $field_id             = $field[ 'id' ];
                $field_name           = $field[ 'name' ];
                $prefill_cities       = [];
                $prefill_states       = [];
                $province_state_label = apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) );
                $selected_country     = ( isset( $field[ 'value' ][ 'countryCode' ] ) ) ? $field[ 'value' ][ 'countryCode' ] : false;
                $selected_state       = ( isset( $field[ 'value' ][ 'stateCode' ] ) ) ? $field[ 'value' ][ 'stateCode' ] : false;
                $selected_city        = ( isset( $field[ 'value' ][ 'cityName' ] ) ) ? $field[ 'value' ][ 'cityName' ] : false;
                $selected_selected    = ' selected="selected"';
                $show_first           = true;
                $show_labels          = ( isset( $field[ 'show_labels' ] ) ) ? $field[ 'show_labels' ] : true;
                $which_fields         = ( isset( $field[ 'which_fields' ] ) ) ? $field[ 'which_fields' ] : 'all';
                $use_select2          = ( isset( $field[ 'use_select2' ] ) ) ? $field[ 'use_select2' ] : false;
                $dropdown_class       = ( true == $use_select2 ) ? 'select2 ' . $acfcs_dropdown : $acfcs_dropdown;
                $data_label_value     = ( true == $show_labels ) ? '1' : '0';

                if ( false !== $default_country && false == $selected_country ) {
                    // New post with default country, so load all states + cities for $default_country
                    $prefill_states = acfcs_get_states( $default_country, $show_first, $field );
                    $prefill_cities = acfcs_get_cities( $default_country, false, $field );

                } elseif ( false !== $selected_country ) {
                    if ( in_array( $which_fields, [ 'all', 'country_state', 'state_city' ] ) ) {
                        $prefill_states = acfcs_get_states( $selected_country, $show_first, $field );
                    }
                    if ( in_array( $which_fields, [ 'all', 'country_city', 'state_city' ] ) ) {
                        $prefill_cities = acfcs_get_cities( $selected_country, $selected_state, $field );
                    }

                    // @TODO: do I still need this ?
                    // maybe only for non-select2
                    if ( 'all' == $which_fields ) {
                        $selected_state = $selected_country . '-' . $selected_state;
                    } elseif ( 'country_state' == $which_fields ) {
                        $selected_state = $selected_country . '-' . $selected_state;
                    } elseif ( 'country_city' == $which_fields ) {
                        // ?? none here ??
                    } elseif ( 'state_city' == $which_fields ) {
                        $selected_state = $selected_country . '-' . $selected_state;
                    }

                } elseif ( false == $default_country ) {
                    // no country set
                    if ( 'state_city' == $which_fields ) {
                        echo '<div class="acfcs"><div class="acfcs__notice field-error">';
                        echo esc_html__( "You haven't set a default country, so NO provinces/states and cities will be loaded.", 'acf-city-selector' );
                        echo '</div></div>';
                    }
                }

                if ( true == $use_select2 ) {
                    if ( isset( $field[ 'parent_layout' ] ) ) {
                        echo '<div class="acfcs"><div class="acfcs__notice field-error">';
                        echo esc_html__( "Sorry, select2 doesn't work (yet) in a flexible content block.", 'acf-city-selector' );
                        echo '</div></div>';
                    } elseif ( strpos( $field[ 'prefix' ], 'acfcloneindex' ) !== false ) {
                        // repeater
                        echo '<div class="acfcs"><div class="acfcs__notice field-error">';
                        echo esc_html__( "Sorry, select2 doesn't work (yet) in a repeater block.", 'acf-city-selector' );
                        echo '</div></div>';
                    }
                }

                if ( 'state_city' != $which_fields ) {
                ?>
                    <div class="dropdown-box cs-countries">
                        <?php if ( $show_labels ) { ?>
                            <div class="acf-input-header">
                                <?php echo $country_label; ?>
                            </div>
                        <?php } ?>
                        <label for="<?php echo $field_id; ?>countryCode" class="screen-reader-text">
                            <?php echo apply_filters( 'acfcs_select_country_label', esc_html__( 'Select a country', 'acf-city-selector' ) ); ?>
                        </label>
                        <select name="<?php echo $field_name; ?>[countryCode]" id="<?php echo $field_id; ?>countryCode" class="<?php echo $dropdown_class; ?> <?php echo $acfcs_dropdown; ?>--country" data-show-label="<?php echo $data_label_value; ?>">
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
                <?php } ?>

                <?php if ( 'all' == $which_fields || strpos( $which_fields, 'state' ) !== false ) { ?>
                    <div class="dropdown-box cs-provinces">
                        <?php if ( $show_labels ) { ?>
                            <div class="acf-input-header">
                                <?php echo $province_state_label; ?>
                            </div>
                        <?php } ?>
                        <label for="<?php echo $field_id; ?>stateCode" class="screen-reader-text">
                            <?php echo apply_filters( 'acfcs_select_province_state_label', esc_html__( 'Select a province/state', 'acf-city-selector' ) ); ?>
                        </label>
                        <select name="<?php echo $field_name; ?>[stateCode]" id="<?php echo $field_id; ?>stateCode" class="<?php echo $dropdown_class; ?> <?php echo $acfcs_dropdown; ?>--state" data-show-label="<?php echo $data_label_value; ?>">
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

                <?php if ( 'all' == $which_fields || strpos( $which_fields, 'city' ) !== false ) { ?>
                    <div class="dropdown-box cs-cities">
                        <?php if ( $show_labels ) { ?>
                            <div class="acf-input-header">
                                <?php echo $city_label; ?>
                            </div>
                        <?php } ?>
                        <label for="<?php echo $field_id; ?>cityName" class="screen-reader-text">
                            <?php echo apply_filters( 'acfcs_select_city_label', esc_html__( 'Select a city', 'acf-city-selector' ) ); ?>
                        </label>
                        <select name="<?php echo $field_name; ?>[cityName]" id="<?php echo $field_id; ?>cityName" class="<?php echo $dropdown_class; ?> <?php echo $acfcs_dropdown; ?>--city" data-show-label="<?php echo $data_label_value; ?>">
                            <?php
                                if ( ! empty( $prefill_cities ) ) {
                                    foreach( $prefill_cities as $city_name => $label ) {
                                        $selected = ( $selected_city == $city_name ) ? $selected_selected : false;
                                        ?>
                                        <option value="<?php echo $city_name; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                        <?php
                                    }
                                } else {
                                    // content will be dynamically generated on.change country or state
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
                }
                $load_vars[ 'show_labels' ]  = ( isset( $all_info[ 'show_labels' ] ) ) ? $all_info[ 'show_labels' ] : true;
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

                $state_code   = false;
                $country_code = ( isset( $value[ 'countryCode' ] ) ) ? $value[ 'countryCode' ] : false;

                if ( isset( $value[ 'stateCode' ] ) ) {
                    if ( 3 < strlen( $value[ 'stateCode' ] ) ) {
                        // $value[ 'stateCode' ] is longer than 3 characters, which starts with xx-
                        // where xx is the country code
                        $state_code = substr( $value[ 'stateCode' ], 3 );
                    } elseif ( 1 <= strlen( $value[ 'stateCode' ] ) ) {
                        // this is a fallback and is probably never reached
                        $state_code = $value[ 'stateCode' ];
                    }
                }

                if ( strlen( $country_code ) == 2 && false != $state_code ) {
                    global $wpdb;
                    $sql_query              = $wpdb->prepare( "SELECT country, state_name FROM {$wpdb->prefix}cities WHERE country_code= %s AND state_code= %s", $country_code, $state_code );
                    $row                    = $wpdb->get_row( $sql_query );
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
                    if ( isset( $field[ 'which_fields' ] ) && 'all' == $field[ 'which_fields' ] || ! isset( $field[ 'which_fields' ] ) ) {
                        // if nothing is selected, set value to false
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        } elseif ( empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'country_only' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'country_state' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'stateCode' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'country_city' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'state_city' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'stateCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    }
                } else {
                    // field == required
                    if ( isset( $field[ 'which_fields' ] ) && 'all' == $field[ 'which_fields' ] || ! isset( $field[ 'which_fields' ] ) ) {
                        // if nothing is selected, set value to false
                        if ( empty( $value[ 'countryCode' ] ) && empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        } elseif ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'stateCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'country_only' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'country_state' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'stateCode' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'country_city' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'countryCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    } elseif ( isset( $field[ 'which_fields' ] ) && 'state_city' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'stateCode' ] ) || empty( $value[ 'cityName' ] ) ) {
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

                if ( 1 == $field[ 'required' ] ) {
                    $nothing       = __( "You didn't select anything.", 'acf-city-selector' );
                    $no_city       = __( "You didn't select a city.", 'acf-city-selector' );
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
                    } elseif ( 'state_city' == $field[ 'which_fields' ] ) {
                        if ( empty( $value[ 'stateCode' ] ) && empty( $value[ 'cityName' ] ) ) {
                            $valid = $nothing;
                        } elseif ( empty( $value[ 'stateCode' ] ) ) {
                            $valid = $no_state;
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
