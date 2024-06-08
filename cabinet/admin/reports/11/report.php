<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Models\User;
use Teaching\CourseCompletion;
use Teaching\Courses;

global $USER;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '11', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = 9999;
$month = $_REQUEST['month']??date('m');
$year = $_REQUEST['year']??date('Y');
$rows = [];
$data = [];
$by_days = false;
$courses = \Models\Course::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_SCORM']);
if((int)$_REQUEST['course']>0){
    $current_course = $courses[(int)$_REQUEST['course']];
} else {
    $current_course = current($courses);
}
$test_course = false;
if(Courses::isFreeSheduleCourse($current_course['ID'])){
    $max_ball = 0;
    if(\Models\Course::isScormCourse($current_course['ID'])){
        $max_ball = 100;
    } else {
        $test_course = true;
        $test = current(\Teaching\Tests::getTestByCourse($current_course['ID']));
        if(check_full_array($test)) {
            $questions = \Teaching\Tests::getQuestionsByTest($test['ID']);
            if(check_full_array($questions)){
                foreach ($questions as $question){
                    $max_ball+=$question['PROPERTIES']['POINTS'];
                }
            }
        }
    }

}

//получим курсы доступные для роли
$filter = ['UF_COURSE_ID' => $current_course['ID']];

if(!empty($_REQUEST['date_to']))
    $filter["<=UF_DATE"] = date('d.m.Y', strtotime($_REQUEST['date_to']));

if(!empty($_REQUEST['date_from']))
    $filter[">=UF_DATE"] = date('d.m.Y', strtotime($_REQUEST['date_from']));

$role_ids = [];
switch ($_REQUEST['direction']) {
    case 'A01':
        $roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $role_ids = array_keys($roles);
        break;
    case 'S01':
        $roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $role_ids = array_keys($roles);
        break;
    case 'M01':
        $roles = \Models\Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $role_ids = array_keys($roles);
        break;
}
if(!check_full_array($_REQUEST['role'])&&check_full_array($role_ids)){
    $_REQUEST['role'] = $role_ids;
}
if(check_full_array($_REQUEST['role'])) {
    $user_ids = [];
    foreach ($_REQUEST['role'] as $role_id_){
        foreach (User::getByRole($role_id_) as $u) {
            $user_ids[] = $u['ID'];
        }
    }
    $filter['UF_USER_ID'] = $user_ids;
}

if(check_full_array($_REQUEST['dealer_names'])) {
    $user_ids = [];
    foreach ($_REQUEST['dealer_names'] as $dealer_id){
        $users = \Helpers\UserHelper::getListByDealer($dealer_id);
        if (check_full_array($users)){
            foreach ($users as $user){
                $user_ids[] = $user['ID'];
            }
        }
        unset($users);
    }
    if (check_full_array($user_ids)){
        if(check_full_array($filter['UF_USER_ID'])) {
            $filter['UF_USER_ID'] = array_values(array_intersect($filter['UF_USER_ID'], $user_ids));
        } else {
            $filter['UF_USER_ID'] = $user_ids;
        }
    }
}
if(check_full_array($_REQUEST['fio'])) {
    $filter['UF_USER_ID'] = $_REQUEST['fio'];
}
$list = (new CourseCompletion)->get($filter);

$data = [];
$roles = [];
foreach ($list as $item){
    if($test_course && (int)$item['UF_POINTS']==0){
        $test = current((new \Teaching\ProcessTest())->get(['UF_COMPLETION' => $item['ID']], ['UF_POINTS'])->getArray());
        if(check_full_array($test))
            $item['UF_POINTS'] = (int)$test['UF_POINTS'];
    }

    $item['USER'] = User::find($item['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE', 'WORK_POSITION', 'EMAIL', 'UF_DEALER']);
    if((int)$item['USER']['UF_DEALER']==0)
        continue;
    $item['USER']['UF_ROLE'] = check_full_array($item['USER']['UF_ROLE'])?$item['USER']['UF_ROLE']:[];
    $roles = array_merge($roles, $item['USER']['UF_ROLE']);
    $item['USER']['DEALER'] = \Models\Dealer::find((int)$item['USER']['UF_DEALER']);
    if($item['UF_PRETEST_PROCESS']==1){
        if((int)$item['UF_PRETEST_POINTS']==0) {
            $item['PROGRESS'] = "0%";
        } else {
            $item['PROGRESS'] = abs(round((((int)$item['UF_POINTS'] - (int)$item['UF_PRETEST_POINTS'])/(int)$item['UF_PRETEST_POINTS']*100), 0))."%";
        }
    } else {
        $item['PROGRESS'] = "-";
    }
    $data[] = $item;
}
$roles = \Models\Role::getArray(['ID' => array_unique($roles)]);
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
$years = range((int)date('Y')-5, (int)date('Y'));
?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase">Отчет по тестированию курса "<?=$current_course['NAME']?>"</h2>
            <div class="table-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                    <div class="btn-center">
                        <button class="btn" id="gen"><span>Excel</span></button>
                    </div>
                </div>
                <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                    <thead class="thead-dark">
                        <tr>
                            <th>Код ДЦ</th>
                            <th>Название ДЦ</th>
                            <th>ФИО</th>
                            <th>Роль</th>
                            <th>Дата</th>
                            <th>Баллы (входной)</th>
                            <th>Баллы (выход)</th>
                            <th>Прогресс</th>
                            <th>Прошел</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $key => $row){
                        $row_roles = [];
                        if(check_full_array($row['USER']['UF_ROLE'])){
                            foreach ($row['USER']['UF_ROLE'] as $r_id)
                                $row_roles[] = $roles[$r_id];
                        }
                        ?>
                        <tr style="height: 50px">
                            <td><?=$row['USER']['DEALER']['CODE']?></td>
                            <td><?=$row['USER']['DEALER']['NAME']?></td>

                            <td><?=$row['USER']['LAST_NAME']?> <?=$row['USER']['NAME']?></td>

                            <td><?=implode('<br />', $row_roles)?></td>
                            <td><?=Courses::isFreeSheduleCourse($row['UF_COURSE_ID']) && $row['UF_COMPLETED_TIME']?$row['UF_COMPLETED_TIME']:$row['UF_DATE']?></td>
                            <td><?=$row['UF_PRETEST_PROCESS']==1?(int)$row['UF_PRETEST_POINTS']:'-'?></td>

                            <?php if($max_ball>0&&$row['UF_POINTS']>$max_ball) {
                                $row['UF_POINTS'] = $max_ball;
                                (new CourseCompletion())->setPoints($max_ball, $row['ID']);
                            }?>
                            <td><?=$row['UF_POINTS']?></td>
                            <td><?=$row['PROGRESS']?></td>
                            <td><?=$row['UF_IS_COMPLETE']?'Да':'Нет'?></td>
                        </tr>
                    <?php
                    }?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>