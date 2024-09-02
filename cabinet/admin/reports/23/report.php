<?php

use Helpers\RequestHelper;
use Models\Course;
use Models\Role;
use Teaching\CourseCompletion;
use Teaching\Roles;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.7/xlsx.full.min.js"></script>

<?php
$_REQUEST['report_id'] = 9999;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

global $USER, $APPLICATION;
//получение списка ролей
if(check_full_array($_REQUEST['role']))
    $roles = $_REQUEST['role'];
else {
    $roles = array_keys(match ($_REQUEST["direction"]) {
        'S01' => Roles::getOPRoles(),
        'A01' => Roles::getPPORoles(),
        'M01' => Roles::getMarketingRoles(),
        default => Roles::getAll(),
    });
}
$roles_array = Roles::getById($roles);
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Обученность персонала по обязательным модулям</h2>
                <div class="text-content text-content--long">
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">К генератору</a>
                            </div>
                            <div class="btn-center">
                                <button class="btn" id="exportButton"><span>Экспортировать в Excel</span></button>
                            </div>
                        </div>
                        <?php
                        $dealer = \Models\Dealer::find((int)$_REQUEST['dealer_name'], [
                            'ID',
                            'NAME'
                        ]);
                        foreach ($roles_array as $role_id => $role) {
                            //$courses =
                            $ddd = current(Role::getList(['ID' => $role_id], ['ID', 'PROPERTY_COURSES']));
                            if (check_full_array($ddd['PROPERTY_COURSES_VALUE']))
                                $courses_list = Course::getList(['ID' => $ddd['PROPERTY_COURSES_VALUE']], ['ID', 'NAME', 'PROPERTY_COURSE_FORMAT']);
                            $direction_courses = match ($_REQUEST["direction"]) {
                                'S01' => Course::getOPList(true),
                                'A01' => Course::getPPOList(true),
                                'M01' => Course::getMarketingList(true),
                                default => []
                            };
                            if($_REQUEST["direction"]!='all' && check_full_array($direction_courses)) {
                                foreach ($courses_list as $course_id_temp => $c_a) {
                                    if (!in_array($course_id_temp, $direction_courses)){
                                        unset($courses_list[$course_id_temp]);
                                    }
                                }
                            }
                            if(!check_full_array($courses_list))
                                continue;
                            $users = \Models\User::get([
                                'UF_DEALER' => (int)$_REQUEST['dealer_name'],
                                'UF_ROLE' => $role_id
                            ]);

                            if(!check_full_array($users))
                                continue;
                            $data = [];
                            foreach ($courses_list as $course){
                                $course_type = $course['PROPERTY_COURSE_FORMAT_VALUE']??'Offline';
                                $data[$course_type][] = $course;
                            }?>
                            <table class="table table-bordered tableReport" data-name="<?=$role?>" style="padding-top: 25px">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="vertical-align: middle" rowspan="2" class="text-center"><?=$role?></th>
                                        <?php if (array_key_exists('Online', $data)) {?>
                                            <th style="vertical-align: middle" colspan="<?=count($data['Online'])?>" class="text-center">Онлайн модули</th>
                                        <?php }?>
                                        <?php if (array_key_exists('Offline', $data)) {?>
                                            <th style="vertical-align: middle" colspan="<?=count($data['Offline'])?>" class="text-center">Тренинги</th>
                                        <?php }?>
                                    </tr>
                                    <tr>
                                        <?php if (array_key_exists('Online', $data)) {
                                            foreach ($data['Online'] as $online_course){?>
                                                <th style="vertical-align: middle" class="text-center"><?=$online_course['NAME']?></th>
                                            <?php }?>
                                        <?php }?>
                                        <?php if (array_key_exists('Offline', $data)) {
                                            foreach ($data['Offline'] as $online_course){?>
                                                <th style="vertical-align: middle" class="text-center"><?=$online_course['NAME']?></th>
                                            <?php }?>
                                        <?php }?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user){?>
                                        <tr>
                                            <td><?=$user['NAME']?> <?=$user['LAST_NAME']?></td>
                                            <?php if (array_key_exists('Online', $data)) {
                                                foreach ($data['Online'] as $online_course){
                                                    $status = Course::getReportStatus($online_course['ID'], $user['ID'])?>
                                                    <td style="vertical-align: middle" class="text-center">
                                                        <?php echo match ($status['status']) {
                                                            'completed' => $status['date'],
                                                            'in_process' => "Проходит",
                                                            'enrolled' => "Записан<br/>" . $status['date'],
                                                            'failed' => "Не пройден<br/>" . $status['date'],
                                                            'expired', 'expired_date' => "<span style='color: red'>" . $status['date'] . "</span>",
                                                            'retest_failed' => "<span style='color: red'>Ретест провален<br/>" . $status['date'] . "</span>",
                                                            'uncompleted' => "<span style='color: red'>Не записан</span>",
                                                            default => $status['status'],
                                                        }; ?>
                                                    </td>
                                                <?php }?>
                                            <?php }?>
                                            <?php if (array_key_exists('Offline', $data)) {
                                                foreach ($data['Offline'] as $online_course){
                                                    $status = Course::getReportStatus($online_course['ID'], $user['ID'])?>
                                                    <td style="vertical-align: middle" class="text-center">
                                                        <?php echo match ($status['status']) {
                                                            'completed' => $status['date'],
                                                            'in_process' => "Проходит",
                                                            'enrolled' => "Записан<br/>" . $status['date'],
                                                            'failed' => "Не пройден<br/>" . $status['date'],
                                                            'expired', 'expired_date' => "<span style='color: red'>" . $status['date'] . "</span>",
                                                            'retest_failed' => "<span style='color: red'>Ретест провален<br/>" . $status['date'] . "</span>",
                                                            'uncompleted' => "<span style='color: red'>Не записан</span>",
                                                            default => $status['status'],
                                                        }; ?>
                                                    </td>
                                                <?php }?>
                                            <?php }?>
                                        </tr>
                                    <?php }?>
                                </tbody>
                            </table>
                        <?php
                        unset($data);
                        }?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('#exportButton').click(function () {
                // Создание новой рабочей книги
                var wb = XLSX.utils.book_new();

                // Перебираем все таблицы с классом tableReport
                $('.tableReport').each(function (index) {
                    // Преобразуем текущую таблицу в лист Excel
                    var ws = XLSX.utils.table_to_sheet(this);

                    // Получаем диапазон листа (range)
                    var range = XLSX.utils.decode_range(ws['!ref']);

                    // Массив для хранения ширины столбцов
                    var colWidths = [];
                    // Массив для хранения высоты строк
                    var rowHeights = [];

                    // Перебираем все столбцы для установки ширины
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var maxWidth = 10; // Минимальная ширина столбца

                        for (var R = range.s.r; R <= range.e.r; ++R) {
                            var cellAddress = XLSX.utils.encode_cell({r: R, c: C});
                            var cell = ws[cellAddress];

                            if (cell && cell.v) {
                                var cellValue = cell.v.toString();
                                var width = cellValue.length;
                                if (width > maxWidth) maxWidth = width;

                                // Устанавливаем стили: вертикальное и горизонтальное выравнивание, перенос текста
                                if (!ws[cellAddress].s) ws[cellAddress].s = {};
                                ws[cellAddress].s.alignment = {
                                    vertical: "center",   // Выравнивание по вертикали
                                    horizontal: "center", // Выравнивание по горизонтали
                                    wrapText: true        // Перенос текста
                                };
                            }
                        }

                        colWidths.push({wch: maxWidth});
                    }

                    // Устанавливаем ширину столбцов
                    ws['!cols'] = colWidths;

                    // Устанавливаем высоту строк на две строки (примерно 30 единиц высоты)
                    for (var R = range.s.r; R <= range.e.r; ++R) {
                        rowHeights.push({hpx: 30}); // Высота в пикселях
                    }

                    // Применяем высоту строк к листу
                    ws['!rows'] = rowHeights;

                    // Добавляем лист в рабочую книгу
                    XLSX.utils.book_append_sheet(wb, ws, $(this).data('name'));
                });

                // Сохранение файла Excel
                XLSX.writeFile(wb, "Обученность персонала по обязательным модулям '<?=$dealer['NAME']?>'.xlsx");
            });



            $(document).on('change', '.checkbox-item input[type="checkbox"]', function (){
                if($(this).attr('name')=='op_servis_op') {
                    $('label[for="op_servis_servis"]').trigger('click');
                }
                if($(this).attr('name')=='op_servis_servis') {
                    $('label[for="op_servis_op"]').trigger('click');
                }
            });
        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>