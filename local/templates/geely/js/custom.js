$(document).on('change','.poll_question_radio, .test_question_checkbox', function(e){
    $('.btn.next_question_btn').removeClass('disabled_link');
    $('.btn.next_pretest_question_btn').removeClass('disabled_link');
    $('.btn.next_retest_question_btn').removeClass('disabled_link');
});
$(document).on('change','.test_question_radio', function(e){
    $('.btn.next_test_question_btn').removeClass('disabled_link');
    $('.btn.next_retest_question_btn').removeClass('disabled_link');

});
$(document).on('change','.test_question_checkbox', function(e){
    var chck = false;
    $('.test_question_checkbox:checked').each(function (i, elem){
        chck = true;
    });
    if(chck)
        $('.btn.next_test_question_btn').removeClass('disabled_link');
    else
        $('.btn.next_test_question_btn').addClass('disabled_link');
});
$(document).on('change','.select_courses_category', function(e){
    let val = $(this).val()
    if(val == 'all'){
        location.href = "/courses/";
    } else {
        location.href = "/courses/?cat="+val;
    }
    console.log($(this).val());
});
$(document).on('click','.edit_all', function(e){
    e.preventDefault();
    $('.fact_value').css('display', 'none');
    $('.form_value').css('display', 'block');
    $('.save_all_records_block').removeClass('hidden');
});
$(document).on('click','.save_all_records', function(e){
    e.preventDefault();
    var data = {};
    $('.record_completion').each(function (i, item){
        data[$(item).data('id')] = {};
        data[$(item).data('id')]['was_on_course'] = $(item).find('[name="was_on_course"]').is(':checked')?true:false;
        data[$(item).data('id')]['is_complete'] = $(item).find('[name="is_complete"]').is(':checked')?true:false;
        data[$(item).data('id')]['points'] = $(item).find('[name="points"]').val();
        data[$(item).data('id')]['coment'] = $(item).find('[name="coment"]').val();
    });
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/add_mass_course_compl_data.php',
        data: {
            fields: data
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            document.location.reload()
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.save_additional_contacts', function(e){
    e.preventDefault();
    var form = $('#additional_contacts');
    $.ajax({
        type: 'GET',
        url: '/local/templates/geely/ajax/update_additional_contacts.php?'+$(form).serialize(),
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(responce){
            if(responce.success)
                document.location.href = '/cabinet/settings/';
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.process_step_course', function(e){
    e.preventDefault();
    var course_id = $(this).data('course');
    var user_id = $(this).data('user');
    var current_step = $(this).data('current-step');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_step_course.php',
        data: {
            course_id: course_id,
            user_id: user_id,
            current_step: current_step,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(responce){
            if(responce.success)
                document.location.reload()
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.prev_step_course', function(e){
    e.preventDefault();
    var course_id = $(this).data('course');
    var user_id = $(this).data('user');
    var current_step = $(this).data('current-step');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_step_course.php',
        data: {
            course_id: course_id,
            user_id: user_id,
            current_step: current_step,
            action: 'prev',
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(responce){
            if(responce.success)
                document.location.reload()
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});

$(document).on('submit', '.course_user_form', function (e) {
    e.preventDefault();
    var form = document.getElementsByClassName('course_user_form')[0];
    var data = new FormData(form);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/add_course_compl_data.php',
        processData: false,
        contentType: false,
        data: data,
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success){
                document.location.reload();
            }else{
                alert(response.message)
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
    console.log(data);
});
$(document).on('click', '.comment_modal', function (e) {
    e.preventDefault();
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_course_compl_data.php',
        data: {
            id:$(button).data('id')
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            $('#ex5 .modal-content').empty().html(response.html);
            $('a[href="#ex5"]').click();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});

$(document).on('click','.return_to_course', function(e){
    e.preventDefault();
    var course_id = $(this).data('course');
    var user_id = $(this).data('user');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_step_course.php',
        data: {
            course_id: course_id,
            user_id: user_id,
            action: 'back_from_test',
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(responce){
            if(responce.success)
                document.location.href = '/cabinet/courses/completions/'+course_id+'/';
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.btn.next_question_btn', function(e){
    e.preventDefault();
    var value = $('.poll_question_radio:checked').val();
    var poll_id = $(this).data('poll-id');
    var user_id = $(this).data('user-id');
    var cur_question = $(this).data('current-question');
    var all_questions = $(this).data('all-questions');
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_poll.php',
        data: {
            value: value,
            poll_id: poll_id,
            user_id: user_id,
            cur_question: cur_question,
            all_questions: all_questions,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success) {
                if(response.finished)
                    location.reload();
                else
                    location.href = $(button).attr('href');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.btn.next_test_question_btn', function(e){
    e.preventDefault();
    if($(this).data('many-answers')==1){
        var value = new Array();
        $('.test_question_checkbox:checked').each(function (i, elem){
            value.push($(elem).val())
        });
        value = value.join(';')
    }else{
        var value = $('.test_question_radio:checked').val();
    }
    var test_id = $(this).data('test-id');
    var user_id = $(this).data('user-id');
    var cur_question = $(this).data('current-question');
    var all_questions = $(this).data('all-questions');
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_test.php',
        data: {
            value: value,
            test_id: test_id,
            user_id: user_id,
            cur_question: cur_question,
            many_answers: cur_question,
            all_questions: all_questions,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success) {
                if(response.finished)
                    location.reload();
                else
                    location.href = response.href;
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.btn.next_pretest_question_btn', function(e){
    e.preventDefault();
    if($(this).data('many-answers')==1){
        var value = new Array();
        $('.test_question_checkbox:checked').each(function (i, elem){
            value.push($(elem).val())
        });
        value = value.join(';')
    }else{
        var value = $('.test_question_radio:checked').val();
    }
    var test_id = $(this).data('test-id');
    var user_id = $(this).data('user-id');
    var cur_question = $(this).data('current-question');
    var all_questions = $(this).data('all-questions');
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_pretest.php',
        data: {
            value: value,
            test_id: test_id,
            user_id: user_id,
            cur_question: cur_question,
            many_answers: cur_question,
            all_questions: all_questions,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success) {
                if(response.finished)
                    location.reload();
                else
                    location.href = response.href;
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.btn.next_retest_question_btn', function(e){
    e.preventDefault();
    if($(this).data('many-answers')==1){
        var value = new Array();
        $('.test_question_checkbox:checked').each(function (i, elem){
            value.push($(elem).val())
        });
        value = value.join(';')
    }else{
        var value = $('.test_question_radio:checked').val();
    }
    var test_id = $(this).data('test-id');
    var user_id = $(this).data('user-id');
    var cur_question = $(this).data('current-question');
    var all_questions = $(this).data('all-questions');
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/process_retest.php',
        data: {
            value: value,
            test_id: test_id,
            user_id: user_id,
            cur_question: cur_question,
            many_answers: cur_question,
            all_questions: all_questions,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success) {
                if(response.finished)
                    location.reload();
                else
                    location.href = response.href;
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('change','#checkbox2', function(e){
    let checked = true;
    let schedule = $(this).data('schedule');
    if($(this).is(':checked')){
        $("#main_test_text_alert").css('color', 'green').empty().text('Выходное тестирование разрешено')
        $(".allow_testing_block").empty().text('Запретить выходное тестирование')
    } else {
        checked = false;
        $("#main_test_text_alert").css('color', 'red').empty().text('Выходное тестирование запрещено')
        $(".allow_testing_block").empty().text('Разрешить выходное тестирование')
    }
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/allow_main_test_actions.php',
        data: {
            schedule: schedule,
            checked: checked,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(responce){
            console.log(responce)
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('change','[name="method"]', function(e){
    var value = $(this).val();
    var user_id = $(this).data('user-id');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/set_notifications_method.php',
        data: {
            value: value,
            user_id: user_id,
        },
        dataType: 'html',
        beforeSend: function () {
        },
        success: function(responce){
            console.log(responce)
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});

$(document).on('change','select.roles_list_select, select.roles_month_select', function(e){
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_shedules.php',
        data: {
            role: $('.roles_list_select').val(),
            date: $('.roles_month_select').val(),
            ajax: 'Y',
        },
        dataType: 'html',
        beforeSend: function () {
            $('.courses_ajax').empty();
        },
        success: function(responce){
            $('.courses_ajax').empty().html(responce);
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('keyup','input[name="promo"]', function(e){
    if($('.popup_error.error').length>0)
        $('.popup_error.error').remove();
});
$(document).on('click','.send_request_to_course', function(e){
    e.preventDefault();
    $('.popup_error.error').remove();
    var id = $(this).data('id');
    var user_id = $(this).data('user-id');
    var schedule = $('[name="schedule_id"]').val();
    var promo = $('input[name="promo"]').val();
    var need_coupon = $('input[name="need_coupon"]').val();
    var from_balance = $('input[name="from_balance"]').val();
    var need_answer = false;
    var title = '';
    var answer = '';
    if($('#need_answer').val()==='radio'){
        need_answer = true;
        title = $('.answer_title').text();
        answer = $('[name="reg_answer"]:checked').val();
    }
    if($('#need_answer').val()==='checkbox'){
        answer = ''
        $('[name="reg_answer[]"]:checked').each(function (i, elem){
            let str = ''
            if(i!==0)
                str = ', '
            answer+=(str+$(elem).val())
        });
        need_answer = true;
        title = $('.answer_title').text();
    }

    if($('#need_answer').val()==='text'){
        answer = $('[name="reg_answer"]').val();
        need_answer = true;
        title = $('.answer_title').text();
    }
    if(need_answer&&answer===''){
        $('.btn.send_request_to_course').parent('.btn-center').before('<div class="popup_error error">Ответ обязателен</div>')
    } else {
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/send_participate_request.php',
            data: {
                need_answer: need_answer,
                title: title,
                answer: answer,
                id: id,
                user: user_id,
                need_coupon: need_coupon,
                from_balance: from_balance,
                promo: promo,
                schedule: schedule,
                ajax_card: 'Y',
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function(response){
                if(response.success) {
                    $('.close-modal').click();
                    setModalText(response.title, response.body);
                    showCommonModal();
                    if ($('.course-main-info.course-main-info--third').length) {
                        loadSheduleData($('.course-main-info.course-main-info--third'));
                    }
                }else{
                    $('.btn.send_request_to_course').parent('.btn-center').before('<div class="popup_error error">'+response.message+'</div>')
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }

});
//назначение сотрудника
$(document).on('click','.set_employee_to_course', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var employee = $('select[name="employee_id"]').val();
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/set_employee.php',
        data: {
            id: id,
            employee: employee,
            ajax_card: 'Y',
        },
        dataType: 'html',
        beforeSend: function () {
        },
        success: function(response){
            $('.close-modal').click();
            setModalText('Курс успешно назначен!', 'Курс был успешно назначен пользователю как обязательный');
            showCommonModal();
            if ($('.course-main-info.course-main-info--third').length){
                loadData($('.course-main-info.course-main-info--third'));
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//перезапись сотрудника
$(document).on('click','.replace_employee', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var employee = $('select[name="employee_id"]').val();
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/replace_employee.php',
        data: {
            id: id,
            employee: employee,
            ajax_card: 'Y',
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success) {
                location.reload();
            } else {
                setModalText(response.popup_title, response.popup_body);
                showCommonModal();
            }



        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//запись сотрудника на курс
$(document).on('click','.enroll_employee_to_course', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var employee = $('select[name="employee_id"]').val();
    var schedule = $('select[name="schedule_id"]').val();
    var promo = $('input[name="promo"]').val();
    var need_coupon = $('input[name="need_coupon"]').val();
    var from_balance = $('input[name="from_balance"]').val();
    var need_answer = false;
    var title = '';
    var answer = '';
    if($('#need_answer').val()==='radio'){
        need_answer = true;
        title = $('.answer_title').text();
        answer = $('[name="reg_answer"]:checked').val();
    }
    if($('#need_answer').val()==='checkbox'){
        $('[name="reg_answer[]"]:checked').each(function (i, elem){
            let str = ''
            if(i!==0)
                str = ', '
            answer+=(str+$(elem).val())
        });
        need_answer = true;
        title = $('.answer_title').text();
    }
    if($('#need_answer').val()==='text'){
        answer = $('[name="reg_answer"]').val();
        need_answer = true;
        title = $('.answer_title').text();
    }
    if(need_answer&&answer===''){
        $('.btn.enroll_employee_to_course').parent('.btn-center').before('<div class="popup_error error">Ответ обязателен</div>')
    } else {
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/enroll_employee_to_course.php',
            data: {
                need_answer: need_answer,
                title: title,
                answer: answer,
                course_id: id,
                need_coupon: need_coupon,
                from_balance: from_balance,
                promo: promo,
                employee_id: employee,
                schedule_id: schedule,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $('.close-modal').click();
                    setModalText(response.title, response.body);
                    showCommonModal();
                    if ($('.course-main-info.course-main-info--third').length) {
                        loadData($('.course-main-info.course-main-info--third'));
                    }
                } else {
                    $('.btn.enroll_employee_to_course').parent('.btn-center').before('<div class="popup_error error">' + response.message + '</div>')
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }
});
//запись сотрудника на распиание
$(document).on('click','.enroll_employee_to_shedule', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var employee = $('select[name="employee_id"]').val();
    var schedule = $(this).data('shedule_id');
    var promo = $('input[name="promo"]').val();
    var need_coupon = $('input[name="need_coupon"]').val();
    var from_balance = $('input[name="from_balance"]').val();
    var need_answer = false;
    var title = '';
    var answer = '';
    if($('#need_answer').val()==='radio'){
        need_answer = true;
        title = $('.answer_title').text();
        answer = $('[name="reg_answer"]:checked').val();
    }
    if($('#need_answer').val()==='checkbox'){
        $('[name="reg_answer[]"]:checked').each(function (i, elem){
            let str = ''
            if(i!==0)
                str = ', '
            answer+=(str+$(elem).val())
        });
        need_answer = true;
        title = $('.answer_title').text();
    }
    if($('#need_answer').val()==='text'){
        answer = $('[name="reg_answer"]').val();
        need_answer = true;
        title = $('.answer_title').text();
    }
    if(need_answer&&answer===''){
        $('.btn.enroll_employee_to_course').parent('.btn-center').before('<div class="popup_error error">Ответ обязателен</div>')
    } else {
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/enroll_employee_to_course.php',
            data: {
                need_answer: need_answer,
                title: title,
                answer: answer,
                course_id: id,
                need_coupon: need_coupon,
                from_balance: from_balance,
                promo: promo,
                employee_id: employee,
                schedule_id: schedule,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $('.close-modal').click();
                    setModalText(response.title, response.body);
                    showCommonModal();
                    if ($('.course-main-info.course-main-info--third').length) {
                        loadSheduleData($('.course-main-info.course-main-info--third'));
                    }
                } else {
                    $('.btn.enroll_employee_to_shedule').parent('.btn-center').before('<div class="popup_error error">' + response.message + '</div>')
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }
});

function loadData(course) {
    var id = $(course).data('id');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/load_data.php',
        data: {
            id: id,
            load_ajax: 'enrolled_data'
        },
        dataType: 'html',
        beforeSend: function () {
        },
        success: function(response){
            $(course).empty().html(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
}
function loadSheduleData(schedule){
    var id = $(schedule).data('id');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/load_shedule_data.php',
        data: {
            id: id,
            load_ajax: 'enrolled_data'
        },
        dataType: 'html',
        beforeSend: function () {
        },
        success: function(response){
            $(schedule).empty().html(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
}
//регистрация на курс без заявки
$(document).on('click','.send_request_to_free_course', function(e){
    e.preventDefault();
    $('.popup_error.error').remove();
    var id = $(this).data('id');
    var user_id = $(this).data('user-id');
    var promo = $('input[name="promo"]').val();
    var need_coupon = $('input[name="need_coupon"]').val();
    var from_balance = $('input[name="from_balance"]').val();
    var need_answer = false;
    var title = '';
    var answer = '';
    if($('#need_answer').val()==='radio'){
        need_answer = true;
        title = $('.answer_title').text();
        answer = $('[name="reg_answer"]:checked').val();
    }
    if($('#need_answer').val()==='checkbox'){
        answer = ''
        $('[name="reg_answer[]"]:checked').each(function (i, elem){
            let str = ''
            if(i!==0)
                str = ', '
            answer+=(str+$(elem).val())
        });
        need_answer = true;
        title = $('.answer_title').text();
    }

    if($('#need_answer').val()==='text'){
        answer = $('[name="reg_answer"]').val();
        need_answer = true;
        title = $('.answer_title').text();
    }
    if(need_answer&&answer===''){
        $('.btn.send_request_to_free_course').parent('.btn-center').before('<div class="popup_error error">Ответ обязателен</div>')
    } else {
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/send_participate_request.php',
            data: {
                need_answer: need_answer,
                title: title,
                answer: answer,
                id: id,
                user: user_id,
                need_coupon: need_coupon,
                from_balance: from_balance,
                promo: promo,
                schedule: 0,
                ajax_card: 'Y',
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $('.close-modal').click();
                    setModalText(response.title, response.body);
                    showCommonModal();
                    if ($('.course-main-info.course-main-info--third').length) {
                        loadData($('.course-main-info.course-main-info--third'));
                    }
                } else {
                    $('.btn.send_request_to_free_course').parent('.btn-center').before('<div class="popup_error error">' + response.message + '</div>')
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }

});
$(document).on('mouseenter','.load_info', function(e){
    console.log('qwerty')
})
$(document).on('click','.go_to_report a', function(e){
    //e.preventDefault();
    $(this).parent().css('padding-left', '0px');
    $(this).css('margin-left', '20px');
    $(this).parent().find('.link_loading_spinner').addClass('showing');
})
$(document).on('click','.send_to_free_schedule', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var user_id = $(this).data('user-id');
    var select_date = $('.datepicker--cell.datepicker--cell-day.-selected-');
    var dat = $(select_date).data('date')+'.'+(parseInt($(select_date).data('month'))+1)+'.'+$(select_date).data('year');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/send_participate_request_by_date.php',
        data: {
            id: id,
            user: user_id,
            date: dat,
            ajax_card: 'Y',
        },
        dataType: 'html',
        beforeSend: function () {
        },
        success: function(responce){
            $('.close-modal').click();
            $('.list_item_card[data-id="'+id+'"]').empty().html(responce);
            $('.detail_enroll_butt').remove();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click','.unenroll_by_completion', function(e){
    e.preventDefault();
    var yes = confirm("Вы уверены?")
    if (yes) {
        var id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/delete_completion.php',
            data: {
                id: id,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {
                if (response.success)
                    $('.courses_enrolled_item[data-id="' + id + '"]').remove();
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }
});
$(document).on('click','.unenroll_course', function(e){
    e.preventDefault();
    var yes = confirm("Вы уверены?")
    if (yes) {
        var id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/unenroll.php',
            data: {
                id: id,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {
                if (response.success) {
                    $('.courses_enrolled_item[data-id="' + id + '"]').remove();
                    setModalText(response.title, response.body);
                    showCommonModal();
                }else{
                    setModalText(response.title, response.body);
                    showCommonModal();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }
});
$(document).on('click','.unenroll_completion', function(e){
    e.preventDefault();
    var yes = confirm("Вы уверены?")
    if (yes) {
        var id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: '/local/templates/geely/ajax/unenroll_completion.php',
            data: {
                id: id,
            },
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (response) {
                if (response.success) {
                    $('.courses_enrolled_item[data-id="' + id + '"]').remove();
                    setModalText(response.title, response.body);
                    showCommonModal();
                }else{
                    setModalText(response.title, response.body);
                    showCommonModal();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
        });
    }
});

function showCommonModal() {
    $('a[href="#ex4"]').click();
}
function setModalText(title='', body=''){
    $('#ex4 .modal-content .h3.center').empty().text(title);
    $('#ex4 .modal-content p').empty().text(body);
}
//обработка заявок админом
$(document).on('click','.course_application_actions', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var action = $(this).data('action');
    var block = $(this).parents('.application-item');
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/course_application_actions.php',
        data: {
            id: id,
            action: action,
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            if(response.success)
                $(block).remove();
            setModalText(response.popup_title, response.popup_body);
            showCommonModal();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
$(document).on('click', '.replace_employee_popup', function (e) {
    e.preventDefault();
    var button = $(this);
    $.ajax({
        method: 'POST',
        url: "/local/templates/geely/ajax/get_replace_data.php",
        data: {
            id: $(button).data('id'),
        },
        dataType: 'json',
        success: function (response){
            $('#ex3 .modal-content').empty().html(response.html);
            if(response.type==='select'){
                $('.select2').select2({
                    placeholder: 'Select Me',
                    theme: 'which'
                });
                /*var enabledDates = Object.values(response.enableDates);
                $('.datepicker_here').datepicker({
                    language: 'en',
                    inline: true,
                    startDate: new Date(),
                    onRenderCell: function (date, cellType) {
                        if (enabledDates.length>0) {
                            if (cellType === 'day') {
                                let cur_date = date.getDate() + '.' + (date.getUTCMonth() + 1) + '.' + date.getFullYear();
                                let isDisabled = enabledDates.indexOf(cur_date) === -1;
                                return {
                                    disabled: isDisabled
                                }
                            }
                        }
                    }
                })*/
            }
            $('a[href="#ex3"]').click();
        }
    });
});
//подгрузка элементов
$(document).on('click','.btn.load_elements', function(e){
    e.preventDefault();
    var button = $(this);
    var cur = $('.item.course_item').last().data('page');
    var next = cur+1;
    var max = $(button).data('max-page');
    var default_url = $(button).data('default-url');
    $.ajax({
        type: 'POST',
        url: default_url+'PAGEN_1='+next,
        data: {
            ajax_load_more:'Y'
        },
        dataType: 'html',
        beforeSend: function () {
        },
        success: function(response){
            if(next===max)
                $(button).remove();
            else {
                $(button).attr('data-cur-page', next);
                next++;
                $(button).attr('data-url', $(button).data('default-url') + 'PAGEN_1=' + next);
            }
            $('.courses_list .materials-block__content').append(response);
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//запись на курс
$(document).on('click','.detail_enroll_butt', function(e){
    e.preventDefault();
    var button = $(this);
    let need_schedule = 0;
    if($('#need_shedule_in_select').length>0 && parseInt($('#need_shedule_in_select').val())>0){
        need_schedule = $('#need_shedule_in_select').val();
    }
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_enroll_data.php',
        data: {
            course_id:$(button).data('course-id'),
            need_schedule: need_schedule
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            $('#ex3 .modal-content').empty().html(response.html);
            if(response.type==='select'){
                /*$('.select2').select2({
                    placeholder: 'Select Me',
                    theme: 'which'
                });*/
                /*var enabledDates = Object.values(response.enableDates);
                $('.datepicker_here').datepicker({
                    language: 'en',
                    inline: true,
                    startDate: new Date(),
                    onRenderCell: function (date, cellType) {
                        if (enabledDates.length>0) {
                            if (cellType === 'day') {
                                let cur_date = date.getDate() + '.' + (date.getUTCMonth() + 1) + '.' + date.getFullYear();
                                let isDisabled = enabledDates.indexOf(cur_date) === -1;
                                return {
                                    disabled: isDisabled
                                }
                            }
                        }
                    }
                })*/
            }
            $('.select2').select2({
                placeholder: 'Select Me',
                theme: 'which'
            });
            $('a[href="#ex3"]').click();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});

//запись на курс через расписание
$(document).on('click','.detail_enroll_shedule_butt', function(e){
    e.preventDefault();
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_shedule_enroll_data.php',
        data: {
            shedule_id:$(button).data('course-id'),
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            $('#ex3 .modal-content').empty().html(response.html);
            if(response.type==='select'){
                /*$('.select2').select2({
                    placeholder: 'Select Me',
                    theme: 'which'
                });*/
                /*var enabledDates = Object.values(response.enableDates);
                $('.datepicker_here').datepicker({
                    language: 'en',
                    inline: true,
                    startDate: new Date(),
                    onRenderCell: function (date, cellType) {
                        if (enabledDates.length>0) {
                            if (cellType === 'day') {
                                let cur_date = date.getDate() + '.' + (date.getUTCMonth() + 1) + '.' + date.getFullYear();
                                let isDisabled = enabledDates.indexOf(cur_date) === -1;
                                return {
                                    disabled: isDisabled
                                }
                            }
                        }
                    }
                })*/
            }
            $('.select2').select2({
                placeholder: 'Select Me',
                theme: 'which'
            });
            $('a[href="#ex3"]').click();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//назначение сотруднику курса обязательным
$(document).on('click','.set_course_to_employee_butt', function(e){
    e.preventDefault();
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_enroll_data.php',
        data: {
            course_id:$(button).data('course-id'),
            action:'set_course_for_employee'
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            $('#ex3 .modal-content').empty().html(response.html);
            if(response.type==='select'){
                $('.select2').select2({
                    placeholder: 'Выберите из списка',
                    theme: 'which'
                });
            }
            $('a[href="#ex3"]').click();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//запись сотрудника на курс
$(document).on('click','.employee_enroll_butt', function(e){
    e.preventDefault();
    var button = $(this);
    let need_schedule = 0;
    if($('#need_shedule_in_select').length>0 && parseInt($('#need_shedule_in_select').val())>0){
        need_schedule = $('#need_shedule_in_select').val();
    }
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_enroll_employee_block.php',
        data: {
            course_id:$(button).data('course-id'),
            need_schedule: need_schedule
            //action:'set_course_for_employee'
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            $('#ex3 .modal-content').empty().html(response.html);
            $('.select2').select2({
                placeholder: 'Выберите из списка',
                theme: 'which'
            });
            $('a[href="#ex3"]').click();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//запись сотрудника на расписание
$(document).on('click','.employee_shedule_enroll_butt', function(e){
    e.preventDefault();
    var button = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/get_enroll_shedule_employee_block.php',
        data: {
            shedule_id:$(button).data('course-id'),
        },
        dataType: 'json',
        beforeSend: function () {
        },
        success: function(response){
            $('#ex3 .modal-content').empty().html(response.html);
            $('.select2').select2({
                placeholder: 'Выберите из списка',
                theme: 'which'
            });
            $('a[href="#ex3"]').click();
        },
        error: function (xhr, ajaxOptions, thrownError) {
        },
    });
});
//вызов пояснялки
$(document).on('click','.alert_popup', function(e){
    e.preventDefault();
    setModalText('Подсказка', $(this).find('span').text());
    showCommonModal();
});
//удаление уведомления с рабочего стола
$(document).on('click','.delete_notification', function(e){
    e.preventDefault();
    var link = $(this);
    $.ajax({
        type: 'POST',
        url: '/local/templates/geely/ajax/notifications_actions.php',
        data: {
            id: $(this).data('id'),
            action: 'make_read'
        },
        dataType: 'json',
        beforeSend: function () {

        },
        success: function(response){
            if(response.success)
                $(link).parent('.course-info-item').remove();
        },
        error: function (xhr, ajaxOptions, thrownError) {

        },
    });
});
$(document).on('change', '[name="payment_method"]', function(e){
    e.preventDefault();
    var value = $(this).val()
    if(value == 159){
        $('.rendered_by_payment_method').remove()
        let html = '' +
            '<input type="hidden" name="need_coupon" class="rendered_by_payment_method" value="Y">' +
            '<div class="form-group rendered_by_payment_method">' +
            '<label for="">Курс платный, необходимо ввести промокод:</label>\n' +
            '<input type="text" name="promo" value="" placeholder="Введите промокод">\n' +
            '</div>'
        $('.payment_method_select').after(html)
        console.log("Показываем поле для ввода купона")
    } else {
        $('.rendered_by_payment_method').remove()
        let html = '<input type="hidden" name="from_balance" class="rendered_by_payment_method" value="Y">'
        $('.payment_method_select').after(html)
        console.log("Скрываем поле для ввода купона")
    }
});
function showCalendar(id){
    $('#course_id_hidden').val(id);
    $('a[href="#ex2"]').click();
}
function showEnrollForm(id, date, time){
    $('#course_id_hidden').val(id);
    $('#beg_date').empty().text(date);
    $('#beg_time').empty().text(time);
    $('a[href="#ex1"]').click();
}
$(function (){
    var element = document.getElementsByClassName('phone_mask')[0];
    var maskOptions = {
        mask: '+{7}(000)000-0000',
        //lazy: false
    };
    var mask = IMask(element, maskOptions);
})