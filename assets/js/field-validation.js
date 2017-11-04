(function($) {

    $(document).on('acf/validate_field', function (e, field) {

        // vars
        $field = $(field);

        if ($field.find('select#countryCode').val() === '0') {
            $field.data('validation', false);
        }
        if ($field.find('select#stateCode').val() === '-') {
            $field.data('validation', false);
        }
        if ($field.find('select#cityName').val() === 'Select a city') {
            $field.data('validation', false);
        }

    });
})(jQuery);
