<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Models\Employee;
use Teaching\Courses;
use Teaching\SheduleCourses;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

$months = [
    "01" => "Январь",
    "02" => "Февраль",
    "03" => "Март",
    "04" => "Апрель",
    "05" => "Май",
    "06" => "Июнь",
    "07" => "Июль",
    "08" => "Август",
    "09" => "Сентябрь",
    "10" => "Октябрь",
    "11" => "Ноябрь",
    "12" => "Декабрь",
];
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '14', 'request' => $_REQUEST]);
$years = range((int)date('Y')-5, (int)date('Y')+1);
$_REQUEST['report_id'] = 123123123;
foreach ($_REQUEST as $key=>$value){
    $request_string[] = $key."=".$value;
}
$_REQUEST['month']=$_REQUEST['month']&&$_REQUEST['month']!=""?$_REQUEST['month']:date('m');
$_REQUEST['year']=$_REQUEST['year']&&$_REQUEST['year']!=""?$_REQUEST['year']:date('Y');
if($_REQUEST['month'])
    $selected_month = $_REQUEST['month'];
if($_REQUEST['year'])
    $selected_year = $_REQUEST['year'];
if(!empty($selected_month)||!empty($selected_year)) {
    if(!empty($selected_year)&&!empty($selected_month)) {
        $first_day_of_month = '01.'.$selected_month.'.'.$selected_year;
        $last_day_of_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year).'.'.$selected_month.'.'.$selected_year;
        $filter = [
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:00', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
        $filter_end = [
            '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:00', "YYYY-MM-DD H:i:s"),
            '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
        ];
    } else {
        if(!empty($selected_year)&&empty($selected_month)) {
            $first_day_of_month = '01.01.'.$selected_year;
            $last_day_of_month = '31.12.'.$selected_year;;
            $filter = [
                '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($first_day_of_month.' 00:00:00', "YYYY-MM-DD H:i:s"),
                '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
            ];
            $filter_end = [
                '>=PROPERTY_END_DATE' => ConvertDateTime($first_day_of_month.' 00:00:00', "YYYY-MM-DD H:i:s"),
                '<=PROPERTY_END_DATE' => ConvertDateTime($last_day_of_month.' 23:59:59', "YYYY-MM-DD H:i:s"),
            ];
        } else {
            $sch = \Teaching\SheduleCourses::getIdsByTrainer(Employee::getTrainerId());
        }
    }
    if(!check_full_array($sch)) {
        $this_months_list = $temp_arr = SheduleCourses::collectInfo($filter, false);
        $this_months_end = SheduleCourses::collectInfo($filter_end, false);
        $sch = [];
        if ( check_full_array($this_months_list) ) {
            foreach ( $this_months_list as $item )
                $sch[] = $item['ID'];
        }
        if ( check_full_array($this_months_end) ) {
            foreach ($this_months_end as $item)
                if (!in_array($item['ID'], $sch))
                    $sch[] = $item['ID'];

        }
    }
} else {
    $sch = \Teaching\SheduleCourses::getIdsByTrainer(Employee::getTrainerId());
}
$schedules = [];
if(check_full_array($sch)>0) {
    $schedules = SheduleCourses::getArray(['ID' => $sch]);
    foreach ($schedules as $key => $schedule){
        $course = Courses::getById($schedule['PROPERTIES']['COURSE']);
        if(!$course['ID']>0) {
            unset($schedules[$key]);
            continue;
        }
        switch($_REQUEST['direction']){
            case 'S01':
                if(Models\Course::isOP($course['ID']))
                    $schedules[$key]['COURSE'] = $course;
                else
                    unset($schedules[$key]);
                break;
            case 'A01':
                if(Models\Course::isPPO($course['ID']))
                    $schedules[$key]['COURSE'] = $course;
                else
                    unset($schedules[$key]);
                break;
            case 'M01':
                if(Models\Course::isMarketing($course['ID']))
                    $schedules[$key]['COURSE'] = $course;
                else
                    unset($schedules[$key]);
                break;
            case 'all':
                $schedules[$key]['COURSE'] = $course;
                break;
        }
    }
}
$list = [];
foreach ($schedules as $schedule) {
    $schedule = current(\Teaching\SheduleCourses::getById($schedule['ID']));
    if ($schedule['PROPERTIES']['TRAINERS'][0]['VALUE'] > 0) {
        $schedule['PROPERTIES']['TRAINER'] = current(\Teaching\Trainers::get(['ID' => $schedule['PROPERTIES']['TRAINERS'][0]['VALUE']]));
    }
    $begin_tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
    $now_stmp = time();
    $end_tmsmp = strtotime($schedule['PROPERTIES']['END_DATE'] . ' 23:59:59');
    $started = $begin_tmstmp < $now_stmp;
    $ended = $begin_tmstmp < $now_stmp && $end_tmsmp < $now_stmp;
    $course = \Teaching\Courses::getByScheduleId($schedule['ID']);
    if (!is_array($course) || $course === []) {
        Helpers\PageHelper::set404(Loc::getMessage('COURSE_NOT_FOUND'));
    }

    $completions = new \Teaching\CourseCompletion();
    $role_ids = [];
    $shedule_list = [];
    foreach ($completions->getFullApprListBySchedule($schedule['ID']) as $item) {
        $item['USER'] = \Models\User::find($item['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE']);
        if (check_full_array($item['USER']['UF_ROLE']))
            $role_ids = array_merge($role_ids, $item['USER']['UF_ROLE']);
        $item['USER']['DEALER'] = $item['USER']['UF_DEALER'] > 0 ? \Models\Dealer::find($item['USER']['UF_DEALER'], ['ID', 'NAME', 'CODE']) : [];
        $item['TRAINER'] = $schedule['PROPERTIES']['TRAINER'];
        $item['COURSE'] = $course;
        $shedule_list[] = $item;
    }
    $roles = \Teaching\Roles::getById($role_ids);
    if (check_full_array($roles)) {
        foreach ($shedule_list as &$l) {
            $role_arr = [];
            if (check_full_array($l['USER']['UF_ROLE'])) {
                foreach ($l['USER']['UF_ROLE'] as $r_id) {
                    $role_arr[] = $roles[$r_id];
                }
            }
            $l['USER']['UF_ROLE_LIST'] = implode(', ', $role_arr);
        }
    }
    $list = array_merge($list, $shedule_list);
}
?>
<div class="main-content">
    <div class="content">
        <h2 class="h2">Выгрузка пользователей</h2>
        <div class="content-block">
            <h3 class="text-center mt-20"><?= Loc::getMessage('SCHEDULE_INFO_TITLE_PARTICIPANTS') ?></h3>
            <div class="table-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen"><span>Excel</span></button>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-responsive-stack" id="table-report" style="padding-top: 25px">
                    <thead class="thead-dark">
                        <tr>
                            <th>Код дилера</th>
                            <th>Название дилера</th>
                            <th>Фамилия имя</th>
                            <th>Роль</th>
                            <th>Тренинг</th>
                            <th>Online/Offline</th>
                            <th>Дата</th>
                            <th>Баллы</th>
                            <th>Фио тренера</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $item){ ?>
                        <tr data-id="<?=$item['ID']?>" class="record_completion">
                            <td style="white-space: nowrap"><?=$item['USER']['DEALER']['CODE']?></td>
                            <td><?=$item['USER']['DEALER']['NAME']?></td>
                            <td><?=$_REQUEST['hide_fio'] == 'Y'?mb_substr($item['USER']['LAST_NAME'], 0, 1, "UTF-8").".":$item['USER']['LAST_NAME']?> <?=$_REQUEST['hide_fio'] == 'Y'?mb_substr($item['USER']['NAME'], 0, 1, "UTF-8").".":$item['USER']['NAME']?> <?=$_REQUEST['hide_fio'] == 'Y'&&!empty($item['USER']['SECOND_NAME'])?mb_substr($item['USER']['SECOND_NAME'], 0, 1, "UTF-8").".":""?></td>
                            <td><?=$item['USER']['UF_ROLE_LIST']?></td>
                            <td><?=$item['COURSE']['NAME']?></td>
                            <td><?=$item['COURSE']['PROPERTIES']['COURSE_FORMAT']?></td>
                            <td><?=$item['UF_DATE']?></td>
                            <td><?=$item['UF_POINTS']??'-'?></td>
                            <td><?=explode(' ', $item['TRAINER']['NAME'])[0]?> <?=$_REQUEST['hide_fio'] == 'Y'?mb_substr(explode(' ', $item['TRAINER']['NAME'])[1], 0, 1, "UTF-8").".":explode(' ', $item['TRAINER']['NAME'])[1]?></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php /*if($USER->GetID()==2){*/?>
    <style>
        .for_admin{
            text-align: right;
            padding: 15px 0px;
        }
        .form_value{
            display: none;
        }
    </style>

<?php /*} else {*/?><!--
    <style>
        .for_admin{
            display: none!important;
        }
    </style>
--><?php /*}*/?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


