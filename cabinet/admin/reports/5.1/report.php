<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

$settings = \Settings\Reports::getMenDaysPerfReport();

\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '5', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = 1;
$schedules_filter = [
    'ACTIVE' =>'Y',
    '>=PROPERTY_BEGIN_DATE'=>$_REQUEST['year'].'-01-01 00:00:01',
    '<=PROPERTY_END_DATE'=>$_REQUEST['year'].'-12-31 23:59:59',
    '!PROPERTY_COURSE'=>false
];

$no_fnd_by_filter = false;
if(!empty($_REQUEST['dir'])&&$_REQUEST['dir']!=0){
    $no_fnd_by_filter = true;
    $direction = $_REQUEST['dir']=='OTHERS'?false:strtoupper($_REQUEST['dir']);
    $cat_courses = \Teaching\Courses::getByDirection($direction);
    foreach ($cat_courses as $cat_course){
        $no_fnd_by_filter = false;
        $schedules_filter['PROPERTY_COURSE'][] = $cat_course['ID'];
    }
}

$schedules_filter['PROPERTY_COURSE'] = check_full_array($schedules_filter['PROPERTY_COURSE'])?$schedules_filter['PROPERTY_COURSE']:[];


switch ($_REQUEST['direction']) {
    case 'S01':
        $schedules_filter['PROPERTY_COURSE'] = \Models\Course::getOPList(true);
        break;
    case 'A01':
        $schedules_filter['PROPERTY_COURSE'] = \Models\Course::getPPOList(true);
        break;
    case 'M01':
        $schedules_filter['PROPERTY_COURSE'] = \Models\Course::getMarketingList(true);
        break;
}

if(check_full_array($_REQUEST['role'])){
    $must_course_ids = [];
    foreach ($_REQUEST['role'] as $role_item){
        $must_course_ids = array_merge($must_course_ids, \Models\Course::getMustByRole($role_item, true));
    }
    if(!check_full_array($schedules_filter['PROPERTY_COURSE']))
        $schedules_filter['PROPERTY_COURSE'] = $must_course_ids;
    else
        $schedules_filter['PROPERTY_COURSE'] = array_intersect($must_course_ids, $schedules_filter['PROPERTY_COURSE']);
}



$schedules = \Teaching\SheduleCourses::getArray(
    $schedules_filter,
    [
        'ID',
        'PROPERTY_BEGIN_DATE',
        'PROPERTY_END_DATE',
        'PROPERTY_COURSE',
        'PROPERTY_LIMIT',
    ]
);

$course_ids = [];
foreach($schedules as &$schedule){
    $schedule['DURATION'] = \Teaching\SheduleCourses::getDuration($schedule['ID']);

    $course_ids[] = $schedule['PROPERTY_COURSE_VALUE'];
}



$courses = check_full_array($course_ids)
    ? \Teaching\Courses::getList(['ID' => array_unique($course_ids)], ['NAME', 'PROPERTY_CITY', 'PROPERTY_COURSE_CATEGORY', 'PROPERTY_COURSE_TYPE', 'PROPERTY_COURSE_FORMAT'])
    :[];

foreach ($courses as &$course){
    $course['TRAINING_DAYS'] = 0;
    foreach ($schedules as $schedule){
        if($course['ID'] == $schedule['PROPERTY_COURSE_VALUE']) {
            $course['TRAINING_DAYS']+=$schedule['DURATION'];
            $course['DURATION']=$schedule['DURATION'];
            $course['SCHEDULE_IDS'][] = $schedule['ID'];
            $course['SCHEDULES'][] = $schedule;
        }
    }
}
foreach ($courses as &$course){

    $setted = \Models\User::getBySettedCourse($course['ID'], true);
    $needed = \Models\User::getEmployeesByRoleToCourse($course['ID'], true);
    $course['ROLES'] = \Teaching\Roles::getRolesForCourse($course['ID'], false);
    $course['USERS'] = array_unique(array_merge($needed, $setted));
    $course['COMPLETIONS'] = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $course['SCHEDULE_IDS'], 'UF_IS_COMPLETE' => 1]);
}
?>
<div class="main-content">
    <div class="content">
        <div class="content-block">
                <div class="text-content text-content--long">
                    <h2 class="h2 center"><?= Loc::getMessage('TITLE') ?></h2>
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">К фильтру</a>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Наименование тренинга</th>
                                    <th class="text-center">Онлайн / очный</th>
                                    <th class="text-center">Целевая группа</th>
                                    <th class="text-center">Продолжительность тренинга Дней</th>
                                    <th class="text-center">Продолжительность тренинга в днях</th>
                                    <th class="text-center">Кол-во персонала текущее, для кого модуль обязательный</th>
                                    <th class="text-center">Обучены в году</th>
                                    <th class="text-center">Men days год</th>
                                    <th class="text-center">Роль, для кого обязательный модуль</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $key = 0;
                                foreach ($courses as $one_course){
                                    $key++; ?>
                                    <tr>
                                        <td><?=$key?></td>
                                        <?php /*<td>
                                            <?php if(\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')==\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_END_DATE_VALUE'], 'd.m.Y')){?>
                                                <?=\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')?>
                                            <?php } else {?>
                                                <?=\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')?> - <?=\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_END_DATE_VALUE'], 'd.m.Y')?>
                                            <?php }?>
                                        </td>*/?>
                                        <td class="text-left"><?=$one_course['NAME']?></td>
                                        <td class="text-left"><?=$one_course['PROPERTY_COURSE_FORMAT_VALUE']?></td>
                                        <td><?=$one_course['PROPERTY_COURSE_CATEGORY_VALUE']?></td>
                                        <td><?=$one_course['DURATION']?></td>
                                        <td><?=$one_course['TRAINING_DAYS']?></td>
                                        <td><?=count($one_course['USERS'])?></td>
                                        <td><?=count($one_course['COMPLETIONS'])?></td>
                                        <td><?=$one_course['DURATION']*count($one_course['COMPLETIONS'])?></td>
                                        <td><?=$one_course['ROLES']?></td>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                        <button class="dt-button buttons-pdf buttons-html5" id="gen"><span>Excel</span></button>

                    </div>
                </div>

            </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
