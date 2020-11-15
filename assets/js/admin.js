jQuery(document).ready(function () {

    jQuery('.upload_button').click(function () {
        var type = jQuery(this).data('type');
        jQuery("#" + type).trigger('click');
    })

    jQuery("input[type='file']").change(function () {
        var type = jQuery(this).attr('id');
        jQuery('.form--' + type + ' .val').text(this.value.replace(/C:\\fakepath\\/i, ''))
    })

    $(document).ready(function() {
        $('select.acfcs__dropdown.select2').select2({
            allowClear: true,
            placeholder: '',
            width: '100%'
        });
    });
});
