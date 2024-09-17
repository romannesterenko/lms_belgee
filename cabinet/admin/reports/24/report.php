<?php

use Bitrix\Main\Localization\Loc;
use Models\Course;
use Models\Dealer;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
$_REQUEST['report_id'] = 9999999;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
//dump($_REQUEST);
$filter_completions = ['UF_IS_COMPLETE' => 1];
$user_ids = [];
$dealer_ids = [];
$role_ids = [];
$completion_ids = [];
$users = [];
$dealers = [];
$roles = [];
$cert_array = [];
if (check_full_array($_REQUEST['courses'])){
    $filter_completions['UF_COURSE_ID'] = $_REQUEST['courses'];
} else {
    switch ($_REQUEST['direction']) {
        case 'S01':
            $filter_completions['UF_COURSE_ID'] = Course::getOPList(true);
            break;
        case 'A01':
            $filter_completions['UF_COURSE_ID'] = Course::getPPOList(true);
            break;
        case 'M01':
            $filter_completions['UF_COURSE_ID'] = Course::getMarketingList(true);
            break;
    }
}
switch ($_REQUEST['direction']) {
    case 'S01':
        $direction = 'Sales';
        break;
    case 'A01':
        $direction = 'Aftersales';
        break;
    case 'M01':
        $direction = 'Marketing';
        break;
}
$courses = Course::getList(['ID' => $filter_completions['UF_COURSE_ID']], ['ID', 'NAME', "PROPERTY_COURSE_CATEGORY"]);
//dump($courses);
$list = (new \Teaching\CourseCompletion())->get($filter_completions);
foreach ($list as $completion) {
    $user_ids[] = $completion['UF_USER_ID'];
    $completion_ids[] = $completion['ID'];
}
$certificates = \Models\Sertificate::get(['UF_COMPLETION_ID' => $completion_ids]);
if (check_full_array($certificates)){
    foreach ($certificates as $certificate) {
        $cert_array[$certificate['UF_COMPLETION_ID']] = $certificate;
    }
}
if (check_full_array($user_ids)){
    $users = User::get(['ACTIVE' => "ALL", 'ID' => array_values(array_unique($user_ids))], ['ID', 'NAME', 'EMAIL', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE', 'PERSONAL_MOBILE']);
}
if (check_full_array($users)){
    foreach ($users as $user) {
        $dealer_ids[] = $user['UF_DEALER'];
        if (check_full_array($user['UF_ROLE']))
            $role_ids = array_merge($role_ids, $user['UF_ROLE']);
    }
}
if (check_full_array($dealer_ids)){
    $dealers = Dealer::getList(['ID' => array_values(array_unique($dealer_ids))]);
}
if (check_full_array($role_ids)){
    $roles = \Models\Role::getList(['ID' => array_values(array_unique($role_ids))], ['ID', 'NAME']);
}
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen"><span>Excel</span></button>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
                        <thead class="thead-dark">
                            <tr>
                                <th>Dealer code<br/>网点代码</th>
                                <th>Dealer name<br/>网点名称</th>
                                <th>Department<br/>部门</th>
                                <th>Position<br/>岗位</th>
                                <th>Name<br/>姓名</th>
                                <th>Tel. (not mandatory)<br/>电话</th>
                                <th>E-mail (not mandatory)<br/>邮箱</th>
                                <th>Full-time or part-time<br/>是否专职</th>
                                <th>Onboard date<br/>入职时间</th>
                                <th>Certification status<br/>认证状态</th>
                                <th>Qualification certificate<br/>岗位认证</th>
                                <th>Certificate No.<br/>证书编号</th>
                                <th>Certificate issued date<br/>证书有效期开始日期</th>
                                <th>Certificate expired date<br/>证书有效期截至日期</th>
                                <th>Training courses<br/>培训课程</th>
                                <th>Completion certificate No.<br/>结业证书编码</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $completion_item) { ?>
                                <tr>
                                    <td><?=$dealers[$users[$completion_item['UF_USER_ID']]['UF_DEALER']]['CODE']?></td>
                                    <td><?=$dealers[$users[$completion_item['UF_USER_ID']]['UF_DEALER']]['NAME']?></td>
                                    <td><?=$direction?></td>
                                    <td><?=$roles[$users[$completion_item['UF_USER_ID']]['UF_ROLE'][0]]['NAME']?></td>
                                    <td><?=$_REQUEST['hide_fio'] == 'Y'?mb_substr($users[$completion_item['UF_USER_ID']]['LAST_NAME'], 0, 1, "UTF-8").".":$users[$completion_item['UF_USER_ID']]['LAST_NAME']?> <?=$_REQUEST['hide_fio'] == 'Y'?mb_substr($users[$completion_item['UF_USER_ID']]['NAME'], 0, 1, "UTF-8").".":$users[$completion_item['UF_USER_ID']]['NAME']?></td>
                                    <td><?=$users[$completion_item['UF_USER_ID']]['PERSONAL_MOBILE']?></td>
                                    <td><?=$users[$completion_item['UF_USER_ID']]['EMAIL']?></td>
                                    <td>full-time</td>
                                    <td><?=date('d.m.Y', strtotime($completion_item['UF_COMPLETED_TIME']))?></td>
                                    <td>Certified</td>
                                    <td><?=$courses[$completion_item['UF_COURSE_ID']]['PROPERTY_COURSE_CATEGORY_VALUE']?></td>
                                    <td><?=$cert_array[$completion_item['ID']]['UF_CERT_NUMBER']?></td>
                                    <td><?=check_full_array($cert_array[$completion_item['ID']]) && !empty($cert_array[$completion_item['ID']]['UF_CREATED_AT'])?$cert_array[$completion_item['ID']]['UF_CREATED_AT']->format('d.m.Y'):''?></td>
                                    <td><?=check_full_array($cert_array[$completion_item['ID']]) && !empty($cert_array[$completion_item['ID']]['UF_EXPIRED_AT'])?$cert_array[$completion_item['ID']]['UF_EXPIRED_AT']->format('d.m.Y'):''?></td>
                                    <td><?=$courses[$completion_item['UF_COURSE_ID']]['NAME']?></td>
                                    <td><?=$cert_array[$completion_item['ID']]['UF_CERT_NUMBER']?></td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <style>
        .container{
            max-width: none;
        }
    </style>
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
                XLSX.writeFile(wb, "Сертификация персонала.xlsx");
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
<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>