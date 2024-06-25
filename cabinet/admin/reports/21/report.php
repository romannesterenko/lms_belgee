<?php

use Helpers\RequestHelper;
use Models\Course;
use Teaching\CourseCompletion;
use Teaching\Roles;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

global $USER, $APPLICATION;
$dealers_filter = ['ACTIVE' => 'Y'];
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '21', 'request' => $_REQUEST]);
if(check_full_array($_REQUEST['dealer_names'])){
    $dealers_filter['ID'] = $_REQUEST['dealer_names'];
} else {
    if (check_full_array($_REQUEST['regional_op'])) {
        $dealers_filter['PROPERTY_REGIONAL'] = $_REQUEST['regional_op'];
    }
    if (check_full_array($_REQUEST['regional_ppo'])) {
        $dealers_filter['PROPERTY_REGIONAL_PPO'] = $_REQUEST['regional_ppo'];
    }
    if (check_full_array($_REQUEST['regional_marketing'])) {
        $dealers_filter['PROPERTY_REGIONAL_MARKETING'] = $_REQUEST['regional_marketing'];
    }
    if (check_full_array($_REQUEST['city'])) {
        $dealers_filter['PROPERTY_CITY'] = $_REQUEST['city'];
    }
}
$dealers = \Models\Dealer::getList($dealers_filter, ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_MARKETING', "PROPERTY_CITY"]);
$regionals_ppo = [];
$regionals_op = [];
$regionals_marketing = [];
foreach ($dealers as &$dealer){
    if($dealer['PROPERTY_REGIONAL_PPO_VALUE']&&!check_full_array($regionals_ppo[$dealer['PROPERTY_REGIONAL_PPO_VALUE']])) {
        $regional_ppo_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_PPO_VALUE']];
        $regional_ppo_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_ppo = current(\Models\User::getArray($regional_ppo_params));
        if($regional_ppo['ID']){
            $regionals_ppo[$regional_ppo['ID']] = $dealer['REGIONAL_PPO'] = $regional_ppo['LAST_NAME']." ".$regional_ppo['NAME'];
        }
    }
    if($dealer['PROPERTY_REGIONAL_VALUE']&&!check_full_array($regionals_op[$dealer['PROPERTY_REGIONAL_VALUE']])) {
        $regional_op_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_VALUE']];
        $regional_op_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_op = current(\Models\User::getArray($regional_op_params));
        if($regional_op['ID']){
            $regionals_op[$regional_op['ID']] = $dealer['REGIONAL_OP'] = $regional_op['LAST_NAME']." ".$regional_op['NAME'];
        }
    }
    if($dealer['PROPERTY_REGIONAL_MARKETING_VALUE']&&!check_full_array($regionals_marketing[$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']])) {
        $regional_marketing_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_MARKETING_VALUE']];
        $regional_marketing_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_marketing = current(\Models\User::getArray($regional_marketing_params));
        if($regional_marketing['ID']){
            $regionals_marketing[$regional_marketing['ID']] = $dealer['REGIONAL_MARKETING'] = $regional_marketing['LAST_NAME']." ".$regional_marketing['NAME'];
        }
    }
}
if(check_full_array($_REQUEST['courses'])) {
    $courses = Course::getList(['ID' => $_REQUEST['courses']], ['ID', 'NAME']);
} else {
    switch ($_REQUEST['direction']) {
        case 'A01':
            $courses = Course::getPPOList();
            break;
        case 'M01':
            $courses = Course::getMarketingList();
            break;
        case 'S01':
            $courses = Course::getOPList();
            break;
        case 'all':
            $courses = Course::getAll(['ID', 'NAME']);
    }
}
if((int)$_REQUEST['course_format'] > 0) {
    $format_courses = Course::getList(['PROPERTY_COURSE_FORMAT' => (int)$_REQUEST['course_format']], ['ID']);
    foreach ($courses as $key => $response_course){
        if(!in_array($response_course['ID'], array_keys($format_courses)))
            unset($courses[$key]);
    }
}
if(check_full_array($_REQUEST['role'])){
    $role_courses = Roles::getRequiredCoursesForRolesArray($_REQUEST['role']);
    $role_course_ids = [];
    foreach ($role_courses as $courses_array) {
        $role_course_ids = array_merge($role_course_ids, $courses_array);
    }
    $array = array_diff(array_keys($courses), $role_course_ids);
    foreach ($array as $i){
        unset($courses[$i]);
    }
}
$all_role_ids = [];
foreach ($dealers as &$dealer_temp) {
    if($dealer_temp["ID"]==360)
        continue;
    $params['filter'] = ['ACTIVE' => 'Y', '=UF_DEALER' => $dealer_temp['ID'], "!UF_ROLE" => false];
    $params['select'] = ['ID', 'UF_CERT_USER', "UF_DEALER", "UF_ROLE", "UF_REQUIRED_COURSES"];
    $dealer_temp['USERS'] = \Models\User::getArray($params);
    $new_user_array = [];
    foreach ($dealer_temp['USERS'] as $user){
        if(check_full_array($_REQUEST['role'])){
            if(check_full_array(array_intersect($_REQUEST['role'], $user["UF_ROLE"]))){
                $new_user_array[$user['ID']] = $user;
            }
        } else {
            $new_user_array[$user['ID']] = $user;
            $all_role_ids = array_merge($all_role_ids, $user["UF_ROLE"]);
        }
    }
    $dealer_temp['USERS'] = $new_user_array;
}
unset($dealer_temp);
if(check_full_array($_REQUEST['role'])){
    $all_role_ids = $_REQUEST['role'];
} else {
    $all_role_ids = array_values(array_unique($all_role_ids));
}
$all_courses_roles = Roles::getRequiredCoursesForRolesArray($all_role_ids);
$data = [];
foreach ($dealers as $dealer_temp) {
    if($dealer_temp["ID"]==360)
        continue;
    $item = [
        "NAME" => $dealer_temp['NAME'],
        "CODE" => $dealer_temp['CODE'],
        "CITY" => $dealer_temp['PROPERTY_CITY_VALUE'],
        "REGIONAL_OP" => $regionals_op[$dealer_temp['PROPERTY_REGIONAL_VALUE']],
        "REGIONAL_PPO" => $regionals_ppo[$dealer_temp['PROPERTY_REGIONAL_PPO_VALUE']],
        "REGIONAL_MARKETING" => $regionals_marketing[$dealer_temp['PROPERTY_REGIONAL_MARKETING_VALUE']],
        "USERS_CNT" => count($dealer_temp['USERS']),
        "COURSES_CNT" => count($courses),
        "ALL_COURSES_ARRAY" => [],
        "ALL_COURSES" => 0,
        "COMPLETED_COURSES" => 0
    ];
    foreach ($dealer_temp['USERS'] as $dealer_user) {
        foreach ($dealer_user['UF_ROLE'] as $user_role) {
            if(check_full_array($all_courses_roles[$user_role])) {
                $need_courses = array_intersect($all_courses_roles[$user_role], array_keys($courses));
                $item["ALL_COURSES_ARRAY"] = array_merge($item["ALL_COURSES_ARRAY"], $need_courses);
                $item["ALL_COURSES"] += count($need_courses);
                $item["ALL_USERS_ARRAY"][] = $dealer_user['ID'];
            }
        }
    }
    $completed = (new CourseCompletion())->get(['UF_IS_COMPLETE' => 1, 'UF_USER_ID' => array_keys($dealer_temp['USERS']), "UF_COURSE_ID" => array_values(array_unique($item["ALL_COURSES_ARRAY"]))]);

    $arr = [];

    foreach ($completed as $one_completion) {
        $arr[$one_completion['UF_USER_ID']."_".$one_completion['UF_COURSE_ID']][] =  $one_completion;
    }

    foreach ($dealer_temp['USERS'] as $dealer_user){
        foreach ($dealer_user['UF_ROLE'] as $user_role) {
            if(check_full_array($all_courses_roles[$user_role])) {
                $need_courses = array_intersect($all_courses_roles[$user_role], array_keys($courses));
                foreach ($need_courses as $need_course) {
                    if (check_full_array($arr[$dealer_user['ID'] . "_" . $need_course])) {
                        $item["COMPLETED_COURSES"]++;
                    }
                }
            }
        }
    }
    $item['DIFFERENT'] = $item["ALL_COURSES"] - $item["COMPLETED_COURSES"];
    $item['PERCENT'] = $item["ALL_COURSES"]>0?round($item["COMPLETED_COURSES"]/$item["ALL_COURSES"]*100):0;
    $data[] = $item;
    unset($item);
}
usort($data, function($a, $b){
    return ($b['PERCENT'] - $a['PERCENT']);
});
$_REQUEST['report_id'] = 1; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Рейтинг обученности дилеров по курсам</h2>
                <div class="text-content text-content--long">
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">К генератору</a>
                            </div>
                            <div class="btn-center">
                                <button class="btn" id="gen"><span>Excel</span></button>
                            </div>
                        </div>
                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="vertical-align: middle" class="text-center"></th>
                                    <th style="vertical-align: middle" class="text-center">Код дилера</th>
                                    <th style="vertical-align: middle" class="text-center">Название ДЦ</th>
                                    <th style="vertical-align: middle" class="text-center">Город</th>
                                    <?php if($_REQUEST['direction'] == 'all'){?>
                                        <th style="vertical-align: middle" class="text-center">Регионал ОП</th>
                                        <th style="vertical-align: middle" class="text-center">Регионал ППО</th>
                                        <th style="vertical-align: middle" class="text-center">Регионал Маркетинг</th>
                                    <?php } else {
                                        switch ($_REQUEST['direction']){
                                            case 'S01';
                                                echo '<th style="vertical-align: middle" class="text-center">Регионал ОП</th>';
                                                break;
                                            case 'A01';
                                                echo '<th style="vertical-align: middle" class="text-center">Регионал ППО</th>';
                                                break;
                                            case 'M01';
                                                echo '<th style="vertical-align: middle" class="text-center">Регионал Маркетинг</th>';
                                                break;
                                        }
                                    }?>
                                    <th style="vertical-align: middle" class="text-center">Всего сотрудников ОП</th>
                                    <th style="vertical-align: middle" class="text-center">Всего выбрано курсов</th>
                                    <th style="vertical-align: middle" class="text-center">Пройдено</th>
                                    <th style="vertical-align: middle" class="text-center">Осталось пройти</th>
                                    <th style="vertical-align: middle" class="text-center">% прохождения</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data as $key => $item){?>
                                <tr>
                                    <td><?=($key+1)?></td>
                                    <td><?=$item['CODE']?></td>
                                    <td><?=$item['NAME']?></td>
                                    <td><?=$item['CITY']?></td>
                                    <?php if($_REQUEST['direction'] == 'all'){?>
                                        <td><?=$item['REGIONAL_OP']?></td>
                                        <td><?=$item['REGIONAL_PPO']?></td>
                                        <td><?=$item['REGIONAL_MARKETING']?></td>
                                    <?php } else {
                                        switch ($_REQUEST['direction']){
                                            case 'S01';
                                                echo '<td>'.$item['REGIONAL_OP'].'</td>';
                                                break;
                                            case 'A01';
                                                echo '<td>'.$item['REGIONAL_PPO'].'</td>';
                                                break;
                                            case 'M01';
                                                echo '<td>'.$item['REGIONAL_MARKETING'].'</td>';
                                                break;
                                        }
                                    }?>
                                    <td><?=$item['USERS_CNT']?></td>
                                    <td><?=$item['ALL_COURSES']?> </td>
                                    <td><?=$item['COMPLETED_COURSES']?></td>
                                    <td><?=$item['DIFFERENT']?></td>
                                    <td><?=$item['PERCENT']?>%</td>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
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