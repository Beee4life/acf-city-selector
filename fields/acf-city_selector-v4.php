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
					1 => __( 'Yes', 'acf-city-selector' ),
					0 => __( 'No', 'acf-city-selector' )
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
				$countries = populate_country_select( '', $field );
				if ( isset( $countrycode ) && 0 != $countrycode ) {
					$stateCode = $field['value']['stateCode'];
					if ( '-' != $stateCode ) {
						$cityName = $field['value']['cityName'];
					}
					$states = get_states( $countrycode );
				}
				$stateName = ! empty( $states ) ? $states[ substr( $stateCode, 3 ) ] : false;
				?>
                <div class="dropdown-box cs-countries">
					<?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select country', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="countryCode" class="screen-reader-text"></label>
                    <select name="acf[<?php echo $field['key']; ?>][countryCode]" id="countryCode" class="countrySelect">
						<?php
							foreach ( $countries as $key => $country ) {
								if ( isset( $countrycode ) ) {
									$selected = ( $countrycode === $key ) ? " selected=\"selected\"" : false;
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
                    <select name="acf[<?php echo $field['key']; ?>][stateCode]" id="stateCode" class="countrySelect">
                    </select>
                </div>

                <div class="dropdown-box cs-cities">
					<?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select city', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="cityName" class="screen-reader-text"></label>
                    <select name="acf[<?php echo $field['key']; ?>][cityName]" id="cityName" class="countrySelect">
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
			*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
			*  @type	action
			*  @since	3.6
			*  @date	23/01/13
			*/

			function input_admin_enqueue_scripts() {

			    $url     = $this->settings['url'];
				$version = $this->settings['version'];

				// register & include JS
				// wp_register_script( 'acf-input-city-selector', "{$url}assets/js/city-selector.js", '', $version );
				// wp_enqueue_script( 'acf-input-city-selector' );
				//
				// if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
				// 	$fields     = get_field_objects( get_the_ID() );
				// 	$field_name = 'acf_city_selector';
				// 	if ( is_array( $fields ) && count( $fields ) > 0 ) {
				// 		foreach( $fields as $field ) {
				// 		    // echo '<pre>'; var_dump($field); echo '</pre>'; exit;
				// 			if ( isset( $field['type' ] ) && $field['type'] == 'acf_city_selector' ) {
				// 				$field_name = $field['name'];
				// 				break;
				// 			}
				// 		}
				// 	}
				// 	$post_meta = get_post_meta( get_the_ID(), $field_name, 1 );
				//
				// 	if ( ! empty( $post_meta['cityName'] ) ) {
				// 		wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', array(
				// 			'countryCode' => $post_meta['countryCode'],
				// 			'stateCode'   => $post_meta['stateCode'],
				// 			'cityName'    => $post_meta['cityName'],
				// 		) );
				// 	}
				// }

			}


			/*
			*  input_admin_head()
			*
			*  This action is called in the admin_head action on the edit screen where your field is created.
			*  Use this action to add CSS and JavaScript to assist your create_field() action.
			*
			*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
			*  @type	action
			*  @since	3.6
			*  @date	23/01/13
			*/
			function input_admin_head() {

				$url     = $this->settings['url'];
				$version = $this->settings['version'];

				// register & include JS
				wp_register_script( 'acf-city-selector-js', "{$url}assets/js/city-selector.js", '', $version );
				wp_enqueue_script( 'acf-city-selector-js' );

				if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
					$fields     = get_field_objects( get_the_ID() );
					$field_name = 'acf_city_selector';
					if ( is_array( $fields ) && count( $fields ) > 0 ) {
						foreach( $fields as $field ) {
							if ( isset( $field['type' ] ) && $field['type'] == 'acf_city_selector' ) {
								$field_name = $field['name'];
								break;
							}
						}
					}
					$post_meta = get_post_meta( get_the_ID(), $field_name, 1 );

					if ( ! empty( $post_meta['cityName'] ) ) {
						wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', array(
							'countryCode' => $post_meta['countryCode'],
							'stateCode'   => $post_meta['stateCode'],
							'cityName'    => $post_meta['cityName'],
						) );
					}
				}
			}


			/*
			*  field_group_admin_enqueue_scripts()
			*
			*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
			*  Use this action to add CSS + JavaScript to assist your create_field_options() action.
			*
			*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
			*  @type	action
			*  @since	3.6
			*  @date	23/01/13
			*/
			function field_group_admin_enqueue_scripts() {
				// Note: This function can be removed if not used
			}


			/*
			*  field_group_admin_head()
			*
			*  This action is called in the admin_head action on the edit screen where your field is edited.
			*  Use this action to add CSS and JavaScript to assist your create_field_options() action.
			*
			*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
			*  @type	action
			*  @since	3.6
			*  @date	23/01/13
			*/

			function field_group_admin_head() {
				// Note: This function can be removed if not used
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

			    // echo '<pre>'; var_dump($value); echo '</pre>'; exit;

				global $wpdb;
				$country_code = $value['countryCode'];
				if ( strlen( $country_code ) == 2 ) {
					$table                = $wpdb->prefix . 'cities';
					$row                  = $wpdb->get_row( "SELECT country, state_name FROM $table WHERE country_code= '$country_code'" );
					$country              = $row->country;
					$state_name           = $row->state_name;
					$value['stateCode']   = substr( $value['stateCode'], 3 );
					$value['stateName']   = $state_name;
					$value['countryName'] = $country;
				}

				return $value;
			}


			/*
			*  update_value()
			*
			*  This filter is applied to the $value before it is updated in the db
			*
			*  @type	filter
			*  @since	3.6
			*  @date	23/01/13
			*
			*  @param	$value - the value which will be saved in the database
			*  @param	$post_id - the $post_id of which the value will be saved
			*  @param	$field - the field array holding all the field options
			*
			*  @return	$value - the modified value
			*/
			function update_value( $value, $post_id, $field ) {
				return $value;
			}


			/*
			*  format_value()
			*
			*  This filter is applied to the $value after it is loaded from the db and before it is passed to the create_field action
			*
			*  @type	filter
			*  @since	3.6
			*  @date	23/01/13
			*
			*  @param	$value	- the value which was loaded from the database
			*  @param	$post_id - the $post_id from which the value was loaded
			*  @param	$field	- the field array holding all the field options
			*
			*  @return	$value	- the modified value
			*/

			function format_value( $value, $post_id, $field ) {
				// defaults?
				/*
				$field = array_merge($this->defaults, $field);
				*/

				// perhaps use $field['preview_size'] to alter the $value?


				// Note: This function can be removed if not used
				return $value;
			}


			/*
			*  format_value_for_api()
			*
			*  This filter is applied to the $value after it is loaded from the db and before it is passed back to the API functions such as the_field
			*
			*  @type	filter
			*  @since	3.6
			*  @date	23/01/13
			*
			*  @param	$value	- the value which was loaded from the database
			*  @param	$post_id - the $post_id from which the value was loaded
			*  @param	$field	- the field array holding all the field options
			*
			*  @return	$value	- the modified value
			*/

			function format_value_for_api( $value, $post_id, $field ) {
				// defaults?
			    /*
				$field = array_merge($this->defaults, $field);
				*/

				global $wpdb;
				$country_code = $value['countryCode'];
				if ( strlen( $country_code ) == 2 ) {
					$table                = $wpdb->prefix . 'cities';
					$row                  = $wpdb->get_row( "SELECT country, state_name FROM $table WHERE country_code= '$country_code'" );
					$country              = $row->country;
					$state_name           = $row->state_name;
					$value['stateCode']   = substr( $value['stateCode'], 3 );
					$value['stateName']   = $state_name;
					$value['countryName'] = $country;
				}

				return $value;
			}


			/*
			*  load_field()
			*
			*  This filter is applied to the $field after it is loaded from the database
			*
			*  @type	filter
			*  @since	3.6
			*  @date	23/01/13
			*
			*  @param	$field - the field array holding all the field options
			*
			*  @return	$field - the field array holding all the field options
			*/

			function load_field( $field ) {
				// Note: This function can be removed if not used
				return $field;
			}


			/*
			*  update_field()
			*
			*  This filter is applied to the $field before it is saved to the database
			*
			*  @type	filter
			*  @since	3.6
			*  @date	23/01/13
			*
			*  @param	$field - the field array holding all the field options
			*  @param	$post_id - the field group ID (post_type = acf)
			*
			*  @return	$field - the modified field
			*/

			function update_field( $field, $post_id ) {
				// Note: This function can be removed if not used
				return $field;
			}

		}


		// initialize
		new acf_field_city_selector( $this->settings );


		// class_exists check
	endif;

?>
