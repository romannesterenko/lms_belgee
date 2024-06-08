<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
$dealers = \Models\Dealer::getAll(['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', "PROPERTY_CITY"]);
$regionals_ppo = [];
$regionals_op = [];
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
}
$roles = \Teaching\Roles::getOPRoles();
if($_REQUEST['op_servis_servis']=='on'){
    $roles = \Teaching\Roles::getPPORoles();
}
$courses = \Teaching\Roles::getRequiredCoursesForRolesArray(array_keys($roles));
$course_ids = [];
foreach ($courses as $rf){
    $course_ids = array_merge($course_ids, $rf);
}
$data = [];
$params['filter'] = ['!UF_DEALER' => false, "UF_ROLE" => array_keys($roles)];
$params['select'] = ['ID', 'LAST_NAME', 'NAME', 'UF_CERT_USER', "UF_DEALER", "UF_ROLE"];
$users = \Models\User::getArray($params);
$completed = (new \Teaching\CourseCompletion())->get(['UF_IS_COMPLETE' => 1, 'UF_COURSE_ID' => $course_ids]);
foreach ($dealers as $dealer_){
    if($dealer_["ID"]==360)
        continue;
    foreach ($users as $key => $user){
        if($user['UF_DEALER']!=$dealer_['ID'])
            continue;
        $row = [];
        $row['DEALER'] = $dealer_;
        $row['NAME'] = $user['LAST_NAME']." ".$user['NAME'];
        $row['USER'] = $user;
        $row['USER']['MUST_COURSES'] = [];
        $row['USER']['C_MUST_COURSES'] = [];
        $row['USER']['COMPLETED_MUST_COURSES'] = 0;
        $rs = [];
        foreach ($user['UF_ROLE'] as $r_id){
            if(check_full_array($courses[$r_id])) {
                $row['USER']['MUST_COURSES'] = array_unique(array_merge($row['USER']['MUST_COURSES'], $courses[$r_id]));
            }
            $rs[] = $roles[$r_id];
        }
        foreach ($completed as $key_k_i => $completed_item){
            if($completed_item['UF_USER_ID']!=$user['ID'])
                continue;
            if(!in_array($completed_item['UF_COURSE_ID'], $row['USER']['MUST_COURSES']))
                continue;
            if(in_array($completed_item['UF_COURSE_ID'], $row['USER']['C_MUST_COURSES'])) {
                unset($completed[$key_k_i]);
                continue;
            }

            $row['USER']['C_MUST_COURSES'][] = $completed_item['UF_COURSE_ID'];
            $row['USER']['COMPLETED_MUST_COURSES']++;
            unset($completed[$key_k_i]);
        }
        $row['USER']['DIFF'] = count($row['USER']['MUST_COURSES'])-$row['USER']['COMPLETED_MUST_COURSES'];
        $row['PERCENT'] = count($row['USER']['MUST_COURSES'])>0?round($row['USER']['COMPLETED_MUST_COURSES']/count($row['USER']['MUST_COURSES'])*100):0;
        $row['ROLES'] = implode('<br />', $rs);
        $data[] = $row;
        unset($users[$key]);
    }
}
usort($data, function($a, $b){
    return ($b['PERCENT'] - $a['PERCENT']);
});
//$users = \Models\User::getArray($params);
$_REQUEST['report_id'] = 1; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Рейтинг обученности по ролям</h2>
                <div class="text-content text-content--long">
                    <div class="form-div">
                        <form class="report_generator" action="" method="post" style="display: flex; justify-content: space-between">
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="">ОП / Сервис</label>
                                    <div class="form-group" style="display: flex">
                                        <div class="checkbox-item" style="padding-right: 20px">
                                            <input type="checkbox" id="op_servis_op" name="op_servis_op" <?=$_REQUEST['op_servis_op']=='on'||(empty($_REQUEST['op_servis_op'])&&empty($_REQUEST['op_servis_servis']))?' checked':''?> style="display: none">
                                            <label for="op_servis_op" style="padding-left: 30px;">ОП</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="op_servis_servis" name="op_servis_servis" <?=$_REQUEST['op_servis_servis']=='on'?' checked':''?> style="display: none">
                                            <label for="op_servis_servis" style="padding-left: 30px;">Сервис</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="display: flex; padding-top: 1rem;">
                                <div class="btn-center">
                                    <button class="btn" type="submit">Генерировать</button>
                                </div>

                            </div>
                        </form>
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn" id="gen"><span>Excel</span></button>
                            </div>
                        </div>
                    </div>

                    <div class="table-block">
                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                            <tr>
                                <th style="vertical-align: middle" class="text-center"></th>
                                <th style="vertical-align: middle" class="text-center">Код дилера</th>
                                <th style="vertical-align: middle" class="text-center">Название ДЦ</th>
                                <th style="vertical-align: middle" class="text-center">Город</th>
                                <th style="vertical-align: middle" class="text-center">Региональный менеджер</th>
                                <th style="vertical-align: middle" class="text-center">Региональный менеджер ППО</th>
                                <th style="vertical-align: middle" class="text-center">ФИО</th>
                                <th style="vertical-align: middle" class="text-center">Роль</th>
                                <th style="vertical-align: middle" class="text-center">Аттестован</th>
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
                                    <td><?=$item['DEALER']['REGIONAL_OP']?></td>
                                    <td><?=$item['DEALER']['REGIONAL_PPO']?></td>
                                    <td><?=$item['NAME']?></td>
                                    <td><?=$item['ROLES']?></td>
                                    <td><?=$item['USER']['UF_CERT_USER']==1?'Да':'Нет'?></td>
                                    <td><?=count($item['USER']['MUST_COURSES'])?></td>
                                    <td><?=$item['USER']['COMPLETED_MUST_COURSES']?></td>
                                    <td><?=$item['USER']['DIFF']?></td>
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
        $(function (){

            $(document).on('change', '.checkbox-item input[type="checkbox"]', function (){
                if($(this).attr('name')=='op_servis_op'){
                    $('label[for="op_servis_servis"]').trigger('click');
                }
                if($(this).attr('name')=='op_servis_servis'){
                    $('label[for="op_servis_op"]').trigger('click');
                }
            });
        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>