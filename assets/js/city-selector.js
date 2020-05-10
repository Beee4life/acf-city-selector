// JS File for Country Field
(function($) {
    jQuery(document).ready(function() {

        jQuery(".acf-input .button").click(function () {
            if ( 'add-row' === $(this).data('event') ) {
                setTimeout(function() {
                    change_dropdowns($('select[name*="countryCode"]'));
                },0);
            }

            if ( 'add-layout' === $(this).data('name') ) {
                setTimeout(function() {
                    jQuery('.acf-tooltip ul li').on('click','a',function(e){
                        setTimeout(function() {
                            change_dropdowns($('select[name*="countryCode"]'));
                        },0);
                    });
                },0);
            }
        });

        /**
         * Change dropdowns
         */
        function change_dropdowns( $instance ) {

            if (typeof $instance === "undefined") {
                $countries = $('select[name*="countryCode"]');
            } else {
                $countries = $instance;
            }
            var countries = $countries;
            var state = $('select[name*="stateCode"]');

            /**
             * If there are any selects with name*=countryCode
             */
            if (countries.length) {
                countries.on('change', function () {
                    const response_states = []
                    var $this             = $(this);
                    var country_code      = $this.val();
                    var country_field_id  = $this.attr('id');
                    var state_field_id    = country_field_id.replace( 'countryCode', 'stateCode' );
                    var city_field_id     = country_field_id.replace( 'countryCode', 'cityName' );
                    var changed_state     = $('select[id="' + state_field_id + '"]');
                    var changed_city      = $('select[id="' + city_field_id + '"]');
                    const d               = get_states(country_code);
                    response_states.push(d)

                    Promise.all(response_states).then(function(jsonResults) {
                        for (i = 0; i < jsonResults.length; i++) {
                            var obj          = JSON.parse(jsonResults[i]);
                            var len          = obj.length;
                            var $stateValues = '';

                            changed_city.empty();
                            changed_city.fadeIn();
                            changed_state.empty();
                            changed_state.fadeIn();
                            for (j = 0; j < len; j++) {
                                $selected = '';
                                var state = obj[j];
                                $stateValues += '<option value="' + state.country_state + '">' + state.state_name + '</option>';
                            }
                            changed_state.append($stateValues);
                            // @TODO: translate string
                            $select_city = '<option value="">Select a province/state first</option>';
                            changed_city.append($select_city);
                        }
                    });
                });
            }

            // if there are any selects with name*=stateCode
            if (state.length) {
                state.on('change', function () {
                    const response_cities = []
                    var $this = $(this);
                    var state_code = $this.val();
                    var state_field_id = $this.attr('id');
                    var city_field_id = state_field_id.replace( 'stateCode', 'cityName' );
                    var changed_city = $('select[id="' + city_field_id + '"]');
                    const d = get_cities(state_code);
                    response_cities.push(d)

                    Promise.all(response_cities).then(function(jsonResults) {
                        for (i = 0; i < jsonResults.length; i++) {
                            var obj         = JSON.parse(jsonResults);
                            var len         = obj.length;
                            var $cityValues = '';

                            changed_city.empty();
                            changed_city.fadeIn();
                            for (j = 0; j < len; j++) {
                                var city = obj[j];
                                $cityValues += '<option value="' + city.city_name + '">' + city.city_name + '</option>';
                            }
                            changed_city.append($cityValues);
                        }
                    });
                });
            }
        }

        /**
         * Load select states when editing a post
         */
        function admin_post_edit_load_states() {

            if (true === Array.isArray(city_selector_vars)) {
                // preparing the response array
                const response_states = []
                for (i = 0; i < city_selector_vars.length; i++) {
                    const d = get_states(city_selector_vars[i].countryCode);
                    response_states.push(d)
                }

                Promise.all(response_states).then(function(jsonResults) {
                    var state_instance_count = 0;
                    for (i = 0; i < jsonResults.length; i++) {
                        var obj = JSON.parse(jsonResults[i]);
                        var len = obj.length;
                        var $stateValues = '';
                        var select_state = $('select[name*="row-' + state_instance_count + '"][name*="stateCode"]');
                        var stored_state = city_selector_vars[state_instance_count].stateCode;

                        select_state.fadeIn();
                        for (j = 0; j < len; j++) {
                            $selected = '';
                            var state = obj[j];
                            var current_state = state.country_code + '-' + state.state_code;
                            if (current_state === stored_state) {
                                $selected = ' selected="selected"';
                            }
                            var selected = $selected;
                            $stateValues += '<option value="' + state.country_code + '-' + state.state_code + '"' + selected + '>' + state.state_name + '</option>';
                        }
                        select_state.append($stateValues);
                        state_instance_count++;
                    }
                });

            } else {

                const response_states = [];
                if ( 'object' == typeof city_selector_vars ) {
                    // flex block
                    Object.size = function(obj) {
                        var size = 0, key;
                        for (key in obj) {
                            if (obj.hasOwnProperty(key)) size++;
                        }
                        return size;
                    };
                    var len = Object.size(city_selector_vars);
                    for( $i = 1; $i <= len ; $i++ ) {
                        const d = get_states(city_selector_vars[$i].countryCode);
                        response_states.push(d)
                    }

                } else {
                    // single/group
                    const d = get_states(city_selector_vars.countryCode);
                    response_states.push(d)
                }

                Promise.all(response_states).then(function(jsonResults) {
                    for (i = 0; i < jsonResults.length; i++) {
                        var obj          = JSON.parse(jsonResults[i]);
                        var len          = obj.length;
                        var $stateValues = '';
                        var select_state = $("select[name*='stateCode']");
                        if ( 'object' == typeof city_selector_vars ) {
                            // @TODO: get selected state code(s) properly
                            $stored_state = city_selector_vars[1].stateCode;
                        } else {
                            $stored_state = city_selector_vars.stateCode;
                        }
                        var stored_state = $stored_state;

                        select_state.fadeIn();
                        for (j = 0; j < len; j++) {
                            $selected = '';
                            var state = obj[j];
                            var current_state = state.country_code + '-' + state.state_code;
                            if (current_state === stored_state) {
                                $selected = ' selected="selected"';
                            }
                            var selected = $selected;
                            $stateValues += '<option value="' + state.country_code + '-' + state.state_code + '"' + selected + '>' + state.state_name + '</option>';
                        }
                        select_state.append($stateValues);
                    }
                });
            }
        }

        /**
         * Load select cities when editing a post
         */
        function admin_post_edit_load_cities() {
            if ( true === Array.isArray(city_selector_vars) ) {
                const response_cities = []
                for (i = 0; i < city_selector_vars.length; i++) {
                    const d = get_cities(city_selector_vars[i].stateCode);
                    response_cities.push(d)
                }

                Promise.all(response_cities).then(function(jsonResults) {
                    var city_instance_count = 0;
                    for (i = 0; i < jsonResults.length; i++) {
                        var obj = JSON.parse(jsonResults[i]);
                        var len = obj.length;
                        var $cityValues = '';
                        var select_city = $('select[name*="row-' + city_instance_count + '"][name*="cityName"]');
                        var stored_city = city_selector_vars[city_instance_count].cityName;

                        select_city.fadeIn();
                        for (j = 0; j < len; j++) {
                            $selected = '';
                            var city = obj[j];
                            var city_name = city.city_name;
                            if (city_name === stored_city) {
                                $selected = ' selected="selected"';
                            }
                            var selected = $selected;
                            $cityValues += '<option value="' + city_name + '"' + selected + '>' + city_name + '</option>';
                        }
                        select_city.append($cityValues);
                        city_instance_count++;
                    }

                });

            } else {

                const response_cities = [];
                if ( 'object' == typeof city_selector_vars ) {
                    // flex block
                    Object.size = function(obj) {
                        var size = 0, key;
                        for (key in obj) {
                            if (obj.hasOwnProperty(key)) size++;
                        }
                        return size;
                    };
                    var len = Object.size(city_selector_vars);
                    for( $i = 1; $i <= len ; $i++ ) {
                        const d = get_cities(city_selector_vars[$i].stateCode);
                        response_cities.push(d)
                    }

                } else {
                    // single/group
                    const d = get_cities(city_selector_vars.stateCode);
                    response_cities.push(d)
                }

                Promise.all(response_cities).then(function(jsonResults) {
                    for (i = 0; i < jsonResults.length; i++) {
                        var obj = JSON.parse(jsonResults[i]);
                        var len = obj.length;
                        var $cityValues = '';
                        var select_city = $('select[name*="cityName"]');
                        if ( 'object' == typeof city_selector_vars ) {
                            // @TODO: get selected city name properly
                            $stored_city = city_selector_vars[1].cityName;
                        } else {
                            $stored_city = city_selector_vars.cityName;
                        }
                        var stored_city = $stored_city;

                        select_city.fadeIn();
                        for (j = 0; j < len; j++) {
                            $selected = '';
                            var city = obj[j];
                            var city_name = city.city_name;
                            if (city_name === stored_city) {
                                $selected = ' selected="selected"';
                            }
                            var selected = $selected;
                            $cityValues += '<option value="' + city_name + '"' + selected + '>' + city_name + '</option>';
                        }
                        select_city.append($cityValues);
                    }
                });
            }
        }

        /**
         * Get states
         *
         * @param countryCode
         * @param callback
         */
        function get_states(countryCode, callback) {
            const state_data = {
                action: 'get_states_call',
                country_code: countryCode
            };

            return new Promise((resolve, reject) => {
                $.post(ajaxurl, state_data, (response) => {
                    resolve(response);
                });
            })
        }

        /**
         * Get cities
         *
         * @param stateCode
         * @param callback
         */
        function get_cities(stateCode, callback) {
            const city_data = {
                action: 'get_cities_call',
                state_code: stateCode
            };

            return new Promise((resolve, reject) => {
                $.post(ajaxurl, city_data, (response) => {
                    resolve(response);
                });
            })
        }

        /**
         * Function calls
         */
        if (typeof city_selector_vars !== "undefined") {
            admin_post_edit_load_states();
            admin_post_edit_load_cities();
        }
        change_dropdowns();

    });

})(jQuery);

