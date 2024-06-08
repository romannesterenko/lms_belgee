<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
use Models\User;
if (!$USER->isAdmin()){
    LocalRedirect('/cabinet/common/');
} else {
$courses = Models\Course::getList(['ACTIVE' => 'Y', '!PROPERTY_COURSE_TYPE' => 6], ['ID', 'NAME']);
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Удаление участников из тренинга</h2>
                <div class="form-div">
                    <?php if($_REQUEST['added']=='Y'){?>
                        <div class="" style="color: green; padding: 15px 0px">Информация успешно добавлена</div>
                    <?php }?>
                    <div class="error-text" style="color: red; padding: 15px 0px"></div>
                    <form class="report_form" action="" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Курс</label>
                            <select class="js-example-basic-multiple" name="course_id" style="width: 100%;" required>
                                <option value="">Выберите курс</option>
                                <?php foreach ($courses as $id => $course){?>
                                    <option value="<?=$course['ID']?>"><?=$course['NAME']?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group selectable">
                            <label for="">Расписание курса</label>
                            <select class="js-example-basic-multiple" name="schedule_id" style="width: 100%;" required disabled>
                                <option value="">Сначала выберите курс</option>
                            </select>
                        </div>
                    </form>
                    <div id="participants_table">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function loadParticipantsTable(){
            $("#participants_table").empty()
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/loadParticipantsTable.php',
                data: {
                    'shedule_id': $("[name='schedule_id']").val(),
                },
                dataType: 'html',
                beforeSend: function () {
                },
                success: function (response) {
                    $("#participants_table").empty().html(response)
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
        function removeCompletion(completion_id) {
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/delete_completion.php',
                data: {
                    'id': completion_id,
                },
                dataType: 'html',
                beforeSend: function () {
                },
                success: function (response) {
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
        $(function (){
            $(document).on('change', '[name="course_id"]', function (){
                let course_id = $(this).val();
                $("#participants_table").empty()
                if(!isNaN(parseInt(course_id))) {
                    $.ajax({
                        type: 'POST',
                        url: '/local/templates/geely/ajax/getNextShedules.php',
                        data: {
                            'course': course_id,
                        },
                        dataType: 'json',
                        beforeSend: function () {
                        },
                        success: function (response) {
                            if (response.count > 0) {
                                let html = '';
                                for (var k in response.list) {
                                    html += '<option value="' + response.list[k].ID + '">' + response.list[k].PROPERTY_BEGIN_DATE_VALUE + ' - ' + response.list[k].PROPERTY_END_DATE_VALUE + '</option>';
                                }
                                $("[name='schedule_id']").empty().prop('disabled', false).html(html)
                                loadParticipantsTable();
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                        },
                    });
                } else {

                    let html = '<option value="">Сначала выберите курс</option>';
                    $("[name='schedule_id']").empty().prop('disabled', true).html(html)
                }
            });
            $(document).on('change', '[name="schedule_id"]', function (){
                loadParticipantsTable()
            });
            $(document).on('click', '.delete_participant_completion', function (){
                if(confirm('Вы удаляете участника из тренинга. Вы уверены?')){
                    console.log($(this).data('completion'))
                    removeCompletion($(this).data('completion'))
                    loadParticipantsTable()
                }
            });
        })
    </script>

<?php }
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>