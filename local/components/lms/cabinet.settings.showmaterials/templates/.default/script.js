$(function (){
    $('.settings').on('change', 'input[name="setting"]', function (e){
        e.preventDefault();
        var values = [];
        $('input[name="setting"]').each(function (i, elem){
            if($(elem).is(':checked'))
                values.push($(elem).val());
        });
        $.ajax({
            type: 'POST',
            url: '/local/components/lms/cabinet.settings.showmaterials/ajax.php',
            data: {
                value: values,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function(responce){
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });

    });
});