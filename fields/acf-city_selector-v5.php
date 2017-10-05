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
					'show_labels'   => 1
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
					'value'        => $field['show_labels'],
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
                        <span class="acf-input-header"><?php esc_html_e( 'Select a country', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="countryCode" class="screen-reader-text"></label>
                    <select name="<?php echo $field['name']; ?>[countryCode]" id="countryCode" class="countrySelect">
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
                        <span class="acf-input-header"><?php esc_html_e( 'Select a province/state', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="stateCode" class="screen-reader-text"></label>
                    <select name="<?php echo $field['name']; ?>[stateCode]" id="stateCode" class="countrySelect">
                    </select>
                </div>

                <div class="dropdown-box cs-cities">
					<?php if ( $field['show_labels'] == 1 ) { ?>
                        <span class="acf-input-header"><?php esc_html_e( 'Select a city', 'acf-city-selector' ); ?></span>
					<?php } ?>
                    <label for="cityName" class="screen-reader-text"></label>
                    <select name="<?php echo $field['name']; ?>[cityName]" id="cityName" class="countrySelect">
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
				$url     = $this->settings['url'];
				$version = $this->settings['version'];

				wp_register_script( 'acf-city-selector-js', "{$url}assets/js/city-selector.js", array( 'acf-input' ), $version );
				wp_enqueue_script( 'acf-city-selector-js' );

				if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' || isset( $_GET['id'] ) ) {

				    if ( isset( $_GET['id'] ) ) {
				        $post_id = $_GET['id'];
                    } else {
				        $post_id = get_the_ID();
                    }

					$fields     = get_field_objects( $post_id );
					$field_name = 'acf_city_selector';
					if ( is_array( $fields ) && count( $fields ) > 0 ) {
					    foreach( $fields as $field ) {
					        if ( isset( $field['type' ] ) && $field['type'] == 'acf_city_selector' ) {
					            $field_name = $field['name'];
					            break;
                            }
                        }
                    }
					$post_meta = get_post_meta( $post_id, $field_name, 1 );

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
			 * load_value()
			 *
			 * This filter is applied to the $value after it is loaded from the db
			 * This returns false if no country/state is selected (but empty values are stored)
			 * @TODO: fix save empty value
			 *
			 * @type    filter
			 * @param   $value (mixed) the value found in the database
			 * @param   $post_id (mixed) the $post_id from which the value was loaded
			 * @param   $field (array) the field array holding all the field options
			 * @return  $value
			 */
			function load_value( $value, $post_id, $field ) {

				global $wpdb;
				$country_code = $value['countryCode'];
				if ( '0' != $country_code ) {
					$state_code = substr( $value['stateCode'], 3 );
				}
				if ( strlen( $country_code ) == 2 && ( isset( $value['stateCode'] ) && '-' != $value['stateCode'] ) && ( isset( $value['cityName'] ) && 'Select a city' != $value['cityName'] ) ) {
					$table                = $wpdb->prefix . 'cities';
					$row                  = $wpdb->get_row( "SELECT country, state_name FROM $table WHERE country_code= '$country_code' AND state_code= '$state_code'" );
					$country              = $row->country;
					$state_name           = $row->state_name;
					$value['stateCode']   = $state_code;
					$value['stateName']   = $state_name;
					$value['countryName'] = $country;
				}

			    return $value;
			}


			/*
			 * update_value()
			 *
			 * This filter is applied to the $value before it is saved in the db
			 *
			 * @type    filter
			 * @param   $value (mixed) the value found in the database
			 * @param   $post_id (mixed) the $post_id from which the value was loaded
			 * @param   $field (array) the field array holding all the field options
			 * @return  $value
			 */
			// function update_value( $value, $post_id, $field ) {
             //    return $value;
			// }


			/*
			 * format_value()
			 *
			 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
			 *
			 * @type    filter
			 * @param   $value (mixed) the value which was loaded from the database
			 * @param   $post_id (mixed) the $post_id from which the value was loaded
			 * @param   $field (array) the field array holding all the field options
			 * @return  $value (mixed) the modified value
			 */
			// function format_value( $value, $post_id, $field ) {
			//     // bail early if no value
			//     if( empty( $value ) ) {
			//         return $value;
			//     }

			//     // return
			//     return $value;
			// }


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

                if ( 1 == $field['required'] ) {
				    if ( ! isset( $value['cityName'] ) || $value['cityName'] == 'Select a city' ) {
					    $valid = __( 'You didn\'t select a city', 'acf-city-selector' );
				    }
                }

				// return
				return $valid;

			}


			/*
			 * delete_value()
			 *
			 * This action is fired after a value has been deleted from the db.
			 * Please note that saving a blank value is treated as an update, not a delete
			 *
			 * @type    action
			 * @param   $post_id (mixed) the $post_id from which the value was deleted
			 * @param   $key (string) the $meta_key which the value was deleted
			 * @return  n/a
			 */
			// function delete_value( $post_id, $key ) {
			// }


			/*
			 * load_field()
			 *
			 * This filter is applied to the $field after it is loaded from the database
			 *
			 * @type    filter
			 * @param   $field (array) the field array holding all the field options
			 * @return  $field
			 */
			// function load_field( $field ) {
			//     return $field;
			// }

			/*
			 * update_field()
			 *
			 * This filter is applied to the $field before it is saved to the database
			 *
			 * @type    filter
			 * @param   $field (array) the field array holding all the field options
			 * @return  $field
			 */
			// function update_field( $field ) {
			//     return $field;
			// }


			/*
			 * delete_field()
			 *
			 * This action is fired after a field is deleted from the database
			 *
			 * @type    action
			 * @param   $field (array) the field array holding all the field options
			 * @return  n/a
			 */
			// function delete_field( $field ) {
			// }


			/*
			 * Get Countries
			 *
			 * Get all countries from the database
			 */
			public function _acf_get_countries() {
				global $wpdb;
				$countries_db = $wpdb->get_results( "
                    SELECT DISTINCT *
                    FROM " . $wpdb->prefix . "cities
                    group by country
                    order by country ASC
                " );

				$countries = array();
				foreach ( $countries_db as $country ) {
					if ( trim( $country->country ) == '' ) {
						continue;
					}
					$countries[ $country->id ] = $country->country;
				}

				return $countries;
			}

		}

		// initialize
		new acf_field_city_selector( $this->settings );

	endif; // class_exists check

