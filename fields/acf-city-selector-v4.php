<?php
    // exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    // check if class already exists
    if ( ! class_exists( 'acf_field_city_selector' ) ) :

        class acf_field_city_selector extends acf_field {

            // vars
            var $settings, // will hold info such as dir / path
                $defaults; // will hold default field options


            /*
            *  __construct
            *
            *  Set name / label needed for actions / filters
            *
            *  @since	3.6
            *  @date	23/01/13
            */
            function __construct( $settings ) {

                $this->name     = 'acf_city_selector';
                $this->label    = 'City Selector';
                $this->category = esc_attr__( 'Choice', 'acf-city-selector' );
                $this->defaults = array(
                    'show_labels'  => 1,
                    'which_fields' => 'all',
                );

                $this->l10n = acfcs_get_js_translations();

                parent::__construct();

                $this->settings = $settings;

            }


            /*
            *  create_options()
            *
            *  Create extra options for your field. This is rendered when editing a field.
            *  The value of $field['name'] can be used (like below) to save extra data to the $field
            *
            *  @type	action
            *  @since	3.6
            *  @date	23/01/13
            *
            *  @param	$field	- an array holding all the field's data
            */
            function create_options( $field ) {
                $field = array_merge($this->defaults, $field);

                // key is needed in the field names to correctly save the data
                $key       = $field[ 'name' ];
                $countries = acfcs_get_countries( true, false, true );

                $default_country_fields = array(
                    'all'           => esc_attr__( 'All fields [default]', 'acf-city-selector' ),
                    'country_only'  => esc_attr__( 'Country only', 'acf-city-selector' ),
                    'country_state' => esc_attr__( 'Country + State/province', 'acf-city-selector' ),
                    'country_city'  => esc_attr__( 'Country + City', 'acf-city-selector' ),
                    'state_city'    => esc_attr__( 'State/province + City', 'acf-city-selector' ),
                );

                $select_options = array(
                    1 => esc_attr__( 'Yes', 'acf-city-selector' ),
                    0 => esc_attr__( 'No', 'acf-city-selector' )
                );

                // Create Field Options HTML
                ?>
                <tr class="field_option field_option_<?php echo $this->name; ?>">
                    <td class="label">
                        <label><?php esc_attr_e('Show labels','acf-city-selector'); ?></label>
                        <p class="description"><?php esc_html_e( 'Show field labels above the dropdown menus', 'acf-city-selector' ); ?></p>
                    </td>
                    <td>
                        <?php
                            do_action('acf/create_field', array(
                                'type'    => 'radio',
                                'name'    => 'fields[' . $key . '][show_labels]',
                                'choices' => $select_options,
                                'value'   => $field[ 'show_labels' ],
                                'layout'  => 'horizontal',
                            ));
                        ?>
                    </td>
                </tr>
                <tr class="field_option field_option_<?php echo $this->name; ?>">
                    <td class="label">
                        <label><?php esc_attr_e('Default country','acf-city-selector'); ?></label>
                        <p class="description"><?php esc_html_e( 'Select a default country for a new field', 'acf-city-selector' ); ?></p>
                    </td>
                    <td>
                        <?php
                            do_action('acf/create_field', array(
                                'type'    => 'select',
                                'name'    => 'fields[' . $key . '][default_country]',
                                'choices' => $countries,
                                'value'   => ( isset( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false,
                                'layout'  => 'horizontal',
                            ));
                        ?>
                    </td>
                </tr>
                <tr class="field_option field_option_<?php echo $this->name; ?>">
                    <td class="label">
                        <label><?php esc_attr_e('Fields to use','acf-city-selector'); ?></label>
                        <p class="description"><?php esc_html_e( 'Select which fields are used', 'acf-city-selector' ); ?></p>
                    </td>
                    <td>
                        <?php
                            do_action('acf/create_field', array(
                                'type'    => 'radio',
                                'name'    => 'fields[' . $key . '][which_fields]',
                                'choices' => $default_country_fields,
                                'value'   => ( isset( $field[ 'which_fields' ] ) ) ? $field[ 'which_fields' ] : 'all',
                            ));
                        ?>
                    </td>
                </tr>
                <?php
            }


            /*
            *  create_field()
            *
            *  Create the HTML interface for your field
            *
            *  @param	$field - an array holding all the field's data
            *
            *  @type	action
            *  @since	3.6
            *  @date	23/01/13
            */
            function create_field( $field ) {
                $field            = array_merge( $this->defaults, $field );
                $default_country  = ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
                $prefill_cities   = array();
                $prefill_states   = array();
                $selected_country = ( isset( $field[ 'value' ][ 'countryCode' ] ) ) ? $field[ 'value' ][ 'countryCode' ] : false;
                $selected_state   = ( isset( $field[ 'value' ][ 'stateCode' ] ) ) ? $field[ 'value' ][ 'stateCode' ] : false;
                $selected_city    = ( isset( $field[ 'value' ][ 'cityName' ] ) ) ? $field[ 'value' ][ 'cityName' ] : false;
                $show_first       = true;
                $which_fields     = ( isset( $field[ 'which_fields' ] ) ) ? $field[ 'which_fields' ] : 'all';

                if ( false !== $default_country && false == $selected_country ) {
                    // New post with default country
                    if ( in_array( $which_fields, [ 'all', 'country_state', 'state_city' ] ) ) {
                        $prefill_states = acfcs_get_states( $default_country, $show_first, $field );
                    }
                    if ( in_array( $which_fields, [ 'country_city', 'state_city' ] ) ) {
                        $prefill_cities = acfcs_get_cities( $default_country, false, $field );
                    }

                } elseif ( false !== $selected_country ) {
                    if ( in_array( $which_fields, [ 'all', 'country_state', 'state_city' ] ) ) {
                        $prefill_states = acfcs_get_states( $selected_country, $show_first, $field );
                    }
                    if ( in_array( $which_fields, [ 'all', 'country_city', 'state_city' ] ) ) {
                        $prefill_cities = acfcs_get_cities( $selected_country, $selected_state, $field );
                    }
                    if ( 'country_city' != $which_fields ) {
                        if ( false != $selected_country && false != $selected_state ) {
                            $selected_state = $selected_country . '-' . $selected_state;
                        }
                    }

                } elseif ( false == $default_country && 'state_city' == $which_fields ) {
                    // no default country is set, so show warning
                    $error_message = esc_html__( "You haven't set a default country, so NO provinces/states and cities will be loaded.", 'acf-city-selector' );
                    echo sprintf( '<div class="acfcs"><div class="acfcs__notice field__message field__message--error">%s</div></div>', $error_message );
                }

                $prefill_values = [
                    'prefill_states' => $prefill_states,
                    'prefill_cities' => $prefill_cities,
                ];

                if ( 'state_city' != $which_fields ) {
                    echo acfcs_render_dropdown( 'country', $field, $selected_country, $prefill_values );
                }
                if ( 'all' == $which_fields || strpos( $which_fields, 'state' ) !== false ) {
                    echo acfcs_render_dropdown( 'state', $field, $selected_state, $prefill_values );
                }
                if ( 'all' == $which_fields || strpos( $which_fields, 'city' ) !== false ) {
                    echo acfcs_render_dropdown( 'city', $field, $selected_city, $prefill_values );
                }
            }


            /*
            *  input_admin_enqueue_scripts()
            *
            *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
            *  Use this action to add CSS + JavaScript to assist your create_field() action.
            *
            *  $info	https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
            *  @type	action
            *  @since	3.6
            *  @date	23/01/13
            *
            *  @TODO: DRY
            */
            function input_admin_enqueue_scripts() {

                $plugin_url     = $this->settings[ 'url' ];
                $plugin_version = $this->settings[ 'version' ];

                wp_enqueue_script( 'acf-custom-validation', "{$plugin_url}assets/js/field-validation.js", array( 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acf-custom-validation' );

                wp_register_script( 'acfcs-init', "{$plugin_url}assets/js/init.js", array( 'jquery', 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acfcs-init' );

                wp_register_script( 'acfcs-process', "{$plugin_url}assets/js/city-selector.js", array( 'jquery', 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acfcs-process' );

                $all_info                     = acfcs_get_field_settings();
                $js_vars[ 'ajaxurl' ]         = admin_url( 'admin-ajax.php' );
                $js_vars[ 'default_country' ] = ( isset( $all_info[ 'default_country' ] ) && false != $all_info[ 'default_country' ] ) ? $all_info[ 'default_country' ] : false;
                $js_vars[ 'post_id' ]         = ( isset( $_GET[ 'post' ] ) ) ? (int) $_GET[ 'post' ] : false;
                $js_vars[ 'show_labels' ]     = ( isset( $all_info[ 'show_labels' ] ) ) ? $all_info[ 'show_labels' ] : apply_filters( 'acfcs_show_labels', true );
                $js_vars[ 'use_select2' ]     = ( isset( $all_info[ 'use_select2' ] ) ) ? $all_info[ 'use_select2' ] : false;
                $js_vars[ 'which_fields' ]    = ( isset( $all_info[ 'which_fields' ] ) ) ? $all_info[ 'which_fields' ] : 'all';

                if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'edit' ) {
                    if ( isset( $_GET[ 'id' ] ) ) {
                        $post_id = (int) $_GET[ 'id' ];
                    } else {
                        $post_id = get_the_ID();
                    }

                    $fields     = get_field_objects( $post_id );
                    $field_name = 'acf_city_selector';
                    if ( is_array( $fields ) && count( $fields ) > 0 ) {
                        foreach( $fields as $field ) {
                            if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                                $field_name = $field[ 'name' ];
                                break;
                            }
                        }
                    }
                    $post_meta = get_post_meta( $post_id, $field_name, true );

                    if ( ! empty( $post_meta[ 'cityName' ] ) ) {
                        $v4_vars = array(
                            'countryCode' => $post_meta[ 'countryCode' ],
                            'stateCode'   => $post_meta[ 'stateCode' ],
                            'cityName'    => $post_meta[ 'cityName' ],
                        );
                        $js_vars = array_merge( $js_vars, $v4_vars );
                    }
                }
                wp_localize_script( 'acfcs-process', 'city_selector_vars', $js_vars );

            }


            /*
            *  load_value()
            *
            *  This filter is applied to the $value after it is loaded from the db
            *
            *  @type	filter
            *  @since	3.6
            *  @date	23/01/13
            *
            *  @param	$value - the value found in the database
            *  @param	$post_id - the $post_id from which the value was loaded
            *  @param	$field - the field array holding all the field options
            *
            *  @TODO: DRY
            *
            *  @return	$value - the value to be saved in the database
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


            /**
             * Update value before it's changed in the database
             *
             * @param   $value (mixed) the value found in the database
             * @param   $post_id (mixed) the $post_id from which the value was loaded
             * @param   $field (array) the field array holding all the field options
             *
             * @since: 1.5.0
             *
             * @TODO: DRY
             *
             * @return false|mixed
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
                        if ( isset( $field[ 'default_country' ] ) ) {
                            $value[ 'countryCode'] = $field[ 'default_country' ];
                        }
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
                        if ( isset( $field[ 'default_country' ] ) ) {
                            $value[ 'countryCode'] = $field[ 'default_country' ];
                        }
                        if ( empty( $value[ 'stateCode' ] ) || empty( $value[ 'cityName' ] ) ) {
                            $value = false;
                        }
                    }
                }

                return $value;
            }
        }


        // initialize
        new acf_field_city_selector( $this->settings );


        // class_exists check
    endif;
