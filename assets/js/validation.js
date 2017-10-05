(function($) {

    $(document).on('acf/validate_field', function (e, field) {

        // vars
        $field = $(field);

        console.log('VALIDATION');

        // set validation to false on this field
        if ($field.find('select#countryCode').val() === '0') {
            $field.data('validation', false);
        }

    });
});
