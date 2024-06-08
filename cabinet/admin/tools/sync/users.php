<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Bitrix\Main\Loader;

//$_REQUEST['report_id'] = 99999999;
if (!Loader::includeModule('main')) {
    die('Error: Unable to load module "main".');
}
$all_lms = \Settings\Synchronization::getLMSList(['PROPERTY_IS_CURRENT' => false]);
$dealers = \Models\Dealer::getAll(['ID', 'NAME', 'CODE']);
?>

    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center" style="margin-bottom:10px">Миграция пользователей</h2>
                <div class="form-div">
                    <form class="report_generator" action="report.php" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">LMS</label>
                            <select class="js-example-basic-multiple" name="lms" style="width: 100%;">
                                <?php foreach ($all_lms as $id => $lms){?>
                                    <option value="<?=$lms['ID']?>"><?=$lms['NAME']?></option>
                                <?php }?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="table-block">
                    <div style="display: flex; align-items: center; justify-content: space-between">
                        <div style="display: flex; flex-direction: column">
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="link_if_exists" checked><label for="link_if_exists" style="cursor: pointer">Связывать, если пользователь существует</label></div>
                            <div class="form-group selectable" style="margin-bottom: 10px">
                                <label for="">Действия с переносимым сотрудником в целевой LMS</label>
                                <select class="js-example-basic-multiple" id="migrated_user_actions" name="" style="width: 100%;">
                                    <option value="activate">Активировать</option>
                                    <option value="deactivate">Деактивировать</option>
                                </select>
                            </div>
                            <div class="form-group selectable" style="margin-bottom: 10px">
                                <label for="">Действия с переносимым сотрудником в исходной LMS</label>
                                <select class="js-example-basic-multiple" id="migrated_user_actions_this_lms" name="" style="width: 100%;">
                                    <option value="none_actions">Оставить как есть</option>
                                    <option value="deactivate">Деактивировать</option>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_op_completions"><label for="migrate_op_completions" style="cursor: pointer">Перенести прохождения курсов отдела продаж<sup style="color:red">*</sup></label></div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_ppo_completions"><label for="migrate_ppo_completions" style="cursor: pointer">Перенести прохождения курсов отдела послепродажного обслуживания<sup style="color:red">*</sup></label></div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_marketing_completions"><label for="migrate_marketing_completions" style="cursor: pointer">Перенести прохождения курсов отдела маркетинга<sup style="color:red">*</sup></label></div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="select_all"><label for="select_all" style="cursor: pointer">Выбрать всех</label></div>
                            <div style="display: none; align-items: center; margin-bottom: 10px" id="selectFilteredBlock"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="select_filtered"><label for="select_filtered" style="cursor: pointer">Выбрать только отфильтрованных</label></div>
                            <p style="font-size: 12px; margin-bottom: 5px; color: #3f3f3f"><span style="color:red;">*</span> - Выполнится только в случае, если пользователь будет создан или привязан к удаленной LMS</p>
                        </div>
                        <div style="display: flex; flex-direction: column">
                            <div>Выбрано пользователей - <span id="selected_users_count">0</span> </div>
                            <button style=" margin-bottom: 10px; height: 40px; cursor: pointer" id="sendButton" disabled>Перенести</button>
                        </div>

                    </div>
                    <?php /*<div style="margin-bottom: 10px">
                        <input type="text" id="search_input" placeholder="Поиск..." style="width: 100%; padding: 5px; box-sizing: border-box;">
                    </div>*/?>
                    <div class="form-group selectable" style="margin-bottom: 10px">
                        <label for="">Дилер</label>
                        <select class="js-example-basic-multiple filter_select" name="filter_dealer" style="width: 100%;">
                            <option value="all">Выбрать дилера</option>
                            <?php foreach ($dealers as $id => $one_dealer){?>
                                <option value="<?=$one_dealer['ID']?>"><?=$one_dealer['NAME']?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="form-group selectable" style="margin-bottom: 10px">
                        <label for="">Направление</label>
                        <select class="js-example-basic-multiple filter_select" name="filter_direction" style="width: 100%;">
                            <option value="all">Все направления</option>
                            <option value="op">ОП</option>
                            <option value="ppo">ППО</option>
                            <option value="marketing">Маркетинг</option>
                        </select>
                    </div>
                    <!-- Добавляем элемент для отображения прогресса -->
                    <div id="progress-container" style="margin-bottom: 10px;" class="hidden">
                        <span id="progress-text">Отправлено: 0%</span>
                        <div id="progress-bar" style="width: 0%; height: 20px; background-color: green;"></div>
                        <h4 style="margin-top: 10px">Лог обработки</h4>
                        <div id="log"></div>
                    </div>
                    <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                        <thead class="thead-dark">
                        <tr>
                            <th style="text-align: left">#</th>
                            <th style="text-align: left">ID</th>
                            <th style="text-align: left">ФИО</th>
                            <th style="text-align: left">Email</th>
                            <th style="text-align: left">Роли</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script>
        function updateSelectedCount() {
            const selectedCount = $('#table-report tbody input[type="checkbox"]:checked').length;
            $('#selected_users_count').text(selectedCount);
            $('#sendButton').attr('disabled', selectedCount===0)
        }
        $(document).ready(function() {
            // Обработчик для чекбокса "Выбрать только отфильтрованных"
            $('.filter_select').on('change', function() {
                let data = {
                    method: 'getFilteredUsers',
                    dealer: $('[name="filter_dealer"]').val(),
                    direction: $('[name="filter_direction"]').val(),
                }

                $.ajax({
                    url: '/cabinet/admin/tools/sync/ajax/users.php',
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        $('tbody').empty()
                        $('#select_all').prop('checked', false);
                        updateSelectedCount()
                        for (let id in response.users){
                            let user = response.users[id];
                            let tr = `<tr>
                                <td><input type="checkbox" style="width: 20px; height: 20px" class="checkboxUser" id="user_${user.ID}" name="users[]" value="${user.ID}"></td>
                                <td style="text-align: left" class="idTD"><label style="cursor: pointer" for="user_${user.ID}">${user.ID}</label></td>
                                <td style="text-align: left"><label style="cursor: pointer" for="user_${user.ID}">${user.NAME} ${user.LAST_NAME}</label></td>
                                <td style="text-align: left"><label style="cursor: pointer" for="user_${user.ID}">${user.EMAIL}</label></td>
                                <td style="text-align: left"><label style="cursor: pointer" for="user_${user.ID}">${user.ROLES}</label></td>
                            </tr>`;
                            $('tbody').append(tr);
                        }

                    },
                    error: function(xhr, status, error) {}
                });
            });
            $('#select_filtered').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('#table-report tbody input[type="checkbox"]').prop('checked', false);
                if (isChecked) {
                    $('#table-report tbody tr:visible input[type="checkbox"]').prop('checked', true);
                }
                updateSelectedCount();
            });

            // Обработчик для чекбокса "Выбрать всех"
            $('#select_all').on('change', function () {
                var isChecked = $(this).is(':checked');
                $('#table-report tbody input[type="checkbox"]').prop('checked', isChecked);
                updateSelectedCount()
            });

            // Обработчик для снятия отметки с "Выбрать всех", если один из чекбоксов снимается
            $(document).on('change', '.checkboxUser', function (){
                if (!$(this).is(':checked')) {
                    $('#select_all').prop('checked', false);
                } else {
                    // Если все чекбоксы отмечены, отметить "Выбрать всех"
                    if ($('#table-report tbody input[type="checkbox"]:checked').length === $('#table-report tbody input[type="checkbox"]').length) {
                        $('#select_all').prop('checked', true);
                    }
                }
                updateSelectedCount()
            });

            // Обработчик для фильтрации строк таблицы
            $('#search_input').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                if(searchText.length > 0)
                    $('#selectFilteredBlock').css('display', 'flex')
                else
                    $('#selectFilteredBlock').css('display', 'none')
                $('#table-report tbody tr').each(function() {
                    var rowText = $(this).text().toLowerCase();
                    if (rowText.indexOf(searchText) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });

            // Обработчик для клика отправки
            $('#sendButton').on('click', function() {
                $('#progress-container').removeClass('hidden');
                $('#log').empty();
                let data = {
                    lms: parseInt($('[name="lms"]').val()),
                    ids: []
                };
                $('.checkboxUser:checked').each(function() {
                    let id = parseInt($(this).closest('tr').find('td.idTD label').text());
                    data.ids.push(id);
                });
                function sendUser(index) {
                    if (index < data.ids.length) {
                        $.ajax({
                            url: '/cabinet/admin/tools/sync/ajax/users.php',
                            method: 'POST',
                            data: {
                                lms: data.lms,
                                id: data.ids[index],
                                addActive: $('#add_active').is(':checked'),
                                linkIfExists: $('#link_if_exists').is(':checked'),
                                migrate_op_completions: $('#migrate_op_completions').is(':checked'),
                                migrate_ppo_completions: $('#migrate_ppo_completions').is(':checked'),
                                migrate_marketing_completions: $('#migrate_marketing_completions').is(':checked'),
                                migrated_user_actions: $('#migrated_user_actions').val(),
                                migrated_user_actions_this_lms: $('#migrated_user_actions_this_lms').val(),
                            },
                            dataType: 'json',
                            success: function(response) {
                                $("#log").append('<p style="margin-top: 5px; padding-bottom: 5px; border-bottom: 1px solid">'+response.result+'</p>')
                                // Обновление прогресса
                                let progress = Math.round((index + 1) / data.ids.length * 100);
                                if(progress===100)
                                    $('#progress-text').text('Отправка завершена');
                                else
                                    $('#progress-text').text('Отправлено: ' + progress + '%');
                                $('#progress-bar').css('width', progress + '%');

                                // Отправка следующего пользователя
                                sendUser(index + 1);
                            },
                            error: function(xhr, status, error) {
                                console.error('Ошибка при отправке данных:', error);
                                // Можно продолжить отправку следующих данных или остановиться
                                sendUser(index + 1); // Пропустить ошибочный запрос и продолжить
                            }
                        });
                    }
                }
                sendUser(0)
            });
        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");