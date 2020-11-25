(function($){

    /**
     *  initialize_field
     *
     *  This function will initialize the $field.
     *
     *  @since	0.29.0
     *
     *  @param $field
     *  @return	n/a
     */
    function initialize_field( $field ) {

        if ( $.isFunction($.fn.select2) ) {
            render_field( $field );
        }

        $(".acf-input .button").click(function () {
            if ( 'add-row' === $(this).data('event') ) {
                setTimeout(function() {
                    render_field( $field );
                },0);
            }

            if ( 'add-layout' === $(this).data('name') ) {
                setTimeout(function() {
                    $('.acf-tooltip ul li').on('click','a',function(e){
                        setTimeout(function() {
                            render_field( $field );
                            $(".acf-input .button").click(function () {
                                setTimeout(function() {
                                    render_field( $field );
                                },0);
                            });
                        },0);
                    });
                },0);
            }
        });
    }

    function render_field( $field ) {

        $select_country = '-';
        $select_state = '-';
        $select_city = '-';
        $show_labels = "1";

        if(typeof(city_selector_vars) != "undefined" && city_selector_vars !== null) {
            $show_labels = city_selector_vars[ 'show_labels' ];
        }
        var show_labels = $show_labels;

        if ( '0' === show_labels ) {
            $select_country = acf._e('acf_city_selector', 'select_country');
            $select_state = acf._e('acf_city_selector', 'select_state');
            $select_city = acf._e('acf_city_selector', 'select_city');
        }

        $('select.select2.acfcs__dropdown--countries').select2({
            allowClear: true,
            placeholder: $select_country
        });
        $('select.select2.acfcs__dropdown--states').select2({
            allowClear: true,
            placeholder: $select_state
        });
        $('select.select2.acfcs__dropdown--cities').select2({
            allowClear: true,
            placeholder: $select_city
        });

    }

    if( typeof acf.add_action !== 'undefined' ) {
        acf.add_action('ready_field/type=acf_city_selector', initialize_field);
        acf.add_action('append_field/type=acf_city_selector', initialize_field);
    }

})(jQuery);
