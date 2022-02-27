// for v4
(function ($) {
  $(document).on('acf/validate_field', function (e, field) {

    $field = $(field);
    $country_field = $field.find('select#acf-field-city_selectorcountryCode');
    $country_value = $country_field.val();

    $state_field = $field.find('select#acf-field-city_selectorstateCode');
    $state_value = $state_field.val();

    $city_field = $field.find('select#acf-field-city_selectorcityName');
    $city_value = $city_field.val();

    if ($country_value === '' || typeof ($country_value) === "undefined") {
      $field.data('validation', false);
      $field.data('validation_message', acf.l10n.validation.select_country);
    }
    if ($state_value === '' || typeof ($state_value) === "undefined") {
      $field.data('validation', false);
      $field.data('validation_message', acf.l10n.validation.select_state);
    }
    if ($city_value === '' || typeof ($city_value) === "undefined") {
      $field.data('validation', false);
      $field.data('validation_message', acf.l10n.validation.select_city);
    }

  });
})(jQuery);
