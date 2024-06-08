<?php

use Bitrix\Main\Localization\Loc;
use Models\Dealer;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$completions = new \Teaching\CourseCompletion();
$enrollments = new \Teaching\Enrollments();
$roles_po_servis = getSectionList(['IBLOCK_ID' => \Helpers\IBlockHelper::getRolesIBlock()], ['ID', 'NAME']);
$qwerty = [];
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '8.1', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = 999999;

$user_array = [];
$need_setted_courses = true;
foreach ($roles_po_servis['CHILDS'] as $key => $sect) {
    $qwerty[$sect['ID']] = $sect['NAME'];
    if(check_full_array($sect['CHILDS'])) {
        foreach ($sect['CHILDS'] as $child)
            $qwerty[$child['ID']] = $sect['NAME'];
    }
}
$role_ids = [];
$user_filter['ACTIVE'] = 'Y';
$user_filter['!UF_DEALER'] = false;
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
    $need_setted_courses = false;
}

//$user_filter['UF_DEALER'] = [];
//if(check_full_array($_REQUEST['regional_op'])){
if(check_full_array($_REQUEST['regional_ppo'])){
    $dealers = \Models\Dealer::getByRegionalPPO($_REQUEST['regional_ppo']);
    $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
}
if(check_full_array($_REQUEST['regional_op'])){
    $dealers = \Models\Dealer::getByRegionalOP($_REQUEST['regional_op']);
    $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER'], array_keys($dealers));
}
//}
if(check_full_array($_REQUEST['dealer_codes'])){
    $user_filter['UF_DEALER'] = $_REQUEST['dealer_codes'];
}
$title_sop = false;
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
if(check_full_array($_REQUEST['role'])){
    $user_filter['UF_ROLE'] = $_REQUEST['role'];
    $need_setted_courses = false;
}
if (check_full_array($_REQUEST['fio'])){
    unset($user_filter);
    $user_filter = ["ID" => implode(' | ', $_REQUEST['fio'])];
}
if (check_full_array($_REQUEST['courses'])){
    $ids = [];
    foreach ($_REQUEST['courses'] as $c_id){
        $cs = \Models\User::getEmployeesByRoleToCourse($c_id, true);
        if (check_full_array($cs)){
            $ids = array_merge($ids, $cs);
        }

        $setted = \Models\User::getBySettedCourse($c_id, true);
        if (check_full_array($setted)){
            $ids = array_merge($ids, $setted);
        }
    }
    if(check_full_array($ids))
        $user_filter["ID"] = implode(' | ', $ids);
}
$rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $user_filter, ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_MOBILE', 'WORK_POSITION'], 'SELECT' => [ 'UF_ROLE','UF_WORK_START_DATE', 'UF_DEALER', 'UF_REQUIRED_COURSES']]); // выбираем пользователей
while($arUser = $rsUsers->Fetch()){
    $dealer_ids[] = $arUser['UF_DEALER'];
    if($arUser['UF_ROLE'])
        $role_ids = array_merge($role_ids, $arUser['UF_ROLE']);
    $users[$arUser['ID']] = $arUser;

}
$temp_users = $users;

$roless = \Models\Role::getList(['ID' => array_unique($role_ids)], ['ID', 'NAME', 'IBLOCK_SECTION_ID']);

$dealers = Dealer::getList(['ID' => $dealer_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']);
$filter_completions['UF_USER_ID'] = check_full_array($users)?array_keys($users):[];
if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        $filter_completions['UF_COURSE_ID'] = [];
        foreach ($role_ids as $role_id__) {
            $courses_by_role = \Models\Course::getMustByRole($role_id__, true);
            foreach ($users as $id => $user) {
                if(in_array($role_id__, $user['UF_ROLE']))
                    $users[$id]['MUST_COURSES'] = $courses_by_role;
            }
            $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], $courses_by_role));
        }
        //$filter_completions['UF_COURSE_ID'] = \Models\Course::getOPList(true);
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $filter_completions['UF_COURSE_ID'] = [];
        foreach ($role_ids as $role_id__) {
            $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], \Models\Course::getMustByRole($role_id__, true)));
        }
        //$filter_completions['UF_COURSE_ID'] = \Models\Course::getPPOList(true);
    }
}
if(check_full_array($_REQUEST['role'])){
    $filter_completions['UF_COURSE_ID'] = \Models\Course::getMustByRole($_REQUEST['role'], true);
    foreach ($users as $key => $user){
        $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
    }
}
if(!empty($_REQUEST['course_date_before']))
    $filter_completions['>UF_DATE'] = date('d.m.Y 00:00:01', strtotime($_REQUEST['course_date_before']));
if(!empty($_REQUEST['course_date_after']))
    $filter_completions['<UF_DATE'] = date('d.m.Y 23:59:59', strtotime($_REQUEST['course_date_after']));
$all_completions = (new \Teaching\CourseCompletion())->get($filter_completions);
if($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on') {
    if($_REQUEST['status_complete']=='on'&&$_REQUEST['status_not_complete']!='on')
        $filter_completions['UF_IS_COMPLETE'] = 1;
    if($_REQUEST['status_complete']!='on'&&$_REQUEST['status_not_complete']=='on')
        $filter_completions['UF_IS_COMPLETE'] = false;
}

