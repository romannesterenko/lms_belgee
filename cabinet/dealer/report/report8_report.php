<?php

use Bitrix\Main\Localization\Loc;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$completions = new \Teaching\CourseCompletion();
$enrollments = new \Teaching\Enrollments();
$roles_po_servis = getSectionList(['IBLOCK_ID' => \Helpers\IBlockHelper::getRolesIBlock()], ['ID', 'NAME']);
$qwerty = [];
$_REQUEST['report_id'] = 999999;
foreach ($roles_po_servis['CHILDS'] as $key => $sect){
    $qwerty[$sect['ID']] = $sect['NAME'];
    if(check_full_array($sect['CHILDS'])){
        foreach ($sect['CHILDS'] as $child)
            $qwerty[$child['ID']] = $sect['NAME'];
    }
}
$roless = \Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
$filter['ACTIVE'] = 'Y';
if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        $roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
    }
}
if(check_full_array($_REQUEST['fio']))
    $filter['ID'] = $_REQUEST['fio'];
if(check_full_array($_REQUEST['role']))
    $filter['UF_ROLE'] = $_REQUEST['role'];
if(check_full_array($_REQUEST['regional_ppo'])){
    $dealers = \Models\Dealer::getByRegionalPPO($_REQUEST['regional_ppo']);
    $filter['UF_DEALER'] = array_keys($dealers);
}
if(check_full_array($_REQUEST['dealer_codes'])){
    $dealers = \Models\Dealer::getList(['ID' => $_REQUEST['dealer_codes']]);
    $filter['UF_DEALER'] = array_keys($dealers);
}
if($_REQUEST['registered_employee']=='on'||$_REQUEST['deleted_employee']=='on'){
    if($_REQUEST['deleted_employee']=='on'&&$_REQUEST['registered_employee']!='on'){
        $filter['UF_DEALER'] = false;
    }
}
if($_REQUEST['last_authorization_before']){
    $filter['>LAST_LOGIN'] = date('d.m.Y 00:00:01', strtotime($_REQUEST['last_authorization_before']));
}
if($_REQUEST['last_authorization_after']){
    $filter['<LAST_LOGIN'] = date('d.m.Y 23:59:59', strtotime($_REQUEST['last_authorization_after']));
}
$users = \Models\Employee::getList($filter, ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE', 'UF_DEALER', 'WORK_POSITION']);
$courses = [];
$dealers = [];
foreach ($users as $user){
    $courses = array_merge($courses, \Teaching\Courses::getCoursesByUser($user['ID']));
    $dealers[] = $user['UF_DEALER'];
}
$courses_array = \Models\Course::getList(['ID' => array_unique($courses)], ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID']);
$dealers_array = \Models\Dealer::getList(['ID' => array_unique($dealers)]);
foreach ($users as &$user){
    $user['COURSE_IDS'] = \Teaching\Courses::getCoursesByUser($user['ID']);
    if(check_full_array($_REQUEST['courses'])){
        $new_courses_array = [];
        foreach ($_REQUEST['courses'] as $request_course){
            if(in_array($request_course, $user['COURSE_IDS']))
                $new_courses_array[] = $request_course;
        }
        $user['COURSE_IDS'] = $new_courses_array;
    }

    $courses = array_merge($courses, $user['COURSE_IDS']);
}

foreach ($users as &$user){
    if(!$user['ID']>0)
        continue;
    $user['DEALER'] = $dealers_array[$user['UF_DEALER']];
    if(check_full_array($filter['UF_ROLE'])){
        $u_roles = [];
        $user['UF_TD_ROLE'] = $user['UF_ROLE'];
        foreach ($user['UF_ROLE'] as $u_role) {
            if (in_array($u_role, $filter['UF_ROLE'])) {
                $u_roles[] = $u_role;
            }
        }
        $user['UF_ROLE'] = $u_roles;

    }
    foreach ($user['COURSE_IDS'] as $course){
        $item['INFO'] = $courses_array[$course];
        $item['COMPLETIONS'] = $completions->getByCourseAndUser($user['ID'], (int)$course, ['ID', 'UF_IS_COMPLETE', 'UF_DATE', 'UF_COMPLETED_TIME', 'UF_FAILED']);
        $request_complete_course = $_REQUEST['status_complete']=='on'&&$_REQUEST['status_not_complete']!='on';
        if($_REQUEST['course_date_before']||$_REQUEST['course_date_after']) {
            $request_complete_course = true;
        }
        if($request_complete_course&&$item['COMPLETIONS']['UF_IS_COMPLETE']!=1)
            continue;
        if($request_complete_course){
            if($_REQUEST['course_date_before']){
                $dt = $item['COMPLETIONS']['UF_COMPLETED_TIME']??$item['COMPLETIONS']['UF_DATE'];
                if(strtotime($dt)>strtotime($_REQUEST['course_date_before'])){

                }else{
                    continue;
                }
            }
            if($_REQUEST['course_date_after']){
                $dt = $item['COMPLETIONS']['UF_COMPLETED_TIME']??$item['COMPLETIONS']['UF_DATE'];
                if(strtotime($dt)<strtotime($_REQUEST['course_date_after'])){

                }else{
                    continue;
                }
            }

            //$item['COMPLETIONS']['UF_IS_COMPLETE']
        }
        if($_REQUEST['status_complete']!='on'&&$_REQUEST['status_not_complete']=='on'&&$item['COMPLETIONS']['UF_IS_COMPLETE']==1)
            continue;
        $item['ENROLLMENTS'] = current($enrollments->getByUserAndCourse($course, $user['ID']));
        $schedules = \Teaching\SheduleCourses::getNearestForCourse($course);
        if(check_full_array($schedules))
            $item['SCHEDULE'] = current($schedules);
        $user['COURSES'][] = $item;
        unset($item);
    }
}
$user_array = $users;
$user_array = prepareRowsFromUsers($users);
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="report.php" class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen1"><span>Excel</span></button>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report1">
                        <thead class="thead-dark">
                        <tr>
                            <th>Код дилера</th>
                            <th>Название дилера</th>
                            <th>ОП / Сервис</th>
                            <th>Роль</th>
                            <th>ФИО</th>
                            <th>ID</th>
                            <th>Должность</th>
                            <th>Курс</th>
                            <th>Статус</th>
                            <th>Дата прохождения курса</th>
                            <th>Ближайшая дата в расписании</th>
                            <th>Записан на курс</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                            <?php foreach ($user_array as $item){
                                dd($item)?>
                                <tr>
                                    <td><?=$item['DEALER']['CODE']?></td>
                                    <td><?=$item['DEALER']['NAME']?></td>
                                    <td><?=$qwerty[$roless[$item['UF_ROLE'][0]]['IBLOCK_SECTION_ID']]?></td>
                                    <td><?php foreach ($item['UF_TD_ROLE'] as $r){?><?=$roless[$r]['NAME']?><br /><?php }?></td>
                                    <td><?=$item['NAME']?> <?=$item['LAST_NAME']?></td>
                                    <td><?=$item['ID']?></td>
                                    <td><?=$item['WORK_POSITION']?></td>
                                    <td><?=$item['COURSE']['INFO']['NAME']?></td>
                                    <td>
                                        <?=$item['COURSE']['COMPLETIONS']['UF_IS_COMPLETE']==1?'Пройден':'Не пройден'?>
                                    </td>
                                    <td>
                                        <?=$item['COURSE']['COMPLETIONS']['UF_IS_COMPLETE']==1?($item['COURSE']['COMPLETIONS']['UF_COMPLETED_TIME']?\Helpers\DateHelper::getHumanDate($item['COURSE']['COMPLETIONS']['UF_COMPLETED_TIME']):$item['COURSE']['COMPLETIONS']['UF_DATE']):''?>
                                    </td>
                                    <td>
                                        <?=$item['COURSE']['SCHEDULE']['ID']>0?$item['COURSE']['SCHEDULE']['PROPERTIES']['BEGIN_DATE']:''?>
                                    </td>
                                    <td>
                                        <?=$item['COURSE']['ENROLLMENTS']['UF_IS_APPROVED']==1?$item['COURSE']['ENROLLMENTS']['UF_CREATED_AT']:''?>
                                    </td>
                                    <td>
                                        <?php if($item['COURSE']['SCHEDULE']['ID']>0){?>
                                            <?=$item['COURSE']['ENROLLMENTS']['UF_IS_APPROVED']!=1&&$item['COURSE']['COMPLETIONS']['UF_IS_COMPLETE']!=1?'<a href="/courses/'.$item['COURSE']['INFO']['CODE'].'/" style="cursor: pointer">Запись на курс</a>':''?>
                                        <?php } else {?>
                                            <?=$item['COURSE']['COMPLETIONS']['UF_IS_COMPLETE']!=1?'<a href="/courses/'.$item['COURSE']['INFO']['CODE'].'/" style="cursor: pointer">Запись на курс</a>':''?>
                                        <?php }?>
                                    </td>
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
        $user['DEALER'] = $dealer!=false?$dealer:\Models\Dealer::find($user['UF_DEALER'], ['ID', 'NAME', 'CODE']);
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
            $item['COMPLETIONS'] = $completions->getByCourseAndUser($user['ID'], $course, ['ID', 'UF_IS_COMPLETE', 'UF_DATE', 'UF_COMPLETED_TIME']);
            $item['ENROLLMENTS'] = current($enrollments->getByUserAndCourse($course, $user['ID']));
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