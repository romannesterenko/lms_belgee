$(function (){
    $(document).on('keyup', '.profile-edit-item__content input', function(){
        $('.profile-edit-item.message_info').empty().addClass('hidden').removeClass('error').removeClass('success')
    })
    $('.send_change_password').click(function (e){
        e.preventDefault();
        var old = $('input[name="old_pass"]').val();
        var new_pass = $('input[name="new_pass"]').val();
        var renew_pass = $('input[name="renew_pass"]').val();
        $.ajax({
            type: 'POST',
            url: '/local/components/lms/cabinet.settings.changepassword/ajax.php',
            data: {
                old: old,
                new_pass: new_pass,
                renew_pass: renew_pass,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function(response){
                $('.profile-edit-item.message_info').removeClass('hidden').addClass(response.div_class).text(response.message)
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    });
});