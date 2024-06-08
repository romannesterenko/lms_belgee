<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Helpers\PageHelper;

if((int)$_REQUEST['id']<=0) {
    Helpers\PageHelper::set404("Расписание не найдено");
    die();
}
$schedule = \Teaching\SheduleCourses::getById((int)$_REQUEST['id']);
if(!check_full_array($schedule)){
    Helpers\PageHelper::set404("Курс не найден");
    die();
}else{
    $schedule = current($schedule);
}
$completions = new \Teaching\CourseCompletion();
$ids = [];
$user_ids = [];
$users = \Models\User::getEmployeesByAdmin();
if(check_full_array($users)) {

    foreach ($users as $user) {
        $user_ids[] = $user['ID'];
    }
}
foreach($completions->getListByScheduleAndUser($user_ids, $schedule['ID']) as $completion){
    $ids[] = $completion['ID'];
}
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2">Прохождение курса "<?=$schedule['NAME']?>"</h2>
            <div class="content-block  content-block--margin">
                <div class="table-block">
                    <?php $completed_courses_filter['ID']=$ids;
                    $APPLICATION->IncludeComponent(
                        "bitrix:highloadblock.list",
                        'compls',
                        Array(
                            "BLOCK_ID" => "2",
                            //"CHECK_PERMISSIONS" => "Y",
                            "DETAIL_URL" => "detail.php?BLOCK_ID=#BLOCK_ID#&ROW_ID=#ID#",
                            "FILTER_NAME" => "completed_courses_filter",
                            "PAGEN_ID" => "page",
                            "ROWS_PER_PAGE" => "20"
                        )
                    );?>
                </div>
            </div>
        </div>
    </div>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>