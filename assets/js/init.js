(function($){

    /**
     *  initialize_field
     *
     *  This function will initialize the $field for select2.
     *
     *  @since	0.29.0
     *
     *  @param $field
     *  @return	n/a
     */
    function initialize_field( $field ) {

        render_field();

        $(".acf-input .button").click(function () {
            if ( 'add-row' === $(this).data('event') ) {
                setTimeout(function() {
                    render_field();
                },0);
            }

            if ( 'add-layout' === $(this).data('name') ) {
                setTimeout(function() {
                    $('.acf-tooltip ul li').on('click','a',function(e){
                        setTimeout(function() {
                            render_field();
                            $(".acf-input .button").click(function () {
                                setTimeout(function() {
                                    render_field();
                                },0);
                            });
                        },0);
                    });
                },0);
            }
        });
    }

    function render_field() {

        $no_countries = acf._e('acf_city_selector', 'no_countries');
        $select_city = '-';
        $select_country = '-';
        $select_country_first = acf._e('acf_city_selector', 'select_country_first');
        $select_state = '-';
        $select_state_first = acf._e('acf_city_selector', 'select_state_first');

        if(typeof(city_selector_vars) !== "undefined" && city_selector_vars !== null) {
            $show_labels = city_selector_vars[ 'show_labels' ];
            $which_fields = city_selector_vars[ 'which_fields' ];
        } else {
            $show_labels = '1';
            $which_fields = 'all';
        }
        var show_labels = $show_labels;
        var which_fields = $which_fields;

        if ( '1' !== show_labels ) {
            $select_country = acf._e('acf_city_selector', 'select_country');
            $select_state = acf._e('acf_city_selector', 'select_state');
            $select_city = acf._e('acf_city_selector', 'select_city');
        }

        if ( 'country_city' === which_fields ) {
            $select_country_first = acf._e('acf_city_selector', 'select_country_first');
        }

        if ( $.isFunction($.fn.select2) ) {
            $('select.select2.acfcs__dropdown--countries').select2({
                allowClear: true,
                placeholder: $select_country,
                language: {
                    noResults: function() {
                        return $no_countries
                    }
                }
            });

            $('select.select2.acfcs__dropdown--states').select2({
                allowClear: true,
                placeholder: $select_state,
                language: {
                    noResults: function() {
                        return $select_country_first
                    }
                }
            });

            $('select.select2.acfcs__dropdown--cities').select2({
                allowClear: true,
                placeholder: $select_city,
                language: {
                    noResults: function() {
                        return $select_state_first
                    }
                }
            });
        }
    }

    if( typeof acf.add_action !== 'undefined' ) {
        acf.add_action('ready_field/type=acf_city_selector', initialize_field);
        acf.add_action('append_field/type=acf_city_selector', initialize_field);
    }

})(jQuery);