if($need_setted_courses){
    $setted_courses_ids = [];
    $filter_completions['UF_COURSE_ID'] = check_full_array($filter_completions['UF_COURSE_ID'])?$filter_completions['UF_COURSE_ID']:[];
    foreach ($users as $key => $user){
        if(check_full_array($user['UF_REQUIRED_COURSES'])) {
            $setted_courses_ids = array_unique(array_merge($setted_courses_ids, $user['UF_REQUIRED_COURSES']));
            $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], $user['UF_REQUIRED_COURSES']));
            $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
        }
        if(check_full_array($user['UF_ROLE'])) {
            $filter_completions['UF_COURSE_ID'] = array_unique(array_merge($filter_completions['UF_COURSE_ID'], \Models\Course::getMustByRole($user['UF_ROLE'], true)));
            $users[$key]['MUST_COURSES'] = $filter_completions['UF_COURSE_ID'];
        }
    }
}
if(check_full_array($_REQUEST['courses'])){
    $filter_completions['UF_COURSE_ID'] = $_REQUEST['courses'];
    foreach ($users as $key => $user){
        $users[$key]['MUST_COURSES'] = $_REQUEST['courses'];
    }
}
$completions = (new \Teaching\CourseCompletion())->get($filter_completions);
$course_ids = [];
if(check_full_array($filter_completions['UF_COURSE_ID'])) {
    $course_ids = $filter_completions['UF_COURSE_ID'];
} else {
    foreach ($completions as $one_completion)
        $course_ids[] = $one_completion['UF_COURSE_ID'];
}
$courses = \Models\Course::getList(['ID' => $course_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_SCORM', 'PROPERTY_COURSE_TYPE', 'PROPERTY_COURSE_FORMAT']);
foreach ($courses as $kk => &$c_temp){
    if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']!=5) {
        if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']==125) {
            $c_temp['MAX_POINTS'] = \Teaching\Tests::getMaxPointsByCourse($c_temp['ID']);
        } else {
            if(check_full_array($c_temp['PROPERTY_SCORM_VALUE']))
                $c_temp['MAX_POINTS'] = 100;
            else
                $c_temp['MAX_POINTS'] = \Teaching\Tests::getMaxPointsByCourse($c_temp['ID']);
        }
    }
}
$all_user_array = [];
foreach ($all_completions as $completion){
    $all_user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $completion;
}

unset($completion);
foreach($completions as &$completion){
    $completion['COMPLETION_ID'] = $completion['ID'];
    $completion['ID'] = $completion['UF_USER_ID'];
    $completion['NAME'] = $users[$completion['UF_USER_ID']]['NAME'];
    $completion['LAST_NAME'] = $users[$completion['UF_USER_ID']]['LAST_NAME'];
    $completion['EMAIL'] = $users[$completion['UF_USER_ID']]['EMAIL'];
    $completion['UF_ROLE'] = $users[$completion['UF_USER_ID']]['UF_ROLE'];
    $completion['UF_WORK_START_DATE'] = $users[$completion['UF_USER_ID']]['UF_WORK_START_DATE'];

    $completion['PERSONAL_MOBILE'] = $users[$completion['UF_USER_ID']]['PERSONAL_MOBILE'];
    $completion['WORK_POSITION'] = $users[$completion['UF_USER_ID']]['WORK_POSITION'];
    $completion['DEALER'] = $dealers[$users[$completion['UF_USER_ID']]['UF_DEALER']];
    $completion['COURSE']['INFO'] = $courses[$completion['UF_COURSE_ID']];
    $user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $completion;
}
if(!$_REQUEST['status_complete']=='on'&&!$_REQUEST['status_not_complete']=='on') {
    foreach ($users as $user) {
        foreach ($courses as $course) {
            if (!check_full_array($all_user_array[$user['ID'] . "_" . $course['ID']])) {
                $completion['ID'] = $user['ID'];
                $completion['NAME'] = $user['NAME'];
                $completion['LAST_NAME'] = $user['LAST_NAME'];
                $completion['EMAIL'] = $user['EMAIL'];
                $completion['UF_ROLE'] = $user['UF_ROLE'];
                $completion['UF_WORK_START_DATE'] = $user['UF_WORK_START_DATE'];
                $completion['UF_USER_ID'] = $user['ID'];
                $completion['UF_COURSE_ID'] = $course['ID'];
                $completion['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
                $completion['WORK_POSITION'] = $user['WORK_POSITION'];
                $completion['DEALER'] = $dealers[$user['UF_DEALER']];
                $completion['COURSE']['INFO'] = $courses[$course['ID']];
                if(check_full_array($user['MUST_COURSES'])&&in_array($course['ID'], $user['MUST_COURSES'])) {
                    $completion['NOT_ENROLLED'] = 1;
                } else {
                    $completion['NOT_NEEDED'] = 1;
                }
                $completion['UF_IS_COMPLETE'] = false;
                $user_array[$user['ID'] . "_" . $course['ID']] = $completion;
                unset($completion);
            }
        }
    }
}

$user_array = \Settings\Reports::generate();
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
                            <th>Регионал ОП</th>
                            <th>Регионал ППО</th>
                            <th>ФИО</th>
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
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($new_array as $item){
                            $first_item = current($item);
                            ?>
                            <tr>
                                <td><?=$first_item['DEALER']['CODE']?></td>
                                <td><?=$first_item['DEALER']['NAME']?></td>
                                <td><?=Dealer::getRegionalOP($first_item['DEALER']['ID'])?></td>
                                <td><?=Dealer::getRegionalPPO($first_item['DEALER']['ID'])?></td>
                                <td><?=$first_item['NAME']?> <?=$first_item['LAST_NAME']?></td>
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