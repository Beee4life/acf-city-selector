// JS File for Country Field
(function($) {

//    console.log('-= ACF City Selector reached ');

//    var country = $("select[name*='countryCode']");
//    var state = $("select[name*='stateCode']");

var citySelectWidget = null;
var users = null;
var prefix = window.location.pathname;



$.createCache = function(requestFunction) {
    var cache = {};
    return function(key, callback) {
        if ( !cache[key] ) {
            cache[ key ] = $.Deferred(function(defer) {
                requestFunction(defer, key);
            }).promise();
        }
        console.log("called: ", key);
        return cache[key].done(callback);
    };
};

var getData = $.createCache(function(defer, query) {
    // the version for my RESTful services I built around your data.
    /*
    $.ajax({
        url: prefix + 'd/' + query,
        dataType: 'json',
        success: defer.resolve,
        error: defer.reject
    });
    */

    $.ajax({
        url: ajaxurl, // assuming this is a global var from desclared else where.
        type: 'POST',
        data: query,
        dataType: 'json',
        success: defer.resolve,
        error: defer.reject
    });
});


var CitySelector = function(countrySelectId, stateSelectId, citySelectId) {

    var nullOption = '0';
    var countrySelect = $(countrySelectId);
    var stateSelect = $(stateSelectId);
    var citySelect = $(citySelectId);

    var makeOption = function(value, text) {
        return '<option value="' + value + '">' + text + '</option>';
    };

   var countrySelectChangeHandler = function() {
        var target = $(this);
        if (target.val() != nullOption) {
//            getData('country/' + target.val() + '/states', function(data) {
            getData({action: 'get_states_call', country_code: country},
                function(data) {
                    var html;
                    stateSelect.prop('disabled', 'disabled');
                    stateSelect.empty();
                    citySelect.prop('disabled', 'disabled');
                    citySelect.empty();
                    html =  makeOption(nullOption, 'Select State');
                    $.each(data.data, function(index, value) {
                        html += makeOption(value.code, value.name);
                    });
                    stateSelect.html(html);
                    stateSelect.prop('disabled', false);
                });
        } else {
            stateSelect.prop('disabled', 'disabled');
            stateSelect.empty();
            citySelect.prop('disabled', 'disabled');
            citySelect.empty();
        }
    };


    var stateSelectChangeHandler = function() {
        var target = $(this);
        if (target.val() != nullOption) {
//            getData('cities/' + countrySelect.val() + '/' + target.val(), function(data) {
            getData(
                {action: 'get_states_call', row_code: target.val()},
                function(data) {
                    var html;
                    citySelect.prop('disabled', 'disabled');
                    citySelect.empty();
                    $.each(data.data, function(index, value) {
                        html += makeOption(value, value);
                    });
                    citySelect.html(html);
                    citySelect.prop('disabled', false);
                });
        } else {
            citySelect.prop('disabled', 'disabled');
            citySelect.empty();
        }
    };


    this.select = function(country, state, city) {

        countrySelect.prop('disabled', 'disabled');
        stateSelect.prop('disabled', 'disabled');
        citySelect.prop('disabled', 'disabled');
        countrySelect.val(country);

//        getData('country/' + country + '/states', function(data) {
        getData({action: 'get_states_call', country_code: country},
            function(data) {
                var html;
                stateSelect.empty();
                html =  makeOption(nullOption, 'Select State');
                $.each(data.data, function(index, value) {
                    html += makeOption(value.code, value.name);
                });
                stateSelect.html(html);
                stateSelect.val(user.state);
            })
        .then(function() {
//        getData('cities/' + country + '/' + state, function(data) {
             getData({action: 'get_cities_call', row_code: state},
                function(data) {
                    var html;
                    citySelect.prop('disabled', 'disabled');
                    citySelect.empty();
                    $.each(data.data, function(index, value) {
                        html += makeOption(value, value);
                    });
                    citySelect.html(html);
                    citySelect.val(city);
                    countrySelect.prop('disabled', false);
                    stateSelect.prop('disabled', false);
                    citySelect.prop('disabled', false);
                });
        });
    };


    this.updateUser= function(user) {
        user.country = countrySelect.val();
        user.state = stateSelect.val();
        user.city = citySelect.val();
    };


    // Initliase the select elements
    countrySelect.prop('disabled', 'disabled');
    stateSelect.prop('disabled', 'disabled');
    citySelect.prop('disabled', 'disabled');

    getData('countries', function(data) {
        var html;
        countrySelect.prop('disabled', true);
        countrySelect.empty();
        html =  makeOption(nullOption, 'Select Country');
        $.each(data.data, function(index, value) {
            html += makeOption(value.code, value.name);
        });
        countrySelect.html(html);
        countrySelect.prop('disabled', false);
        countrySelect.change( countrySelectChangeHandler );
        stateSelect.change( stateSelectChangeHandler );
        citySelect.change( function() { console.log("Selected " + citySelect.val() + "."); } );
    });

};

//citySelectWidget = new CitySelector('#acfCountrySelect', '#acfStateSelect', '#acfCitySelect');

citySelectWidget = new CitySelector(
        "select[name*='countryCode']",
        "select[name*='stateCode']",
        "select[name*='cityNameAscii']");
})(jQuery);

/*

    if (country.length) {

        // get_field( 'sd_city_selector' )
        // if has value, split array in 3 values
        // $country, $state, $city
        // else country.change

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

            /* JSON populate Region/State Listbox * /
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
    /* JSON populate Cities Listbox * /

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

}
*/
