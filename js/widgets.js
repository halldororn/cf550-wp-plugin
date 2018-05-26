jQuery(document).ready(function($) {
    // Skrá skor
    $("#cf-add-attendance").click(function() {
        var _this = this;
        console.log("ERMAGERD");
        return;
        var date = $("#cf-input-program-date");
        var title = $("#cf-input-program-title");
        var description = $("#cf-input-program-description");
        if (! /^(\d{4})-(\d{2})-(\d{2})$/.test(date.val())) {
            $('#programs-error').text('Dagsetning þarf að vera á forminu: ÁÁÁÁ-MM-DD (t.d. 2016-02-05 fyrir fimmta febrúar 2016).');
            return;
        }
        $('#programs-error').text("");
        $.post(my_ajax_obj.ajax_url, {
            _ajax_nonce: my_ajax_obj.nonce,
            action: "cf_add_program",
            date: date.val(),
            title: title.val(),
            description: description.val(),
        }, function(data) {
            if (data.success) {
                location.reload();
            } else {
                $('#programs-error').text('Ekki tókst að bæta við æfingu');
            }
        }
        );
    });
});
