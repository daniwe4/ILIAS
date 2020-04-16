$(document).ready(function() {
    $('#set_trainingtimes_manually').on('change', function ($event) {
            if($event.currentTarget.value == "0") {
                $("select[name^='time']").attr("disabled", "disabled");
            } else {
                $("select[name^='time']").removeAttr("disabled");
            }
        }
    );

    $('#set_trainingtimes_manually').trigger('change');
});