<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Helpers\DateHelper;
use Helpers\DealerHelper;
use Models\Dealer;
use Models\User;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

global $USER;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '9', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = 9999;

?>

<?php
$rows = [];
$user_ids = [];
$course_ids = [];
$dealer_ids = [];
$role_ids = [];
$schedule_ids = [];
$users = [];
$courses = [];
$dealers = [];
$roles = [];
$schedules = [];
$completions = new CourseCompletion();
$enrollments = new Enrollments();
$filter = ['>ID'=>0];
if($_REQUEST['year']&&$_REQUEST['year']!=0){
    if($_REQUEST['month']&&$_REQUEST['month']!=0){
        if($_REQUEST['date']&&$_REQUEST['date']!=0){
            $filter['UF_DATE'] = str_pad($_REQUEST['date'], 2, '0', STR_PAD_LEFT).'.'.$_REQUEST['month'].'.'.$_REQUEST['year'];
            unset($filter['>=UF_DATE']);
            unset($filter['<=UF_DATE']);
        } else {
            $start = '01.' . $_REQUEST['month'] . '.' . $_REQUEST['year'];
            $end = '31.' . $_REQUEST['month'] . '.' . $_REQUEST['year'];
            $filter_qwe = [
                '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($start, "YYYY-MM-DD")." 00:00:01",
                '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($end, "YYYY-MM-DD")." 23:59:59",
            ];
            $list = SheduleCourses::getArray($filter_qwe);
            if (check_full_array($list)) {
                foreach ($list as $schedule) {
                    $filter['UF_SHEDULE_ID'][] = $schedule['ID'];
                }
            }
        }
    } else {
        $start = '01.01.' . $_REQUEST['year'];
        $end = '31.12.' . $_REQUEST['year'];
        $filter_qwe = [
            '>=PROPERTY_BEGIN_DATE' => ConvertDateTime($start, "YYYY-MM-DD")." 00:00:01",
            '<=PROPERTY_BEGIN_DATE' => ConvertDateTime($end, "YYYY-MM-DD")." 23:59:59",
        ];
        $list = SheduleCourses::getArray($filter_qwe);
        if (check_full_array($list)) {
            foreach ($list as $schedule) {
                $filter['UF_SHEDULE_ID'][] = $schedule['ID'];
            }
        }
    }

}
switch ($_REQUEST['direction']){
    case 'S01':
        $roles_ = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles_))
            $user_filter['UF_ROLE'] = array_keys($roles_);
        break;
    case 'A01':
        $roles_ = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles_))
            $user_filter['UF_ROLE'] = array_keys($roles_);
        break;
    case 'M01':
        $roles_ = \Models\Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles_))
            $user_filter['UF_ROLE'] = array_keys($roles_);
        break;
}
if(check_full_array($_REQUEST['role'])){
    $user_filter['UF_ROLE'] = $_REQUEST['role'];
}
if(check_full_array($user_filter)){
    $user_filter['ACTIVE'] = 'Y';
    $user_filter['!UF_DEALER'] = false;
}
$params['filter'] = $user_filter;
$params['select'] = ['ID'];
$user_list = User::getArray($params);
if(check_full_array($user_list)){
    foreach ($user_list as $user_list_item)
        $filter['UF_USER_ID'][] = $user_list_item['ID'];
}
if($_REQUEST['status']&&$_REQUEST['status']!='all') {
    if($_REQUEST['status']=='approved')
        $filter['UF_IS_APPROVED'] = 1;
    elseif($_REQUEST['status']=='not_approved')
        $filter['UF_IS_APPROVED'] = false;
}
if(check_full_array($_REQUEST['courses'])&&current($_REQUEST['courses'])>0) {
    $filter['UF_COURSE_ID'] = (int)current($_REQUEST['courses']);
}
$only_compls = false;
if($filter['UF_COURSE_ID']>0&& \Teaching\Courses::isFreeSheduleCourse($filter['UF_COURSE_ID'])){
    $items = $completions->get($filter);
    $only_compls = true;
} else {
    $items = $enrollments->get($filter);
}
foreach($items as $enroll){
    $user_ids[] = $enroll['UF_USER_ID'];
    $course_ids[] = $enroll['UF_COURSE_ID'];
    if($enroll['UF_SHEDULE_ID']>0)
        $schedule_ids[] = $enroll['UF_SHEDULE_ID'];
    $rows[] = $enroll;
}
if(count($user_ids)>0){
    foreach (User::getByIds(array_unique($user_ids), ['ID', 'NAME', 'EMAIL', 'LAST_NAME', 'PERSONAL_MOBILE', 'WORK_POSITION', 'UF_DEALER', 'UF_ROLE']) as $user){
        if(!$users[$user['ID']]['ID']>0) {
            $dealer_ids[] = $user['UF_DEALER'];
            if(check_full_array($user['UF_ROLE']))
                $role_ids = array_unique(array_merge($role_ids, $user['UF_ROLE']));
            $users[$user['ID']] = $user;
        }
    }
}
if(count($dealer_ids)>0){
    foreach (DealerHelper::getList(['ID'=>array_unique($dealer_ids)], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']) as $dealer){
        //dump($dealer);
        if(!$dealers[$dealer['ID']]['ID']>0) {
            $dealers[$dealer['ID']] = $dealer;
        }
    }
}
if(check_full_array($role_ids)){
    foreach (\Teaching\Roles::getRolesList(['ID'=>$role_ids], ['ID', 'NAME', 'CODE']) as $role){
        if(!$roles[$role['ID']]['ID']>0) {
            $roles[$role['ID']] = $role;
        }
    }
}
if(count($course_ids)>0){
    foreach (Courses::getList(['ID' => array_unique($course_ids)], ['ID', 'NAME', 'CODE']) as $course){
        if(!$courses[$course['ID']]['ID']>0) {
            $courses[$course['ID']] = $course;
        }
    }
}
if(count($schedule_ids)>0){
    foreach (SheduleCourses::getArray(['ID' => array_unique($schedule_ids)]) as $schedule){
        if(!$schedules[$schedule['ID']]['ID']>0) {
            $schedules[$schedule['ID']] = $schedule;
        }
    }
}

foreach ($rows as $key => &$row1) {
    $users[$row1['UF_USER_ID']]['DEALER'] = $dealers[$users[$row1['UF_USER_ID']]['UF_DEALER']];
    $row1['USER'] = $users[$row1['UF_USER_ID']];
    if(check_full_array($row1['USER']['UF_ROLE'])){
        $string = '';
        foreach ($row1['USER']['UF_ROLE'] as $f => $r){
            if($f!=0)
                $string.=', ';
            $string.=$roles[$r]['NAME'];
        }
        $row1['USER']['UF_ROLE'] = $string;
    }
    if($schedules[$row1['UF_SHEDULE_ID']]['ID']>0)
        $row1['SCHEDULE'] = $schedules[$row1['UF_SHEDULE_ID']];
    if($courses[$row1['UF_COURSE_ID']]['ID']>0)
        $row1['COURSE'] = $courses[$row1['UF_COURSE_ID']];
    else
        unset($rows[$key]);
}

$rows = array_values($rows);

$months = [
    "01" => "Январь",
    "02" => "Февраль",
    "03" => "Март",
    "04" => "Апрель",
    "05" => "Май",
    "06" => "Июнь",
    "07" => "Июль",
    "08" => "Август",
    "09" => "Сентябрь",
    "10" => "Октябрь",
    "11" => "Ноябрь",
    "12" => "Декабрь",
];
$years = range(2019, (int)date('Y')+1);
$selected_month = date('m');
if($_REQUEST['month'])
    $selected_month = $_REQUEST['month'];
if($_REQUEST['date'])
    $selected_date = $_REQUEST['date'];
$selected_year = date('Y');
if($_REQUEST['year'])
    $selected_year = $_REQUEST['year'];

?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase">Отчет по записям на курсы</h2>
            <div class="form-group" style="display: flex; padding-top: 1rem;">
                <div class="btn-center">
                    <a href="." class="btn">К генератору</a>
                </div>
                <div class="btn-center">
                    <button class="btn" id="gen"><span>Excel</span></button>
                </div>
            </div>
            <div class="table-block">
                <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                    <thead class="thead-dark">
                        <tr>
                            <th>Код дилера</th>
                            <th>Дилер</th>
                            <th>Город</th>
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
                            <th>Сотрудник</th>
                            <th>Роль</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Курс</th>
                            <th>Дата начала курса</th>
                            <th>Вопрос</th>
                            <th>Подтверждена</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $key => $row){
                        if($only_compls||$row['SCHEDULE']['PROPERTIES']['BEGIN_DATE']):?>
                            <tr style="height: 50px">
                                <td class="text-left"><?=$row['USER']['DEALER']['CODE']?></td>
                                <td class="text-left"><?=$row['USER']['DEALER']['NAME']?></td>
                                <td class="text-left"><?=$row['USER']['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                <?php
                                if ($_REQUEST['direction']=='all') {?>
                                    <td><?=Dealer::getRegionalOP($row['USER']['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalPPO($row['USER']['DEALER']['ID'])?></td>
                                    <td><?=Dealer::getRegionalMarketing($row['USER']['DEALER']['ID'])?></td>
                                <?php } else {
                                    switch ($_REQUEST['direction']) {
                                        case "S01":
                                            echo "<td>".Dealer::getRegionalOP($row['USER']['DEALER']['ID'])."</td>";
                                            break;
                                        case "A01":
                                            echo "<td>".Dealer::getRegionalPPO($row['USER']['DEALER']['ID'])."</td>";
                                            break;
                                        case "M01":
                                            echo "<td>".Dealer::getRegionalMarketing($row['USER']['DEALER']['ID'])."</td>";
                                            break;
                                    }
                                }?>
                                <td class="text-left"><?=$row['USER']['LAST_NAME']?> <?=$row['USER']['NAME']?></td>
                                <td class="text-left"><?=$row['USER']['UF_ROLE']?></td>
                                <td class="text-left"><?=$row['USER']['EMAIL']?></td>
                                <td class="text-left"><?=$row['USER']['PERSONAL_MOBILE']?></td>
                                <td class="text-left"><a href="/courses/<?=$row['COURSE']['CODE']?>/" target="_blank"><?=$row['COURSE']['NAME']?></a></td>
                                <td><?php if($row['SCHEDULE']['ID']>0){echo DateHelper::getHumanDate($row['SCHEDULE']['PROPERTIES']['BEGIN_DATE'], 'd.m.Y');} else { if($only_compls){echo DateHelper::getHumanDate($row['UF_DATE'], 'd.m.Y');}}?></td>
                                <td>
                                    <?php if(!empty($row['UF_REGISTER_ANSWER'])){
                                        $ans_arr = json_decode($row['UF_REGISTER_ANSWER'], true);
                                        if(!empty($ans_arr['question'])&&!empty($ans_arr['answer'])){
                                            echo $ans_arr['question'].': '.$ans_arr['answer'];
                                        }
                                        ?>
                                    <?php }?>
                                </td>
                                <td>
                                    <?php if($only_compls||$row['UF_IS_APPROVED']==1){?>
                                        <span class="status status--passed"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt=""></span>Подтверждена</span>
                                    <?php } else {?>
                                        <span class="status status--passed">Не подтверждена</span>
                                    <?php }?>
                                </td>
                            </tr>
                        <?php endif;?>
                    <?php }?>
                    </tbody>
                </table>

            </div>


        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>