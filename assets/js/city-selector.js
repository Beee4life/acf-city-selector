// JS File for Country Field
(function($) {
    jQuery(document).ready(function() {

        jQuery(".acf-input .button").click(function () {
            if ( 'add-row' === $(this).data('event') ) {
                setTimeout(function() {
                    change_dropdowns();
                },0);
            }

            if ( 'add-layout' === $(this).data('name') ) {
                setTimeout(function() {
                    jQuery('.acf-tooltip ul li').on('click','a',function(e){
                        setTimeout(function() {
                            change_dropdowns();
                            jQuery(".acf-input .button").click(function () {
                                setTimeout(function() {
                                    change_dropdowns();
                                },0);
                            });
                        },0);
                    });
                },0);
            }
        });

        /**
         * Change dropdowns
         */
        function change_dropdowns( $instance ) {
            $post_id = false;
            var countries = $('select[name*="countryCode"]');
            var state = $('select[name*="stateCode"]');

            var parts = window.location.search.substr(1).split("&");
            var $_GET = {};
            for (var i = 0; i < parts.length; i++) {
                var temp = parts[i].split("=");
                $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
            }
            if ( $_GET[ 'post' ] ) {
                $post_id = $_GET[ 'post' ];
            }
            var post_id = $post_id;

            /**
             * If there are any selects with name*=countryCode
             */
            if (countries.length) {
                countries.on('change', function () {
                    const response_cities = []
                    const response_states = []

                    var $this             = $(this);
                    var country_code      = $this.val();
                    var country_field_id  = $this.attr('id');
                    var state_field_id    = country_field_id.replace( 'countryCode', 'stateCode' );
                    var city_field_id     = country_field_id.replace( 'countryCode', 'cityName' );
                    var changed_state     = $('select[id="' + state_field_id + '"]');
                    var changed_city      = $('select[id="' + city_field_id + '"]');

                    // @TODO: maybe get from data-
                    $which_fields = 'all';
                    if(typeof(city_selector_vars) != "undefined" && city_selector_vars !== null) {
                        $which_fields = city_selector_vars[ 'which_fields' ];
                    }
                    var show_labels = $(this).data('show-label');
                    var which_fields = $which_fields;

                    if ( jQuery.inArray(which_fields, [ 'country_state', 'all' ] ) !== -1 ) {
                        const d = get_states(country_code, show_labels, post_id);
                        response_states.push(d);
                        const e = get_cities(country_code, show_labels, post_id);
                        response_cities.push(e);

                        Promise.all(response_states).then(function(jsonResults) {
                            for (i = 0; i < jsonResults.length; i++) {
                                var obj          = JSON.parse(jsonResults[i]);
                                var len          = obj.length;
                                var $stateValues = '';

                                changed_state.empty();
                                changed_state.fadeIn();
                                for (j = 0; j < len; j++) {
                                    var state = obj[j];
                                    $stateValues += '<option value="' + state.country_state + '">' + state.state_name + '</option>';
                                }
                                changed_state.append($stateValues);
                            }
                        });

                        Promise.all(response_cities).then(function(jsonResults) {
                            for (i = 0; i < jsonResults.length; i++) {
                                var obj         = JSON.parse(jsonResults);
                                var len         = obj.length;
                                var $cityValues = '';

                                changed_city.empty();
                                changed_city.fadeIn();
                                for (j = 0; j < len; j++) {
                                    var city = obj[j];
                                    if ( j === 0 ) {
                                        $cityValues += '<option value="">' + city.city_name + '</option>';
                                    } else {
                                        $cityValues += '<option value="' + city.city_name + '">' + city.city_name + '</option>';
                                    }
                                }
                                changed_city.append($cityValues);
                            }
                        });

                    } else if ( jQuery.inArray(which_fields, [ 'country_city' ] ) !== -1 ) {
                        const d = get_cities(country_code, show_labels, post_id);
                        response_cities.push(d);

                        Promise.all(response_cities).then(function(jsonResults) {
                            for (i = 0; i < jsonResults.length; i++) {
                                var obj         = JSON.parse(jsonResults);
                                var len         = obj.length;
                                var $cityValues = '';

                                changed_city.empty();
                                changed_city.fadeIn();
                                for (j = 0; j < len; j++) {
                                    var city = obj[j];
                                    if ( j === 0 ) {
                                        $cityValues += '<option value="">' + city.city_name + '</option>';
                                    } else {
                                        $cityValues += '<option value="' + city.city_name + '">' + city.city_name + '</option>';
                                    }
                                }
                                changed_city.append($cityValues);
                            }
                        });
                    }
                });
            }

            // if there are any selects with name*=stateCode
            if (state.length) {
                state.on('change', function () {

                    // @TODO: maybe get from data-
                    $which_fields = 'all';
                    if(typeof(city_selector_vars) != "undefined" && city_selector_vars !== null) {
                        $which_fields = city_selector_vars[ 'which_fields' ];
                    }
                    var show_labels = $(this).data('show-label');
                    var which_fields = $which_fields;

                    if ( 'all' === which_fields || which_fields.indexOf("city") >= 0 ) {
                        const response_cities = [];
                        var $this = $(this);
                        var state_code = $this.val();
                        var state_field_id = $this.attr('id');
                        var city_field_id = state_field_id.replace('stateCode', 'cityName');
                        var changed_city = $('select[id="' + city_field_id + '"]');
                        const d = get_cities(state_code, show_labels, post_id);
                        response_cities.push(d);

                        Promise.all(response_cities).then(function(jsonResults) {
                            for (i = 0; i < jsonResults.length; i++) {
                                var obj         = JSON.parse(jsonResults);
                                var len         = obj.length;
                                var $cityValues = '';

                                changed_city.empty();
                                changed_city.fadeIn();
                                for (j = 0; j < len; j++) {
                                    var city = obj[j];
                                    if ( j === 0 ) {
                                        $cityValues += '<option value="">' + city.city_name + '</option>';
                                    } else {
                                        $cityValues += '<option value="' + city.city_name + '">' + city.city_name + '</option>';
                                    }
                                }
                                changed_city.append($cityValues);
                            }
                        });
                    }
                });
            }
        }

        /**
         * Get states on change
         *
         * @param countryCode
         * @param showLabels
         * @param postID
         * @param callback
         * @returns {Promise<unknown>}
         */
        function get_states(countryCode, showLabels, postID, callback) {
            const state_data = {
                action: 'get_states_call',
                country_code: countryCode,
                post_id: postID,
                show_labels: showLabels
            };

            return new Promise((resolve, reject) => {
                $.post(ajaxurl, state_data, (response) => {
                    resolve(response);
                });
            })
        }

        /**
         * Get cities on change
         *
         * @param stateCode
         * @param showLabels
         * @param postID
         * @param callback
         * @returns {Promise<unknown>}
         */
        function get_cities(stateCode, showLabels, postID, callback) {
            const city_data = {
                action: 'get_cities_call',
                post_id: postID,
                show_labels: showLabels,
                state_code: stateCode
            };

            return new Promise((resolve, reject) => {
                $.post(ajaxurl, city_data, (response) => {
                    resolve(response);
                });
            })
        }

        acf.addAction('new_field/type=xacf_city_selector', function($field){
            if ( jQuery.isFunction(jQuery.fn.select2) ) {
                console.log($field);
                // console.log('select2 is available');
                jQuery('select.select2.acfcs__dropdown--countries').select2();
            }
        });

        /**
         * Function calls
         */
        change_dropdowns();

    });

    // src: https://www.advancedcustomfields.com/resources/javascript-api/#acf.field-extend

    // check
    // https://support.advancedcustomfields.com/forums/topic/dependent-dropdown-select-field/
    // https://pastebin.com/ABFTEzL4
    // https://github.com/Hube2/acf-dynamic-ajax-select-example/blob/master/dynamic-fields-on-relationship/dynamic-fields-on-relationship.js
    // https://github.com/Hube2/acf-dynamic-ajax-select-example/blob/master/dynamic-select-example/dynamic-select-on-select.js

    var ACFCS = acf.Field.extend({
        type: 'acf_city_selector',
        actions: {
            'append': 'onAppend'
        },
        onAppend: function($el){
            // check if select2 is available
            if ( jQuery.isFunction(jQuery.fn.select2) ) {
                this.render();
            }
        },
        render: function(){
            $('select.select2.acfcs__dropdown--countries').select2();
            $('select.select2.acfcs__dropdown--states').select2();
            $('select.select2.acfcs__dropdown--cities').select2();
        }
    });

    acf.registerFieldType( ACFCS );

})(jQuery);

