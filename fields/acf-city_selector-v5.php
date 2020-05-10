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

                $selected_country = false;
                $post_id = get_the_ID();
                if ( strpos( $field[ 'name' ], 'row' ) !== false ) {
                    // if $field[ 'name' ] contains 'row' it's a repeater field
                    $strip_last_char = substr( $field[ 'prefix' ], 0, -1 );
                    $index           = substr( $strip_last_char, 29 ); // 29 => acf[field_xxxxxxxxxxxxx
                    $field_object    = get_field_objects( $post_id );
                    if ( is_array( $field_object ) ) {
                        $repeater_name = array_keys( $field_object )[ 0 ];
                        $meta_key      = $repeater_name . '_' . $index . '_' . $field[ '_name' ];
                        if ( isset( $meta_key ) ) {
                            $post_meta = get_post_meta( $post_id, $meta_key, true );
                        }
                        $selected_country = ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : false;
                    }
                } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'flexible_content' ) {
                    error_log('type = flexible_content');
                } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                    // if $field[ 'name' ] doesn't contain 'row' it's a single or group field

                    /**
                     * Why is 24 set as length ?
                     * Because the length of a single $field['name'] = 24.
                     * The length of a group $field['name'] = 45.
                     * So if it's bigger than 24, it's a group name.
                     *
                     * group            = acf[field_5e9f4b3b50ea2][field_5e9f4b4450ea3] (45)
                     * single           = acf[field_5e950320fef17] (24)
                     * flexible content = (60)
                     */
                    if ( 24 < strlen( $field[ 'name' ] ) ) {
                        // Group + Flexible content
                        $field_object = get_field_objects( $post_id );

                        if ( 45 < strlen( $field[ 'name' ] ) ) {
                            // Flexible content
                            if ( is_array( $field_object ) ) {
                                $flexible_content_name         = array_keys( $field_object )[ 0 ]; // flexible_countries_change
                                $flexible_content              = get_field_object( $flexible_content_name, $post_id );
                                $flexible_content_block_values = $flexible_content[ 'value' ];
                                $flexible_names                = [];
                                $layouts                       = $flexible_content[ 'layouts' ];

                                foreach( $layouts as $layout ) {
                                    $sub_fields = $layout[ 'sub_fields' ];
                                    foreach( $sub_fields as $subfield ) {
                                        // if type == acfcs, add name to array
                                        if ( 'acf_city_selector' == $subfield[ 'type' ] ) {
                                            $flexible_names[ $layout[ 'name' ] ] = $subfield[ 'name' ];
                                        }
                                    }
                                }

                                $layout_index = 0;
                                $meta_keys    = [];
                                foreach( $flexible_content_block_values as $values ) {
                                    if ( array_key_exists( $values[ 'acf_fc_layout' ], $flexible_names ) ) {
                                        $field_name                 = $flexible_names[ $values[ 'acf_fc_layout' ] ];
                                        $meta_keys[ $layout_index ] = $flexible_content_name . '_' . $layout_index . '_' . $field_name;
                                    }
                                    $layout_index++;
                                }
                                if ( ! empty( $meta_keys ) ) {
                                    foreach( $meta_keys as $key => $value ) {
                                        $post_meta[ $key ]        = get_post_meta( $post_id, $value, true );
                                        $selected_country[ $key ] = ( isset( $post_meta[ $key ][ 'countryCode' ] ) ) ? $post_meta[ $key ][ 'countryCode' ] : false;
                                    }
                                }
                            }
                        } else {
                            // Group
                            if ( is_array( $field_object ) ) {
                                $group_name       = array_keys( $field_object )[ 0 ];
                                $meta_key         = $group_name . '_' . $field[ '_name' ];
                                $post_meta        = get_post_meta( $post_id, $meta_key, true );
                                $selected_country = ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : false;
                            }
                        }

                    } else {
                        // Single
                        $selected_country = ( isset( $field[ 'value' ][ 'countryCode' ] ) ) ? $field[ 'value' ][ 'countryCode' ] : false;
                    }
                } else {
                    error_log('else');
                }

                $countries         = acfcs_populate_country_select( $field );
                $default_country   = ( isset( $field[ 'default_country' ] ) ) ? $field[ 'default_country' ] : false;
                $field_id          = $field[ 'id' ];
                $field_name        = $field[ 'name' ];
                $show_labels       = $field[ 'show_labels' ];
                $prefill_states    = [];
                $selected_selected = ' selected="selected"';

                if ( ! empty( $default_country ) && false == $selected_country ) {
                    // New post
                    // Load all states for $default_country
                    $first_option   = [ '' => esc_html__( 'Select a province/state', 'acf-city-selector' ) ];
                    $states         = acfcs_get_states( $field );
                    $prefill_states = array_merge( $first_option, $states );
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
                            foreach ( $countries as $key => $country ) {
                                $selected = false;
                                if ( false !== $selected_country ) {
                                    if ( $selected_country == $key ) {
                                        $selected = $selected_selected;
                                    }
                                } elseif ( ! empty( $default_country ) ) {
                                    if ( $default_country == $key ) {
                                        $selected = $selected_selected;
                                    }
                                }
                            ?>
                            <option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
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
                                foreach( $prefill_states as $scc => $label ) {
                                    ?>
                                    <option value="<?php echo $scc; ?>"><?php echo $label; ?></option>
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
                        <?php if ( ! empty( $prefill_states ) ) { ?>
                            <option value=""><?php esc_html_e( 'First select a province/state', 'acf-city-selector' ); ?></option>
                        <?php } else { ?>
                            <?php // content will be dynamically generated on.change country ?>
                        <?php } ?>
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

                if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'edit' || isset( $_GET[ 'id' ] ) || defined('IS_PROFILE_PAGE') ) {
                    $activate = false;

                    if ( isset( $_GET[ 'user_id' ] ) ) {
                        $activate = true;
                        $user_id  = $_GET[ 'user_id' ];
                    } elseif ( isset( $_GET[ 'id' ] ) ) {
                        // this is for my custom project
                        $activate = true;
                        $post_id  = $_GET[ 'id' ];
                    } elseif ( isset( $_GET[ 'post' ] ) ) {
                        $post_id = $_GET[ 'post' ];
                        if ( 'acf-field-group' != get_post_type( $post_id ) ) {
                            $activate = true;
                        }
                    } else {
                        $activate = true;
                        if ( defined( 'IS_PROFILE_PAGE' ) ) {
                            $user_id = get_current_user_id();
                        } else {
                            $post_id = get_the_ID();
                        }
                    }

                    if ( false != $activate ) {
                        if ( isset( $user_id ) && false !== $user_id ) {
                            $fields = get_field_objects( 'user_' . $user_id );
                        } elseif ( isset( $post_id ) && false !== $post_id ) {
                            $fields = get_field_objects( $post_id );
                        }

                        /*
                         * Get the field['name'] for the City Selector field
                         */
                        if ( isset( $fields ) && is_array( $fields ) && count( $fields ) > 0 ) {
                            foreach( $fields as $field ) {
                                if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                                    $field_name = $field[ 'name' ];
                                    break;
                                } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'repeater' ) {
                                    $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                                    if ( false !== $array_key ) {
                                        $city_selector_name = $field[ 'sub_fields' ][ $array_key ][ 'name' ];
                                        $repeater_name      = $field[ 'name' ];
                                        $repeater_count     = get_post_meta( $post_id, $repeater_name, true );
                                        break;
                                    }
                                } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'group' ) {
                                    // get acfcs field_names
                                    if ( ! empty( $field[ 'value' ] ) ) {
                                        foreach( $field[ 'value' ] as $key => $values ) {
                                            if ( is_array( $values ) ) {
                                                $index = array_key_exists( 'countryCode', $values );
                                                if ( true === $index ) {
                                                    $field_name = $field[ 'name' ] . '_' . $key;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'flexible_content' ) {
                                    $flexible_name                 = $field[ 'name' ];
                                    $flexible_content_name         = array_keys( $fields )[ 0 ];
                                    $flexible_content              = get_field_object( $flexible_content_name, $post_id );
                                    $flexible_content_block_values = $flexible_content[ 'value' ];
                                    $flexible_names                = [];
                                    $layouts                       = $flexible_content[ 'layouts' ];

                                    foreach( $layouts as $layout ) {
                                        $sub_fields = $layout[ 'sub_fields' ];
                                        foreach( $sub_fields as $subfield ) {
                                            // if type == acfcs, add name to array
                                            if ( 'acf_city_selector' == $subfield[ 'type' ] ) {
                                                $flexible_names[ $layout[ 'name' ] ] = $subfield[ 'name' ];
                                            }
                                        }
                                    }

                                    $layout_index = 0;
                                    $meta_keys    = [];
                                    foreach( $flexible_content_block_values as $values ) {
                                        if ( array_key_exists( $values[ 'acf_fc_layout' ], $flexible_names ) ) {
                                            $meta_keys[ $layout_index ] = $flexible_content_name . '_' . $layout_index . '_' . $flexible_names[ $values[ 'acf_fc_layout' ] ];
                                        }
                                        $layout_index++;
                                    }
                                }
                            }
                        }

                        /*
                         * Get post_meta
                         */
                        if ( isset( $repeater_count ) && 0 < $repeater_count ) {
                            for( $i = 0; $i < $repeater_count; $i++ ) {
                                $repeater_field_name = $repeater_name . '_' . $i . '_' . $city_selector_name;
                                $post_meta[]         = get_post_meta( $post_id, $repeater_field_name, true );
                            }

                            if ( isset( $post_meta ) && ! empty( $post_meta ) ) {
                                foreach( $post_meta as $meta ) {
                                    $meta_values[] = array(
                                        'countryCode' => ( isset( $meta[ 'countryCode' ] ) ) ? $meta[ 'countryCode' ] : '',
                                        'stateCode'   => ( isset( $meta[ 'stateCode' ] ) ) ? $meta[ 'stateCode' ] : '',
                                        'cityName'    => ( isset( $meta[ 'cityName' ] ) ) ? $meta[ 'cityName' ] : '',
                                    );
                                }
                            }
                        } else {
                            if ( isset( $field_name ) ) {
                                if ( isset( $user_id ) ) {
                                    $post_meta = get_user_meta( $user_id, $field_name, true );
                                } elseif ( isset( $post_id ) ) {
                                    if ( isset( $flexible_name ) ) {
                                        // flexible content
                                    } else {
                                        // single/group
                                        $post_meta = get_post_meta( $post_id, $field_name, true );
                                        if ( ! empty( $post_meta[ 'cityName' ] ) ) {
                                            $meta_values = array(
                                                'countryCode' => ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : '',
                                                'stateCode'   => ( isset( $post_meta[ 'stateCode' ] ) ) ? $post_meta[ 'stateCode' ] : '',
                                                'cityName'    => ( isset( $post_meta[ 'cityName' ] ) ) ? $post_meta[ 'cityName' ] : '',
                                            );
                                        }
                                    }
                                }
                            } elseif ( isset( $flexible_name ) ) {
                                // flexible content
                                // die('XXX');
                                if ( isset( $meta_keys ) && is_array( $meta_keys ) && ! empty( $meta_keys ) ) {
                                    foreach( $meta_keys as $key => $value ) {
                                        $meta_values[ $key ] = get_post_meta( $post_id, $value, true );
                                    }
                                }
                            } elseif ( isset( $counter_field_name ) ) {

                            }
                        }
                        /*
                         * Localize post_meta
                         */
                        if ( isset( $meta_values ) ) {
                            wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', $meta_values );
                        }
                    }
                }
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
