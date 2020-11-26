// JS File for upload csv field
(function($) {
    $(document).ready(function () {

        $('.upload_button').click(function () {
            var type = $(this).data('type');
            $("#" + type).trigger('click');
        });

        $("input[type='file']").change(function () {
            var type = $(this).attr('id');
            $('.form--' + type + ' .val').text(this.value.replace(/C:\\fakepath\\/i, ''))
        });

    });
})(jQuery);
