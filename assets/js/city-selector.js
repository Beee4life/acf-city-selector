// JS File for Country Field
(function($) {
    jQuery(document).ready(function() {

        jQuery(".acf-input .button").click(function () {
            var event = $(this).data('event');
            if ( 'add-row' === event ) {
                setTimeout(function() {
                    change_dropdowns($('select[name*="countryCode"]'));
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

            // if there are any selects with name*=stateCode
            if (countries.length) {
                countries.on('change', function () {
                    var $this = $(this);
                    var field_name_country_code = $this.attr('name');
                    var field_name_state_code = field_name_country_code.replace( 'countryCode', 'stateCode' );
                    var field_name_city = field_name_country_code.replace( 'countryCode', 'cityName' );

                    get_states($(this).val(), function (response) {
                        var obj = JSON.parse(response);
                        var len = obj.length;
                        var select_state = $('select[name="' + field_name_state_code + '"]');
                        var $stateValues = '';

                        select_state.empty();
                        $('select[name="' + field_name_city + '"]').empty();
                        for (i = 0; i < len; i++) {
                            var mystate = obj[i];
                            $stateValues += '<option value="' + mystate.country_code + '-' + mystate.state_code + '">' + mystate.state_name + '</option>';
                        }
                        select_state.append($stateValues);

                    });
                });
            }

            // if there are any selects with name*=stateCode
            if (state.length) {
                state.on('change', function () {
                    var $this = $(this);
                    var field_name = $this.attr('name');
                    var field_name_city = field_name.replace( 'stateCode', 'cityName' );

                    get_cities( $(this).val(), function (response) {
                        var $cityValues = '';
                        var obj = JSON.parse(response);
                        var len = obj.length;
                        var select_city = $("select[name='" + field_name_city + "']");

                        select_city.empty();
                        for (i = 0; i < len; i++) {
                            var mycity = obj[i];
                            $cityValues += '<option value="' + mycity.city_name + '">' + mycity.city_name + '</option>';
                        }
                        select_city.append($cityValues);

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
                    // try - catch to handle errors
                    try {
                        // await the response
                        const d = get_states(city_selector_vars[i].countryCode);
                        // add response to the response array
                        response_states.push(d)
                    } catch (err) {
                        // handle error
                        console.log(err)
                    }
                }
                console.log(response_states);

                var instance_count = 0;
                for (i = 0; i < city_selector_vars.length; i++ ) {
                    Promise.resolve(response_states[i]).then((jsonResults) => {
                        var obj          = JSON.parse(jsonResults);
                        var len          = obj.length;
                        var $stateValues = '';
                        var select_state = $('select[name*="row-' + instance_count + '"][name*="stateCode"]');
                        var stored_state = city_selector_vars[instance_count].stateCode;
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
                        instance_count++;
                    });
                }
            }
        }

        /**
         * Load select cities when editing a post
         */
        function admin_post_edit_load_cities() {

            if ( true === Array.isArray(city_selector_vars) ) {
                for (i = 0; i < city_selector_vars.length; i++) {
                    var instance_count = 0;
                    get_cities(city_selector_vars[i].stateCode, (response)=> {
                        var obj         = JSON.parse(response);
                        var len         = obj.length;
                        var $cityValues = '';
                        var select_city = $('select[name*="row-' + instance_count + '"][name*="cityName"]');
                        var stored_city = city_selector_vars[instance_count].cityName;

                        select_city.fadeIn();
                        for (i = 0; i < len; i++) {
                            $selected = '';
                            var mycity = obj[i];
                            if (mycity.city_name === stored_city) {
                                $selected = ' selected="selected"';
                            }
                            var selected = $selected;
                            $cityValues += '<option value="' + mycity.city_name + '"' + selected + '>' + mycity.city_name + '</option>';
                        }
                        select_city.append($cityValues);
                        instance_count++;
                    });
                }

            } else {

                get_cities(city_selector_vars.stateCode, function (response) {
                    var obj         = JSON.parse(response);
                    var len         = obj.length;
                    var $cityValues = '';
                    var select_city = $("select[name*='cityName']");
                    var stored_city = city_selector_vars.cityName;

                    select_city.fadeIn();
                    for (i = 0; i < len; i++) {
                        $selected = '';
                        var mycity = obj[i];
                        if (mycity.city_name === stored_city) {
                            $selected = ' selected="selected"';
                        }
                        var selected = $selected;
                        $cityValues += '<option value="' + mycity.city_name + '"' + selected + '>' + mycity.city_name + '</option>';
                    }
                    select_city.append($cityValues);

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

