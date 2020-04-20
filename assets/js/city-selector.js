// JS File for Country Field
(function($) {

    jQuery(document).ready(function() {

        // var countries = $('select[name*="countryCode"]');
        // console.log(countries);

        // console.log(countries.val());
        jQuery(".acf-input .button").click(function () {
            var event = $(this).data('event'); // add-row
            if ( 'add-row' === event ) {
                // @TODO: get unique_ID
                var unique_ID = '';
                // @TODO: change_dropdowns( unique_ID );
                change_dropdowns( unique_ID );
            }
        });

        /**
         * Change dropdowns
         */
        function change_dropdowns( $instance ) {
            var state = $('select[name*="stateCode"]');
            if (typeof $instance === "undefined") {
                $countries = $('select[name*="countryCode"]');
            } else {
                $countries = $('select[name*="' + $instance + '"][name*="countryCode"]');
            }
            console.log($countries);
            var countries = $countries;
            if (countries.length) {

                countries.on('change', function () {
                    // doesn't get hit on change in repeater

                    var $this = $(this);
                    var field_name = $this.attr('name');
                    var field_name_country_code = $this.attr('name');
                    var field_name_state_code = field_name_country_code.replace( 'countryCode', 'stateCode' );
                    var field_name_city = field_name_country_code.replace( 'countryCode', 'cityName' );

                    get_states($(this).val(), field_name, function (response) {
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

            // if there are any selects with name*=stateCode when document.ready
            if (state.length) {

                state.on('change', function () {
                    var $this = $(this);
                    var field_name = $this.attr('name');
                    var field_name_city = field_name.replace( 'stateCode', 'cityName' );

                    get_cities($(this).val(), function (response) {
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

            if ( true === Array.isArray(city_selector_vars) ) {
                // repeater
                // var select_states = $("select[name*='stateCode']");
                for (i = 0; i < city_selector_vars.length; i++ ) {
                    var instance_count = i;
                    get_states(city_selector_vars[i].countryCode, '', function (response) {
                        var obj          = JSON.parse(response);
                        var len          = obj.length;
                        var $stateValues = '';
                        var select_state = $('select[name*="row-' + instance_count + '"][name*="stateCode"]');
                        var stored_state = city_selector_vars[instance_count].stateCode;

                        select_state.fadeIn();
                        for (i = 0; i < len; i++) {
                            var state = obj[i];
                            var current_state = state.country_code + '-' + state.state_code;
                            if (current_state === stored_state) {
                                $selected = ' selected="selected"';
                            } else {
                                $selected = '';
                            }
                            var selected = $selected;
                            $stateValues += '<option value="' + state.country_code + '-' + state.state_code + '"' + selected + '>' + state.state_name + '</option>';
                        }
                        select_state.append($stateValues);
                    });
                }

            } else {
                // single
                get_states(city_selector_vars.countryCode, '', function (response) {

                    var stored_state = city_selector_vars.stateCode;
                    var obj          = JSON.parse(response);
                    var len          = obj.length;
                    var select_state = $("select[name*='stateCode']"); // @TODO: use field_name into select_state
                    var $stateValues = '';

                    select_state.fadeIn();
                    for (i = 0; i < len; i++) {
                        var state = obj[i];
                        var current_state = state.country_code + '-' + state.state_code;
                        if (current_state === stored_state) {
                            $selected = ' selected="selected"';
                        } else {
                            $selected = '';
                        }
                        var selected = $selected;
                        $stateValues += '<option value="' + state.country_code + '-' + state.state_code + '"' + selected + '>' + state.state_name + '</option>';
                    }
                    select_state.append($stateValues);

                });
            }
        }

        /**
         * Load select cities when editing a post
         */
        function admin_post_edit_load_cities() {

            if ( true === Array.isArray(city_selector_vars) ) {
                // var select_cities = $("select[name*='cityName']");
                for (i = 0; i < city_selector_vars.length; i++) {
                    var instance_count = i;

                    get_cities(city_selector_vars[i].stateCode, function (response) {

                        var obj         = JSON.parse(response);
                        var len         = obj.length;
                        var $cityValues = '';
                        var select_city = $('select[name*="row-' + instance_count + '"][name*="cityName"]');
                        var stored_city = city_selector_vars[instance_count].cityName;

                        select_city.fadeIn();
                        for (i = 0; i < len; i++) {
                            var mycity = obj[i];
                            if (mycity.city_name === stored_city) {
                                $selected = ' selected="selected"';
                            } else {
                                $selected = '';
                            }
                            var selected = $selected;
                            $cityValues += '<option value="' + mycity.city_name + '"' + selected + '>' + mycity.city_name + '</option>';
                        }
                        select_city.append($cityValues);

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
                        var mycity = obj[i];
                        if (mycity.city_name === stored_city) {
                            $selected = ' selected="selected"';
                        } else {
                            $selected = '';
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
         * @param field_name
         * @param callback
         */
        function get_states(countryCode, field_name, callback) {
            var data = {
                action: 'get_states_call',
                country_code: countryCode,
                field_name: field_name
            };

            $.post(ajaxurl, data, function (response) {
                callback(response);
            });
        }

        /**
         * Get cities
         *
         * @param stateCode
         * @param callback
         */
        function get_cities(stateCode, callback) {
            var data = {
                action: 'get_cities_call',
                row_code: stateCode
            };

            $.post(ajaxurl, data, function (response) {
                callback(response);
            });
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

