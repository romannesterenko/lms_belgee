<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Bitrix\Main\Loader;

//$_REQUEST['report_id'] = 99999999;
if (!Loader::includeModule('main')) {
    die('Error: Unable to load module "main".');
}
$all_lms = \Settings\Synchronization::getLMSList(['PROPERTY_IS_CURRENT' => false]);
$dealers = \Models\Dealer::getAll(['ID', 'NAME', 'CODE', 'PROPERTY_CITY']);
?>

    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center" style="margin-bottom:10px">Миграция дилеров</h2>
                <div class="form-div">
                    <form class="report_generator" action="report.php" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Целевая LMS</label>
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
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="link_if_exists" checked><label for="link_if_exists" style="cursor: pointer">Связывать, если дилер существует</label></div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="add_active" checked><label for="add_active" style="cursor: pointer">Активировать при добавлении</label></div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_users"><label for="migrate_users" style="cursor: pointer">Перенести сотрудников дилера</label></div>
                            <div id="migrate_users_block" class="hidden" style="margin-left: 10px;">
                                <h5 style="margin-bottom: 10px; text-decoration: underline">Миграция сотрудников дилера</h5>
                                <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_op_users"><label for="migrate_op_users" style="cursor: pointer">Перенести сотрудников отдела продаж</label></div>
                                <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_ppo_users"><label for="migrate_ppo_users" style="cursor: pointer">Перенести сотрудников отдела послепродажного обслуживания</label></div>
                                <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_marketing_users"><label for="migrate_marketing_users" style="cursor: pointer">Перенести сотрудников отдела маркетинга</label></div>
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
                                <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="migrate_completions"><label for="migrate_completions" style="cursor: pointer">перенести и связать прохождения курсов сотрудника</label></div>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 10px"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="select_all"><label for="select_all" style="cursor: pointer">Выбрать всех</label></div>
                            <div style="display: none; align-items: center; margin-bottom: 10px" id="selectFilteredBlock"><input type="checkbox" style="width: 20px; height: 20px; margin-right: 10px" id="select_filtered"><label for="select_filtered" style="cursor: pointer">Выбрать только отфильтрованных</label></div>
                        </div>
                        <div style="display: flex; flex-direction: column">
                            <div>Выбрано дилеров - <span id="selected_users_count">0</span> </div>
                            <button style=" margin-bottom: 10px; height: 40px; cursor: pointer" id="sendButton" disabled>Перенести</button>
                        </div>

                    </div>
                    <div style="margin-bottom: 10px">
                        <input type="text" id="search_input" placeholder="Поиск..." style="width: 100%; padding: 5px; box-sizing: border-box;">
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
                                <th style="text-align: left">Дилер</th>
                                <th style="text-align: left">Код дилера</th>
                                <th style="text-align: left">Город</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dealers as $dealer){?>
                                <tr>
                                    <td><input type="checkbox" style="width: 20px; height: 20px" class="checkboxUser" id="user_<?=$dealer['ID']?>" name="users[]" value="<?=$dealer['ID']?>"></td>
                                    <td style="text-align: left" class="idTD"><label style="cursor: pointer" for="user_<?=$dealer['ID']?>"><?=$dealer['ID']?></label></td>
                                    <td style="text-align: left"><label style="cursor: pointer" for="user_<?=$dealer['ID']?>"><?=$dealer['NAME']?></label></td>
                                    <td style="text-align: left"><label style="cursor: pointer" for="user_<?=$dealer['ID']?>"><?=$dealer['CODE']?></label></td>
                                    <td style="text-align: left"><label style="cursor: pointer" for="user_<?=$dealer['ID']?>"><?=$dealer['PROPERTY_CITY_VALUE']?></label></td>
                                </tr>
                            <?php }?>
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
            $('#select_filtered').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('#table-report tbody input[type="checkbox"]').prop('checked', false);
                if (isChecked) {
                    $('#table-report tbody tr:visible input[type="checkbox"]').prop('checked', true);
                }
                updateSelectedCount();
            });

            // Обработчик для чекбокса "Выбрать всех"
            $('#migrate_users').on('change', function () {
                if($(this).is(':checked')){
                    $('#migrate_users_block').removeClass('hidden')
                } else {
                    $('#migrate_users_block').addClass('hidden')
                }
            });
            $('#select_all').on('change', function () {
                var isChecked = $(this).is(':checked');
                $('#table-report tbody input[type="checkbox"]').prop('checked', isChecked);
                updateSelectedCount()
            });

            // Обработчик для снятия отметки с "Выбрать всех", если один из чекбоксов снимается
            $('#table-report tbody input[type="checkbox"]').on('change', function() {
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
                            url: '/cabinet/admin/tools/sync/ajax/dealers.php',
                            method: 'POST',
                            data: {
                                lms: data.lms,
                                id: data.ids[index],
                                addActive: $('#add_active').is(':checked'),
                                linkIfExists: $('#link_if_exists').is(':checked'),
                                migrate_users: $('#migrate_users').is(':checked'),
                                migrate_op_users: $('#migrate_op_users').is(':checked'),
                                migrate_ppo_users: $('#migrate_ppo_users').is(':checked'),
                                migrate_marketing_users: $('#migrate_marketing_users').is(':checked'),
                                migrated_user_actions: $('#migrated_user_actions').val(),
                                migrated_user_actions_this_lms: $('#migrated_user_actions_this_lms').val(),
                                migrate_completions: $('#migrate_completions').is(':checked'),
                            },
                            dataType: 'json',
                            success: function(response) {
                                $("#log").append('<p style="margin-top: 5px; padding-bottom: 5px; border-bottom: 1px solid">'+response.result+'</p>')
                                // Обновление прогресса
                                let progress = Math.round((index + 1) / data.ids.length * 100);
                                if(progress === 100)
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