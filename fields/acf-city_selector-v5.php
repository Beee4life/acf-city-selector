<?php

    // exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    // check if class already exists
    if ( ! class_exists( 'acf_field_city_selector' ) ) :

        class acf_field_city_selector extends acf_field {

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
                 * Keep for now
                 * l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
                 * var message = acf._e('city_selector', 'error');
                 */

                // $this->l10n = array(
                //     'error' => __('Error! Please enter a higher value', 'acf-city-selector'),
                // );

                /*
                 *  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
                 */
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

                /*
                 * acf_render_field_setting
                 *
                 * This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
                 * Please note that you must also have a matching $defaults value for the field name (show_labels)
                 */
                $select_options = array(
                    1 => __( 'Yes', 'acf-city-selector' ),
                    0 => __( 'No', 'acf-city-selector' )
                );
                acf_render_field_setting( $field, array(
                    'type'         => 'radio',
                    'name'         => 'show_labels',
                    'choices'      => $select_options,
                    'value'        => $field[ 'show_labels' ],
                    'layout'       => 'horizontal',
                    'label'        => esc_html__( 'Show labels', 'acf-city-selector' ),
                    'instructions' => esc_html__( 'Show field labels above the dropdown menus', 'acf-city-selector' ),
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

                if ( strpos( $field[ 'name' ], 'row' ) !== false ) {
                    // repeater
                    $strip_last_char = substr( $field[ 'prefix' ], 0, -1 );
                    $index           = substr( $strip_last_char, 29 ); // 29 => acf[field_xxxxxxxxxxxxx
                    $field_object    = get_field_objects( get_the_ID() );
                    if ( is_array( $field_object ) ) {
                        $repeater_name = array_keys( $field_object )[ 0 ];
                        $meta_key      = $repeater_name . '_' . $index . '_' . $field[ '_name' ];
                        if ( isset( $meta_key ) ) {
                            $post_meta = get_post_meta( get_the_ID(), $meta_key, true );
                        }
                        $selected_country = ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : false;
                    }
                } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                    // single or group

                    /**
                     * Why is 24 set as length ?
                     * Because the length of a single $field['name'] = 24.
                     * The length of a group $field['name'] = 45.
                     *
                     * group = acf[field_5e9f4b3b50ea2][field_5e9f4b4450ea3] (45)
                     * single   = acf[field_5e950320fef17] (24)
                     */
                    if ( 24 < strlen( $field[ 'name' ] ) ) {
                        // group
                        $field_object = get_field_objects( get_the_ID() );
                        if ( is_array( $field_object ) ) {
                            $group_name       = array_keys( $field_object )[ 0 ];
                            $meta_key         = $group_name . '_' . $field[ '_name' ];
                            $post_meta        = get_post_meta( get_the_ID(), $meta_key, true );
                            $selected_country = ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : false;
                        }
                    } else {
                        // single
                        $selected_country = ( isset( $field[ 'value' ][ 'countryCode' ] ) ) ? $field[ 'value' ][ 'countryCode' ] : false;
                    }
                }

                $countries   = acfcs_populate_country_select( $field );
                $field_id    = $field[ 'id' ];
                $field_name  = $field[ 'name' ];
                $show_labels = $field[ 'show_labels' ];
                ?>
                <div class="dropdown-box cs-countries">
                    <?php if ( 1 == $show_labels ) { ?>
                        <div class="acf-input-header">
                            <?php esc_html_e( 'Select a country', 'acf-city-selector' ); ?>
                        </div>
                    <?php } ?>
                    <label for="<?php echo $field_id; ?>countryCode" class="screen-reader-text"><?php esc_html_e( 'Select a country', 'acf-city-selector' ); ?></label>
                    <select name="<?php echo $field_name; ?>[countryCode]" id="<?php echo $field_id; ?>countryCode" class="countrySelect">
                        <?php foreach ( $countries as $key => $country ) { ?>
                            <?php $selected = ( isset( $selected_country ) ) ? ( $selected_country === $key ) ? ' selected="selected"' : false : false; ?>
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
                    <label for="<?php echo $field_id; ?>stateCode" class="screen-reader-text"><?php esc_html_e( 'Select a province/state', 'acf-city-selector' ); ?></label>
                    <select name="<?php echo $field_name; ?>[stateCode]" id="<?php echo $field_id; ?>stateCode" class="countrySelect">
                        <?php // content will be dynamically generated ?>
                    </select>
                </div>

                <div class="dropdown-box cs-cities">
                    <?php if ( 1 == $show_labels ) { ?>
                        <div class="acf-input-header">
                            <?php esc_html_e( 'Select a city', 'acf-city-selector' ); ?>
                        </div>
                    <?php } ?>
                    <label for="<?php echo $field_id; ?>cityName" class="screen-reader-text"><?php esc_html_e( 'Select a city', 'acf-city-selector' ); ?></label>
                    <select name="<?php echo $field_name; ?>[cityName]" id="<?php echo $field_id; ?>cityName" class="countrySelect">
                        <?php // content will be dynamically generated ?>
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

                if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'edit' || isset( $_GET[ 'id' ] ) ) {

                    if ( isset( $_GET[ 'id' ] ) ) {
                        $post_id = $_GET[ 'id' ];
                    } else {
                        $post_id = get_the_ID();
                    }

                    if ( isset( $post_id ) && false !== $post_id ) {
                        $fields = get_field_objects( $post_id );
                        // get the field name for acfcs
                        if ( is_array( $fields ) && count( $fields ) > 0 ) {
                            foreach( $fields as $field ) {
                                // check if field_name is overridden
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
                                    if ( ! empty( $field['value'] ) ) {
                                        foreach( $field['value'] as $key => $values ) {
                                            if ( is_array( $values ) ) {
                                                $index = array_key_exists( 'countryCode', $values );
                                                if ( true === $index ) {
                                                    $field_name = $field[ 'name' ] . '_' . $key;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // get and localize post_meta
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
                                if ( isset( $meta_values ) ) {
                                    wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', $meta_values );
                                }
                            }

                        } else {
                            if ( isset( $field_name ) ) {
                                $post_meta = get_post_meta( $post_id, $field_name, true );

                                wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', array(
                                    'countryCode' => ( isset( $post_meta[ 'countryCode' ] ) ) ? $post_meta[ 'countryCode' ] : '',
                                    'stateCode'   => ( isset( $post_meta[ 'stateCode' ] ) ) ? $post_meta[ 'stateCode' ] : '',
                                    'cityName'    => ( isset( $post_meta[ 'cityName' ] ) ) ? $post_meta[ 'cityName' ] : '',
                                ) );
                            }
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

                $country_code = '';
                if ( isset( $value[ 'countryCode' ]) ) {
                    $country_code = $value[ 'countryCode' ];
                    if ( '0' != $country_code && isset( $value[ 'stateCode' ] ) ) {
                        $state_code = substr( $value[ 'stateCode' ], 3 );
                    } else {
                        $value = false;
                    }
                }
                if ( strlen( $country_code ) == 2 && ( isset( $value[ 'stateCode' ] ) && '-' != $value[ 'stateCode' ] ) && ( isset( $value[ 'cityName' ] ) && 'Select a city' != $value[ 'cityName' ] ) ) {
                    global $wpdb;
                    $table                  = $wpdb->prefix . 'cities';
                    $row                    = $wpdb->get_row( "SELECT country, state_name FROM $table WHERE country_code= '$country_code' AND state_code= '$state_code'" );
                    $country                = $row->country;
                    $state_name             = $row->state_name;
                    $value[ 'stateCode' ]   = $state_code;
                    $value[ 'stateName' ]   = $state_name;
                    $value[ 'countryName' ] = $country;
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
                // @TODO: fix save empty value for countryCode, stateName and cityName
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

                // @TODO: fix save empty value for countryCode, stateName and cityName
                if ( 1 == $field[ 'required' ] ) {
                    if ( ! isset( $value[ 'cityName' ] ) || $value[ 'cityName' ] == 'Select a city' ) {
                        $valid = __( "You didn't select a city", "acf-city-selector" );
                    }
                }

                return $valid;
            }
        }

        // initialize
        new acf_field_city_selector( $this->settings );

    endif; // class_exists check
