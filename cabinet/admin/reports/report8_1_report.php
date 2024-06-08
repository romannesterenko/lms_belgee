<?php

use Bitrix\Main\Localization\Loc;
use Models\Dealer;
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
//dump($_REQUEST);
/*if(check_full_array($_REQUEST['courses'])){

    foreach ($roles_po_servis['CHILDS'] as $key => $sect){
        $qwerty[$sect['ID']] = $sect['NAME'];
        if(check_full_array($sect['CHILDS'])){
            foreach ($sect['CHILDS'] as $child)
                $qwerty[$child['ID']] = $sect['NAME'];
        }
    }
    $courses = \Models\Course::getList(['ID' => $_REQUEST['courses']], ['ID', 'NAME', 'CODE']);
    $user_array = [];
    $filter_completions = ['UF_COURSE_ID' => $_REQUEST['courses']];
    $request_dealers = [];
    if(check_full_array($_REQUEST['regional_ppo'])){
        $dealers = \Models\Dealer::getByRegionalPPO($_REQUEST['regional_ppo']);
        $request_dealers = array_merge($request_dealers, array_keys($dealers));
    }
    if(check_full_array($_REQUEST['regional_op'])){
        $dealers = \Models\Dealer::getByRegionalOP($_REQUEST['regional_op']);
        $request_dealers = array_merge($request_dealers, array_keys($dealers));
    }
    if(check_full_array($_REQUEST['dealer_codes'])){
        $request_dealers = $_REQUEST['dealer_codes'];
    }
    if(check_full_array($request_dealers)){
        $dealer_users = \Helpers\UserHelper::getListByDealer($request_dealers);
        foreach ($dealer_users as $du)
            $filter_completions['UF_USER_ID'][] = $du['ID'];
    }
    if(!empty($_REQUEST['course_date_before']))
        $filter_completions['>UF_DATE'] = date('d.m.Y 00:00:01', strtotime($_REQUEST['course_date_before']));
    if(!empty($_REQUEST['course_date_after']))
        $filter_completions['<UF_DATE'] = date('d.m.Y 23:59:59', strtotime($_REQUEST['course_date_after']));
    if($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on') {
        if($_REQUEST['status_complete']=='on'&&$_REQUEST['status_not_complete']!='on')
            $filter_completions['UF_IS_COMPLETE'] = 1;
        if($_REQUEST['status_complete']!='on'&&$_REQUEST['status_not_complete']=='on')
            $filter_completions['UF_IS_COMPLETE'] = false;
    }
    $completions = (new \Teaching\CourseCompletion())->get($filter_completions);
    $user_ids = [];
    $dealer_ids = [];
    $role_ids = [];
    foreach($completions as $completion){
        $user_ids[] = $completion['UF_USER_ID'];
    }
    $users=[];
    $dealers=[];
    $filter = Array
    (
        "ID" => implode(' | ', $user_ids),
    );

    $title_sop = false;
    if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
        if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
            $title_sop = 'Отдел продаж';
            $roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
            if(check_full_array($roles))
                $filter['UF_ROLE'] = array_keys($roles);
        }
        if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
            $title_sop = 'Отдел послепродажного обслуживания';
            $roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
            if(check_full_array($roles))
                $filter['UF_ROLE'] = array_keys($roles);
        }
    }

    $filter['ACTIVE'] = 'Y';
    $filter['!UF_DEALER'] = false;
    $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter, ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_MOBILE', 'WORK_POSITION'], 'SELECT' => [ 'UF_ROLE', 'UF_DEALER']]); // выбираем пользователей
    while($arUser = $rsUsers->Fetch()){
        $dealer_ids[] = $arUser['UF_DEALER'];
        if(check_full_array($arUser['UF_ROLE']))
            $role_ids = array_merge($role_ids, $arUser['UF_ROLE']);
        $users[$arUser['ID']] = $arUser;
    }
    $roless = \Models\Role::getList(['ID' => array_unique($role_ids)], ['ID', 'NAME', 'IBLOCK_SECTION_ID']);

    $dealers = Dealer::getList(['ID' => $dealer_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']);
    foreach($completions as &$completion){
        $completion['ID'] = $completion['UF_USER_ID'];
        $completion['NAME'] = $users[$completion['UF_USER_ID']]['NAME'];
        $completion['LAST_NAME'] = $users[$completion['UF_USER_ID']]['LAST_NAME'];
        $completion['EMAIL'] = $users[$completion['UF_USER_ID']]['EMAIL'];
        $completion['UF_ROLE'] = $users[$completion['UF_USER_ID']]['UF_ROLE'];
        $completion['PERSONAL_MOBILE'] = $users[$completion['UF_USER_ID']]['PERSONAL_MOBILE'];
        $completion['WORK_POSITION'] = $users[$completion['UF_USER_ID']]['WORK_POSITION'];
        $completion['DEALER'] = $dealers[$users[$completion['UF_USER_ID']]['UF_DEALER']];
        $completion['COURSE']['INFO'] = $courses[$completion['UF_COURSE_ID']];
        $user_array[] = $completion;
    }
} else {*/
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
    $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $user_filter, ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_MOBILE', 'WORK_POSITION'], 'SELECT' => [ 'UF_ROLE', 'UF_DEALER']]); // выбираем пользователей
    while($arUser = $rsUsers->Fetch()){
        $dealer_ids[] = $arUser['UF_DEALER'];
        if($arUser['UF_ROLE'])
            $role_ids = array_merge($role_ids, $arUser['UF_ROLE']);
        $users[$arUser['ID']] = $arUser;
    }
    $roless = \Models\Role::getList(['ID' => array_unique($role_ids)], ['ID', 'NAME', 'IBLOCK_SECTION_ID']);

    $dealers = Dealer::getList(['ID' => $dealer_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']);
    $filter_completions['UF_USER_ID'] = array_keys($users);
    if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
        if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
            $filter_completions['UF_COURSE_ID'] = \Models\Course::getOPList(true);

        }
        if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
            $filter_completions['UF_COURSE_ID'] = \Models\Course::getPPOList(true);
        }
    }
    if(check_full_array($_REQUEST['role'])){
        $filter_completions['UF_COURSE_ID'] = \Models\Course::getMustByRole($_REQUEST['role'], true);
    }
    if(check_full_array($_REQUEST['courses'])){
        $filter_completions['UF_COURSE_ID'] = $_REQUEST['courses'];
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
    if(check_full_array($_REQUEST['courses'])){
        $filter_completions['UF_COURSE_ID'] = $_REQUEST['courses'];
    }
    $completions = (new \Teaching\CourseCompletion())->get($filter_completions);
    $course_ids = [];
    if(check_full_array($filter_completions['UF_COURSE_ID'])){
        $course_ids = $filter_completions['UF_COURSE_ID'];
    } else {
        foreach ($completions as $one_completion)
            $course_ids[] = $one_completion['UF_COURSE_ID'];
    }
    $courses = \Models\Course::getList(['ID' => $course_ids], ['ID', 'NAME', 'CODE', 'PROPERTY_SCORM', 'PROPERTY_COURSE_TYPE']);
    foreach ($courses as $kk => &$c_temp){
        if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']!=5){
            if($c_temp['PROPERTY_COURSE_TYPE_ENUM_ID']==125){
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
        $completion['ID'] = $completion['UF_USER_ID'];
        $completion['NAME'] = $users[$completion['UF_USER_ID']]['NAME'];
        $completion['LAST_NAME'] = $users[$completion['UF_USER_ID']]['LAST_NAME'];
        $completion['EMAIL'] = $users[$completion['UF_USER_ID']]['EMAIL'];
        $completion['UF_ROLE'] = $users[$completion['UF_USER_ID']]['UF_ROLE'];
        $completion['PERSONAL_MOBILE'] = $users[$completion['UF_USER_ID']]['PERSONAL_MOBILE'];
        $completion['WORK_POSITION'] = $users[$completion['UF_USER_ID']]['WORK_POSITION'];
        $completion['DEALER'] = $dealers[$users[$completion['UF_USER_ID']]['UF_DEALER']];
        $completion['COURSE']['INFO'] = $courses[$completion['UF_COURSE_ID']];
        $user_array[$completion['UF_USER_ID']."_".$completion['UF_COURSE_ID']] = $completion;
    }
    if($_REQUEST['status_complete']=='on'||$_REQUEST['status_not_complete']=='on') {
        if($_REQUEST['status_not_complete']=='on') {
            foreach ($users as $user) {
                foreach ($courses as $course) {
                    if (!check_full_array($all_user_array[$user['ID'] . "_" . $course['ID']])) {
                        $completion['ID'] = $user['ID'];
                        $completion['NAME'] = $user['NAME'];
                        $completion['LAST_NAME'] = $user['LAST_NAME'];
                        $completion['EMAIL'] = $user['EMAIL'];
                        $completion['UF_ROLE'] = $user['UF_ROLE'];
                        $completion['UF_USER_ID'] = $user['ID'];
                        $completion['UF_COURSE_ID'] = $course['ID'];
                        $completion['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
                        $completion['WORK_POSITION'] = $user['WORK_POSITION'];
                        $completion['DEALER'] = $dealers[$user['UF_DEALER']];
                        $completion['COURSE']['INFO'] = $courses[$course['ID']];
                        $completion['NOT_ENROLLED'] = true;
                        $completion['UF_IS_COMPLETE'] = false;
                        $user_array[$user['ID'] . "_" . $course['ID']] = $completion;
                    }
                }
            }
        }
    } else {
        foreach ($users as $user) {
            foreach ($courses as $course) {
                if (!check_full_array($all_user_array[$user['ID'] . "_" . $course['ID']])) {
                    $completion['ID'] = $user['ID'];
                    $completion['NAME'] = $user['NAME'];
                    $completion['LAST_NAME'] = $user['LAST_NAME'];
                    $completion['EMAIL'] = $user['EMAIL'];
                    $completion['UF_ROLE'] = $user['UF_ROLE'];
                    $completion['UF_USER_ID'] = $user['ID'];
                    $completion['UF_COURSE_ID'] = $course['ID'];
                    $completion['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
                    $completion['WORK_POSITION'] = $user['WORK_POSITION'];
                    $completion['DEALER'] = $dealers[$user['UF_DEALER']];
                    $completion['COURSE']['INFO'] = $courses[$course['ID']];
                    $completion['NOT_ENROLLED'] = true;
                    $completion['UF_IS_COMPLETE'] = false;
                    $user_array[$user['ID'] . "_" . $course['ID']] = $completion;
                }
            }
        }
    }
    $new_array = [];
    $table_courses = [];
    foreach ($user_array as $array){
        $new_array[$array['UF_USER_ID']][$array['UF_COURSE_ID']] = $array;
        if ($array['COURSE']['INFO']['ID']) {
            $table_courses[$array['COURSE']['INFO']['ID']] = $array['COURSE']['INFO'];
        }
    }

?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="report8_1.php" class="btn">К генератору</a>
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
                            <th>Роль</th>
                            <?php foreach ($table_courses as $table_course){?>
                                <th><?=$table_course['NAME']?></th>
                                <th>Дата</th>
                                <th>Баллы</th>
                            <?php }?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($new_array as $item){
                            $first_item = current($item);
                            //dump($item);
                            ?>
                                <tr>
                                    <td><?=$first_item['DEALER']['CODE']?></td>
                                    <td><?=$first_item['DEALER']['NAME']?></td>
                                    <td><?=Dealer::getRegionalOP($first_item['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalPPO($first_item['DEALER']['ID'])?></td>
                                    <td><?=$first_item['NAME']?> <?=$first_item['LAST_NAME']?></td>
                                    <td><?php foreach ($first_item['UF_ROLE'] as $r){?><?=$roless[$r]['NAME']?><br /><?php }?></td>
                                    <?php foreach ($table_courses as $table_course){
                                        if($item[$table_course['ID']]['NOT_ENROLLED']!=1){
                                            if($item[$table_course['ID']]['UF_IS_COMPLETE']==1){?>
                                                <td>Пройден</td>
                                                <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                                <td><?=$item[$table_course['ID']]['UF_POINTS']?><?=$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']>0?"/".$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']:""?></td>
                                            <?php } else {?>
                                                <td>Не пройден</td>
                                                <td><?=$item[$table_course['ID']]['UF_DATE']?></td>
                                                <td><?=(int)$item[$table_course['ID']]['UF_POINTS']?><?=$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']>0?"/".$item[$table_course['ID']]['COURSE']['INFO']['MAX_POINTS']:""?></td>
                                            <?php }
                                        } else {?>
                                            <td>Не записывался</td>
                                            <td></td>
                                            <td></td>
                                        <?php }?>

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