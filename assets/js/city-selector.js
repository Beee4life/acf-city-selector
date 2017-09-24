// JS File for Country Field
(function($) {

    console.log('-= ACF City Selector reached ');

    var country = $("select[name*='countryCode']");
    var state   = $("select[name*='stateCode']");

    if (country.length) {

        country.change(function() {

            console.log('-= Country selected');

            var $this = $(this);

            get_states($(this).val(), function(response) {

                var obj          = JSON.parse(response);
                var len          = obj.length;
                var $stateValues = '';

                $("select[name*='stateCode']").empty();
                $("select[name*='cityNameAscii']").empty();
                for (i = 0; i < len; i++) {
                    var mystate = obj[i];

                    $stateValues += '<option value="'+mystate.country_code+'-'+mystate.state_code+'">'+mystate.states+'</option>';

                }
                $("select[name*='stateCode']").append($stateValues);

            });

            /* JSON populate Region/State Listbox */
        });
    }

    if (state.length) {

        state.change(function() {

            console.log('-= State selected');

            var $this = $(this);

            get_cities($(this).val(), function(response) {

                var obj         = JSON.parse(response);
                var len         = obj.length;
                var $cityValues = '';

                $("select[name*='cityNameAscii']").empty();
                for (i = 0; i < len; i++) {
                    var mycity = obj[i];
                    $cityValues += '<option value="'+mycity.city_name+'">'+mycity.city_name+'</option>';
                }
                $("select[name*='cityNameAscii']").append($cityValues);

            });

        });
    /* JSON populate Cities Listbox */
    }

    function get_states(countryCODE, callback) {

        var data = {
            action: 'get_states_call',
            country_code: countryCODE
        };

        $.post( ajaxurl, data, function(response) {
            callback(response);
        });
    }

    function get_cities(rowCODE, callback) {

        var data = {
            action: 'get_cities_call',
            row_code: rowCODE
        };

        $.post( ajaxurl, data, function(response) {
            callback(response);
        });
    }

    // Load select states when editing a post
    function admin_post_edit_load_states() {
        get_states(city_selector_vars.countryCode, function(response) {

            var obj          = JSON.parse(response);
            var len          = obj.length;
            var $stateValues = '';

            $("select[name*='stateCode']").fadeIn();
            for (i = 0; i < len; i++) {
                var mystate = obj[i];

                $stateValues += '<option value="'+mystate.country_code+'-'+mystate.state_code+'">'+mystate.states+'</option>';

            }
            $("select[name*='stateCode']").append($stateValues);

        });
    }

    // Load select cities when editing a post
    function admin_post_edit_load_cities() {
        // $("select[name*='cityNameAscii']").hide();
        get_cities(city_selector_vars.stateCode, function(response) {

            var obj          = JSON.parse(response);
            var len          = obj.length;
            var $cityValues = '';

            $("select[name*='cityNameAscii']").fadeIn();
            for (i = 0; i < len; i++) {
                var mycity = obj[i];
                $cityValues += '<option value="'+mycity.city_name+'">'+mycity.city_name+'</option>';
            }
            $("select[name*='cityNameAscii']").append($cityValues);

        });
    }

    if(typeof city_selector_vars != "undefined") {
        admin_post_edit_load_states();
        admin_post_edit_load_cities();
    }

})(jQuery);

