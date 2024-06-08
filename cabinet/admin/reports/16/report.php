<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

$dealers_filter = ['ACTIVE' => 'Y'];
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '16', 'request' => $_REQUEST]);
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
$regionals_marketing=[];
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
if(check_full_array($_REQUEST['role'])){
    $roles = \Teaching\Roles::getRolesList(['ID' => $_REQUEST['role'], 'ACTIVE' => 'Y']);
} else {
    $roles = [];
    switch ($_REQUEST['direction']) {
        case 'A01':
            $roles = \Teaching\Roles::getPPORoles();
            break;
        case 'M01':
            $roles = \Teaching\Roles::getMarketingRoles();
            break;
        case 'S01':
            $roles = \Teaching\Roles::getOPRoles();
            break;
        case 'all':
            $roles = \Teaching\Roles::getAll();
    }
}
$courses = \Teaching\Roles::getRequiredCoursesForRolesArray(array_keys($roles));

$course_ids = [];
foreach ($courses as $rf){
    $course_ids = array_merge($course_ids, $rf);
}

$data = [];

//$completed = (new \Teaching\CourseCompletion())->get(['UF_IS_COMPLETE' => 1, 'UF_COURSE_ID' => array_values(array_unique($course_ids))]);
foreach ($dealers as $dealer_) {
    if($dealer_["ID"]==360)
        continue;
    $params['filter'] = ['ACTIVE' => 'ALL', '=UF_DEALER' => $dealer_['ID'], "!UF_ROLE" => false];
    $params['select'] = ['ID', 'UF_CERT_USER', "UF_DEALER", "UF_ROLE"];
    $users = \Models\User::getArray($params);

    $data[$dealer_['ID']]['DEALER'] = $dealer_;
    $data[$dealer_['ID']]['USERS'] = [];
    $data[$dealer_['ID']]['ALL_COURSES_'] = [];
    $user_ids = [];
    $UC = [];
    foreach ($users as $key => $user){
        $UC[$user['ID']]['COURSES'] = [];
        foreach ($user['UF_ROLE'] as $r_id){
            if(check_full_array($courses[$r_id])) {
                $UC[$user['ID']]['COURSES'] = array_merge($UC[$user['ID']]['COURSES'], $courses[$r_id]);
                $data[$dealer_['ID']]['ALL_COURSES_'] = array_merge($data[$dealer_['ID']]['ALL_COURSES_'], $courses[$r_id]);
            }
        }
        if(count(array_intersect($user['UF_ROLE'], array_keys($roles)))>0){
            $data[$dealer_['ID']]['USERS'][] = $user;
            $user_ids[] = $user['ID'];
            unset($users[$key]);
        }
    }
    $n_u = [];
    foreach ($users as $user__){
        $n_u[$user__['ID']] = $user__;
    }
    /*if($dealer_['CODE']=='501504265') {
        dump(array_values(array_unique($course_ids)));
        dump(array_values(array_unique($data[$dealer_['ID']]['ALL_COURSES_'])));
        dump(array_diff(array_values(array_unique($course_ids)), array_values(array_unique($data[$dealer_['ID']]['ALL_COURSES_']))));
    }*/
    //dump($data[$dealer_['ID']]['ALL_COURSES_']);
    $data[$dealer_['ID']]['ALL_COURSES'] = count($data[$dealer_['ID']]['ALL_COURSES_']);
    $completed = (new \Teaching\CourseCompletion())->get(['UF_IS_COMPLETE' => 1, 'UF_USER_ID' => $user_ids, "UF_COURSE_ID" => array_values(array_unique($data[$dealer_['ID']]['ALL_COURSES_']))]);
    $new_completions = [];
    $completions_by_users = [];
    foreach ($completed as $one) {
        $completions_by_users[$one['UF_USER_ID']][] = $one;
    }
    foreach ($completions_by_users as $uid => $courses_){
        foreach ($courses_ as $course__){
            if(in_array($course__['UF_COURSE_ID'], $UC[$course__['UF_USER_ID']]['COURSES']))
                $new_completions[] = $course__;
        }
    }
    $data[$dealer_['ID']]['COMPLETED'] = count($new_completions);
    $data[$dealer_['ID']]['DIFFERENT'] = $data[$dealer_['ID']]['ALL_COURSES'] - $data[$dealer_['ID']]['COMPLETED'];
    $data[$dealer_['ID']]['PERCENT'] = $data[$dealer_['ID']]['ALL_COURSES']>0?round($data[$dealer_['ID']]['COMPLETED']/$data[$dealer_['ID']]['ALL_COURSES']*100):0;
}
usort($data, function($a, $b){
    return ($b['PERCENT'] - $a['PERCENT']);
});
//$users = \Models\User::getArray($params);
$_REQUEST['report_id'] = 1; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Рейтинг дилеров по обученности</h2>
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
                                <th style="vertical-align: middle" class="text-center">Всего обязательных курсов</th>
                                <th style="vertical-align: middle" class="text-center">Пройдено обязательных курсов</th>
                                <th style="vertical-align: middle" class="text-center">Осталось пройти</th>
                                <th style="vertical-align: middle" class="text-center">% прохождения</th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($data as $key => $item){?>
                                <tr>
                                    <td><?=($key+1)?></td>
                                    <td><?=$item['DEALER']['CODE']?></td>
                                    <td><?=$item['DEALER']['NAME']?></td>
                                    <td><?=$item['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                    <?php if($_REQUEST['direction'] == 'all'){?>
                                        <td><?=$item['DEALER']['REGIONAL_OP']?></td>
                                        <td><?=$item['DEALER']['REGIONAL_PPO']?></td>
                                        <td><?=$item['DEALER']['REGIONAL_MARKETING']?></td>
                                    <?php } else {
                                        switch ($_REQUEST['direction']){
                                            case 'S01';
                                                echo '<td>'.$item['DEALER']['REGIONAL_OP'].'</td>';
                                                break;
                                            case 'A01';
                                                echo '<td>'.$item['DEALER']['REGIONAL_PPO'].'</td>';
                                                break;
                                            case 'M01';
                                                echo '<td>'.$item['DEALER']['REGIONAL_MARKETING'].'</td>';
                                                break;
                                        }
                                    }?>
                                    <td><?=count($item['USERS'])?></td>
                                    <td><?=$item['ALL_COURSES']?></td>
                                    <td><?=$item['COMPLETED']?></td>
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