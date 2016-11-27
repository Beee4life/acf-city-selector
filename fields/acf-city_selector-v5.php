<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_city_selector') ) :


class acf_field_city_selector extends acf_field {


    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type    function
    *  @param   n/a
    *  @return  n/a
    */

    function __construct( $settings ) {

        $this->name         = 'sd_city_selector';
        $this->label        = __('City Selector', 'acf-city_selector');
        $this->category     = 'choice';
        $this->defaults     = array(
            // 'font_size'         => 14,
            // 'initial_value'     => 1,
            'country_name'      => '',
            'city_name'         => '',
            'provence_name'     => 0,
            'country_id'        => 0,
            'city_id'           => 0,
            'provence_id'       => '',
            'show_labels'       => 1
        );

        /*
        *  Keep for now
        *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
        *  var message = acf._e('city_selector', 'error');
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
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type    action
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field_settings( $field ) {

        /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  Please note that you must also have a matching $defaults value for the field name (show_labels)
        */

        $select_options = array(
            1 => __( 'Yes', 'acf-city-selector' ),
            0 => __( 'No', 'acf-city-selector' )
        );
        acf_render_field_setting( $field, array(
            'type'          => 'radio',
            'choices'       => $select_options,
            'layout'        =>  'horizontal',
            'label'         => __( 'Show labels', 'acf-city-selector' ),
            'instructions'  => __( 'Show field labels (or not)', 'acf-city-selector' ),
            'name'          => 'show_labels',
            // 'default_value' => 'Yes'
            // 'prepend'       => 'px',
        ));

    }

    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @type    action
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field( $field ) {

        // field output in form
        // echo '<pre>'; print_r( $field['value'] ); echo '</pre>';
        // exit;

        ?>
        <div class="cs_countries">
            <?php if ( $field['show_labels'] == 1 ) { ?>
                <span class="acf-input-header"><?php _e( 'Select country', 'acf-city-selector' ); ?></span>
            <?php } ?>
            <select name="acf[<?php echo $field['key']; ?>][countryCode]" id="countryCode" class="countrySelect">
            <?php foreach( populate_country_select() as $key => $country ) { ?>
                <option value="<?php echo $key; ?>"><?php echo $country; ?></option>
            <?php } ?>
            </select>
        </div>

        <div class="cs_provences">
            <?php if ( $field['show_labels'] == 1 ) { ?>
                <span class="acf-input-header"><?php _e( 'Select provence/state', 'acf-city-selector' ); ?></span>
            <?php } ?>
            <select name="acf[<?php echo $field['key']; ?>][stateCode]" id="stateCode" class="countrySelect"></select>
        </div>

        <div class="cs_cities">
            <?php if ( $field['show_labels'] == 1 ) { ?>
                <span class="acf-input-header"><?php _e( 'Select city', 'acf-city-selector' ); ?></span>
            <?php } ?>
            <select name="acf[<?php echo $field['key']; ?>][cityNameAscii]" id="cityNameAscii" class="countrySelect"></select>
        </div>
        <?php
    }


    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @param   n/a
    *  @return  n/a
    */

    // function input_admin_enqueue_scripts() {
    // }

    /*
    *  input_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_head)
    *  @param   n/a
    *  @return  n/a
    */

    function input_admin_head() {
        // vars
        $url        = $this->settings['url'];
        $version    = $this->settings['version'];

        // register & include JS
        wp_register_script( 'acf-city-selector-js', "{$url}assets/js/city-selector.js", array('acf-input'), $version );
        wp_enqueue_script( 'acf-city-selector-js' );

        // register & include CSS
        wp_register_style( 'acf-city-selector-css', "{$url}assets/css/acf-city-selector.css", array('acf-input'), $version );
        wp_enqueue_style( 'acf-city-selector-css' );
    }



    /*
    *  input_form_data()
    *
    *  This function is called once on the 'input' page between the head and footer
    *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
    *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
    *  seen on comments / user edit forms on the front end. This function will always be called, and includes
    *  $args that related to the current screen such as $args['post_id']
    *
    *  @type    function
    *  @param   $args (array)
    *  @return  n/a
    */

    // function input_form_data( $args ) {
    // }


    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type    filter
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    // function load_value( $value, $post_id, $field ) {
    //     return $value;
    // }


    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type    filter
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    // function update_value( $value, $post_id, $field ) {
    //     return $value;
    // }


    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type    filter
    *  @param   $value (mixed) the value which was loaded from the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value (mixed) the modified value
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
    *  validate_value()
    *
    *  This filter is used to perform validation on the value prior to saving.
    *  All values are validated regardless of the field's required setting. This allows you to validate and return
    *  messages to the user if the value is not correct
    *
    *  @type    filter
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $valid (boolean) validation status based on the value and the field's required setting
    *  @param   $value (mixed) the $_POST value
    *  @param   $field (array) the field array holding all the field options
    *  @param   $input (string) the corresponding input name for $_POST value
    *  @return  $valid
    */


    function validate_value( $valid, $value, $field, $input ) {

        // Advanced usage
        if ( !isset( $value['cityNameAscii'] ) || $value['cityNameAscii'] == 'Select city' ) {
            $valid = __('You didn\'t select a city', 'acf-city-selector' );
        }

        // return
        return $valid;

    }


    /*
    *  delete_value()
    *
    *  This action is fired after a value has been deleted from the db.
    *  Please note that saving a blank value is treated as an update, not a delete
    *
    *  @type    action
    *  @param   $post_id (mixed) the $post_id from which the value was deleted
    *  @param   $key (string) the $meta_key which the value was deleted
    *  @return  n/a
    */

    // function delete_value( $post_id, $key ) {
    // }


    /*
    *  load_field()
    *
    *  This filter is applied to the $field after it is loaded from the database
    *
    *  @type    filter
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    // function load_field( $field ) {
    //     return $field;
    // }

    /*
    *  update_field()
    *
    *  This filter is applied to the $field before it is saved to the database
    *
    *  @type    filter
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    // function update_field( $field ) {
    //     return $field;
    // }


    /*
    *  delete_field()
    *
    *  This action is fired after a field is deleted from the database
    *
    *  @type    action
    *  @param   $field (array) the field array holding all the field options
    *  @return  n/a
    */

    // function delete_field( $field ) {
    // }


    /**
     * Get Countries
     *
     * Get all countries from the database
     *
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
            if ( trim( $country->country ) == '' ) continue;
            $countries[$country->id] = $country->country;
        }

        return $countries;
    }

}

// initialize
new acf_field_city_selector( $this->settings );

// class_exists check
endif;

?>
