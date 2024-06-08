$(function (){
    $('.save_send_me_nots').click(function (e){
        e.preventDefault();
        var send = $('#setting-show-me-notifies').is(':checked')?1:0;
        $.ajax({
            type: 'POST',
            url: '/local/components/lms/cabinet.settings.showmenotifications/ajax.php',
            data: {
                send: send,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function(response){
                $('.show_me_nots_message').removeClass('hidden').empty().text(response.message)
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });

    });
});