<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
$courses = Models\Course::getList(['ACTIVE' => 'Y'], ['ID', 'NAME'])

?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Ручное добавление прохождений тренингов</h2>
                <div class="form-div">
                    <?php if($_REQUEST['added']=='Y'){?>
                        <div class="" style="color: green; padding: 15px 0px">Информация успешно добавлена</div>
                    <?php }?>
                    <div class="error-text" style="color: red; padding: 15px 0px"></div>
                    <form class="report_form" action="" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Курс</label>
                            <select class="js-example-basic-multiple" name="course_id" style="width: 100%;">
                                <?php foreach ($courses as $id => $course){?>
                                    <option value="<?=$course['ID']?>"><?=$course['NAME']?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group selectable hidden" >
                            <label for="">Сертификат для курса</label>
                            <select class="js-example-basic-multiple" name="certificate" style="width: 100%;">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Дата</label>
                            <input type="date" value="" name="course_date">
                        </div>
                        <div class="timetable-item--small shedule_event_block timetable-item--warning1 hidden">
                          <span class="timetable-item__content">
                                <span class="timetable-item__category-block">
                                  Предупреждение!
                                </span>
                                <span class="timetable-item__text shedule_event_block_text"></span>
                          </span>
                        </div>
                        <div class="form-group">
                            <label for="">Сотрудник</label>
                            <input type="text" value="" name="user" placeholder="Введите имя или фамилию или номер телефона и выберите пользователя из списка">
                        </div>
                        <input type="hidden" name="user_id" value="">
                        <div class="upcoming-courses aside-block aside-block--border users_search_block hidden">

                        </div>
                        <div class="form-group">
                            <label for="">Количество баллов входного теста</label>
                            <input type="number" step="0.1" name="pretest_points" placeholder="Количество баллов для прохождения">
                        </div>
                        <div class="form-group">
                            <label for="">Количество баллов выходного теста</label>
                            <input type="number" step="0.1" name="points" placeholder="Количество баллов для прохождения">
                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn send_compl">Добавить прохождение</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function (){
            $(document).on('change', '[name="course_date"]', function (){
                let course_id = $('[name="course_id"]').val();
                let date = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/getShedule.php',
                    data: {
                        'course': course_id,
                        'date': date
                    },
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        if(!response.is_free_course){
                            if(response.found)
                                $('.shedule_event_block').removeClass('timetable-item--warning1').addClass('timetable-item--important1');
                            else
                                $('.shedule_event_block').removeClass('timetable-item--important1').addClass('timetable-item--warning1');
                            $('.shedule_event_block').removeClass('hidden');
                            $('.shedule_event_block_text').empty().text(response.text);
                        }

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });
            $(document).on('change', '[name="course_id"]', function (){
                let course_id = $(this).val();
                let date = $('[name="course_date"]').val();
                $("[name='certificate']").parent('.form-group.selectable').addClass('hidden');
                $("[name='certificate']").empty();
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/getCertificates.php',
                    data: {
                        'course': course_id,
                    },
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function (response) {
                        if(response.is_payment_course) {
                            let html = '';
                            for (var k in response.list) {
                                html += '<option value="' + response.list[k].ID + '">' + response.list[k].CODE + '</option>';
                            }
                            $("[name='certificate']").parent('.form-group.selectable.hidden').removeClass('hidden');
                            $("[name='certificate']").empty().html(html);
                        }
                        /*if (!response.is_payment_course) {

                        } else {
                            $('.shedule_event_block').addClass('hidden');
                        }*/

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });


                if(date) {
                    $.ajax({
                        type: 'POST',
                        url: '/local/templates/geely/ajax/getShedule.php',
                        data: {
                            'course': course_id,
                            'date': date
                        },
                        dataType: 'json',
                        beforeSend: function () {
                        },
                        success: function (response) {
                            if (!response.is_free_course) {
                                if (response.found)
                                    $('.shedule_event_block').removeClass('timetable-item--warning1').addClass('timetable-item--important1');
                                else
                                    $('.shedule_event_block').removeClass('timetable-item--important1').addClass('timetable-item--warning1');
                                $('.shedule_event_block').removeClass('hidden');
                                $('.shedule_event_block_text').empty().text(response.text);
                            } else {
                                $('.shedule_event_block').addClass('hidden');
                            }

                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                        },
                    });
                }
            });
            $(document).on('click', '.select_user_from_popup', function (e){
                e.preventDefault();
                $("[name='user_id']").val($(this).data('user_id'));
                $("[name='user']").val($(this).data('user_name'));
                $('.users_search_block').addClass('hidden').empty();
            });
            $(document).on('keyup', '[name="user"]', function (){
                let str = $(this).val();
                $('.users_search_block').addClass('hidden').empty();
                $("[name='user_id']").val('');
                if(str.length>2){
                    $.ajax({
                        type: 'POST',
                        url: '/local/templates/geely/ajax/searchUser.php',
                        data: {
                            'search': str,
                        },
                        dataType: 'json',
                        beforeSend: function () {
                        },
                        success: function (response) {
                            if(response.found){
                                let html = '';
                                for(var k in response.list){
                                    let item = '<div class="upcoming-course">' +
                                        '<a href="" class="select_user_from_popup" data-user_name="'+response.list[k].NAME+' '+response.list[k].LAST_NAME+'" data-user_id="'+response.list[k].ID+'">' +
                                        '<span class="upcoming-course__content">' +
                                        '<span class="upcoming-course__top">' +
                                        '<span class="upcoming-course__date">'+response.list[k].NAME+' '+response.list[k].LAST_NAME+'</span>' +
                                        '</span>' +
                                        '<span class="upcoming-course__title">'+response.list[k].DEALER+'</span>' +
                                        '</span>' +
                                        '</a>' +
                                        '</div>';
                                    html+=item;
                                }
                                $('.users_search_block').empty().html(html).removeClass('hidden')
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                        },
                    });
                }
            });
            $(document).on('click', '.send_compl', function (e){
                e.preventDefault();
                let formData = new FormData($('.report_form')[0]);
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/create_completion_manual.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        if(response.success){
                            location.href = '/cabinet/admin/tools/manual_add/?added=Y'
                        } else {
                            $(".error-text").empty().text(response.message)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });

        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>