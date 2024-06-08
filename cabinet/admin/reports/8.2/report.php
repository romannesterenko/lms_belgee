<?php

use Bitrix\Main\Localization\Loc;
use Models\Dealer;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");
$_REQUEST['report_id'] = 999999;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$completions = new \Teaching\CourseCompletion();
$enrollments = new \Teaching\Enrollments();
$roless = \Models\Role::getAll(['ID', 'NAME', 'IBLOCK_SECTION_ID']);
$user_array = \Settings\Reports::generateTest();
$new_array = [];
$table_courses = [];
foreach ($user_array as $key_item => $temp_item){

    if($temp_item['NOT_ENROLLED']==1 || $temp_item['NOT_NEEDED'] == 1)
        continue;
    if( $temp_item['UF_IS_COMPLETE'] != 1 ) {
        if($temp_item['COURSE']['INFO']['PROPERTY_COURSE_TYPE_ENUM_ID']==6) {
            if(!check_full_array($temp_item['COURSE']['INFO']['PROPERTY_SCORM_VALUE'])&&$temp_item['COMPLETION_ID']>0){
                $test = current((new \Teaching\ProcessTest())->get(['UF_COMPLETION' => $temp_item['COMPLETION_ID']], ['UF_POINTS'])->getArray());
                if(check_full_array($test))
                    $user_array[$key_item]['UF_POINTS'] = (int)$test['UF_POINTS'];
            }
        }
    }
}

foreach ($user_array as $array){

    $new_array[$array['UF_USER_ID']][$array['UF_COURSE_ID']] = $array;
    if ($array['COURSE']['INFO']['ID']) {
        //dump($array['COURSE']['INFO']['ID']);
        $table_courses[$array['COURSE']['INFO']['ID']] = $array['COURSE']['INFO'];
    }
}
//dump($table_courses);
usort($table_courses, function($a, $b){
    return ($a['PROPERTY_COURSE_FORMAT_ENUM_ID'] - $b['PROPERTY_COURSE_FORMAT_ENUM_ID']);
});
$tmp_arr = [];
foreach ($table_courses as $t_c){
    $tmp_arr[$t_c['ID']] = $t_c;
}
$table_courses = $tmp_arr;
//dump($new_array);

