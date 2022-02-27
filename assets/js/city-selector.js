// JS File for Country Field
(function($) {
    $(document).ready(function () {

        $(".acf-input .button").click(function () {
            if ( 'add-row' === $(this).data('event') ) {
                setTimeout(function () {
                    change_dropdowns();
                },0);
            }

            if ( 'add-layout' === $(this).data('name') ) {
                setTimeout(function () {
                    $('.acf-tooltip ul li').on('click','a',function (e){
                        setTimeout(function () {
                            change_dropdowns();
                            $(".acf-input .button").click(function () {
                                setTimeout(function () {
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
        function change_dropdowns () {
            $post_id = false;
            var countries = $('select[name*="countryCode"]');
            var state = $('select[name*="stateCode"]');

            if (typeof(city_selector_vars) != "undefined" && city_selector_vars !== null) {
                $post_id = city_selector_vars[ 'post_id' ];
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

                    if (typeof(city_selector_vars) != "undefined" && city_selector_vars !== null) {
                        $show_labels = city_selector_vars[ 'show_labels' ];
                        $which_fields = city_selector_vars[ 'which_fields' ];
                    } else {
                        $show_labels = $(this).data('show-labels');
                        $which_fields = $(this).data('which-fields');
                    }
                    var show_labels = $show_labels;
                    var which_fields = $which_fields;

                    if ( $.inArray(which_fields, [ 'country_state', 'all' ] ) !== -1 ) {
                        const d = acfcs_get_states(country_code, show_labels, post_id);
                        response_states.push(d);

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

                    } else if ( $.inArray(which_fields, [ 'country_city' ] ) !== -1 ) {
                        const d = acfcs_get_cities(country_code, show_labels, post_id);
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

                    if(typeof(city_selector_vars) != "undefined" && city_selector_vars !== null) {
                        $show_labels = city_selector_vars[ 'show_labels' ];
                        $which_fields = city_selector_vars[ 'which_fields' ];
                    } else {
                        $show_labels = $(this).data('show-labels');
                        $which_fields = $(this).data('which-fields');
                    }
                    var show_labels = $show_labels;
                    var which_fields = $which_fields;

                    if ( 'all' === which_fields || which_fields.indexOf("city") >= 0 ) {
                        const response_cities = [];
                        var $this = $(this);
                        var state_code = $this.val();
                        var state_field_id = $this.attr('id');
                        var city_field_id = state_field_id.replace('stateCode', 'cityName');
                        var changed_city = $('select[id="' + city_field_id + '"]');
                        const d = acfcs_get_cities(state_code, show_labels, post_id);
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
        function acfcs_get_states(countryCode, showLabels, postID, callback) {
            const state_data = {
                action: 'get_states_call',
                country_code: countryCode,
                post_id: postID,
                show_labels: showLabels
            };

            return new Promise((resolve, reject) => {
                $.post(city_selector_vars.ajaxurl, state_data, (response) => {
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
        function acfcs_get_cities(stateCode, showLabels, postID, callback) {
            const city_data = {
                action: 'get_cities_call',
                post_id: postID,
                show_labels: showLabels,
                state_code: stateCode
            };

            return new Promise((resolve, reject) => {
                $.post(city_selector_vars.ajaxurl, city_data, (response) => {
                    resolve(response);
                });
            })
        }

        /**
         * Function calls
         */
        change_dropdowns();

    });

})(jQuery);

