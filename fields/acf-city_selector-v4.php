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
					// 'country_name'  => '',
					// 'city_name'     => '',
					// 'province_name' => 0,
					// 'country_id'    => 0,
					// 'city_id'       => 0,
					// 'province_id'   => '',
					'show_labels'   => 1
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
				// defaults?
				/*
				*/
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
                                'name'         => 'fields['.$key.'][show_labels]',
                                'choices'      => $select_options,
                                'value'        => $field['show_labels'],
                                'layout'       => 'horizontal',
                                // 'label'        => esc_html__( 'Show labels', 'acf-city-selector' ), // needed ?
                                // 'instructions' => esc_html__( 'Show field labels above the dropdown menus', 'acf-city-selector' ), // needed ?
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
				// defaults?
				/*
				*/
				$field = array_merge($this->defaults, $field);

				// create Field HTML
				if ( isset( $field[ 'value' ][ 'countryCode' ] ) ) {
					$countrycode = $field[ 'value' ][ 'countryCode' ];
				}
				$countries   = populate_country_select( '', $field );
				if ( $countries ) {
					// $first_item = ( $field['show_labels'] == 1 ) ? '-' : esc_html__( 'Select country', 'acf-city-selector' );
					// array_unshift( $countries, $first_item );

				}
				if ( isset( $countrycode ) && 0 != $countrycode ) {
					$stateCode = $field['value']['stateCode'];
					if ( '-' != $stateCode ) {
						$cityName  = $field['value']['cityNameAscii'];
					}
					$states    = get_states( $countrycode );
				}
				$stateName = ! empty( $states ) ? $states[ substr( $stateCode, 3 ) ] : false;
				?>
                <div class="cs_countries">
					<?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select country', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="countryCode" class="screen-reader-text"></label>
                    <select name="acf[<?php echo $field['key']; ?>][countryCode]" id="countryCode" class="countrySelect">
						<?php
							foreach ( $countries as $key => $country ) {
								if ( isset( $countrycode ) ) {
									$selected = ( $countrycode == $key ) ? " selected=\"selected\"" : false;
								} else {
									$selected = false;
								}
								?>
                                <option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $country; ?></option>
							<?php } ?>
                    </select>
                </div>

                <div class="cs_provinces">
					<?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select province/state', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="stateCode" class="screen-reader-text"></label>
                    <select name="acf[<?php echo $field['key']; ?>][stateCode]" id="stateCode" class="countrySelect">
                    </select>
                </div>

                <div class="cs_cities">
					<?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select city', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="cityNameAscii" class="screen-reader-text"></label>
                    <select name="acf[<?php echo $field['key']; ?>][cityNameAscii]" id="cityNameAscii" class="countrySelect">
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

				// vars
				$url     = $this->settings['url'];
				$version = $this->settings['version'];

				// register & include JS
				wp_register_script( 'acf-input-city_selector', "{$url}assets/js/city-selector.js", array( 'acf-input' ), $version );
				wp_enqueue_script( 'acf-input-city_selector' );

				if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
					$post_meta = get_post_meta( get_the_ID(), 'acf_city_selector', 1 );

					if ( ! empty( $post_meta['cityNameAscii'] ) ) {
						wp_localize_script( 'acf-city-selector-js', 'city_selector_vars', array(
							'countryCode'   => $post_meta['countryCode'],
							'stateCode'     => $post_meta['stateCode'],
							'cityNameAscii' => $post_meta['cityNameAscii'],
						) );
					}
				}

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
				// Note: This function can be removed if not used
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

				if ( 1 == $field['required'] ) {
					if ( ! isset( $value['cityNameAscii'] ) || $value['cityNameAscii'] == 'Select city' || $value['cityNameAscii'] == 0 ) {
						$valid = __( 'You didn\'t select a city', 'acf-city-selector' );
					}
				}

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

				// perhaps use $field['preview_size'] to alter the $value?


				// Note: This function can be removed if not used
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
