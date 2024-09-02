<?php

use Bitrix\Main\Localization\Loc;
use Models\Dealer;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$completions = new \Teaching\CourseCompletion();
$enrollments = new \Teaching\Enrollments();
$roless = \Models\Role::getAll(['ID', 'NAME', 'IBLOCK_SECTION_ID']);

$user_array = \Settings\Reports::generateTest();
foreach ($user_array as $key_item => $temp_item){
    if($temp_item['NOT_ENROLLED']==1)
        continue;
    if($temp_item['UF_IS_COMPLETE']!=1){
        if($temp_item['COURSE']['INFO']['PROPERTY_COURSE_TYPE_ENUM_ID']==6){
            if(!check_full_array($temp_item['COURSE']['INFO']['PROPERTY_SCORM_VALUE'])&&$temp_item['COMPLETION_ID']>0){
                $test = current((new \Teaching\ProcessTest())->get(['UF_COMPLETION' => $temp_item['COMPLETION_ID']], ['UF_POINTS'])->getArray());
                if(check_full_array($test))
                    $user_array[$key_item]['UF_POINTS'] = (int)$test['UF_POINTS'];
            }
        }
    }
}?>
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
                                <th>Город</th>
                                <?php if($_REQUEST['direction'] == 'all'){?>
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
                                <th>Направление</th>
                                <th>Роль</th>
                                <th>ФИО</th>
                                <th>Уровень</th>
                                <?php if($_REQUEST['show_email']=='Y') {?>
                                    <th>Email</th>
                                <?php }?>
                                <th>Телефон</th>
                                <th>ID</th>
                                <th>Должность</th>
                                <th>Курс</th>
                                <th>Статус</th>
                                <th>Дата прохождения курса</th>
                                <?php /*<th>Баллы</th>*/?>
                                <th>Записан на курс</th>
                                <th>Вопрос</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($user_array as $item){
                            $enrolled = false;?>
                                <tr>
                                    <td><?=$item['DEALER']['CODE']?></td>
                                    <td><?=$item['DEALER']['NAME']?></td>
                                    <td><?=$item['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                    <?php
                                    if ($_REQUEST['direction']=='all') {?>
                                        <td><?=Dealer::getRegionalOP($item['DEALER']['ID'])?></td>
                                        <td><?=Dealer::getRegionalPPO($item['DEALER']['ID'])?></td>
                                        <td><?=Dealer::getRegionalMarketing($item['DEALER']['ID'])?></td>
                                    <?php } else {
                                        switch ($_REQUEST['direction']) {
                                            case "S01":
                                                echo "<td>".Dealer::getRegionalOP($item['DEALER']['ID'])."</td>";
                                                break;
                                            case "A01":
                                                echo "<td>".Dealer::getRegionalPPO($item['DEALER']['ID'])."</td>";
                                                break;
                                            case "M01":
                                                echo "<td>".Dealer::getRegionalMarketing($item['DEALER']['ID'])."</td>";
                                                break;
                                        }
                                    }?>
                                    <td>
                                        <?php switch ($_REQUEST['direction']) {
                                                case "S01":
                                                    echo "Отдел продаж";
                                                    break;
                                                case "A01":
                                                    echo "Послепродажное обслуживание";
                                                    break;
                                                case "M01":
                                                    echo "Маркетинг";
                                                    break;
                                                case 'all':
                                                    echo \Models\Course::getDirrection($item['COURSE']['INFO']['ID']);

                                            }
                                        ?>
                                    </td>

                                    <td><?php foreach ($item['UF_ROLE'] as $r){?><?=$roless[$r]['NAME']?><br /><?php }?></td>
                                    <td><?=$item['NAME']?> <?=$item['LAST_NAME']?></td>
                                    <td><?=$item['UF_USER_RATING']?></td>
                                    <?php if($_REQUEST['show_email']=='Y') {?>
                                        <td><?=$item['EMAIL']?></td>
                                    <?php }?>
                                    <td><?=$item['PERSONAL_MOBILE']?></td>
                                    <td><?=$item['ID']?></td>
                                    <td><?=$item['WORK_POSITION']?></td>
                                    <td><?=$item['COURSE']['INFO']['NAME']?></td>
                                    <td>
                                        <?php if($item['UF_IS_COMPLETE']==1){
                                            echo 'Пройден';
                                        } else{
                                            if ($item['NOT_ENROLLED']){
                                                echo "Не записывался";
                                            } else {
                                                if(!empty($item['UF_DATE'])&&time()<$item['UF_DATE']->getTimestamp()){
                                                    $enrolled = true;
                                                    echo "Записан";
                                                } else {
                                                    echo "Не пройден";
                                                }
                                            }
                                        }?>
                                    </td>
                                    <?php if($item['NOT_ENROLLED']!=1&&!$enrolled){?>
                                        <td><?=$item['UF_IS_COMPLETE']==1?($item['UF_COMPLETED_TIME']?\Helpers\DateHelper::getHumanDate($item['UF_COMPLETED_TIME']):\Helpers\DateHelper::getHumanDate($item['UF_DATE'])):\Helpers\DateHelper::getHumanDate($item['UF_DATE'])?></td>
                                        <?php /*if($item['UF_IS_COMPLETE']==1){?>
                                            <td><?=$item['UF_POINTS']?><?=(int)$item['COURSE']['INFO']['MAX_POINTS']>0?"/".(int)$item['COURSE']['INFO']['MAX_POINTS']:""?></td>
                                        <?php } else {?>
                                            <td><?=(int)$item['UF_POINTS']?><?=(int)$item['COURSE']['INFO']['MAX_POINTS']>0?"/".(int)$item['COURSE']['INFO']['MAX_POINTS']:""?></td>
                                        <?php }*/?>
                                    <?php } else {?>
                                        <td>-</td>
                                        <?php /*<td>-</td>*/?>
                                    <?php }?>
                                    <td>
                                        <?php if($enrolled) {
                                            echo \Helpers\DateHelper::getHumanDate($item['UF_DATE']);
                                        }?>
                                    </td>
                                    <td>
                                        <?php if(!empty($item['COURSE']['COMPLETIONS']['ID'])&&!empty($item['COURSE']['COMPLETIONS']['UF_REGISTER_ANSWER'])){
                                            $ans_arr = json_decode($item['COURSE']['COMPLETIONS']['UF_REGISTER_ANSWER'], true);
                                            if(!empty($ans_arr['question'])&&!empty($ans_arr['answer'])){
                                                echo $ans_arr['question'].': '.$ans_arr['answer'];
                                            }
                                            ?>
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