// for v4
(function($) {
    $(document).on('acf/validate_field', function (e, field) {

        $field = $(field);
        $country_value = $field.find('select#acf-field-city_selectorcountryCode').val();
        $state_value = $field.find('select#acf-field-city_selectorstateCode').val();
        $city_value = $field.find('select#acf-field-city_selectorcityName').val();

        if ($country_value === '' || typeof($country_value) === "undefined" ) {
            $field.data('validation', false);
        }
        if ($state_value === '' || typeof($state_value) === "undefined" ) {
            $field.data('validation', false);
        }
        if ($city_value === '' || typeof($city_value) === "undefined" ) {
            $field.data('validation', false);
        }

    });
})(jQuery);
