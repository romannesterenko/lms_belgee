<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
$courses = Models\Course::getList(['ACTIVE' => 'Y', '!PROPERTY_SCORM' => false], ['ID', 'NAME', 'PROPERTY_SCORM']);
$first_course = current($courses);?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Проверка последних данных по прохождению Scorm курсов</h2>
                <div class="form-div">
                    <form class="report_form" action="" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Курс</label>
                            <select class="js-example-basic-multiple" name="course_id" style="width: 100%;">
                                <?php foreach ($courses as $id => $course){?>
                                    <option value="<?=$course['ID']?>"><?=$course['NAME']?></option>
                                <?php }?>

                            </select>
                        </div>
                        <div class="form-group selectable">
                            <label for="">Часть курса</label>
                            <select class="select2" name="part" style="width: 100%;">
                                <option value="last">Последняя пройденная пользователем</option>
                                <?php foreach ($first_course['PROPERTY_SCORM_VALUE'] as $key => $link){?>
                                    <option value="<?=($key+1)?>"><?=($key+1)?> часть </option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Сотрудник</label>
                            <input type="text" value="" name="user" placeholder="Введите ID, имя или фамилию или номер телефона и выберите пользователя из списка">
                        </div>
                        <input type="hidden" name="user_id" value="">
                        <div class="upcoming-courses aside-block aside-block--border users_search_block hidden">

                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem; justify-content: center">
                            <div class="btn-center">
                                <button class="btn load_scorm">Посмотреть результаты</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="loader hidden" style="text-align: center;">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" style="width: 100px" alt="">
                </div>
                <div class="results_block hidden">
                    <h4 style="padding-bottom: 10px;">Результаты поиска</h4>
                    <div class="content_info"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function (){
            $(document).on('click', '.load_scorm', function (e){
                e.preventDefault();
                let formData = new FormData($('.report_form')[0]);
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/get_scorm.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function () {
                        $('.loader').removeClass('hidden');
                        $('.results_block').addClass('hidden');
                    },
                    success: function(response){
                        $('.loader').addClass('hidden');
                        if(response.data.session_time===undefined){

                            $('.results_block').removeClass('hidden');
                            $('.results_block .content_info').empty().html('Информация не найдена')
                        } else {
                            let html = '<table class="table table-bordered table-striped table--white">';
                            html+= '<thead class="thead-dark"><tr><th>Наименование поля</th><th>Значение поля</th></tr></thead><tbody>';
                            for (let field in response.data){
                                html+= '<tr><td>'+field+'</td><td>'+response.data[field]+'</td></tr>';
                            }
                            html+= '</tbody></table>';
                            $('.results_block').removeClass('hidden');
                            $('.results_block .content_info').empty().html(html)
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });
            $(document).on('click', '.select_user_from_popup', function (e){
                e.preventDefault();
                $("[name='user_id']").val($(this).data('user_id'));
                $("[name='user']").val($(this).data('user_name'));
                $('.users_search_block').addClass('hidden').empty();
            });
            $(document).on('change', '[name="course_id"]', function (){
                var course_id = $(this).val()
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/get_count_scorm_files_by_course.php',
                    data: {
                        course_id: course_id
                    },
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        var html = '<option value="last">Последняя пройденная пользователем</option>';
                        if(response.count>0){
                            for (let i=1; i<=response.count; i++)
                                html+= '<option value="'+i+'">'+i+' часть</option>';
                        }
                        $('[name="part"]').empty().html(html)
                        console.log(response);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            })
            $(document).on('keyup', '[name="user"]', function (){
                let str = $(this).val();
                $('.users_search_block').addClass('hidden').empty();
                $("[name='user_id']").val('');
                if(str.length>1) {
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
                            if (response.found) {
                                let html = '';
                                for (var k in response.list) {
                                    let item = '<div class="upcoming-course">' +
                                        '<a href="" class="select_user_from_popup" data-user_name="' + response.list[k].NAME + ' ' + response.list[k].LAST_NAME + '" data-user_id="' + response.list[k].ID + '">' +
                                        '<span class="upcoming-course__content">' +
                                        '<span class="upcoming-course__top">' +
                                        '<span class="upcoming-course__date">' + response.list[k].NAME + ' ' + response.list[k].LAST_NAME + ' (ID:' + response.list[k].ID + ')</span>' +
                                        '</span>' +
                                        '<span class="upcoming-course__title">' + response.list[k].DEALER + '</span>' +
                                        '</span>' +
                                        '</a>' +
                                        '</div>';
                                    html += item;
                                }
                                $('.users_search_block').empty().html(html).removeClass('hidden')
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                        },
                    });
                }
            });

        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>