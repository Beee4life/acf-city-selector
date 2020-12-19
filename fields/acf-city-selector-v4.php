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
                // vars
                $this->name     = 'acf_city_selector';
                $this->label    = 'City Selector';
                $this->category = 'Choice';
                $this->defaults = array(
                    'show_labels' => 1
                );

                // do not delete!
                parent::__construct();

                // settings
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
                $key = $field['name'];

                $select_options = array(
                    1 => esc_attr__( 'Yes', 'acf-city-selector' ),
                    0 => esc_attr__( 'No', 'acf-city-selector' )
                );

                // Create Field Options HTML
                ?>
                <tr class="field_option field_option_<?php echo $this->name; ?>">
                    <td class="label">
                        <label><?php esc_html_e("Show labels",'acf-city-selector'); ?></label>
                        <p class="description"><?php esc_html_e( 'Show field labels above the dropdown menus', 'acf-city-selector' ); ?></p>
                    </td>
                    <td>
                        <?php
                            do_action('acf/create_field', array(
                                'type'         => 'radio',
                                'name'         => 'fields[' . $key . '][show_labels]',
                                'choices'      => $select_options,
                                'value'        => $field['show_labels'],
                                'layout'       => 'horizontal',
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

                $field = array_merge($this->defaults, $field);

                // create Field HTML
                if ( isset( $field[ 'value' ][ 'countryCode' ] ) ) {
                    $countrycode = $field[ 'value' ][ 'countryCode' ];
                }
                $countries = acfcs_get_countries( true, $field );
                ?>
                <div class="dropdown-box cs-countries">
                    <?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select country', 'acf-city-selector' ); ?></span>
                    <?php } ?>
                    <label for="countryCode" class="screen-reader-text"></label>
                    <select name="<?php echo $field['name']; ?>[countryCode]" id="countryCode" class="countrySelect">
                        <?php
                            foreach ( $countries as $key => $country ) {
                                if ( isset( $countrycode ) ) {
                                    $selected = ( $countrycode === $key ) ? ' selected="selected"' : false;
                                } else {
                                    $selected = false;
                                }
                                ?>
                                <option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
                            <?php } ?>
                    </select>
                </div>

                <div class="dropdown-box cs-provinces">
                    <?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select province/state', 'acf-city-selector' ); ?></span>
                    <?php } ?>
                    <label for="stateCode" class="screen-reader-text"></label>
                    <select name="<?php echo $field['name']; ?>[stateCode]" id="stateCode" class="countrySelect">
                    </select>
                </div>

                <div class="dropdown-box cs-cities">
                    <?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select city', 'acf-city-selector' ); ?></span>
                    <?php } ?>
                    <label for="cityName" class="screen-reader-text"></label>
                    <select name="<?php echo $field['name']; ?>[cityName]" id="cityName" class="countrySelect">
                    </select>
                </div>
                <?php

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
            */

            function input_admin_enqueue_scripts() {

                $url     = $this->settings['url'];
                $version = $this->settings['version'];

                // register & include JS
                wp_enqueue_script( 'acf-custom-validation', "{$url}assets/js/field-validation.js", array( 'acf-input' ), $version );
                wp_enqueue_script( 'acf-custom-validation' );
                wp_register_script( 'acf-input-city-selector', "{$url}assets/js/city-selector.js", '', $version );
                wp_enqueue_script( 'acf-input-city-selector' );

            }


            /*
            *  input_admin_head()
            *
            *  This action is called in the admin_head action on the edit screen where your field is created.
            *  Use this action to add CSS and JavaScript to assist your create_field() action.
            *
            *  @info	https://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
            *  @type	action
            *  @since	3.6
            *  @date	23/01/13
            */
            function input_admin_head() {

                $url     = $this->settings[ 'url' ];
                $version = $this->settings[ 'version' ];

                // register & include JS
                wp_enqueue_script( 'acf-custom-validation', "{$url}assets/js/field-validation.js", array( 'acf-input' ), $version );
                wp_enqueue_script( 'acf-custom-validation' );
                wp_register_script( 'acf-city-selector-js', "{$url}assets/js/city-selector.js", '', $version );
                wp_enqueue_script( 'acf-city-selector-js' );

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
                        wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', array(
                            'countryCode' => $post_meta[ 'countryCode' ],
                            'stateCode'   => $post_meta[ 'stateCode' ],
                            'cityName'    => $post_meta[ 'cityName' ],
                        ) );
                    }
                }
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
            *  @return	$value - the value to be saved in the database
            */

            function load_value( $value, $post_id, $field ) {

                global $wpdb;
                $country_code = $value['countryCode'];
                if ( '0' != $country_code ) {
                    $state_code = substr( $value['stateCode'], 3 );
                }
                if ( strlen( $country_code ) == 2 && ( isset( $value['stateCode'] ) && '-' != $value['stateCode'] ) && ( isset( $value['cityName'] ) && 'Select a city' != $value['cityName'] ) ) {
                    $table                  = $wpdb->prefix . 'cities';
                    $sql_query              = $wpdb->prepare( "SELECT country, state_name FROM %s WHERE country_code= %s AND state_code= %s", $table, $country_code, $state_code );
                    $row                    = $wpdb->get_row( $sql_query );
                    $country                = $row->country;
                    $state_name             = $row->state_name;
                    $value[ 'stateCode' ]   = $state_code;
                    $value[ 'stateName' ]   = $state_name;
                    $value[ 'countryName' ] = $country;
                }

                return $value;
            }
        }


        // initialize
        new acf_field_city_selector( $this->settings );


        // class_exists check
    endif;
