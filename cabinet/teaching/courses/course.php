<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Helpers\PageHelper;
use Models\User;

if((int)$_REQUEST['id']<=0) {
    Helpers\PageHelper::set404("Курс не найден");
    die();
}
$course = \Models\Course::find((int)$_REQUEST['id'], ['NAME']);
if(!check_full_array($course)){
    Helpers\PageHelper::set404("Курс не найден");
    die();
}
$completions = new \Teaching\CourseCompletion();
$enrolls = new \Teaching\Enrollments();
$ids = [];
$user_ids = [];
$users = \Models\User::getEmployeesByAdmin();
if(check_full_array($users)) {
    foreach ($users as $user) {
        $user_ids[] = $user['ID'];
    }
}
$allready_ids = [];
$items = [];
$req_ids = array_unique(array_merge(
    User::getEmployeesByRoleToCourse($course['ID'], true),
    User::getRecommendEmployeesByRoleToCourse($course['ID'], true),
    User::getEmployeesByCourse($course['ID'], true),
));
foreach($completions->getListByCourseAndUser($user_ids, $course['ID']) as $completion){
    $allready_ids[] = $completion['UF_USER_ID'];
    $items[] = [
        'user_id' => $completion['UF_USER_ID'],
        'sent_request' => true,
        'approved_request' => true,
        'date' => $completion['UF_DATE'],
        'completed' => $completion['UF_IS_COMPLETE']==1,
        'points' => (int)$completion['UF_POINTS'],
        'max_points' => \Teaching\Tests::getMaxPointsByCourse($completion['UF_COURSE_ID']),
        'didnt_com' => $completion['UF_DIDNT_COM'],
        'completion_fields' => $completion,
    ];
}
foreach ($enrolls->getListByCourseAndUser($user_ids, $course['ID']) as $enrollment){
    $allready_ids[] = $enrollment['UF_USER_ID'];
    $items[] = [
        'user_id' => $enrollment['UF_USER_ID'],
        'sent_request' => true,
        'approved_request' => $enrollment['UF_IS_APPROVED']==1,
        'completed' => false
    ];
}
$lastIds = [];
foreach ($req_ids as $id_){
    if(in_array($id_,$user_ids)&&!in_array($id_, $allready_ids)){
        $lastIds[] = $id_;
    }
}
foreach ($lastIds as $lastId) {
    $items[] = [
        'user_id' => $lastId,
        'sent_request' => false,
        'approved_request' => false,
        'completed' => false
    ];
}
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2">Прохождение курса "<?=$course['NAME']?>"</h2>
            <div class="content-block  content-block--margin">
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left">№</th>
                                <th class="text-left">ФИО</th>
                                <th class="text-left">Заявка отправлена</th>
                                <th class="text-left">Записан на курс</th>
                                <th class="text-left">Пройден</th>
                                <th class="text-left">Баллов</th>
                                <th class="text-left">Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $key => $item){
                                $completed = "Да";
                                if (!$item['completed']) {
                                    $completed = "Нет";
                                    if($item['didnt_com'])
                                        $completed = "Неявка";
                                    if($item['completion_fields']["UF_WAS_ON_COURSE"] && $item['completion_fields']["UF_FAILED"])
                                        $completed = "Нет";
                                    if (!$item['approved_request'])
                                        $completed = "-";
                                }
                                ?>
                                <tr>
                                    <td class="text-left"><?=($key+1)?></td>
                                    <td class="text-left"><?=\Models\User::getFullName($item['user_id'])?></td>
                                    <td class="text-left"><?=$item['sent_request']?'Да':'Нет'?></td>
                                    <td class="text-left"><?=$item['approved_request']?'Да':'Нет'?></td>
                                    <td class="text-left"><?=$completed?></td>
                                    <td class="text-left"><?=$completed=="-"?"-":$item['points']."/".$item['max_points']?></td>
                                    <td class="text-left"><?=$item['date']?></td>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>