?>

    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen8"><span>Excel</span></button>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
                        <thead class="thead-dark">
                        <tr>
                            <th>Код дилера</th>
                            <th>Название дилера</th>
                            <?php
                            if ($_REQUEST['direction']=='all'){?>
                                <th>Регионал ОП</th>
                                <th>Регионал ППО</th>
                                <th>Регионал Маркетинг</th>
                            <?php } else {
                                switch ($_REQUEST['direction']) {
                                    case "S01":
                                        echo "<th>Регионал ОП</th>";
                                        break;
                                    case "A01":
                                        echo "<th>Регионал ППО</th>";
                                        break;
                                    case "M01":
                                        echo "<th>Регионал Маркетинг</th>";
                                        break;
                                }
                            }?>
                            <th>ФИО</th>
                            <?php if($_REQUEST['need_show_ids'] == 'Y') {?>
                                <th>Email</th>
                            <?php }?>
                            <th>Уровень</th>
                            <th>Дата начала работы</th>
                            <th>Роль</th>
                            <?php foreach ($table_courses as $table_course){?>
                                <th><?=$table_course['NAME']?></th>
                                <th>Дата прохождения</th>
                                <th>Дата окончания сертификата</th>
                                <th>Ретест</th>
                                <th>Дата</th>
                                <?php /*<th>Баллы</th>*/?>
                            <?php }?>
                            <?php if($_REQUEST['need_show_ids'] == 'Y') {?>
                                <th>ID</th>
                            <?php }?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($new_array as $item){
                            $first_item = current($item);
                            ?>
                            <tr>
                                <td><?=$first_item['DEALER']['CODE']?></td>
                                <td><?=$first_item['DEALER']['NAME']?></td>
                                <?php
                                if ($_REQUEST['direction']=='all') {?>
                                    <td><?=Dealer::getRegionalOP($first_item['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalPPO($first_item['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalMarketing($first_item['DEALER']['ID'])?></td>
                                <?php } else {
                                    switch ($_REQUEST['direction']) {
                                        case "S01":
                                            echo "<td>".Dealer::getRegionalOP($first_item['DEALER']['ID'])."</td>";
                                            break;
                                        case "A01":
                                            echo "<td>".Dealer::getRegionalPPO($first_item['DEALER']['ID'])."</td>";
                                            break;
                                        case "M01":
                                            echo "<td>".Dealer::getRegionalMarketing($first_item['DEALER']['ID'])."</td>";
                                            break;
                                    }
                                }?>
                                <td><?=$first_item['NAME']?> <?=$first_item['LAST_NAME']?></td>
                                <?php if($_REQUEST['need_show_ids'] == 'Y') {?>
                                    <td><?=$first_item['EMAIL']?></td>
                                <?php }?>
                                <td><?=$first_item['UF_USER_RATING']?></td>
                                <td><?=$first_item['UF_WORK_START_DATE']?></td>
                                <td><?php foreach ($first_item['UF_ROLE'] as $r){?><?=$roless[$r]['NAME']?><br /><?php }?></td>
                                <?php foreach ($table_courses as $table_course) {
                                    $date = $item[$table_course['ID']]['UF_COMPLETED_TIME']??$item[$table_course['ID']]['UF_DATE'];
                                    if($item[$table_course['ID']]['UF_IS_COMPLETE']==1){?>
                                        <td>Пройден</td>
                                        <td><?=Helpers\DateHelper::getFormatted($date, 'd.m.Y')?></td>
                                        <td><?php echo Helpers\DateHelper::getFormatted($date->add('+'.\Models\Course::getExpiredDate($table_course['ID']).' months'), 'd.m.Y')?></td>
                                        <?php if(\Teaching\Courses::isNeedRetest($table_course['ID'])) {
                                            $status = \Models\Course::getStatus($table_course['ID'], $first_item['ID']);
                                            if($status=='expired') {?>
                                                <td>Нужен</td>
                                                <td>-</td>
                                            <?php } else {
                                                if($status=='uncompleted'){
                                                    $test = current(\Teaching\Tests::getTestByCourse($table_course['ID'], ['ID', 'NAME']));
                                                    $process_filter = [
                                                        'UF_TEST_ID' => $test['ID'],
                                                        'UF_IS_RETEST' => true,
                                                        'UF_USER_ID' => $first_item['ID'],
                                                        'UF_FINISHED' => true,
                                                        '>UF_LAST_ACTIVE' => $date
                                                    ];
                                                    $process_test = current((new \Teaching\ProcessTest())->get($process_filter)->getArray());
                                                    ?>
                                                    <td>Не пройден (<?=$process_test['UF_POINTS']?>/<?=\Models\Course::getMaxPoints($table_course['ID'])?>)</td>
                                                    <td><?=$process_test['UF_LAST_ACTIVE']?></td>
                                                <?php } else { ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php }?>
                                            <?php }?>
                                        <?php } else {?>
                                            <td>Выключен</td>
                                            <td>-</td>
                                        <?php }?>

                                        <?php /*<td><?=$item[$table_course['ID']]['UF_POINTS']?><?=$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']>0?"/".$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']:""?></td>*/?>
                                    <?php } else {
                                        if ($item[$table_course['ID']]['NOT_NEEDED']){?>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <?php /*<td>-</td>*/?>
                                        <?php } elseif ($item[$table_course['ID']]['NOT_ENROLLED']){?>
                                            <td>Не записывался</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <?php /*<td>-</td>*/?>
                                        <?php } else {
                                            if(!empty($item[$table_course['ID']]['UF_DATE'])&&time()<$item[$table_course['ID']]['UF_DATE']->getTimestamp()){?>
                                                <td>Записан</td>
                                                <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <?php /*<td>-</td>*/?>
                                            <?php } else {?>
                                                <td>Не пройден</td>
                                                <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <?php /*<td><?=(int)$item[$table_course['ID']]['UF_POINTS']?><?=$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']>0?"/".$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']:""?></td>*/?>
                                            <?php }
                                        }?>
                                    <?php }
                                    /*} else {*/?><!--
                                            <td>Не записывался</td>
                                            <td></td>
                                            <td></td>
                                        --><?php /*}*/?>

                                <?php }?>
                                <?php if($_REQUEST['need_show_ids'] == 'Y') {?>
                                    <td><?=$first_item['ID']?></td>
                                <?php }?>
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
<?php
function prepareInfoByCourseArray($courses, $users=false){
    $rows = [];
    foreach ($courses as $course){
        if($users==false) {
            $req_ids = array_unique(array_merge(
                User::getEmployeesByRoleToCourse($course, true),
                User::getRecommendEmployeesByRoleToCourse($course, true),
                User::getEmployeesByCourse($course, true),
            ));
        }else{
            $req_ids = $users;
        }
        $users = \Models\Employee::getList(['ID' => array_unique($req_ids)], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'WORK_POSITION']);
        if ( check_full_array($users) ) {
            $rows = array_merge($rows, prepareInfoByUsers($users, false, $course));
        }
    }
    return prepareRowsFromUsers($rows);
}
function prepareInfoByUserArray($users){
    $rows = [];
    $users = \Models\Employee::getList(['ID' => $users], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'WORK_POSITION']);
    if(check_full_array($users)){
        $rows = array_merge($rows, prepareInfoByUsers($users));
    }
    return prepareRowsFromUsers($rows);
}
function prepareInfoByRoleArray($roles){
    $rows = [];
    $users = \Models\Employee::getList(['UF_ROLE' => $roles], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'WORK_POSITION']);
    if(check_full_array($users)){
        $rows = array_merge($rows, prepareInfoByUsers($users));
    }
    return prepareRowsFromUsers($rows);
}
function prepareInfoByDealers($dealers, $roles=[]){
    $rows = [];
    foreach ($dealers as $dealer) {
        if(check_full_array($roles))
            $users = \Models\Employee::getList(['UF_DEALER' => $dealer['ID'], 'UF_ROLE' => $roles], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'WORK_POSITION']);
        else
            $users = \Models\Employee::getList(['UF_DEALER' => $dealer['ID']], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'WORK_POSITION']);
        if(check_full_array($users)){
            $rows = array_merge($rows, prepareInfoByUsers($users, $dealer));
        }
    }
    return prepareRowsFromUsers($rows);
}
function prepareInfoByUsers($users, $dealer=false, $course = false){

    $user_array = [];
    foreach ($users as $user){
        if(!$user['ID']>0)
            return [];
        $user['DEALER'] = $dealer!=false?$dealer:\Models\Dealer::find($user['UF_DEALER'], ['ID', 'NAME', 'CODE','PROPERTY_CITY']);
        if($course==false) {
            $courses = \Teaching\Courses::getCoursesByUser($user['ID']);
            if (!check_full_array($courses))
                return [];
            $courses_array = \Models\Course::getList(['ID' => $courses], ['ID', 'NAME', 'CODE']);
            $c = \Teaching\Courses::getCoursesByUser($user['ID']);
        }else{
            $courses_array = \Models\Course::getList(['ID' => $course], ['ID', 'NAME', 'CODE']);
            $c = [$course];
        }
        foreach ($c as $course){
            $item['INFO'] = $courses_array[$course];
            $item['COMPLETIONS'] = (new \Teaching\CourseCompletion())->getByCourseAndUser($user['ID'], $course, ['ID', 'UF_IS_COMPLETE', 'UF_DATE', 'UF_COMPLETED_TIME']);
            $item['ENROLLMENTS'] = current((new \Teaching\Enrollments())->getByUserAndCourse($course, $user['ID']));
            $schedules = \Teaching\SheduleCourses::getNearestForCourse($course);
            if(check_full_array($schedules))
                $item['SCHEDULE'] = current($schedules);
            $user['COURSES'][] = $item;
            unset($item);
        }
        $user_array[] = $user;
    }
    return $user_array;
}
function prepareRowsFromUsers($user_array){
    $rows = [];
    foreach ($user_array as $user_row){
        foreach ($user_row['COURSES'] as $course) {
            $row = $user_row;
            unset($row['COURSES']);
            $row['COURSE'] = $course;
            $rows[] = $row;
        }
    }
    return $rows;
}
function getSectionList($filter, $select)
{
    $dbSection = CIBlockSection::GetList(
        Array(
            'LEFT_MARGIN' => 'ASC',
        ),
        array_merge(
            Array(
                'ACTIVE' => 'Y',
                'GLOBAL_ACTIVE' => 'Y'
            ),
            is_array($filter) ? $filter : Array()
        ),
        false,
        array_merge(
            Array(
                'ID',
                'IBLOCK_SECTION_ID'
            ),
            is_array($select) ? $select : Array()
        )
    );

    while( $arSection = $dbSection-> GetNext(true, false) ){

        $SID = $arSection['ID'];
        $PSID = (int) $arSection['IBLOCK_SECTION_ID'];

        $arLincs[$PSID]['CHILDS'][$SID] = $arSection;

        $arLincs[$SID] = &$arLincs[$PSID]['CHILDS'][$SID];
    }

    return array_shift($arLincs);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>