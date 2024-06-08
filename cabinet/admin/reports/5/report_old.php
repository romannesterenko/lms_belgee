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
    '!PROPERTY_BEGIN_DATE'=>false,
    '!PROPERTY_COURSE'=>false
];
$no_fnd_by_filter = false;
if(!empty($_REQUEST['dir'])&&$_REQUEST['dir']!=0){
    $no_fnd_by_filter = true;
    $direction = $_REQUEST['dir']=='OTHERS'?false:strtoupper($_REQUEST['dir']);
    $cat_courses = \Teaching\Courses::getByDirection($direction);
    //dump($cat_courses);
    foreach ($cat_courses as $cat_course){
        $no_fnd_by_filter = false;
        $schedules_filter['PROPERTY_COURSE'][] = $cat_course['ID'];
    }
}
$schedules_filter['PROPERTY_COURSE'] = check_full_array($schedules_filter['PROPERTY_COURSE'])?$schedules_filter['PROPERTY_COURSE']:[];
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
if($_REQUEST['start_date']){
    $schedules_filter['>=PROPERTY_BEGIN_DATE'] = date('Y-m-d', strtotime($_REQUEST['start_date']))." 00:00:01";
}
if($_REQUEST['end_date']){
    $schedules_filter["<=PROPERTY_END_DATE"] = $_REQUEST['end_date'].' 23:59:59';
}
$schedules = $no_fnd_by_filter?[]: \Teaching\SheduleCourses::getArray(
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
foreach($schedules as $schedule){
    $course_ids[] = $schedule['PROPERTY_COURSE_VALUE'];
}
$courses = check_full_array($course_ids)
    ? \Teaching\Courses::getList(['ID' => array_unique($course_ids)], ['NAME', 'PROPERTY_CITY', 'PROPERTY_COURSE_CATEGORY'])
    :[];
$completions = new \Teaching\CourseCompletion();
$params['filter']['ACTIVE'] = 'Y';
$params['filter']['!UF_DEALER'] = false;
if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        $title_sop = 'Отдел продаж';
        $roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $user_filter['UF_ROLE'] = array_keys($roles);
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $title_sop = 'Отдел послепродажного обслуживания';
        $roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $user_filter['UF_ROLE'] = array_keys($roles);
    }
}
foreach($schedules as $key => &$schedule){
    $schedule['COUNT_DATES'] = \Helpers\DateHelper::getIntervalArray(
            \Helpers\DateHelper::getFormatted($schedule['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y'),
            $schedule['PROPERTY_END_DATE_VALUE']
    );
    if($courses[$schedule['PROPERTY_COURSE_VALUE']]['ID']>0) {
        $schedule['COURSE'] = $courses[$schedule['PROPERTY_COURSE_VALUE']];
    }else
        unset($schedules[$key]);
    $schedule['TR_DAYS'] = count($schedule['COUNT_DATES']);
    $filter = ["UF_IS_COMPLETE" => 1, 'UF_SHEDULE_ID' => $schedule['ID']];
    $schedule['COMPLETIONS'] = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $schedule['ID']]);
    foreach ($schedule['COMPLETIONS'] as $key_compl =>  $compl){
        if($compl['UF_IS_COMPLETE']!=1&&$compl['UF_DIDNT_COM']==1){
            unset($schedule['COMPLETIONS'][$key_compl]);
        }
    }
    $dealers = [];
    $params['select'] = ['ID', 'UF_DEALER'];
    foreach ($schedule['COMPLETIONS'] as $COMPLETION){
        $params['filter']['ID'] = $COMPLETION['UF_USER_ID'];
        $us = current(\Models\User::getArray($params));
        $dealers[] = $us['UF_DEALER'];
    }
    $schedule['DEALERS'] = count(array_unique($dealers));
    $schedule['TRAINED'] = count($schedule['COMPLETIONS']);
    $schedule['MEN_DAYS_PLAN'] = $schedule['TR_DAYS']*(int)$schedule['PROPERTY_LIMIT_VALUE'];
    $schedule['MEN_DAYS_FACT'] = $schedule['TR_DAYS']*$schedule['TRAINED'];
    $schedule['PERFOMANCE'] = $schedule['MEN_DAYS_PLAN']>0?$schedule['MEN_DAYS_FACT']/$schedule['MEN_DAYS_PLAN']*100:0;
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
                                    <th class="text-center">Дата</th>
                                    <th class="text-center">Тренинг</th>
                                    <th class="text-center">Город</th>
                                    <th class="text-center">Дней</th>
                                    <th class="text-center">Участников (план)</th>
                                    <th class="text-center">Дилеров (факт)</th>
                                    <th class="text-center">Участников (факт)</th>
                                    <th class="text-center">Человекодни (план)</th>
                                    <th class="text-center">Человекодни (факт)</th>
                                    <th class="text-center">Продуктивность, %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule_){?>
                                    <tr>
                                        <td><?=$schedule_['COURSE']['PROPERTY_COURSE_CATEGORY_VALUE']?></td>
                                        <td>
                                            <?php if(\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')==\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_END_DATE_VALUE'], 'd.m.Y')){?>
                                                <?=\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')?>
                                            <?php } else {?>
                                                <?=\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_BEGIN_DATE_VALUE'], 'd.m.Y')?> - <?=\Helpers\DateHelper::getFormatted($schedule_['PROPERTY_END_DATE_VALUE'], 'd.m.Y')?>
                                            <?php }?>
                                        </td>
                                        <td class="text-left"><a href="/cabinet/diller/reports/course_completions/<?=$schedule_['COURSE']['ID']?>/" target="_blank"><?=$schedule_['COURSE']['NAME']?></a></td>
                                        <td><?=$schedule_['PROPERTY_CITY_VALUE']?></td>
                                        <td><?=$schedule_['TR_DAYS']?></td>
                                        <td><?=(int)$schedule_['PROPERTY_LIMIT_VALUE']?></td>
                                        <td><?=(int)$schedule_['DEALERS']?></td>
                                        <td><?=$schedule_['TRAINED']?></td>
                                        <td><?=$schedule_['MEN_DAYS_PLAN']?></td>
                                        <td><?=$schedule_['MEN_DAYS_FACT']?></td>
                                        <td><?=round($schedule_['PERFOMANCE'], 1)?>%</td>
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
