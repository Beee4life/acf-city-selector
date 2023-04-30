<?php
    // exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    if ( ! class_exists( 'acf_field_city_selector' ) ) {

        /**
         * Main class
         */
        class acf_field_city_selector extends acf_field {
            /*
             * Function index
             * - construct( $settings )
             * - render_field_settings( $field )
             * - render_field( $field )
             * - input_admin_enqueue_scripts()
             * - load_value( $value, $post_id, $field )
             * - update_value( $value, $post_id, $field )
             * - validate_value( $valid, $value, $field, $input )
             */

            /**
             * acf_field_city_selector constructor
             *
             * This function will set up the class functionality
             *
             * @param $settings
             */
            function __construct( $settings ) {
                $this->name     = 'acf_city_selector';
                $this->label    = 'City Selector';
                $this->category = esc_attr__( 'Choice', 'acf-city-selector' );
                $this->defaults = array(
                    'show_labels'  => 1,
                    'store_meta'   => 0,
                    'which_fields' => 'all',
                    'use_select2'  => 0,
                );

                $this->l10n = acfcs_get_js_translations();

                $this->settings = $settings;

                parent::__construct();
            }


            /**
             * render_field_settings()
             *
             * Create extra settings for your field. These are visible when editing a field
             *
             * @param $field (array) the $field being edited
             */
            function render_field_settings( $field ) {
                $select_options = array(
                    1 => esc_attr__( 'Yes', 'acf-city-selector' ),
                    0 => esc_attr__( 'No', 'acf-city-selector' )
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

				acf_render_field_setting( $field, array(
                    'choices'      => $select_options,
                    'instructions' => esc_html__( 'Use select2 for dropdowns', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Select2', 'acf-city-selector' ),
                    'layout'       => 'horizontal',
                    'name'         => 'use_select2',
                    'type'         => 'radio',
                    'value'        => $field[ 'use_select2' ],
                ) );

                acf_render_field_setting( $field, array(
                    'choices'      => $select_options,
                    'instructions' => esc_html__( 'Store location as single meta values', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Store meta', 'acf-city-selector' ),
                    'layout'       => 'horizontal',
                    'name'         => 'store_meta',
                    'type'         => 'radio',
                    'value'        => $field[ 'store_meta' ],
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
                    'all'           => esc_attr__( 'All fields [default]', 'acf-city-selector' ),
                    'country_only'  => esc_attr__( 'Country only', 'acf-city-selector' ),
                    'country_state' => esc_attr__( 'Country + State/province', 'acf-city-selector' ),
                    'country_city'  => esc_attr__( 'Country + City', 'acf-city-selector' ),
                    'state_city'    => esc_attr__( 'State/province + City', 'acf-city-selector' ),
                );
                acf_render_field_setting( $field, array(
                    'choices'      => $default_country_fields,
                    'instructions' => esc_html__( 'Select which fields are used', 'acf-city-selector' ),
                    'label'        => esc_html__( 'Fields to use', 'acf-city-selector' ),
                    'name'         => 'which_fields',
                    'type'         => 'radio',
                ) );
            }


            /**
             * render_field()
             *
             * Create the HTML interface for your field
             *
             * @param $field (array) the $field being edited
             */
            function render_field( $field ) {
				$default_country  = ( isset( $field[ 'default_country' ] ) && ! empty( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
				$prefill_cities   = [];
				$prefill_states   = [];
				$selected_country = ( isset( $field[ 'value' ][ 'countryCode' ] ) ) ? $field[ 'value' ][ 'countryCode' ] : false;
				$selected_state   = ( isset( $field[ 'value' ][ 'stateCode' ] ) ) ? $field[ 'value' ][ 'stateCode' ] : false;
				$selected_city    = ( isset( $field[ 'value' ][ 'cityName' ] ) ) ? $field[ 'value' ][ 'cityName' ] : false;
				$show_first       = true;
				$store_meta       = ( isset( $field[ 'store_meta' ] ) ) ? $field[ 'store_meta' ] : false;
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
                    $message = esc_html__( "You haven't set a default country, so NO provinces/states and cities will be loaded.", 'acf-city-selector' );
                    echo sprintf( '<div class="acfcs"><div class="acfcs__notice field__message field__message--error">%s</div></div>', $message );
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
				if ( ! isset( $field[ 'parent_layout' ] ) && ! isset( $field[ 'parent_repeater' ] ) && $store_meta ) {
					echo acfcs_render_hidden_field( 'store_meta', '1' );
                }
            }


            /**
             * input_admin_enqueue_scripts()
             *
             * This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
             * Use this action to add CSS + JavaScript to assist your render_field() action.
             */
            function input_admin_enqueue_scripts() {
                $plugin_url     = $this->settings[ 'url' ];
                $plugin_version = $this->settings[ 'version' ];

                wp_register_script( 'acfcs-init', "{$plugin_url}assets/js/init.js", array( 'jquery', 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acfcs-init' );

                wp_register_script( 'acfcs-process', "{$plugin_url}assets/js/city-selector.js", array( 'jquery', 'acf-input' ), $plugin_version );
                wp_enqueue_script( 'acfcs-process' );

                $all_info                     = acfcs_get_field_settings();
                $js_vars[ 'ajaxurl' ]         = admin_url( 'admin-ajax.php' );
                $js_vars[ 'default_country' ] = ( isset( $all_info[ 'default_country' ] ) && false != $all_info[ 'default_country' ] ) ? $all_info[ 'default_country' ] : false;
                $js_vars[ 'post_id' ]         = ( isset( $_GET[ 'post' ] ) ) ? (int) $_GET[ 'post' ] : false;
                $js_vars[ 'show_labels' ]     = ( isset( $all_info[ 'show_labels' ] ) ) ? $all_info[ 'show_labels' ] : apply_filters( 'acfcs_show_labels', true );
                $js_vars[ 'store_meta' ]      = ( isset( $all_info[ 'store_meta' ] ) ) ? $all_info[ 'store_meta' ] : false;
                $js_vars[ 'use_select2' ]     = ( isset( $all_info[ 'use_select2' ] ) ) ? $all_info[ 'use_select2' ] : false;
                $js_vars[ 'which_fields' ]    = ( isset( $all_info[ 'which_fields' ] ) ) ? $all_info[ 'which_fields' ] : 'all';

                wp_localize_script( 'acfcs-process', 'city_selector_vars', $js_vars );
            }


            /*
             * load_value()
             *
             * This filter is applied to the $value after it is loaded from the db
             * This returns false if no country/state is selected (but empty values are stored)
             *
             * @param   $value (mixed) the value found in the database
             * @param   $post_id (mixed) the $post_id from which the value was loaded
             * @param   $field (array) the field array holding all the field options
             *
             * @return  $value
             *
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
					$table                  = $wpdb->prefix . 'cities';
					$sql_query              = $wpdb->prepare( "SELECT country, state_name FROM {$table} WHERE country_code = %s AND state_code = %s", $country_code, $state_code );
					$row                    = $wpdb->get_row( $sql_query );
					$value[ 'stateCode' ]   = $state_code;
					$value[ 'stateName' ]   = ( isset( $row->state_name ) ) ? $row->state_name : false;
					$value[ 'countryName' ] = ( isset( $row->country ) ) ? $row->country : false;
                }

                return $value;
            }


            /*
             * update_value()
             *
             * This filter is applied to the $value before it is saved in the db
             * @param   $value (mixed) the value found in the database
             * @param   $post_id (mixed) the $post_id from which the value was loaded
             * @param   $field (array) the field array holding all the field options
             *
             * @TODO: DRY
             *
             * @return $value
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
                            $value[ 'countryCode' ] = $field[ 'default_country' ];
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

				if ( ! isset( $field[ 'parent_layout' ] ) && ! isset( $field[ 'parent_repeater' ] ) ) {
					do_action( 'acfcs_store_meta', $value, $post_id );
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
             *
             * @return  $valid
             */
            function validate_value( $valid, $value, $field, $input ) {
                if ( 1 == $field[ 'required' ] ) {
                    $nothing       = esc_html__( "You didn't select anything.", 'acf-city-selector' );
                    $no_city       = esc_html__( "You didn't select a city.", 'acf-city-selector' );
                    $no_country    = esc_html__( "You didn't select a country.", 'acf-city-selector' );
                    $no_state      = esc_html__( "You didn't select a state.", 'acf-city-selector' );
                    $no_state_city = esc_html__( "You didn't select a state and city.", 'acf-city-selector' );

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

        new acf_field_city_selector( $this->settings );
    }
