<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Models\User;
$courses = Models\Course::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Объединение сотрудников</h2>
                <div class="form-div">
                    <?php if($_REQUEST['added']=='Y'){?>
                        <div class="" style="color: green; padding: 15px 0px">Информация успешно добавлена</div>
                    <?php }?>
                    <div class="error-text" style="color: red; padding: 15px 0px"></div>
                    <form class="report_form" action="" method="post" style="">
                        <p style="padding: 15px 0px; color: red;">Внимание!!! Сотрудник из которого вы добавляете данные будет дектривирован, данные о нем удалятся</p>
                        <div class="user_blocks" style="display: flex; justify-content: space-between">
                            <div class="to_user_block" style="width: 100%; margin-right: 10px">
                                <div class="form-group">
                                    <label for="">Сотрудник в которого добавить данные</label>
                                    <input type="text" value="" name="to_user" placeholder="Введите имя или фамилию или номер телефона и выберите пользователя из списка">
                                </div>
                                <input type="hidden" name="to_user_id" value="">
                                <div class="upcoming-courses aside-block aside-block--border users_search_block hidden">

                                </div>
                            </div>
                            <div class="from_user_block" style="width: 100%;">
                                <div class="form-group">
                                    <label for="">Сотрудник из которого добавить данные</label>
                                    <input type="text" value="" name="from_user" placeholder="Введите имя или фамилию или номер телефона и выберите пользователя из списка">
                                </div>
                                <input type="hidden" name="from_user_id" value="">
                                <div class="upcoming-courses aside-block aside-block--border users_search_block hidden">

                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn send_compl">Объединить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function (){
            $(document).on('click', '.to_user_block .select_user_from_popup', function (e){
                e.preventDefault();
                $("[name='to_user_id']").val($(this).data('user_id'));
                $("[name='to_user']").val($(this).data('user_name'));
                $('.to_user_block .users_search_block').addClass('hidden').empty();
            });
            $(document).on('keyup', '[name="to_user"]', function (){
                let str = $(this).val();
                $('.to_user_block .users_search_block').addClass('hidden').empty();
                $("[name='to_user_id']").val('');
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
                                        '<span class="upcoming-course__date">'+response.list[k].NAME+' '+response.list[k].LAST_NAME+' (ID: '+response.list[k].ID+')</span>' +
                                        '</span>' +
                                        '<span class="upcoming-course__title">'+response.list[k].DEALER+'</span><br/>' +
                                        '<span class="upcoming-course__title">'+response.list[k].EMAIL+'</span><br/>' +
                                        '<span class="upcoming-course__title">'+response.list[k].PERSONAL_MOBILE+'</span>' +
                                        '</span>' +
                                        '</a>' +
                                        '</div>';
                                    html+=item;
                                }
                                $('.to_user_block .users_search_block').empty().html(html).removeClass('hidden')
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                        },
                    });
                }
            });

            $(document).on('click', '.from_user_block .select_user_from_popup', function (e){
                e.preventDefault();
                $("[name='from_user_id']").val($(this).data('user_id'));
                $("[name='from_user']").val($(this).data('user_name'));
                $('.from_user_block .users_search_block').addClass('hidden').empty();
            });
            $(document).on('keyup', '[name="from_user"]', function (){
                let str = $(this).val();
                $('.from_user_block .users_search_block').addClass('hidden').empty();
                $("[name='from_user_id']").val('');
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
                                        '<span class="upcoming-course__date">'+response.list[k].NAME+' '+response.list[k].LAST_NAME+' (ID: '+response.list[k].ID+')</span>' +
                                        '</span>' +
                                        '<span class="upcoming-course__title">'+response.list[k].DEALER+'</span><br/>' +
                                        '<span class="upcoming-course__title">'+response.list[k].EMAIL+'</span><br/>' +
                                        '<span class="upcoming-course__title">'+response.list[k].PERSONAL_MOBILE+'</span>' +
                                        '</span>' +
                                        '</a>' +
                                        '</div>';
                                    html+=item;
                                }
                                $('.from_user_block .users_search_block').empty().html(html).removeClass('hidden')
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
                    url: '/local/templates/geely/ajax/merge_user_data.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        console.log(response);
                        location.href = '/cabinet/admin/tools/merge_users/?added=Y'
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });

        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>